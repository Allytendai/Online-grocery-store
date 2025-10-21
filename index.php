<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Online grocery store">
    <meta name="keywords" content="grocery, shopping, online">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grocery Store - Home</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Sanel Grocery Store</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['email'])): ?>
          <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fa fa-box"></i> Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php"><i class="fa fa-sign-in-alt"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php"><i class="fa fa-user-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">

    <!-- Slide 1 -->
    <div class="carousel-item active">
      <img src="images/hero.png" class="d-block w-100" alt="Fresh groceries">
      <div class="carousel-caption d-none d-md-block">
        <h1>Welcome to Sanel Grocery Store</h1>
        <p>Fresh produce, delivered to your door</p>
        <a href="products.php" class="btn btn-success btn-lg">Shop Now</a>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="carousel-item">
      <img src="images/hero1.png" class="d-block w-100" alt="Healthy choices">
      <div class="carousel-caption d-none d-md-block">
        <h1>Eat Fresh, Stay Healthy</h1>
        <p>Quality meat, fruits, and dairy for your family</p>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="carousel-item">
      <img src="images/hero3.png" class="d-block w-100" alt="Fast delivery">
      <div class="carousel-caption d-none d-md-block">
        <h1>Fast & Reliable Delivery</h1>
        <p>Shop online and get groceries delivered quickly</p>
      </div>
    </div>
      
      <!-- Slide 4 -->
    <div class="carousel-item">
      <img src="images/hero2.png" class="d-block w-100" alt="Fast delivery">
      <div class="carousel-caption d-none d-md-block">
        <h1>Fast & Reliable Delivery</h1>
        <p>Shop online and get groceries delivered quickly</p>
      </div>
    </div>

    </div>

  <!-- Controls -->
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>

  <!-- Indicators (dots at bottom) -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
  </div>
</div>


<!-- Main Content -->
<div class="container">
    <div class="card p-4">
        <h2 class="mb-4 text-center">Quick Order</h2>

        <!-- Category Select -->
        <div class="mb-3">
            <label for="category" class="form-label fw-bold">Select Category</label>
            <select id="category" class="form-select" onchange="loadProducts()" aria-label="Select product category">
                <option value="">Choose Category...</option>
                <option value="Vegetables">Vegetables</option>
                <option value="Meat">Meat</option>
                <option value="Fruits">Fruits</option>
                <option value="Dairy">Dairy</option>
            </select>
        </div>

        <!-- Product Select -->
        <div class="mb-3">
            <label for="product" class="form-label fw-bold">Select Product</label>
            <select id="product" class="form-select" disabled aria-label="Select product">
                <option value="">Choose Product...</option>
            </select>
        </div>

        <!-- Product Details -->
        <div id="product-details" class="text-center my-3"></div>

        <!-- Order Button -->
        <div class="d-grid">
            <button id="order-btn" class="btn btn-primary btn-lg" onclick="placeOrder()" disabled>
                <i class="fa fa-check-circle"></i> Place Order
            </button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center">
    <div class="container">
        <p class="mb-0">Sanel Grocery Store. All rights reserved © <?php echo date("Y"); ?>.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Load products based on category
    function loadProducts() {
        const category = document.getElementById('category').value;
        const productSelect = document.getElementById('product');
        const detailsDiv = document.getElementById('product-details');
        const orderBtn = document.getElementById('order-btn');

        productSelect.disabled = true;
        productSelect.innerHTML = '<option value="">Choose Product...</option>';
        detailsDiv.innerHTML = '';
        orderBtn.disabled = true;

        if (category) {
            fetch(`get_products.php?category=${encodeURIComponent(category)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const products = data.data;
                        if (products.length === 0) {
                            detailsDiv.innerHTML = '<p class="text-warning">No products found.</p>';
                            return;
                        }
                        products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = product.name;
                            productSelect.appendChild(option);
                        });
                        productSelect.disabled = false;
                    } else {
                        detailsDiv.innerHTML = `<p class="text-danger">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailsDiv.innerHTML = '<p class="text-danger">Failed to load products.</p>';
                });
        }
    }

    // Show product details
    document.getElementById('product').addEventListener('change', function() {
        const productId = this.value;
        const detailsDiv = document.getElementById('product-details');
        const orderBtn = document.getElementById('order-btn');

        detailsDiv.innerHTML = '';
        orderBtn.disabled = true;

        if (productId) {
            fetch(`get_product_details.php?id=${productId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(response => {
                    if (response.success) {
                        const product = response.data;
                        detailsDiv.innerHTML = `
                            <h3>${product.name}</h3>
                            ${product.image ? `<img src="${product.image}" alt="${product.name}" class="product-image">` : ''}
                            <p class="fw-bold">£${product.price}</p>
                            <p>${product.description}</p>
                        `;
                        orderBtn.disabled = false;
                    } else {
                        detailsDiv.innerHTML = `<p class="text-danger">${response.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailsDiv.innerHTML = '<p class="text-danger">Failed to load product details.</p>';
                });
        }
    });

    // Place order
    function placeOrder() {
        <?php if (!isset($_SESSION['email'])): ?>
            window.location.href = 'login.php';
        <?php else: ?>
            const productId = document.getElementById('product').value;
            const detailsDiv = document.getElementById('product-details');

            if (productId) {
                fetch('submit_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ productId })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Order placed successfully!');
                        window.location.href = 'orders.php';
                    } else {
                        alert(`Order failed: ${data.message}`);
                        detailsDiv.innerHTML += `<p class="text-danger">${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Order failed. Please try again.');
                    detailsDiv.innerHTML += '<p class="text-danger">Failed to place order.</p>';
                });
            } else {
                alert('Please select a product.');
                detailsDiv.innerHTML = '<p class="text-danger">Please select a product.</p>';
            }
        <?php endif; ?>
    }
</script>
</body>
</html>
