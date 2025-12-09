<?php
// feedback_submit.php
// Handles AJAX submissions of order feedback

header('Content-Type: application/json');

require_once __DIR__ . '/database.php';
session_start();

$response = [ 'success' => false, 'message' => '' ];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = $_POST;
    // If request is JSON, parse raw body
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }

    $rating   = isset($input['rating']) ? (int)$input['rating'] : 0;
    $comments = trim($input['comments'] ?? '');
    $name     = trim($input['name'] ?? '');
    $email    = trim($input['email'] ?? '');
    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : null;

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Please provide a rating between 1 and 5 stars.');
    }

    // Optional: associate feedback to logged-in user
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    // Save to DB
    $db = (new Database())->getConnection();

    // Ensure feedback table exists (idempotent)
    $db->exec("CREATE TABLE IF NOT EXISTS feedback (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NULL,
        user_id INT NULL,
        rating TINYINT NOT NULL,
        comments TEXT,
        name VARCHAR(100) NULL,
        email VARCHAR(150) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $db->prepare("INSERT INTO feedback (order_id, user_id, rating, comments, name, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $order_id ?: null,
        $user_id ?: null,
        $rating,
        $comments !== '' ? $comments : null,
        $name !== '' ? $name : null,
        $email !== '' ? $email : null,
    ]);

    $response['success'] = true;
    $response['message'] = 'Thank you for your feedback!';
} catch (Throwable $e) {
    http_response_code(400);
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
