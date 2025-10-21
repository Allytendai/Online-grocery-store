<?php
session_start();

// Initialize output buffering to prevent JSON corruption from unexpected output
ob_start();

header('Content-Type: application/json');

// Configures error handling: log errors to file, hide from display
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Defines CAPTCHA code mapping 
$captchaCodes = [
    'captcha1.jpg' => 'x9h24',
    'captcha2.jpg' => 'ecb4f',
    'captcha3.jpg' => 'B37Lvy'
];


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

// Ensures request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Returns error response for invalid request method
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Sanitize and retrieve POST parameters
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$captchaInput = trim($_POST['captcha'] ?? '');
$captchaImage = $_SESSION['captcha_image'] ?? '';

// Validates required fields
if (empty($email) || empty($password) || empty($captchaInput) || empty($captchaImage)) {
    // Returns error response if any required field is missing
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Verifies CAPTCHA code
if (!isset($captchaCodes[$captchaImage]) || $captchaInput !== $captchaCodes[$captchaImage]) {
    // Returns error response if CAPTCHA is invalid
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CAPTCHA code']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Validates email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Returns error response if email format is invalid
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    unset($_SESSION['captcha_image']);
    exit;
}

// Prepares SQL query to fetch user password and role securely
$stmt = $conn->prepare("SELECT password, role FROM users WHERE email = ?");
if (!$stmt) {
    // Returns error response if query preparation fails
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    $conn->close();
    exit;
}

// Binds email parameter to prevent SQL injection
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Checks if user exists
if ($row = $result->fetch_assoc()) {
    // Verifies password using secure hash comparison
    if (password_verify($password, $row['password'])) {
        // Sets session variables for authenticated user
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $row['role']; 
        unset($_SESSION['captcha_image']); 
        // Returns success response
        ob_end_clean();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Login successful', 'role' => $row['role']]);
    } else {
        // Returns error response for incorrect password
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        unset($_SESSION['captcha_image']);
    }
} else {
    // Returns error response if email not found
    ob_end_clean();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    unset($_SESSION['captcha_image']);
}

// Closes statement and database connection
$stmt->close();
$conn->close();
?>