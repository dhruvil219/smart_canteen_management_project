<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    header('Location: orders.php');
    exit();
}

// Get order details
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verify order belongs to current user
    $order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: orders.php');
        exit();
    }
    
    // Get order items
    $items_query = "SELECT oi.*, f.name, f.description FROM order_items oi 
                    JOIN food_items f ON oi.food_item_id = f.id 
                    WHERE oi.order_id = ?";
    $items_stmt = $db->prepare($items_query);
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Order #<?php echo $order['id']; ?> Details - Veg Canteen</title>
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
                <li><a href="orders.php" class="nav-link">My Orders</a></li>
                <li><a href="../auth/logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($current_user['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; padding: 2rem 0;">
        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Order #<?php echo $order['id']; ?> Details</h2>
                <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
            
            <!-- Order Status Timeline -->
            <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3>Order Status</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; margin: 1rem 0;">
                    <div class="status-step <?php echo in_array($order['status'], ['pending', 'confirmed', 'preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                        <div class="status-icon">📝</div>
                        <div>Pending</div>
                    </div>
                    <div class="status-step <?php echo in_array($order['status'], ['confirmed', 'preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                        <div class="status-icon">✅</div>
                        <div>Confirmed</div>
                    </div>
                    <div class="status-step <?php echo in_array($order['status'], ['preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                        <div class="status-icon">👨‍🍳</div>
                        <div>Preparing</div>
                    </div>
                    <div class="status-step <?php echo in_array($order['status'], ['ready', 'delivered']) ? 'active' : ''; ?>">
                        <div class="status-icon">🔔</div>
                        <div>Ready</div>
                    </div>
                    <div class="status-step <?php echo $order['status'] === 'delivered' ? 'active' : ''; ?>">
                        <div class="status-icon">🚚</div>
                        <div>Delivered</div>
                    </div>
                </div>
                <p><strong>Current Status:</strong> <span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
            </div>
            
            <!-- Order Information -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3>Order Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Order Date:</strong><br>
                        <?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?>
                    </div>
                    <div>
                        <strong>Total Amount:</strong><br>
                        ₹<?php echo number_format($order['total_amount'], 2); ?>
                    </div>
                    <div>
                        <strong>Payment Method:</strong><br>
                        Cash on Delivery
                    </div>
                </div>
                <?php if ($order['notes']): ?>
                    <div style="margin-top: 1rem;">
                        <strong>Special Instructions:</strong><br>
                        <?php echo htmlspecialchars($order['notes']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Items -->
            <h3>Ordered Items</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?> 🟢</td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="4">Total Amount:</td>
                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <?php if ($order['status'] === 'delivered'): ?>
                <div style="text-align: center; margin-top: 2rem; padding: 2rem; background: #d4edda; border-radius: 10px; color: #155724;">
                    <h3>✅ Order Delivered Successfully!</h3>
                    <p>Thank you for choosing Veg Canteen. We hope you enjoyed your meal!</p>
                </div>
            <?php elseif ($order['status'] === 'ready'): ?>
                <div style="text-align: center; margin-top: 2rem; padding: 2rem; background: #fff3cd; border-radius: 10px; color: #856404;">
                    <h3>🔔 Your Order is Ready!</h3>
                    <p>Please collect your order from the canteen counter.</p>
                </div>
            <?php elseif ($order['status'] === 'preparing'): ?>
                <div style="text-align: center; margin-top: 2rem; padding: 2rem; background: #e2e3e5; border-radius: 10px; color: #383d41;">
                    <h3>👨‍🍳 Your Order is Being Prepared</h3>
                    <p>Our chefs are working on your delicious vegetarian meal. Please wait a moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .status-step {
            text-align: center;
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        .status-step.active {
            opacity: 1;
        }
        .status-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
    </style>
</body>
</html>
