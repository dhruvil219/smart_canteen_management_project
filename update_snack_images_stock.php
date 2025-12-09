<?php
require_once 'database.php';

/*
This script sets high-quality stock image URLs (Unsplash) for 9 snack items.
It fuzzy-matches item names to update food_items.image_url accordingly.
Usage:
  - Open http://localhost/SGP_project/update_snack_images_stock.php
  - Then hard refresh your menu page.
*/

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🖼️ Set Stock Images for Snack Items</h2>';

echo '<p>This will update the following items with curated stock photos:</p>';

echo '<ul>';
echo '<li>Bhel Puri</li>';
echo '<li>Dabeli</li>';
echo '<li>Dhokla</li>';
echo '<li>Kachori</li>';
echo '<li>Khaman</li>';
echo '<li>Poha</li>';
echo '<li>Sev Puri</li>';
echo '<li>Upma</li>';
echo '<li>Vada Pav</li>';
echo '</ul>';

// Curated Unsplash image URLs (royalty-free, hotlinkable). You can replace with your own later.
$map = [
  'Bhel Puri' => 'https://images.unsplash.com/photo-1601050690597-9b23a2b75f14?auto=format&fit=crop&w=1200&q=80',
  'Dabeli'    => 'https://images.unsplash.com/photo-1617692855026-82ae1fdddb8b?auto=format&fit=crop&w=1200&q=80',
  'Dhokla'    => 'https://images.unsplash.com/photo-1599487488170-d11ec9c61239?auto=format&fit=crop&w=1200&q=80',
  'Kachori'   => 'https://images.unsplash.com/photo-1630699146486-89bf3f6b80de?auto=format&fit=crop&w=1200&q=80',
  'Khaman'    => 'https://images.unsplash.com/photo-1540306286-7f1b9b64d0f8?auto=format&fit=crop&w=1200&q=80',
  'Poha'      => 'https://images.unsplash.com/photo-1622227922684-90ff5a8f26ef?auto=format&fit=crop&w=1200&q=80',
  'Sev Puri'  => 'https://images.unsplash.com/photo-1625944524300-2a39b2639fb3?auto=format&fit=crop&w=1200&q=80',
  'Upma'      => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80',
  'Vada Pav'  => 'https://images.unsplash.com/photo-1601050690393-46efdead7fca?auto=format&fit=crop&w=1200&q=80',
];

try {
  $db = (new Database())->getConnection();
  $upd = $db->prepare('UPDATE food_items SET image_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');

  // Build normalized lookup of existing items
  $rows = $db->query('SELECT id, name FROM food_items')->fetchAll(PDO::FETCH_ASSOC);
  $norm = [];
  foreach ($rows as $r) {
    $k = strtolower(trim($r['name']));
    $k = str_replace([' ', '-', '&', 'and', '.'], '', $k);
    $norm[$k] = $r;
  }

  $normalize = function($s) {
    $s = strtolower(trim($s));
    return str_replace([' ', '-', '&', 'and', '.'], '', $s);
  };

  echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:900px">';
  echo '<tr style="background:#f8f9fa"><th align="left">Requested Item</th><th align="left">Matched DB Name</th><th align="left">New Image URL</th><th align="left">Result</th></tr>';

  foreach ($map as $name => $url) {
    $match = $norm[$normalize($name)] ?? null;
    if (!$match) {
      echo "<tr><td>$name</td><td>(none)</td><td>$url</td><td style='color:#c33'>Item not found</td></tr>";
      continue;
    }
    $id = (int)$match['id'];
    $dbName = htmlspecialchars($match['name']);

    try {
      $upd->execute([$url, $id]);
      echo "<tr><td>$name</td><td>$dbName</td><td><a href='$url' target='_blank' rel='noopener'>$url</a></td><td style='color:#2d7'>Updated ✓</td></tr>";
    } catch (Throwable $t) {
      $msg = htmlspecialchars($t->getMessage());
      echo "<tr><td>$name</td><td>$dbName</td><td>$url</td><td style='color:#c33'>Failed: $msg</td></tr>";
    }
  }

  echo '</table>';
  echo "<p>➡️ <a href='inspect_menu_images.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Inspect menu images</a></p>";
  echo "<p>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Throwable $t) {
  echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
  echo 'Error: ' . htmlspecialchars($t->getMessage());
  echo '</div>';
}
