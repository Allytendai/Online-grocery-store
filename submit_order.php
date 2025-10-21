<?php
session_start();

ob_start();

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Return error response if not logged in
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if user is a customer 
if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager') {
    // Return error response for unauthorized role
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Managers cannot place orders']);
    exit;
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Return error response for invalid request method
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn = new mysqli('localhost', 'x9h24', 'x9h24x9h24', 'x9h24');
if ($conn->connect_error) {
    // Return error response if database connection fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Parse JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['productId'])) {
    // Return error response if JSON is invalid or missing productId
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing product ID']);
    exit;
}

// Sanitize and validate productId
$productId = filter_var($data['productId'], FILTER_VALIDATE_INT);
if ($productId === false || $productId <= 0) {
    // Return error response for invalid productId
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Get user email from session
$email = $_SESSION['email'];

// Verify product exists in products table
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
if (!$stmt) {
    // Return error response if query preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    $conn->close();
    exit;
}
$stmt->bind_param("i", $productId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    // Return error response if product not found
    ob_end_clean();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert order into orders table
$stmt = $conn->prepare("INSERT INTO orders (email, product_id, order_date) VALUES (?, ?, NOW())");
if (!$stmt) {
    // Return error response if insert preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Insert preparation failed']);
    $conn->close();
    exit;
}
$stmt->bind_param("si", $email, $productId);
if ($stmt->execute()) {
    // Return success response
    ob_end_clean();
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
} else {
    // Return error response if insert fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to place order']);
}
$stmt->close();
$conn->close();
?>