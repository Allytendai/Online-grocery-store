<?php
// Set response header to JSON for API output
header('Content-Type: application/json');

// Enables error logging, disable display to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Database connection credentials
$servername = "servername.hosting-data.io"; 
$username   = "username";                     
$password   = "password";                     
$dbname     = "dbname";    

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get category from query parameter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Validate category input (letters + spaces only)
if (empty($category) || !preg_match('/^[a-zA-Z\s]+$/', $category)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid category is required (letters and spaces only)']);
    $conn->close();
    exit;
}

// Prepare SQL query
$stmt = $conn->prepare("SELECT id, name, category, price, description, image FROM products WHERE category = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
    $conn->close();
    exit;
}

// Bind parameter and execute
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

// Fetch products
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'category' => $row['category'],
        'price' => floatval($row['price']),
        'description' => $row['description'] ?? '',
        'image' => $row['image'] ?? ''
    ];
}

$stmt->close();
$conn->close();

// Return response
if (empty($products)) {
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found for this category']);
} else {
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $products]);
}
?>
