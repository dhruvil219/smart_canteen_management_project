<?php
require_once 'database.php';

header('Content-Type: text/html; charset=UTF-8');

$targets = [
  'Bhel Puri', 'Dabeli', 'Dhokla', 'Kachori', 'Khaman', 'Poha', 'Sev Puri', 'Upma', 'Vada Pav'
];

echo '<h2>🔎 Inspect Snack Images</h2>';
echo '<p>This page shows DB image_url, whether the file exists on disk, and a live preview.</p>';

try {
  $db = (new Database())->getConnection();
  $sel = $db->prepare('SELECT id, name, image_url FROM food_items WHERE name = ? LIMIT 1');

  echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:900px">';
  echo '<tr style="background:#f8f9fa"><th align="left">Name</th><th align="left">image_url</th><th align="left">On Disk?</th><th align="left">Preview</th></tr>';

  foreach ($targets as $name) {
    $sel->execute([$name]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      echo "<tr><td>$name</td><td colspan='3' style='color:#c33'>Item not found in food_items</td></tr>";
      continue;
    }
    $url = (string)$row['image_url'];
    $exists = $url ? file_exists(__DIR__ . '/' . $url) : false;
    $existsText = $exists ? '<span style="color:#2d7">Yes</span>' : '<span style="color:#c33">No</span>';
    $safeUrl = htmlspecialchars($url ?: '(empty)');

    echo '<tr>';
    echo '<td>' . htmlspecialchars($name) . '</td>';
    echo '<td>' . $safeUrl . '</td>';
    echo '<td>' . $existsText . '</td>';
    echo '<td style="background:#fff">' . ($url ? '<img src="' . $safeUrl . '" alt="preview" style="width:160px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #eee" />' : '-') . '</td>';
    echo '</tr>';
  }

  echo '</table>';
  echo "<p>➡️ <a href='update_snack_images.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Run image updater</a></p>";
  echo "<p>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Throwable $t) {
  echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
  echo 'Error: ' . htmlspecialchars($t->getMessage());
  echo '</div>';
}
