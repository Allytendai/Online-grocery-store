<?php
session_start();

$captchaImages = ['captcha1.jpg', 'captcha2.jpg', 'captcha3.jpg'];
// Randomly selects a CAPTCHA image
$captchaImage = $captchaImages[array_rand($captchaImages)];
// Stores the selected image in session for verification in register_process.php
$_SESSION['captcha_image'] = $captchaImage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Register for the online grocery store">
    <meta name="keywords" content="grocery, registration, online shopping">
    <title>Register - Grocery Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>SANEL GROCERY STORE</h1>
        <div id="root"></div>
        <footer class="mt-5">
        <p> Sanel Grocery Store. All rights reserved © 2025.</p>
        </footer>
    </div>

    <!-- Bootstrap JavaScript for interactive components -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include React and ReactDOM for client-side rendering -->
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <!-- Include Babel for JSX transformation -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <script type="text/babel">
        // Import React hooks
        const { useState, useEffect } = React;

        // RegisterForm component for user registration
        function RegisterForm() {
            // State for form inputs
            const [form, setForm] = useState({ name: '', phone: '', email: '', password: '', captcha: '' });
            // State for validation errors
            const [errors, setErrors] = useState({});
            // State for CAPTCHA image filename
            const [captchaImage, setCaptchaImage] = useState('<?php echo htmlspecialchars($captchaImage); ?>');

            // Validate form inputs
            const validate = (name, value) => {
                let error = '';
                if (name === 'name') {
                    if (!value) error = 'Name is required';
                    else if (value.length > 100) error = 'Name must be 100 characters or less';
                    else if (!/^[A-Za-z\s]+$/.test(value)) error = 'Name must contain only letters and spaces';
                }
                if (name === 'phone') {
                    if (!value) error = 'Phone number is required';
                    else if (!/^\d{11}$/.test(value)) error = 'Phone number must be 11 digits';
                }
                if (name === 'email') {
                    if (!value) error = 'Email is required';
                    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) error = 'Invalid email address';
                }
                if (name === 'password') {
                    if (!value) error = 'Password is required';
                    else if (value.length < 6) error = 'Password must be at least 6 characters';
                }
                if (name === 'captcha' && !value) error = 'Please enter the CAPTCHA code';
                return error;
            };

            // Handles input changes and validate
            const handleChange = (e) => {
                const { name, value } = e.target;
                setForm({ ...form, [name]: value });
                setErrors({ ...errors, [name]: validate(name, value) });
            };

            // Function to refresh CAPTCHA image via AJAX
            const refreshCaptcha = () => {
                // Clear CAPTCHA input and error
                setForm({ ...form, captcha: '' });
                setErrors({ ...errors, captcha: '' });

                // Fetch new CAPTCHA image from server
                fetch('refresh_captcha.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to refresh CAPTCHA');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Update CAPTCHA image
                        setCaptchaImage(data.captchaImage);
                    })
                    .catch(error => {
                        console.error('CAPTCHA refresh error:', error);
                        setErrors({ ...errors, captcha: 'Failed to refresh CAPTCHA' });
                    });
            };

            // Handle form submission
            const handleSubmit = (e) => {
                e.preventDefault();
                const formErrors = {};
                // Validate all fields
                Object.keys(form).forEach(key => {
                    formErrors[key] = validate(key, form[key]);
                });
                setErrors(formErrors);

                // Check for errors or empty fields
                const hasErrors = Object.values(formErrors).some(e => e) || Object.values(form).some(v => !v);
                if (!hasErrors) {
                    // Sends registration data to server
                    fetch('register_process.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(form)
                    })
                        .then(response => {
                            // Checks for HTTP errors
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Handles server response
                            if (data.success) {
                                // Redirect to login on success
                                window.location.href = 'login.php';
                            } else {
                                // Display server error and refresh CAPTCHA
                                setErrors({ ...errors, form: data.message });
                                refreshCaptcha();
                            }
                        })
                        .catch(error => {
                            // Handle network or fetch errors
                            console.error('Registration error:', error);
                            setErrors({ ...errors, form: 'Failed to register. Please try again.' });
                            refreshCaptcha();
                        });
                }
            };

            // Render the registration form
            return (
                <form onSubmit={handleSubmit} className="mt-4">
                    <h2>Register</h2>
                    {/* Display server-side or general errors */}
                    {errors.form && <div className="alert alert-danger">{errors.form}</div>}
                    {/* Name input */}
                    <div className="mb-3">
                        <label htmlFor="name" className="form-label">Name:</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            className="form-control"
                            value={form.name}
                            onChange={handleChange}
                            placeholder="Enter your name"
                            aria-describedby="name-error"
                            required
                        />
                        {errors.name && <div id="name-error" className="text-danger">{errors.name}</div>}
                    </div>
                    {/* Phone input */}
                    <div className="mb-3">
                        <label htmlFor="phone" className="form-label">Phone:</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            className="form-control"
                            value={form.phone}
                            onChange={handleChange}
                            placeholder="Enter 11-digit phone number"
                            aria-describedby="phone-error"
                            required
                        />
                        {errors.phone && <div id="phone-error" className="text-danger">{errors.phone}</div>}
                    </div>
                    {/* Email input */}
                    <div className="mb-3">
                        <label htmlFor="email" className="form-label">Email:</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            className="form-control"
                            value={form.email}
                            onChange={handleChange}
                            placeholder="Enter your email"
                            aria-describedby="email-error"
                            required
                        />
                        {errors.email && <div id="email-error" className="text-danger">{errors.email}</div>}
                    </div>
                    {/* Password input */}
                    <div className="mb-3">
                        <label htmlFor="password" className="form-label">Password:</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            className="form-control"
                            value={form.password}
                            onChange={handleChange}
                            placeholder="Enter your password"
                            aria-describedby="password-error"
                            required
                        />
                        {errors.password && <div id="password-error" className="text-danger">{errors.password}</div>}
                    </div>
                    {/* CAPTCHA input and image */}
                    <div className="mb-3">
                        <label htmlFor="captcha" className="form-label">Enter CAPTCHA Code:</label>
                        <div>
                            <img
                                src={`images/captcha/${captchaImage}`}
                                alt="CAPTCHA Image"
                                className="captcha-image"
                            />
                            <button
                                type="button"
                                className="btn btn-link"
                                onClick={refreshCaptcha}
                                aria-label="Refresh CAPTCHA image"
                            >
                                Refresh CAPTCHA
                            </button>
                        </div>
                        <input
                            type="text"
                            id="captcha"
                            name="captcha"
                            className="form-control"
                            value={form.captcha}
                            onChange={handleChange}
                            placeholder="Enter CAPTCHA code"
                            aria-describedby="captcha-error"
                            required
                        />
                        {errors.captcha && <div id="captcha-error" className="text-danger">{errors.captcha}</div>}
                    </div>
                    {/* Submit button */}
                    <button type="submit" className="btn btn-primary">Register</button>
                    {/* Link to login page */}
                    <p className="mt-3">
                        Already have an account? <a href="login.php" className="text-primary">Login here</a>.
                    </p>
                </form>
            );
        }

        // Render the RegisterForm component
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<RegisterForm />);
    </script>
</body>
</html>