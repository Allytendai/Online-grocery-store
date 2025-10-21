<?php
session_start();

// List of CAPTCHA images 
$captchaImages = ['captcha1.jpg', 'captcha2.jpg', 'captcha3.jpg'];
// Randomly select a CAPTCHA image
$captchaImage = $captchaImages[array_rand($captchaImages)];
// Stores the selected image in session for verification in login_process.php
$_SESSION['captcha_image'] = $captchaImage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Login to the online grocery store">
    <meta name="keywords" content="grocery, login, online shopping">
    <title>Login - Grocery Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Grocery Store Login</h1>
        <form id="login-form" class="mt-4">
            <h2>Login</h2>
            <!-- Email input field -->
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required 
                       aria-describedby="email-error" placeholder="Enter your email">
                <div id="email-error" class="text-danger" style="display: none;"></div>
            </div>
            <!-- Password input field -->
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required 
                       aria-describedby="password-error" placeholder="Enter your password">
                <div id="password-error" class="text-danger" style="display: none;"></div>
            </div>
            <!-- CAPTCHA input and image -->
            <div class="mb-3">
                <label for="captcha" class="form-label">Enter CAPTCHA Code:</label>
                <div>
                    <img src="images/captcha/ <?php echo htmlspecialchars($captchaImage); ?>" 
                         alt="CAPTCHA Image" class="captcha-image" id="captcha-image">
                    <button type="button" class="btn btn-link" onclick="refreshCaptcha()" 
                            aria-label="Refresh CAPTCHA image">Refresh CAPTCHA</button>
                </div>
                <input type="text" id="captcha" name="captcha" class="form-control" required 
                       aria-describedby="captcha-error" placeholder="Enter CAPTCHA code">
                <div id="captcha-error" class="text-danger" style="display: none;"></div>
            </div>
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Login</button>
            <!-- Link to registration page -->
            <p class="mt-3">
                Don't have an account? <a href="register.php" class="text-primary">Register here</a>.
            </p>
        </form>
        <!-- Page footer -->
        <footer class="mt-5">
            <p>© 2025 Grocery Store. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to refresh CAPTCHA image via AJAX
        function refreshCaptcha() {
            // Get CAPTCHA image element
            const captchaImg = document.getElementById('captcha-image');
            // Clear CAPTCHA input
            document.getElementById('captcha').value = '';
            // Clear CAPTCHA error
            document.getElementById('captcha-error').style.display = 'none';

            // Fetch new CAPTCHA image from server
            fetch('refresh_captcha.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to refresh CAPTCHA');
                    }
                    return response.json();
                })
                .then(data => {
                    captchaImg.src = `images/captcha/${data.captchaImage}`;
                })
                .catch(error => {
                    console.error('CAPTCHA refresh error:', error);
                    document.getElementById('captcha-error').textContent = 'Failed to refresh CAPTCHA';
                    document.getElementById('captcha-error').style.display = 'block';
                });
        }

        // Event listener for form submission
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            let valid = true;
            // Get form input values
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const captcha = document.getElementById('captcha').value;

            // Reset error messages
            document.getElementById('email-error').style.display = 'none';
            document.getElementById('password-error').style.display = 'none';
            document.getElementById('captcha-error').style.display = 'none';

            // Validate email format
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email';
                document.getElementById('email-error').style.display = 'block';
                valid = false;
            }
            // Validate password length
            if (password.length < 6) {
                document.getElementById('password-error').textContent = 'Password must be at least 6 characters';
                document.getElementById('password-error').style.display = 'block';
                valid = false;
            }
            // Validate CAPTCHA input
            if (!captcha) {
                document.getElementById('captcha-error').textContent = 'Please enter the CAPTCHA code';
                document.getElementById('captcha-error').style.display = 'block';
                valid = false;
            }

            // Proceed with form submission if valid
            if (valid) {
                // Create FormData object for POST request
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                formData.append('captcha', captcha);

                // Send login request to login_process.php
                fetch('login_process.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        // Check for HTTP errors
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Handle server response
                        if (data.success) {
                            // Redirect based on user role
                            if (data.role === 'manager') {
                                window.location.href = 'manager_orders.php';
                            } else {
                                window.location.href = 'orders.php';
                            }
                        } else {
                            // Display error message and refresh CAPTCHA
                            document.getElementById('captcha-error').textContent = data.message;
                            document.getElementById('captcha-error').style.display = 'block';
                            refreshCaptcha();
                        }
                    })
                    .catch(error => {
                        // Handle network or fetch errors
                        console.error('Login error:', error);
                        document.getElementById('captcha-error').textContent = 'Failed to login. Please try again.';
                        document.getElementById('captcha-error').style.display = 'block';
                        refreshCaptcha();
                    });
            }
        });
    </script>
</body>
</html>