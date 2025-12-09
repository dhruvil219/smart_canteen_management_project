<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Update Fresh Lime Water image path
    $query = "UPDATE food_items SET image_url = 'images/lime-water.jpg.webp' WHERE name = 'Fresh Lime Water'";
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Fresh Lime Water image path updated successfully!\n";
        echo "Image path: images/lime-water.jpg.webp\n";
    } else {
        echo "Failed to update image path.\n";
    }
    
    // Verify the update
    $query = "SELECT name, image_url FROM food_items WHERE name = 'Fresh Lime Water'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo "\nVerification:\n";
        echo "Item: " . $item['name'] . "\n";
        echo "Image URL: " . $item['image_url'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
