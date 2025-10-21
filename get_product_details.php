<?php

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$servername = "servername.hosting-data.io"; 
$username   = "username";                     
$password   = "password";                     
$dbname     = "dbname";                     

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Gets product ID from query parameter, default to 0 if not provided or invalid
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

// Validates product ID as a positive integer
if ($id === false || $id <= 0) {
    // Returns error response for invalid or missing ID
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID (must be a positive integer)']);
    exit;
}

// Prepares SQL query to fetch product details securely, including all relevant fields
$stmt = $conn->prepare("SELECT id, name, category, price, description, image FROM products WHERE id = ?");
if (!$stmt) {
    // Returns error response if query preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
    $conn->close();
    exit;
}

// Binds product ID parameter to prevent SQL injection
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Checks for query execution errors
if ($result === false) {
    // Returns error response if query execution fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query execution failed']);
    $stmt->close();
    $conn->close();
    exit;
}

// Fetches product data or return empty array if not found
$products = $result->fetch_assoc() ?: [];

// Closes statement and database connection to free resources
$stmt->close();
$conn->close();

// Checks if product was found
if (empty($products)) {
    // Returns error response if product not found
    ob_end_clean();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Formats product data for consistent JSON response
$products = [
    'id' => $products['id'],
    'name' => $products['name'],
    'category' => $products['category'],
    'price' => floatval($products['price']), 
    'description' => $products['description'] ?? '', 
    'image' => !empty($products['image']) ? "images/products/{$products['image']}" : '' 
];

// Returns successful response with product data
ob_end_clean();
http_response_code(200);
echo json_encode(['success' => true, 'data' => $products]);
?>