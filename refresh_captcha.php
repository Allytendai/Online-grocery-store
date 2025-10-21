<?php
// Start a PHP session to manage CAPTCHA state
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

// Define available CAPTCHA images
$captchaImages = ['captcha1.jpg', 'captcha2.jpg', 'captcha3.jpg'];

// Selects a random CAPTCHA image
$captchaImage = $captchaImages[array_rand($captchaImages)];

// Verifies the CAPTCHA image exists in the filesystem
if (!file_exists('images/captcha/' . $captchaImage)) {
    // Return error response if CAPTCHA image is missing
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'CAPTCHA image not found']);
    exit;
}

// Store the selected CAPTCHA image in the session for validation
$_SESSION['captcha_image'] = $captchaImage;

// Return success response with the new CAPTCHA image filename
ob_end_clean();
http_response_code(200);
echo json_encode(['success' => true, 'captchaImage' => $captchaImage]);
?>