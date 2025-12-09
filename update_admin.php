<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Update admin password
    $newPasswordHash = '$2y$10$a2Mu2knddPmVmZPISmLtve.gTDlPNYQDR8II1yWjcBUZSJsycR0Gq';
    $query = "UPDATE users SET password = ? WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$newPasswordHash]);
    
    if ($result) {
        echo "Admin password updated successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin@123\n";
    } else {
        echo "Failed to update password.\n";
    }
    
    // Verify the update
    $query = "SELECT username, password FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\nVerification:\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash: " . $user['password'] . "\n";
        
        // Test password verification
        if (password_verify('admin@123', $user['password'])) {
            echo "Password verification: SUCCESS\n";
        } else {
            echo "Password verification: FAILED\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
