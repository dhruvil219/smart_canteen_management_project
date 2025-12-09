<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

// Get user's orders
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
    $orders_stmt = $db->prepare($orders_query);
    $orders_stmt->execute([$_SESSION['user_id']]);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

$current_user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Veg Canteen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1><a href="../index.php" style="color: #27ae60; text-decoration: none;">🥬 Veg Canteen</a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php" class="nav-link">Home</a></li>
                <li><a href="orders.php" class="nav-link active">My Orders</a></li>
                <li><a href="../auth/logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($current_user['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; padding: 2rem 0;">
        <div class="admin-content">
            <h2>My Orders</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
                    <h3>No orders yet</h3>
                    <p>You haven't placed any orders yet. Start by browsing our delicious vegetarian menu!</p>
                    <a href="../index.php" class="btn btn-primary">Browse Menu</a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Get order items count
                            $items_query = "SELECT COUNT(*) as item_count, SUM(quantity) as total_items FROM order_items WHERE order_id = ?";
                            $items_stmt = $db->prepare($items_query);
                            $items_stmt->execute([$order['id']]);
                            $items_info = $items_stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $items_info['total_items']; ?> items</td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view-order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
