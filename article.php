<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/classes/Article.php';
require_once 'backend/classes/Category.php';

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$article_id = (int)$_GET['id'];

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$article = new Article($db);
$category = new Category($db);

// Get article details
$article_data = $article->getById($article_id);

if (!$article_data) {
    header('Location: index.php');
    exit;
}

// Increment view count
$article->incrementViews($article_id);

// Get related articles from same category
$related_articles = [];
if ($article_data['category_id']) {
    $related_articles = $article->getByCategory($article_data['category_id'], 1, 4);
    // Remove current article from related articles
    $related_articles = array_filter($related_articles, function($item) use ($article_id) {
        return $item['article_id'] != $article_id;
    });
    $related_articles = array_slice($related_articles, 0, 3);
}

// Get all categories for navigation
$categories = $category->getAll();

// Helper function to format date
function formatDate($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article_data['title']); ?> - Global News Network</title>
    <meta name="description" content="<?php echo htmlspecialchars($article_data['summary'] ?: substr(strip_tags($article_data['content']), 0, 160)); ?>">
    <link rel="icon" type="image/x-icon" href="assets/Icon.ico">
    <link rel="stylesheet" href="frontend/css/styles.css">
    <link rel="stylesheet" href="frontend/css/responsive.css">
    <style>
        .article-header {
            margin-bottom: var(--spacing-xl);
        }
        
        .article-main-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .article-content-text {
            font-size: var(--text-lg);
            line-height: 1.8;
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
        }
        
        .article-content-text p {
            margin-bottom: var(--spacing-lg);
        }
        
        .article-info {
            background-color: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .article-meta-full {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-lg);
            align-items: center;
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--border-light);
        }
        
        .related-articles {
            margin-top: var(--spacing-xl);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: var(--medium-blue);
        }
        
        @media (max-width: 768px) {
            .article-content-text {
                font-size: var(--text-base);
            }
            
            .article-meta-full {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo-section">
                    <img src="assets/logo.png" alt="Global News Network" class="logo">
                    <h1 class="site-title">Global News Network</h1>
                </div>
                <div class="header-actions">
                    <div class="search-container">
                        <form method="GET" action="seaarch.php" style="display: flex;">
                            <input type="text" name="search" placeholder="Search news..." class="search-input">
                            <button type="submit" class="search-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    <div class="auth-buttons" id="authButtons" <?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'style="display:none;"' : ''; ?>>
                        <button class="btn btn-outline" onclick="openModal('loginModal')">Login</button>
                        <button class="btn btn-primary" onclick="openModal('registerModal')">Register</button>
                    </div>
                    <div class="user-menu <?php echo !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] ? 'hidden' : ''; ?>" id="userMenu">
                        <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?></span>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php" class="btn btn-outline">Admin Panel</a>
                        <?php endif; ?>
                        <form method="POST" action="logout.php" style="display: inline;">
                            <button type="submit" class="btn btn-outline">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="index.php?category=<?php echo $cat['category_id']; ?>" 
                               class="nav-link <?php echo $article_data['category_id'] == $cat['category_id'] ? 'active' : ''; ?>">
                               <?php echo htmlspecialchars($cat['category_name']); ?>
                            </a></li>
                    <?php endforeach; ?>
                </ul>
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <a href="javascript:history.back()" class="back-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
                Back to News
            </a>

            <article class="article-header">
                <div class="article-info">
                    <div class="article-meta-full">
                        <span class="article-category"><?php echo htmlspecialchars($article_data['category_name'] ?? 'News'); ?></span>
                        <span>By <?php echo htmlspecialchars($article_data['author_name'] ?? 'Admin'); ?></span>
                        <span><?php echo formatDate($article_data['published_date']); ?></span>
                        <span><?php echo number_format($article_data['views']); ?> views</span>
                        <?php if ($article_data['is_breaking']): ?>
                            <span style="background-color: var(--breaking-red); color: white; padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); font-size: var(--text-xs); font-weight: 600;">BREAKING</span>
                        <?php endif; ?>
                        <?php if ($article_data['is_featured']): ?>
                            <span style="background-color: var(--success-green); color: white; padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); font-size: var(--text-xs); font-weight: 600;">FEATURED</span>
                        <?php endif; ?>
                    </div>
                    
                    <h1><?php echo htmlspecialchars($article_data['title']); ?></h1>
                    
                    <?php if ($article_data['summary']): ?>
                        <p class="article-summary" style="font-size: var(--text-lg); color: var(--medium-blue); margin-top: var(--spacing-md);">
                            <?php echo htmlspecialchars($article_data['summary']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($article_data['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($article_data['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($article_data['title']); ?>" 
                         class="article-main-image">
                <?php endif; ?>

                <div class="article-content-text">
                    <?php echo nl2br(htmlspecialchars($article_data['content'])); ?>
                </div>
            </article>

            <?php if (!empty($related_articles)): ?>
                <section class="related-articles">
                    <h2 class="section-title">Related Articles</h2>
                    <div class="news-grid">
                        <?php foreach ($related_articles as $related): ?>
                            <div class="article-card">
                                <?php if ($related['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="article-image">
                                <?php endif; ?>
                                <div class="article-content">
                                    <span class="article-category"><?php echo htmlspecialchars($related['category_name'] ?? 'News'); ?></span>
                                    <h3 class="article-title">
                                        <a href="article.php?id=<?php echo $related['article_id']; ?>"><?php echo htmlspecialchars($related['title']); ?></a>
                                    </h3>
                                    <p class="article-summary"><?php echo htmlspecialchars(substr($related['summary'] ?: $related['content'], 0, 150) . '...'); ?></p>
                                    <div class="article-meta">
                                        <span>By <?php echo htmlspecialchars($related['author_name'] ?? 'Admin'); ?></span>
                                        <span><?php echo date('M j, Y', strtotime($related['published_date'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="assets/logo.png" alt="Global News Network" class="footer-logo-img">
                        <h3>Global News Network</h3>
                    </div>
                    <p class="footer-description">Your trusted source for breaking news and in-depth analysis from around the world.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul class="footer-links">
                        <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                            <li><a href="category.php?category=<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <ul class="footer-links">
                        <li><a href="#" class="social-link">Facebook</a></li>
                        <li><a href="#" class="social-link">Twitter</a></li>
                        <li><a href="#" class="social-link">Instagram</a></li>
                        <li><a href="#" class="social-link">LinkedIn</a></li>
                        </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Global News Network. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Login</h3>
                <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
            </div>
            <form method="POST" action="login.php" class="modal-form">
                <div class="form-group">
                    <label for="loginUsername">Username or Email</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Register</h3>
                <button class="modal-close" onclick="closeModal('registerModal')">&times;</button>
            </div>
            <form method="POST" action="register.php" class="modal-form">
                <div class="form-group">
                    <label for="registerUsername">Username</label>
                    <input type="text" id="registerUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Register</button>
            </form>
        </div>
    </div>

    <!-- Minimal Inline JavaScript -->
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function toggleMobileMenu() {
            const nav = document.querySelector('.nav-list');
            nav.classList.toggle('mobile-open');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        };
    </script>
</body>
</html>

