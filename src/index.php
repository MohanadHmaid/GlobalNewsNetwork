<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/classes/Article.php';
require_once 'backend/classes/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$article = new Article($db);
$category = new Category($db);

// Get page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;

// Get category filter
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch data
$breaking_news = $article->getBreaking(3);
$featured_articles = $article->getFeatured(6);
$categories = $category->getAll();

// Get articles based on filters
if (!empty($search_query)) {
    $articles = $article->search($search_query, $page, $limit);
    $total_articles = count($article->search($search_query, 1, 1000)); // Get total for pagination
} elseif ($category_filter) {
    $articles = $article->getByCategory($category_filter, $page, $limit);
    $total_articles = count($article->getByCategory($category_filter, 1, 1000)); // Get total for pagination
} else {
    $articles = $article->getAll($page, $limit);
    $total_articles = $article->getTotalCount();
}

// Calculate pagination
$total_pages = ceil($total_articles / $limit);

// Helper function to format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Helper function to truncate text
function truncateText($text, $length = 150) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global News Network - Breaking News & Latest Updates</title>
    <link rel="icon" type="image/x-icon" href="assets/Icon.ico">
    <link rel="stylesheet" href="frontend/css/styles.css">
    <link rel="stylesheet" href="frontend/css/responsive.css">
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
                        <form method="GET" action="search.php" style="display: flex;">
                            <input type="text" name="search" placeholder="Search news..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <li><a href="index.php" class="nav-link <?php echo !$category_filter && empty($search_query) ? 'active' : ''; ?>">Home</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="category.php?id=<?php echo $cat['category_id']; ?>" 
                               class="nav-link">
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
            <?php if (!empty($breaking_news) && empty($search_query) && !$category_filter): ?>
            <!-- Breaking News Section -->
            <section class="breaking-news">
                <h2 class="section-title breaking-title">Breaking News</h2>
                <div class="breaking-news-container">
                    <?php foreach ($breaking_news as $news): ?>
                        <div class="article-card" style="margin-bottom: 1rem;">
                            <?php if ($news['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($news['image_url']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="article-image">
                            <?php endif; ?>
                            <div class="article-content">
                                <span class="article-category"><?php echo htmlspecialchars($news['category_name'] ?? 'Breaking'); ?></span>
                                <h3 class="article-title">
                                    <a href="article.php?id=<?php echo $news['article_id']; ?>"><?php echo htmlspecialchars($news['title']); ?></a>
                                </h3>
                                <p class="article-summary"><?php echo htmlspecialchars(truncateText($news['summary'] ?: $news['content'])); ?></p>
                                <div class="article-meta">
                                    <span>By <?php echo htmlspecialchars($news['author_name'] ?? 'Admin'); ?></span>
                                    <span><?php echo formatDate($news['published_date']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <div class="content-grid">
                <!-- Featured Articles -->
                <?php if (!empty($featured_articles) && empty($search_query) && !$category_filter): ?>
                <section class="featured-section">
                    <h2 class="section-title">Featured Stories</h2>
                    <div class="featured-grid">
                        <?php foreach ($featured_articles as $featured): ?>
                            <div class="article-card">
                                <?php if ($featured['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($featured['image_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="article-image">
                                <?php endif; ?>
                                <div class="article-content">
                                    <span class="article-category"><?php echo htmlspecialchars($featured['category_name'] ?? 'News'); ?></span>
                                    <h3 class="article-title">
                                        <a href="article.php?id=<?php echo $featured['article_id']; ?>"><?php echo htmlspecialchars($featured['title']); ?></a>
                                    </h3>
                                    <p class="article-summary"><?php echo htmlspecialchars(truncateText($featured['summary'] ?: $featured['content'])); ?></p>
                                    <div class="article-meta">
                                        <span>By <?php echo htmlspecialchars($featured['author_name'] ?? 'Admin'); ?></span>
                                        <span><?php echo formatDate($featured['published_date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Sidebar -->
                <aside class="sidebar">
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">Trending Topics</h3>
                        <div class="trending-list">
                            <?php 
                            $trending_articles = $article->getAll(1, 5); // Get 5 latest articles as trending
                            foreach ($trending_articles as $t_article): 
                            ?>
                                <div class="trending-item">
                                    <a href="article.php?id=<?php echo $t_article["article_id"]; ?>"><?php echo htmlspecialchars($t_article["title"]); ?></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
            </div>

            <!-- Latest News Section -->
            <section class="latest-news">
                <h2 class="section-title">
                    <?php 
                    if (!empty($search_query)) {
                        echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
                    } elseif ($category_filter) {
                        $current_category = array_filter($categories, function($cat) use ($category_filter) {
                            return $cat['category_id'] == $category_filter;
                        });
                        $current_category = reset($current_category);
                        echo htmlspecialchars($current_category['category_name']) . ' News';
                    } else {
                        echo 'Latest News';
                    }
                    ?>
                </h2>
                
                <?php if (!empty($articles)): ?>
                    <div class="news-grid">
                        <?php foreach ($articles as $article_item): ?>
                            <div class="article-card">
                                <?php if ($article_item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($article_item['image_url']); ?>" alt="<?php echo htmlspecialchars($article_item['title']); ?>" class="article-image">
                                <?php endif; ?>
                                <div class="article-content">
                                    <span class="article-category"><?php echo htmlspecialchars($article_item['category_name'] ?? 'News'); ?></span>
                                    <h3 class="article-title">
                                        <a href="article.php?id=<?php echo $article_item['article_id']; ?>"><?php echo htmlspecialchars($article_item['title']); ?></a>
                                    </h3>
                                    <p class="article-summary"><?php echo htmlspecialchars(truncateText($article_item['summary'] ?: $article_item['content'])); ?></p>
                                    <div class="article-meta">
                                        <span>By <?php echo htmlspecialchars($article_item['author_name'] ?? 'Admin'); ?></span>
                                        <span><?php echo formatDate($article_item['published_date']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-center">No articles found.</p>
                <?php endif; ?>
            </section>
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
                        <li><a href="frontend/about.html">About Us</a></li>
                        <li><a href="frontend/contact.html">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul class="footer-links">
                        <?php foreach (array_slice($categories, 0, 4) as $cat): ?>
                            <li><a href="category.php?id=<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <ul class="footer-links">
                        <li><a href="#" >Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">LinkedIn</a></li>
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

