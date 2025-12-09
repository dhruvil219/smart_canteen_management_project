<?php
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Array of food items and their correct image paths based on existing files
    $updates = [
        'Dal Tadka' => 'images/daltadka.jpg',
        'Gulab Jamun' => 'images/Gulab-Jamun-Creative_img.webp',
        'Paneer Butter Masala' => 'images/paneerbuttermasala.jpg',
        'Vegetable Biryani' => 'images/Vegetable-Biryani.webp',
        'Samosa' => 'images/samosa.jpeg',
        'Masala Chai' => 'images/masala-chai.jpeg'
    ];
    
    echo "<h2>🍽️ Updating All Food Images</h2>";
    echo "<hr>";
    
    $success_count = 0;
    $total_count = count($updates);
    
    foreach ($updates as $food_name => $image_path) {
        // Update each food item
        $query = "UPDATE food_items SET image_url = ? WHERE name = ?";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([$image_path, $food_name]);
        
        if ($result) {
            echo "✅ <strong>$food_name</strong> → $image_path<br>";
            
            // Check if file exists
            if (file_exists($image_path)) {
                echo "&nbsp;&nbsp;&nbsp;📁 File exists<br>";
            } else {
                echo "&nbsp;&nbsp;&nbsp;❌ File not found<br>";
            }
            $success_count++;
        } else {
            echo "❌ Failed to update <strong>$food_name</strong><br>";
        }
        echo "<br>";
    }
    
    echo "<hr>";
    echo "<h3>📊 Summary</h3>";
    echo "Updated: $success_count / $total_count items<br><br>";
    
    // Verify all updates
    echo "<h3>🔍 Verification</h3>";
    $verify_query = "SELECT name, image_url FROM food_items WHERE name IN ('" . implode("', '", array_keys($updates)) . "')";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute();
    $items = $verify_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Food Item</th><th>Image Path</th><th>File Status</th></tr>";
    
    foreach ($items as $item) {
        $file_status = file_exists($item['image_url']) ? "✅ Exists" : "❌ Missing";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['name']) . "</td>";
        echo "<td>" . htmlspecialchars($item['image_url']) . "</td>";
        echo "<td>$file_status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><br>";
    echo "🌐 <a href='index.php' style='color: #4CAF50; font-weight: bold; text-decoration: none;'>View Your Updated Website</a>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . htmlspecialchars($e->getMessage());
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 800px; 
    margin: 20px auto; 
    padding: 20px; 
    background: #f9f9f9; 
}
table { 
    margin: 10px 0; 
}
th, td { 
    padding: 8px 12px; 
    text-align: left; 
}
th { 
    background: #4CAF50; 
    color: white; 
}
tr:nth-child(even) { 
    background: #f2f2f2; 
}
a:hover { 
    text-decoration: underline !important; 
}
</style>
