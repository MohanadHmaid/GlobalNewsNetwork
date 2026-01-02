<?php
session_start();
require_once 'backend/config/database.php';
require_once 'backend/classes/Article.php';
require_once 'backend/classes/Category.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$article = new Article($db);
$category = new Category($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_article':
                $article->title = $_POST['title'];
                $article->content = $_POST['content'];
                $article->summary = $_POST['summary'];
                $article->image_url = $_POST['image_url'];
                $article->category_id = $_POST['category_id'] ?: null;
                $article->author_id = $_SESSION['user_id'];
                $article->is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $article->is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
                
                if ($article->create()) {
                    $_SESSION['success'] = 'Article created successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to create article.';
                }
                break;
                
            case 'update_article':
                $article->article_id = $_POST['article_id'];
                $article->title = $_POST['title'];
                $article->content = $_POST['content'];
                $article->summary = $_POST['summary'];
                $article->image_url = $_POST['image_url'];
                $article->category_id = $_POST['category_id'] ?: null;
                $article->is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $article->is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
                
                if ($article->update()) {
                    $_SESSION['success'] = 'Article updated successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to update article.';
                }
                break;
                
            case 'delete_article':
                $article->article_id = $_POST['article_id'];
                if ($article->delete()) {
                    $_SESSION['success'] = 'Article deleted successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to delete article.';
                }
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: admin.php');
        exit;
    }
}

// Get data for display
$articles = $article->getAll(1, 50); // Get first 50 articles
$categories = $category->getAll();

// Get article for editing if specified
$edit_article = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_article = $article->getById($_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Global News Network</title>
    <link rel="icon" type="image/x-icon" href="assets/Icon.ico">
    <link rel="stylesheet" href="frontend/css/styles.css">
    <link rel="stylesheet" href="frontend/css/responsive.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-lg);
        }
        
        .admin-header {
            background-color: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .admin-tabs {
            display: flex;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .admin-tab {
            padding: var(--spacing-md) var(--spacing-lg);
            background-color: white;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .admin-tab.active {
            background-color: var(--primary-light);
            color: white;
        }
        
        .admin-section {
            background-color: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .articles-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--spacing-md);
        }
        
        .articles-table th,
        .articles-table td {
            padding: var(--spacing-sm);
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }
        
        .articles-table th {
            background-color: var(--bg-light);
            font-weight: 600;
        }
        
        .article-actions {
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .btn-small {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: var(--text-xs);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .articles-table {
                font-size: var(--text-sm);
            }
            
            .admin-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Panel - Global News Network</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="index.php" class="btn btn-outline">Back to Website</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="admin-tabs">
            <div class="admin-tab active" onclick="showTab('create')">
                <?php echo $edit_article ? 'Edit Article' : 'Create Article'; ?>
            </div>
            <div class="admin-tab" onclick="showTab('manage')">Manage Articles</div>
        </div>

        <!-- Create/Edit Article Section -->
        <div class="admin-section" id="createTab">
            <h2><?php echo $edit_article ? 'Edit Article' : 'Create New Article'; ?></h2>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="<?php echo $edit_article ? 'update_article' : 'create_article'; ?>">
                <?php if ($edit_article): ?>
                    <input type="hidden" name="article_id" value="<?php echo $edit_article['article_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo $edit_article ? htmlspecialchars($edit_article['title']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                        <?php echo ($edit_article && $edit_article['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" 
                               value="<?php echo $edit_article ? htmlspecialchars($edit_article['image_url']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="summary">Summary</label>
                    <textarea id="summary" name="summary" placeholder="Brief summary of the article..."><?php echo $edit_article ? htmlspecialchars($edit_article['summary']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" required style="min-height: 200px;" placeholder="Full article content..."><?php echo $edit_article ? htmlspecialchars($edit_article['content']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" value="1" 
                                   <?php echo ($edit_article && $edit_article['is_featured']) ? 'checked' : ''; ?>>
                            Featured Article
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_breaking" value="1" 
                                   <?php echo ($edit_article && $edit_article['is_breaking']) ? 'checked' : ''; ?>>
                            Breaking News
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_article ? 'Update Article' : 'Create Article'; ?>
                </button>
                
                <?php if ($edit_article): ?>
                    <a href="admin.php" class="btn btn-outline">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Manage Articles Section -->
        <div class="admin-section" id="manageTab" style="display: none;">
            <h2>Manage Articles</h2>
            
            <?php if (!empty($articles)): ?>
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Published</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $art): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(substr($art['title'], 0, 50)) . (strlen($art['title']) > 50 ? '...' : ''); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($art['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($art['published_date'])); ?></td>
                                <td>
                                    <?php if ($art['is_breaking']): ?>
                                        <span style="background-color: var(--breaking-red); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">BREAKING</span>
                                    <?php endif; ?>
                                    <?php if ($art['is_featured']): ?>
                                        <span style="background-color: var(--success-green); color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;">FEATURED</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($art['views']); ?></td>
                                <td class="article-actions">
                                    <a href="article.php?id=<?php echo $art['article_id']; ?>" class="btn btn-outline btn-small" target="_blank">View</a>
                                    <a href="admin.php?edit=<?php echo $art['article_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                    <form method="POST" action="admin.php" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this article?');">
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?php echo $art['article_id']; ?>">
                                        <button type="submit" class="btn btn-outline btn-small" style="background-color: var(--breaking-red); color: white;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No articles found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.getElementById('createTab').style.display = 'none';
            document.getElementById('manageTab').style.display = 'none';
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.admin-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab and mark button as active
            if (tabName === 'create') {
                document.getElementById('createTab').style.display = 'block';
                document.querySelectorAll('.admin-tab')[0].classList.add('active');
            } else if (tabName === 'manage') {
                document.getElementById('manageTab').style.display = 'block';
                document.querySelectorAll('.admin-tab')[1].classList.add('active');
            }
        }
        
        // Show manage tab if there are URL parameters for editing
        <?php if ($edit_article): ?>
            showTab('create');
        <?php endif; ?>
    </script>
</body>
</html>