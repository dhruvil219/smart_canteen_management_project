<?php
require_once 'database.php';

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🥙 Adding More Snacks (Category-wise)</h2>';

try {
    $db = (new Database())->getConnection();

    // Ensure Snacks category exists and get its id
    $getCat = $db->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    $getCat->execute(['Snacks']);
    $row = $getCat->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        // Create Snacks category if not present
        $insCat = $db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $insCat->execute(['Snacks', 'Light vegetarian snacks and appetizers']);
        $snacksCatId = (int)$db->lastInsertId();
        echo '<p>Created missing category: <strong>Snacks</strong></p>';
    } else {
        $snacksCatId = (int)$row['id'];
    }

    // New snacks to insert: name, description, price, image_url, is_veg
    $snacks = [
        ['Vada Pav', 'Spicy potato fritter in a bun with chutneys', 35.00, 'images/vada-pav.svg', 1],
        ['Dhokla', 'Steamed fermented gram flour cake, soft and fluffy', 40.00, 'images/dhokla.svg', 1],
        ['Kachori', 'Crispy shell stuffed with spiced lentils', 30.00, 'images/kachori.svg', 1],
        ['Bhel Puri', 'Puffed rice chaat with tamarind and mint chutneys', 45.00, 'images/bhel-puri.svg', 1],
        ['Sev Puri', 'Crisp puris topped with potatoes, chutneys and sev', 45.00, 'images/sev-puri.svg', 1],
        ['Samosa Chaat', 'Crushed samosas with chana, chutneys and yogurt', 55.00, 'images/samosa.jpeg', 1],
        ['Dabeli', 'Kutchi style masala potato in bun with peanuts and pomegranate', 40.00, 'images/dabeli.svg', 1],
        ['Khaman', 'Soft, spongy gram flour squares with tempering', 40.00, 'images/khaman.svg', 1],
        ['Poha', 'Flattened rice cooked with onions, spices and peanuts', 35.00, 'images/poha.svg', 1],
        ['Upma', 'Semolina cooked with vegetables and spices', 35.00, 'images/upma.svg', 1],
    ];

    $insert = $db->prepare('INSERT INTO food_items (name, description, price, category_id, image_url, is_available, is_veg) VALUES (?,?,?,?,?,1,?)');
    $exists = $db->prepare('SELECT id FROM food_items WHERE name = ? LIMIT 1');

    $added = 0; $skipped = 0; $failed = 0;
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:720px">';
    echo '<tr style="background:#f8f9fa"><th align="left">Item</th><th align="left">Category</th><th align="left">Price</th><th align="left">Status</th></tr>';

    foreach ($snacks as $i) {
        [$name, $desc, $price, $img, $isVeg] = $i;

        // Skip if already exists by name
        $exists->execute([$name]);
        if ($exists->fetch()) {
            $skipped++;
            echo "<tr><td>$name</td><td>Snacks</td><td>₹$price</td><td>Skipped (already exists)</td></tr>";
            continue;
        }

        try {
            $insert->execute([$name, $desc, $price, $snacksCatId, $img, $isVeg]);
            $added++;
            $fileStatus = file_exists(__DIR__ . '/' . $img) ? 'Image OK' : 'Image Missing (fallback used)';
            echo "<tr><td>$name</td><td>Snacks</td><td>₹" . number_format($price,2) . "</td><td style='color:#2d7'>Added ✓ ($fileStatus)</td></tr>";
        } catch (Throwable $te) {
            $failed++;
            $msg = htmlspecialchars($te->getMessage());
            echo "<tr><td>$name</td><td>Snacks</td><td>₹$price</td><td style='color:#c33'>Failed: $msg</td></tr>";
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
