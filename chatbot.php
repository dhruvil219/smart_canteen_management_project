<?php
// chatbot.php - Backend proxy to OpenAI API
header('Content-Type: application/json');
header('Cache-Control: no-store');

// Load config with API key
$configPath = __DIR__ . '/config.php';
// basic logger
function chatbot_log($msg) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    @file_put_contents($logDir . '/chatbot.log', $line, FILE_APPEND);
}
if (!file_exists($configPath)) {
    http_response_code(500);
    chatbot_log('config.php missing');
    echo json_encode(['error' => 'Missing config.php. Copy config.example.php to config.php and set OPENAI_API_KEY.']);
    exit;
}
require_once $configPath;

if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) {
    http_response_code(500);
    chatbot_log('OPENAI_API_KEY not configured');
    echo json_encode(['error' => 'OPENAI_API_KEY not configured.']);
    exit;
}

// Health check
if (isset($_GET['health'])) {
    echo json_encode(['ok' => true, 'model' => (defined('OPENAI_MODEL') && OPENAI_MODEL ? OPENAI_MODEL : 'gpt-4o-mini')]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$messages = isset($data['messages']) && is_array($data['messages']) ? $data['messages'] : [];
$userMeta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];

// Safety: Cap messages length
$messages = array_slice($messages, -20);

// System prompt specialized for your canteen site
$systemPrompt = "You are PS Canteen's helpful AI assistant. Help visitors with this website: answer questions about menu items, categories, pricing, our pure-veg policy, opening hours, ordering steps, and general site navigation. Keep answers concise, friendly, and accurate. If unsure, ask a clarifying question. Do not fabricate unavailable items.";

// Build payload
$model = defined('OPENAI_MODEL') && OPENAI_MODEL ? OPENAI_MODEL : 'gpt-4o-mini';
$payload = [
    'model' => $model,
    'messages' => array_merge([
        [ 'role' => 'system', 'content' => $systemPrompt ]
    ], $messages),
    'temperature' => 0.3,
    'max_tokens' => 300,
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . OPENAI_API_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
if ($response === false) {
    http_response_code(500);
    $err = curl_error($ch);
    chatbot_log('cURL error: ' . $err);
    echo json_encode(['error' => 'Upstream request failed. Please check server logs.']);
    curl_close($ch);
    exit;
}
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status < 200 || $status >= 300) {
    http_response_code($status);
    chatbot_log('OpenAI non-2xx response: ' . $response);
    echo $response;
    exit;
}

$out = json_decode($response, true);
if (!isset($out['choices'][0]['message']['content'])) {
    chatbot_log('Unexpected API response: ' . $response);
    echo json_encode(['error' => 'Unexpected API response', 'raw' => $out]);
    exit;
}

$reply = $out['choices'][0]['message']['content'];

echo json_encode([
    'reply' => $reply,
]);
