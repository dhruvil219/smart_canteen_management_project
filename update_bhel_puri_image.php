<?php
require_once 'database.php';

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🖼️ Update Bhel Puri Image</h2>';

$nameCandidates = [
  'Bhel Puri', 'Bhelpuri', 'Bhel-Puri'
];
$newPathRelative = 'images/bhelpuri.jpg';
$newPathAbsolute = '/SGP_project/images/bhelpuri.jpg';

try {
  $db = (new Database())->getConnection();
  $sel = $db->prepare('SELECT id, name FROM food_items WHERE name = ? LIMIT 1');
  $upd = $db->prepare('UPDATE food_items SET image_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');

  $found = null;
  foreach ($nameCandidates as $n) {
    $sel->execute([$n]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);
    if ($row) { $found = $row; break; }
  }

  if (!$found) {
    echo '<p style="color:#c33">Bhel Puri item not found by expected names. Please check the exact name in your DB or run inspect_menu_images.php.</p>';
    echo "<p>➡️ <a href='inspect_menu_images.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Inspect menu images</a></p>";
    exit;
  }

  $id = (int)$found['id'];

  // Set to the requested absolute path explicitly
  $usePath = $newPathAbsolute; // '/SGP_project/images/bhelpuri.jpg'
  $upd->execute([$usePath, $id]);

  echo '<p><strong>Matched item:</strong> ' . htmlspecialchars($found['name']) . '</p>';
  echo '<p><strong>Set image_url to:</strong> ' . htmlspecialchars($usePath) . '</p>';
  $exists = file_exists(__DIR__ . '/' . ltrim($usePath, '/')) ? 'Yes' : 'No';
  echo '<p><strong>File exists on disk:</strong> ' . $exists . '</p>';
  echo '<div style="padding:10px;background:#fff;border:1px solid #eee;display:inline-block;border-radius:6px">';
  echo '<img src="' . htmlspecialchars($usePath) . '" alt="preview" style="width:240px;height:150px;object-fit:cover;border-radius:6px" />';
  echo '</div>';

  echo "<p style='margin-top:12px'>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Throwable $t) {
  echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
  echo 'Error: ' . htmlspecialchars($t->getMessage());
  echo '</div>';
}
