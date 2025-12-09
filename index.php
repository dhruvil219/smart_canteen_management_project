<?php
require_once 'database.php';
require_once 'session.php';

// Get food items and categories
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $categories_query = "SELECT * FROM categories ORDER BY name";
    $categories_stmt = $db->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $food_query = "SELECT f.*, c.name as category_name FROM food_items f 
                   LEFT JOIN categories c ON f.category_id = c.id 
                   WHERE f.is_available = 1 ORDER BY c.name, f.name";
    $food_stmt = $db->prepare($food_query);
    $food_stmt->execute();
    $food_items = $food_stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>PS canteen - Pure Vegetarian Food</title>
    <link rel="stylesheet" href="style.css?v=3">
    <link rel="icon" type="image/svg+xml" href="images/ps-canteen-logo-stylish.svg">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1>
                    <img src="images/ps-canteen-logo-stylish.svg" alt="PS Canteen" />
                </h1>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#menu" class="nav-link">Menu</a></li>
                <?php if ($current_user): ?>
                    <li><a href="orders.php" class="nav-link">My Orders</a></li>
                    <?php if ($current_user['role'] === 'admin' && file_exists(__DIR__ . '/admin/dashboard.php')): ?>
                        <li><a href="admin/dashboard.php" class="nav-link">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($current_user['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="cart-icon" id="cartIcon">
                🛒 <span id="cartCount">0</span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Welcome to Patel &amp; Shah Canteen</h1>
            <p>Delicious, Fresh & 100% Vegetarian Food</p>
            <a href="#menu" class="btn btn-primary">View Menu</a>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="menu-section">
        <div class="container">
            <h2>Our Menu</h2>
            <div class="category-filter">
                <button class="filter-btn active" data-category="all">All Items</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-category="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <div class="menu-grid">
                <?php foreach ($food_items as $item): ?>
                    <div class="menu-item" data-category="<?php echo $item['category_id']; ?>">
                        <div class="menu-item-image">
                            <img src="<?php echo $item['image_url'] ? htmlspecialchars($item['image_url']) : 'images/samosa.jpeg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 onerror="this.onerror=null;this.src='images/samosa.jpeg';">
                            <div class="veg-badge">🟢</div>
                        </div>
                        <div class="menu-item-content">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                            <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-item-footer">
                                <span class="price">₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php if ($current_user): ?>
                                    <button class="btn btn-secondary add-to-cart" 
                                            data-id="<?php echo $item['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-price="<?php echo $item['price']; ?>">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn btn-secondary">Login to Order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Your Cart</h2>
            <div id="cartItems"></div>
            <div class="cart-total">
                <strong>Total: ₹<span id="cartTotal">0.00</span></strong>
            </div>
            <div class="cart-actions">
                <button id="clearCart" class="btn btn-secondary">Clear Cart</button>
                <button id="checkout" class="btn btn-primary">Checkout</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Veg Canteen. All rights reserved. | Pure Vegetarian Food</p>
        </div>
    </footer>

    <!-- Cache-bust to ensure latest JS is loaded -->
    <script src="script.js?v=3"></script>
</body>
</html>
