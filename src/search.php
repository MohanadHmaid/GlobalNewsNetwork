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

// Get search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get page number for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;

// Get all categories for navigation
$categories = $category->getAll();

// Search articles
$articles = [];
$total_articles = 0;
$total_pages = 0;

if (!empty($search_query)) {
    $articles = $article->search($search_query, $page, $limit);
    $total_articles = count($article->search($search_query, 1, 1000)); // Get total for pagination
    $total_pages = ceil($total_articles / $limit);
}

// Helper function to format date
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Helper function to truncate text
function truncateText($text, $length = 150) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}

// Helper function to highlight search terms
function highlightSearchTerms($text, $search_query) {
    if (empty($search_query)) return $text;
    
    $terms = explode(' ', $search_query);
    foreach ($terms as $term) {
        if (strlen($term) > 2) { // Only highlight terms longer than 2 characters
            $text = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $text);
        }
    }
    return $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($search_query) ? 'Search Results for "' . htmlspecialchars($search_query) . '"' : 'Search'; ?> - Global News Network</title>
    <link rel="icon" type="image/x-icon" href="assets/Icon.ico">
    <link rel="stylesheet" href="frontend/css/styles.css">
    <link rel="stylesheet" href="frontend/css/responsive.css">
    <style>
        .search-header {
            background-color: white;
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }
        
        .search-form {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            gap: var(--spacing-sm);
        }
        
        .search-form input {
            flex: 1;
            padding: var(--spacing-md);
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: var(--text-lg);
        }
        
        .search-form button {
            padding: var(--spacing-md) var(--spacing-lg);
            background-color: var(--medium-blue);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: var(--text-lg);
            font-weight: 600;
        }
        
        .search-results-info {
            margin-bottom: var(--spacing-lg);
            color: var(--text-light);
            font-size: var(--text-lg);
        }
        
        .no-results {
            text-align: center;
            padding: var(--spacing-xl);
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .no-results h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .search-suggestions {
            margin-top: var(--spacing-lg);
            text-align: left;
        }
        
        .search-suggestions h4 {
            margin-bottom: var(--spacing-md);
            color: var(--primary-blue);
        }
        
        .search-suggestions ul {
            list-style: none;
            padding: 0;
        }
        
        .search-suggestions li {
            margin-bottom: var(--spacing-sm);
        }
        
        .search-suggestions a {
            color: var(--medium-blue);
            text-decoration: none;
        }
        
        .search-suggestions a:hover {
            text-decoration: underline;
        }
        
        mark {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
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
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li><a href="category.php?id=<?php echo $cat['category_id']; ?>" class="nav-link">
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
            <!-- Search Header -->
            <div class="search-header">
                <h1>Search News Articles</h1>
                <p>Find the latest news and articles from Global News Network</p>
                <form method="GET" action="search.php" class="search-form">
                    <input type="text" name="search" placeholder="Enter your search terms..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                    <button type="submit">Search</button>
                </form>
            </div>

            <?php if (!empty($search_query)): ?>
                <!-- Search Results -->
                <section class="search-results">
                    <div class="search-results-info">
                        <?php if ($total_articles > 0): ?>
                            <p>Found <strong><?php echo number_format($total_articles); ?></strong> result<?php echo $total_articles != 1 ? 's' : ''; ?> for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                        <?php else: ?>
                            <p>No results found for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                        <?php endif; ?>
                    </div>
                    
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
                                            <a href="article.php?id=<?php echo $article_item['article_id']; ?>">
                                                <?php echo highlightSearchTerms(htmlspecialchars($article_item['title']), $search_query); ?>
                                            </a>
                                        </h3>
                                        <p class="article-summary">
                                            <?php echo highlightSearchTerms(htmlspecialchars(truncateText($article_item['summary'] ?: $article_item['content'])), $search_query); ?>
                                        </p>
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
                                    <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <h3>No articles found</h3>
                            <p>We couldn't find any articles matching your search terms. Try adjusting your search or browse our categories below.</p>
                            
                            <div class="search-suggestions">
                                <h4>Browse by Category:</h4>
                                <ul>
                                    <?php foreach ($categories as $cat): ?>
                                        <li><a href="category.php?id=<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php else: ?>
                <!-- Popular Articles when no search -->
                <section class="popular-articles">
                    <h2 class="section-title">Popular Articles</h2>
                    <div class="news-grid">
                        <?php 
                        $popular_articles = $article->getAll(1, 8); // Get 8 latest articles
                        foreach ($popular_articles as $popular): 
                        ?>
                            <div class="article-card">
                                <?php if ($popular['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($popular['image_url']); ?>" alt="<?php echo htmlspecialchars($popular['title']); ?>" class="article-image">
                                <?php endif; ?>
                                <div class="article-content">
                                    <span class="article-category"><?php echo htmlspecialchars($popular['category_name'] ?? 'News'); ?></span>
                                    <h3 class="article-title">
                                        <a href="article.php?id=<?php echo $popular['article_id']; ?>"><?php echo htmlspecialchars($popular['title']); ?></a>
                                    </h3>
                                    <p class="article-summary"><?php echo htmlspecialchars(truncateText($popular['summary'] ?: $popular['content'])); ?></p>
                                    <div class="article-meta">
                                        <span>By <?php echo htmlspecialchars($popular['author_name'] ?? 'Admin'); ?></span>
                                        <span><?php echo formatDate($popular['published_date']); ?></span>
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