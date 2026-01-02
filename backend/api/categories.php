<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Category.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single category
            $result = $category->getById($_GET['id']);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
        } else {
            // Get all categories
            $result = $category->getAll();
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    case 'POST':
        // Create new category (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['category_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            break;
        }

        $category->category_name = $input['category_name'];
        $category->description = $input['description'] ?? '';

        if ($category->create()) {
            echo json_encode(['success' => true, 'message' => 'Category created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create category']);
        }
        break;

    case 'PUT':
        // Update category (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['category_id']) || !isset($input['category_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
            break;
        }

        $category->category_id = $input['category_id'];
        $category->category_name = $input['category_name'];
        $category->description = $input['description'] ?? '';

        if ($category->update()) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update category']);
        }
        break;

    case 'DELETE':
        // Delete category (admin only)
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin access required']);
            break;
        }

        if (!isset($input['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category ID is required']);
            break;
        }

        $category->category_id = $input['category_id'];

        if ($category->delete()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>

