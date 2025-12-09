<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Execute the SQL command
    $query = "UPDATE food_items SET image_url = 'images/masala-chai.jpeg' WHERE name = 'Masala Chai'";
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ SUCCESS: Masala Chai image updated!<br>";
        echo "📁 New image path: images/masala-chai.jpeg<br><br>";
        
        // Verify the change
        $verify_query = "SELECT name, image_url FROM food_items WHERE name = 'Masala Chai'";
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->execute();
        $item = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            echo "📋 <strong>Verification:</strong><br>";
            echo "Name: " . htmlspecialchars($item['name']) . "<br>";
            echo "Image URL: " . htmlspecialchars($item['image_url']) . "<br><br>";
            
            // Check if file exists
            if (file_exists($item['image_url'])) {
                echo "✅ Image file exists and is ready to display!<br>";
                echo "🌐 <a href='index.php'>View your website now</a>";
            } else {
                echo "❌ Warning: Image file not found at " . htmlspecialchars($item['image_url']);
            }
        }
    } else {
        echo "❌ Failed to update masala chai image.";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage());
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f9f9f9; }
a { color: #4CAF50; text-decoration: none; font-weight: bold; }
a:hover { text-decoration: underline; }
</style>
