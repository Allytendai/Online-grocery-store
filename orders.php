<?php
session_start();

// Checks if user is logged in and has the correct role
if (!isset($_SESSION['email'])) {
    // Redirects to login page if not logged in
    header('Location: login.php');
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager') {
    // Redirects managers to manager_orders.php
    header('Location: manager_orders.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Place orders in the online grocery store">
    <!-- keywords for SEO -->
    <meta name="keywords" content="grocery, orders, online shopping">
    <title>Place Orders - Grocery Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
    <div class="container">
        
        <h1>Place Your Order</h1>
        <!-- Displays user info and navigation links -->
        <p>
            Logged in as <?php echo htmlspecialchars($_SESSION['email']); ?> | 
            <a href="index.php" class="btn btn-primary">Browse Products</a> | 
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </p>
        
        <h2>Select Product</h2>
        <!-- Dropdown menu for selecting product category -->
        <select id="category" class="form-select" onchange="loadProducts()" aria-label="Select product category">
            <option value="">Select Category</option>
            <option value="Vegetables">Vegetables</option>
            <option value="Meat">Meat</option>
            <option value="Fruits">Fruits</option>
            <option value="Dairy">Dairy</option>
        </select>
        <!-- Dropdown for selecting products  -->
        <select id="product" class="form-select" disabled aria-label="Select product">
            <option value="">Select Product</option>
        </select>
        <!-- Container for displaying product details -->
        <div id="product-details" class="mt-3"></div>
        <!-- Button to place an orderin whis is initially disabled -->
        <button id="order-btn" class="btn btn-primary mt-2" onclick="placeOrder()" disabled>Place Order</button>
        <!-- Page footer -->
        <footer class="mt-5">
        <p> Sanel Grocery Store. All rights reserved © 2025.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to load products based on selected category
        function loadProducts() {
            // Get DOM elements
            const category = document.getElementById('category').value;
            const productSelect = document.getElementById('product'); 
            const detailsDiv = document.getElementById('product-details');
            const orderBtn = document.getElementById('order-btn');

            // Resets product dropdown and details
            productSelect.disabled = true;
            productSelect.innerHTML = '<option value="">Select Product</option>';
            detailsDiv.innerHTML = '';
            orderBtn.disabled = true;

            // Fetches products if a category is selected
            if (category) {
                fetch(`get_products.php?category=${encodeURIComponent(category)}`)
                    .then(response => {
                        // Checks for HTTP errors
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(response => {
                        if (response.success) {
                            const product = response.data;
                            if (product.length === 0) {
                                detailsDiv.innerHTML = `<p class="text-warning">${response.message || 'No products found for this category.'}</p>`;
                                return;
                            }
                            // Populate product dropdown
                            product.forEach(product => {
                                const option = document.createElement('option');
                                option.value = product.id;
                                option.textContent = product.name;
                                productSelect.appendChild(option);
                            });
                            productSelect.disabled = false;
                        } else {
                            detailsDiv.innerHTML = `<p class="text-danger">${response.message}</p>`;
                        }
                    })
                    .catch(error => {
                        // Displays error for network or fetch issues
                        console.error('Error loading products:', error);
                        detailsDiv.innerHTML = '<p class="text-danger">Failed to load products. Please try again.</p>';
                    });
            }
        }

        // Event listener for product selection changes
        document.getElementById('product').addEventListener('change', function() {
            // Get DOM elements
            const productId = this.value;
            const detailsDiv = document.getElementById('product-details');
            const orderBtn = document.getElementById('order-btn');

            // Reset details and disable order button
            detailsDiv.innerHTML = '';
            orderBtn.disabled = true;

            // Fetch product details if a product is selected
            if (productId) {
                fetch(`get_product_details.php?id=${productId}`)
                    .then(response => {
                        // Check for HTTP errors
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(response => {
                        // Handle server response
                        if (response.success) {
                            const product = response.data;
                            // Display product details
                            detailsDiv.innerHTML = `
                                <h3>${product.name}</h3>
                                ${product.image ? `<img src="${product.image}" alt="${product.name}" class="product-image">` : ''}
                                <p>Price: £${product.price}</p>
                                <p>${product.description}</p>
                            `;
                            orderBtn.disabled = false;
                        } else {
                            detailsDiv.innerHTML = `<p class="text-danger">${response.message}</p>`;
                        }
                    })
                    .catch(error => {
                        // Display error for network or fetch issues
                        console.error('Error loading details:', error);
                        detailsDiv.innerHTML = '<p class="text-danger">Failed to load product details. Please try again.</p>';
                    });
            }
        });

        // Function to handle order placement
        function placeOrder() {
            // Get selected product ID
            const productId = document.getElementById('product').value; 
            const detailsDiv = document.getElementById('product-details');

            // Validate product selection
            if (productId) {
                // Send order request to server
                fetch('submit_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productId })
                })
                    .then(response => {
                        // Check for HTTP errors
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Handle server response
                        if (data.success) {
                            // Clear form and show success message
                            document.getElementById('product').value = '';
                            document.getElementById('category').value = '';
                            document.getElementById('product-details').innerHTML = '<p class="text-success">Order placed successfully!</p>';
                            document.getElementById('order-btn').disabled = true;
                            document.getElementById('product').disabled = true; 
                            document.getElementById('product').innerHTML = '<option value="">Select Product</option>';
                        } else {
                            detailsDiv.innerHTML = `<p class="text-danger">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        // Display error for network or fetch issues
                        console.error('Order error:', error);
                        detailsDiv.innerHTML = '<p class="text-danger">Failed to place order. Please try again.</p>';
                    });
            } else {
                // Display error if no product selected
                detailsDiv.innerHTML = '<p class="text-danger">Please select a product.</p>';
            }
        }
    </script>
</body>
</html>