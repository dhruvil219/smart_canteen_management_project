<?php
require_once 'database.php';

// You can modify these values to update any food item's image
$food_name = 'Masala Chai';  // Change this to the food item you want to update
$new_image = 'images/masala-chai.jpeg';  // Change this to your new image path

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Execute the SQL command using your format
    $query = "UPDATE food_items SET image_url = ? WHERE name = ?";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$new_image, $food_name]);
    
    if ($result) {
        echo "✅ <strong>SUCCESS!</strong><br>";
        echo "Updated: <strong>" . htmlspecialchars($food_name) . "</strong><br>";
        echo "New image: <strong>" . htmlspecialchars($new_image) . "</strong><br><br>";
        
        // Verify the update
        $verify_query = "SELECT name, image_url FROM food_items WHERE name = ?";
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->execute([$food_name]);
        $item = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            echo "📋 <strong>Verification:</strong><br>";
            echo "Name: " . htmlspecialchars($item['name']) . "<br>";
            echo "Image URL: " . htmlspecialchars($item['image_url']) . "<br><br>";
            
            // Check if file exists
            if (file_exists($item['image_url'])) {
                echo "✅ Image file exists!<br>";
            } else {
                echo "❌ Warning: Image file not found<br>";
            }
        }
        
        echo "<br>🌐 <a href='index.php' style='color: #4CAF50; font-weight: bold;'>View Website</a>";
        
    } else {
        echo "❌ Failed to update image for " . htmlspecialchars($food_name);
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage());
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 600px; 
    margin: 50px auto; 
    padding: 20px; 
    background: #f9f9f9; 
}
a { text-decoration: none; }
a:hover { text-decoration: underline; }
</style>

<h2>🖼️ Update Food Image</h2>
<p>To update a different food item, edit the variables at the top of this PHP file:</p>
<pre style="background: #f0f0f0; padding: 10px; border-radius: 5px;">
$food_name = 'Food Name';  // Change this
$new_image = 'images/your-image.jpg';  // Change this
</pre>
