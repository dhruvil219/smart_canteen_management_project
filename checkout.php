<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

requireLogin();

$message = '';
$error = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $cart_data = json_decode($_POST['cart_data'], true);
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'];
        
        if (empty($cart_data)) {
            $error = 'Your cart is empty!';
        } else {
            // Calculate total
            $total_amount = 0;
            foreach ($cart_data as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            // Start transaction
            $db->beginTransaction();
            
            // Insert order
            $order_query = "INSERT INTO orders (user_id, total_amount, notes) VALUES (?, ?, ?)";
            $order_stmt = $db->prepare($order_query);
            $order_stmt->execute([$user_id, $total_amount, $notes]);
            $order_id = $db->lastInsertId();
            
            // Insert order items
            $item_query = "INSERT INTO order_items (order_id, food_item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $db->prepare($item_query);
            
            foreach ($cart_data as $item) {
                $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            }
            
            $db->commit();
            
            // Redirect to order confirmation
            header("Location: order-confirmation.php?order_id=" . $order_id);
            exit();
        }
    } catch (PDOException $e) {
        $db->rollback();
        $error = 'Order failed: ' . $e->getMessage();
    }
}

// Get cart data
$cart_data = [];
if (isset($_POST['cart_data'])) {
    $cart_data = json_decode($_POST['cart_data'], true);
}

$current_user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Veg Canteen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1>
                    <a href="index.php" style="text-decoration: none;">
                        <img src="images/ps-canteen-logo-stylish.svg" alt="PS Canteen" />
                    </a>
                </h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="orders.php" class="nav-link">My Orders</a></li>
                <li><a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($current_user['username']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; padding: 2rem 0;">
        <div class="admin-content">
            <h2>Checkout</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($cart_data)): ?>
                <div class="alert alert-error">Your cart is empty! <a href="index.php">Go back to menu</a></div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <!-- Order Summary -->
                    <div>
                        <h3>Order Summary</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach ($cart_data as $item): 
                                    $item_total = $item['price'] * $item['quantity'];
                                    $total += $item_total;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₹<?php echo number_format($item_total, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background: #f8f9fa;">
                                    <td colspan="3">Total Amount:</td>
                                    <td>₹<?php echo number_format($total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Order Form -->
                    <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px;">
                        <h3>Order Details</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="cart_data" value="<?php echo htmlspecialchars(json_encode($cart_data)); ?>">
                            
                            <div class="form-group">
                                <label for="customer_name">Customer Name</label>
                                <input type="text" id="customer_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" readonly style="background: #e9ecef;">
                            </div>
                            
                            <div class="form-group">
                                <label for="notes">Special Instructions (Optional)</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Any special requests or dietary requirements..."></textarea>
                            </div>
                            
                            <div style="background: white; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                                <h4>Order Total: ₹<?php echo number_format($total, 2); ?></h4>
                                <p style="color: #666; font-size: 0.9rem;">Payment: Cash on Delivery</p>
                            </div>
                            
                            <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%;">
                                Place Order
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</body>
</html>
