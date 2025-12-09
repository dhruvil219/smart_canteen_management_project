<?php
require_once 'database.php';

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🍽️ Adding 10 More Menu Items</h2>';

try {
    $db = (new Database())->getConnection();

    // Helper: get category id by name
    $getCat = $db->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');

    $categories = [
        'Main Course' => null,
        'Snacks' => null,
        'Beverages' => null,
        'Desserts' => null,
    ];

    foreach ($categories as $name => $_) {
        $getCat->execute([$name]);
        $row = $getCat->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new Exception("Category '$name' not found. Please ensure schema.sql was imported.");
        }
        $categories[$name] = (int)$row['id'];
    }

    // 10 items to add (name, description, price, category_name, image_url, is_veg)
    $items = [
        // Main Course (3)
        ['Chole Bhature', 'Spiced chickpeas served with fluffy fried bread', 130.00, 'Main Course', 'images/paneerbuttermasala.jpg', 1],
        ['Aloo Paratha', 'Stuffed whole wheat flatbread with spiced potatoes, served with curd', 90.00, 'Main Course', 'images/Vegetable-Biryani.webp', 1],
        ['Veg Pulao', 'Fragrant basmati rice cooked with mixed vegetables', 110.00, 'Main Course', 'images/Vegetable-Biryani.webp', 1],

        // Snacks (3)
        ['Pav Bhaji', 'Buttery mashed vegetable curry served with toasted buns', 85.00, 'Snacks', 'images/samosa.jpeg', 1],
        ['Paneer Tikka', 'Marinated cottage cheese cubes grilled to perfection', 150.00, 'Snacks', 'images/paneerbuttermasala.jpg', 1],
        ['Idli Sambar', 'Steamed rice cakes served with tangy lentil stew', 70.00, 'Snacks', 'images/daltadka.jpg', 1],

        // Beverages (2)
        ['Mango Lassi', 'Sweet mango yogurt smoothie', 60.00, 'Beverages', 'images/masala-chai.jpeg', 1],
        ['Cold Coffee', 'Chilled coffee with milk and a hint of cocoa', 75.00, 'Beverages', 'images/masala-chai.jpeg', 1],

        // Desserts (2)
        ['Rasgulla', 'Soft and spongy cottage cheese dumplings in light syrup', 45.00, 'Desserts', 'images/Gulab-Jamun-Creative_img.webp', 1],
        ['Kheer', 'Traditional rice pudding flavored with cardamom and nuts', 50.00, 'Desserts', 'images/Gulab-Jamun-Creative_img.webp', 1],
    ];

    $insert = $db->prepare('INSERT INTO food_items (name, description, price, category_id, image_url, is_available, is_veg) VALUES (?,?,?,?,?,1,?)');
    $exists = $db->prepare('SELECT id FROM food_items WHERE name = ? LIMIT 1');

    $added = 0; $skipped = 0; $failed = 0;
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:720px">';
    echo '<tr style="background:#f8f9fa"><th align="left">Item</th><th align="left">Category</th><th align="left">Price</th><th align="left">Status</th></tr>';

    foreach ($items as $i) {
        [$name, $desc, $price, $catName, $img, $isVeg] = $i;
        $catId = $categories[$catName] ?? null;
        if (!$catId) { $failed++; echo "<tr><td>$name</td><td>$catName</td><td>₹$price</td><td style='color:#c33'>Category missing</td></tr>"; continue; }

        // Skip if already exists by name
        $exists->execute([$name]);
        if ($exists->fetch()) {
            $skipped++;
            echo "<tr><td>$name</td><td>$catName</td><td>₹$price</td><td>Skipped (already exists)</td></tr>";
            continue;
        }

        try {
            $insert->execute([$name, $desc, $price, $catId, $img, $isVeg]);
            $added++;
            $fileStatus = file_exists(__DIR__ . '/' . $img) ? 'Image OK' : 'Image Missing (fallback used)';
            echo "<tr><td>$name</td><td>$catName</td><td>₹" . number_format($price,2) . "</td><td style='color:#2d7'>Added ✓ ($fileStatus)</td></tr>";
        } catch (Throwable $te) {
            $failed++;
            $msg = htmlspecialchars($te->getMessage());
            echo "<tr><td>$name</td><td>$catName</td><td>₹$price</td><td style='color:#c33'>Failed: $msg</td></tr>";
        }
    }

    echo '</table>';
    echo "<p><strong>Summary:</strong> Added: $added, Skipped: $skipped, Failed: $failed</p>";
    echo "<p>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Exception $e) {
    echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
