-- Update admin password with correct hash (password: admin@123)
USE canteen_db;

UPDATE users 
SET password = '$2y$10$a2Mu2knddPmVmZPISmLtve.gTDlPNYQDR8II1yWjcBUZSJsycR0Gq' 
WHERE username = 'admin';
