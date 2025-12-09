<?php
require_once 'database.php';

/*
Set snack images to files from your local images/ folder.
Usage:
  - Open http://localhost/SGP_project/update_snack_images_local.php
Then hard refresh your menu page.
*/

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🖼️ Set Local Images for Snack Items</h2>';

// Map menu item names -> actual files currently present in images/
// Using absolute web paths to match your Bhel Puri setting
$map = [
  'Bhel Puri'   => '/SGP_project/images/bhelpuri.jpg',
  'Dabeli'      => '/SGP_project/images/dabeli.jpeg',
  'Dhokla'      => '/SGP_project/images/dhokla.jpg',
  'Kachori'     => '/SGP_project/images/kachori.jpg',
  'Khaman'      => '/SGP_project/images/khaman.jpeg',
  'Poha'        => '/SGP_project/images/poha.jpg',
  'Samosa Chaat'=> '/SGP_project/images/samosa chaat.jpg',
  'Sev Puri'    => '/SGP_project/images/Sev-puri.jpg',
  'Upma'        => '/SGP_project/images/upma.jpg',
  'Vada Pav'    => '/SGP_project/images/vada-pav.jpeg',
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

  echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:1000px">';
  echo '<tr style="background:#f8f9fa"><th align="left">Requested Item</th><th align="left">Matched DB Name</th><th align="left">New Image Path</th><th align="left">On Disk?</th><th align="left">Result</th></tr>';

  foreach ($map as $name => $path) {
    $match = $norm[$normalize($name)] ?? null;
    $diskPath = __DIR__ . '/' . ltrim($path, '/');
    $exists = file_exists($diskPath);
    $existsTxt = $exists ? '<span style="color:#2d7">Yes</span>' : '<span style="color:#c33">No</span>';

    if (!$match) {
      echo "<tr><td>$name</td><td>(none)</td><td>$path</td><td>$existsTxt</td><td style='color:#c33'>Item not found</td></tr>";
      continue;
    }

    $id = (int)$match['id'];
    $dbName = htmlspecialchars($match['name']);

    try {
      $upd->execute([$path, $id]);
      $color = $exists ? '#2d7' : '#e69500';
      $msg = $exists ? 'Updated ✓' : 'Updated (file not found; fallback may show)';
      echo "<tr><td>$name</td><td>$dbName</td><td>$path</td><td>$existsTxt</td><td style='color:$color'>$msg</td></tr>";
    } catch (Throwable $t) {
      $msg = htmlspecialchars($t->getMessage());
      echo "<tr><td>$name</td><td>$dbName</td><td>$path</td><td>$existsTxt</td><td style='color:#c33'>Failed: $msg</td></tr>";
    }
  }

  echo '</table>';
  echo "<p>Tip: Filenames with spaces (like 'samosa chaat.jpg') work, but it's best to rename to 'samosa-chaat.jpg' later for cleaner URLs.</p>";
  echo "<p>➡️ <a href='inspect_menu_images.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Inspect menu images</a></p>";
  echo "<p>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Throwable $t) {
  echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
  echo 'Error: ' . htmlspecialchars($t->getMessage());
  echo '</div>';
}
