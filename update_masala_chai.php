<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Update Masala Chai image path to use existing file
    $query = "UPDATE food_items SET image_url = 'images/masala-chai.jpeg' WHERE name = 'Masala Chai'";
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Masala Chai image path updated successfully!\n";
        echo "Image path: images/masala-chai.jpeg\n";
    } else {
        echo "Failed to update image path.\n";
    }
    
    // Verify the update
    $query = "SELECT name, image_url FROM food_items WHERE name = 'Masala Chai'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo "\nVerification:\n";
        echo "Item: " . $item['name'] . "\n";
        echo "Image URL: " . $item['image_url'] . "\n";
        
        // Check if file exists
        if (file_exists($item['image_url'])) {
            echo "✅ Image file exists!\n";
        } else {
            echo "❌ Image file not found!\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
