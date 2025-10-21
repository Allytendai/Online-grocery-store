<?php
session_start();

// Initialize output buffering to prevent JSON corruption
ob_start();

// Set response header to JSON for API output
header('Content-Type: application/json');

// Configure error handling: log errors to file, hide from display
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Define CAPTCHA code mapping (must match login_process.php and register.php)
$captchaCodes = [
    'captcha1.jpg' => 'x9h24',
    'captcha2.jpg' => 'ecb4f',
    'captcha3.jpg' => 'B37Lvy'
];

// Establish database connection using provided credentials
$conn = new mysqli('localhost', 'x9h24', 'x9h24x9h24', 'x9h24');
if ($conn->connect_error) {
    // Return error response if database connection fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
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

// Parse JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['name'], $data['phone'], $data['email'], $data['password'], $data['captcha'])) {
    // Return error response if JSON is invalid or missing required fields
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing input data']);
    exit;
}

// Sanitize and validate input data
$name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
$phone = trim($data['phone']);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = trim($data['password']);
$captchaInput = trim($data['captcha']);
$captchaImage = $_SESSION['captcha_image'] ?? '';

// Validates required fields
if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($captchaInput) || empty($captchaImage)) {
    // Returns error response if any required field is missing
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Verifies CAPTCHA using session-stored image
if (!isset($captchaCodes[$captchaImage]) || $captchaInput !== $captchaCodes[$captchaImage]) {
    // Returns error response if CAPTCHA is invalid
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CAPTCHA code']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Server-side validation of name 
if (strlen($name) > 100) {
    // Return error response for invalid name
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name is too long (max 100 characters)']);
    unset($_SESSION['captcha_image']);
    exit;
}

// validation of phone (11 digits)
if (!preg_match('/^\d{11}$/', $phone)) {
    // Return error response for invalid phone number
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Phone number must be 11 digits']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Server-side validation of email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Return error response for invalid email
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Server-side validation of password (minimum of 6 characters)
if (strlen($password) < 6) {
    // Return error response for weak password
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Check for duplicate email
$stmt = $conn->prepare("SELECT email FROM customers WHERE email = ?");
if (!$stmt) {
    // Return error response if query preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    $conn->close();
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    // Return error response if email is already registered
    ob_end_clean();
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    unset($_SESSION['captcha_image']);
    exit;
}
$stmt->close();

// Hash password for secure storage
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
// Set default role to 'customer' 
$role = 'customer';

// Insert new customer into database
$stmt = $conn->prepare("INSERT INTO customers (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    // Return error response if insert preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Insert preparation failed']);
    $conn->close();
    exit;
}
$stmt->bind_param("sssss", $name, $phone, $email, $passwordHash, $role);
if (!$stmt->execute()) {
    // Returns error response if insert fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
    $stmt->close();
    $conn->close();
    unset($_SESSION['captcha_image']);
    exit;
}

// Clear CAPTCHA session
unset($_SESSION['captcha_image']);

// Return success response
ob_end_clean();
http_response_code(201);
echo json_encode(['success' => true, 'message' => 'Registered successfully']);

// Close statement and database connection
$stmt->close();
$conn->close();
?>