<?php
// Starts a PHP session to verify user authentication
session_start();

// Checks if user is logged in
if (!isset($_SESSION['email'])) {
    // Redirects to login page if not logged in
    header('Location: login.php');
    exit;
}

// Checks if user is a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    // Redirect non-managers to orders.php
    header('Location: orders.php');
    exit;
}

$servername = "servername.hosting-data.io"; 
$username   = "username";                     
$password   = "password";                     
$dbname     = "dbname"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    $error_message = 'Unable to connect to the database. Please try again later.';
} else {
    // Fetches all orders with product names using a JOIN
    $stmt = $conn->prepare("
        SELECT o.order_id, o.email, o.product_id, p.name AS product_name, o.order_date 
        FROM orders o
        JOIN products p ON o.product_id = p.id
        ORDER BY o.order_date DESC
    ");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        // Log error and set error message
        error_log('Query preparation failed: ' . $conn->error);
        $error_message = 'Failed to retrieve orders. Please try again.';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="View all orders in the online grocery store">
    <meta name="keywords" content="grocery, orders, manager, online shopping">
    <title>Manager Orders - Grocery Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        
        <h1>Manager Order Dashboard</h1>
        <!-- Displays user info and navigation links -->
        <p>
            Logged in as <?php echo htmlspecialchars($_SESSION['email']); ?> | 
            <a href="index.php" class="btn btn-primary">Browse Products</a> | 
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </p>
        
        <h2>All Orders</h2>
        <?php if (isset($error_message)): ?>
            <!-- Displays error message if database query fails -->
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php elseif (empty($orders)): ?>
            <!-- Displays message if no orders exist -->
            <p class="text-danger">No orders found.</p>
        <?php else: ?>
            <!-- Table to display orders -->
            <table class="table table-striped table-bordered" aria-describedby="orders-table">
                <thead>
                    <tr>
                        <th scope="col">Order ID</th>
                        <th scope="col">Customer Email</th>
                        <th scope="col">Product</th>
                        <th scope="col">Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <footer class="mt-5">
            <p>Sanel Grocery Store. All rights reserved © 2025.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>