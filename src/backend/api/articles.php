<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Article.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$database = new Database();
$db = $database->getConnection();
$article = new Article($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single article
            $result = $article->getById($_GET['id']);
            if ($result) {
                // Increment view count
                $article->incrementViews($_GET['id']);
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Article not found']);
            }
        } elseif (isset($_GET['featured'])) {
            // Get featured articles
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $result = $article->getFeatured($limit);
            echo json_encode(['success' => true, 'data' => $result]);
        } elseif (isset($_GET['breaking'])) {
            // Get breaking news
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
            $result = $article->getBreaking($limit);
            echo json_encode(['success' => true, 'data' => $result]);
        } elseif (isset($_GET['category'])) {
            // Get articles by category
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $result = $article->getByCategory($_GET['category'], $page, $limit);
            echo json_encode(['success' => true, 'data' => $result]);
        } elseif (isset($_GET['search'])) {
            // Search articles
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $result = $article->search($_GET['search'], $page, $limit);
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            // Get all articles with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $result = $article->getAll($page, $limit);
            $total = $article->getTotalCount();
            echo json_encode([
                'success' => true, 
                'data' => $result,
                'pagination' => [
                    'current_page' => $page,
                    'total_items' => $total,
                    'items_per_page' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;

    case 'POST':
        // Create new article (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['title']) || !isset($input['content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title and content are required']);
            break;
        }

        $article->title = $input['title'];
        $article->content = $input['content'];
        $article->summary = $input['summary'] ?? '';
        $article->image_url = $input['image_url'] ?? '';
        $article->category_id = $input['category_id'] ?? null;
        $article->author_id = $_SESSION['user_id'];
        $article->is_featured = $input['is_featured'] ?? false;
        $article->is_breaking = $input['is_breaking'] ?? false;

        if ($article->create()) {
            echo json_encode(['success' => true, 'message' => 'Article created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create article']);
        }
        break;

    case 'PUT':
        // Update article (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['article_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Article ID is required']);
            break;
        }

        $article->article_id = $input['article_id'];
        $article->title = $input['title'];
        $article->content = $input['content'];
        $article->summary = $input['summary'] ?? '';
        $article->image_url = $input['image_url'] ?? '';
        $article->category_id = $input['category_id'] ?? null;
        $article->is_featured = $input['is_featured'] ?? false;
        $article->is_breaking = $input['is_breaking'] ?? false;

        if ($article->update()) {
            echo json_encode(['success' => true, 'message' => 'Article updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update article']);
        }
        break;

    case 'DELETE':
        // Delete article (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['article_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Article ID is required']);
            break;
        }

        $article->article_id = $input['article_id'];

        if ($article->delete()) {
            echo json_encode(['success' => true, 'message' => 'Article deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete article']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>

