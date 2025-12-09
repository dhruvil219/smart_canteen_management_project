<?php
require_once 'database.php';

/*
Run this after you save correct images in SGP_project/images/ with the filenames below.
It will update image_url for each snack item by name.
*/

header('Content-Type: text/html; charset=UTF-8');

echo '<h2>🖼️ Update Snack Images</h2>';

// Allow choosing relative paths (more robust across different base URLs)
$useRelative = isset($_GET['relative']) && $_GET['relative'] == '1';
if ($useRelative) {
  $map = [
    'Bhel Puri' => 'images/bhel-puri.svg',
    'Dabeli' => 'images/dabeli.svg',
    'Dhokla' => 'images/dhokla.svg',
    'Kachori' => 'images/kachori.svg',
    'Khaman' => 'images/khaman.svg',
    'Poha' => 'images/poha.svg',
    'Sev Puri' => 'images/sev-puri.svg',
    'Upma' => 'images/upma.svg',
    'Vada Pav' => 'images/vada-pav.svg',
  ];
} else {
  $map = [
    // name => image web path (absolute from web root)
    'Bhel Puri' => '/SGP_project/images/bhel-puri.svg',
    'Dabeli' => '/SGP_project/images/dabeli.svg',
    'Dhokla' => '/SGP_project/images/dhokla.svg',
    'Kachori' => '/SGP_project/images/kachori.svg',
    'Khaman' => '/SGP_project/images/khaman.svg',
    'Poha' => '/SGP_project/images/poha.svg',
    'Sev Puri' => '/SGP_project/images/sev-puri.svg',
    'Upma' => '/SGP_project/images/upma.svg',
    'Vada Pav' => '/SGP_project/images/vada-pav.svg',
  ];
}

try {
    $db = (new Database())->getConnection();

    $upd = $db->prepare('UPDATE food_items SET image_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');

    // Build a normalized map of existing items for fuzzy matching
    $all = $db->query('SELECT id, name FROM food_items')->fetchAll(PDO::FETCH_ASSOC);
    $normMap = [];
    foreach ($all as $r) {
        $n = strtolower(trim($r['name']));
        $n = str_replace([' ', '-', '&', 'and', '.'], '', $n);
        $normMap[$n] = $r; // last one wins, acceptable here
    }

    function norm($s) {
        $s = strtolower(trim($s));
        return str_replace([' ', '-', '&', 'and', '.'], '', $s);
    }

    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;min-width:900px">';
    echo '<tr style="background:#f8f9fa"><th align="left">Requested Item</th><th align="left">Matched DB Name</th><th align="left">New Image</th><th align="left">File</th><th align="left">Result</th></tr>';

    foreach ($map as $name => $img) {
        $diskPath = __DIR__ . '/' . ltrim($img, '/');
        $fileExists = file_exists($diskPath);
        $fileInfo = $fileExists ? 'Found' : '<span style="color:#c33">Missing</span>';

        $matched = $normMap[norm($name)] ?? null;
        if (!$matched) {
            echo "<tr><td>$name</td><td>(none)</td><td>$img</td><td>$fileInfo</td><td style='color:#c33'>Item not found</td></tr>";
            continue;
        }

        $id = (int)$matched['id'];
        $dbName = htmlspecialchars($matched['name']);
        try {
            $upd->execute([$img, $id]);
            $status = $fileExists ? "Updated ✓" : "Updated (but file missing; fallback will show)";
            $color = $fileExists ? '#2d7' : '#e69500';
            echo "<tr><td>$name</td><td>$dbName</td><td>$img</td><td>$fileInfo</td><td style='color:$color'>$status</td></tr>";
        } catch (Throwable $t) {
            $msg = htmlspecialchars($t->getMessage());
            echo "<tr><td>$name</td><td>$dbName</td><td>$img</td><td>$fileInfo</td><td style='color:#c33'>Failed: $msg</td></tr>";
        }
    }

    echo '</table>';
    echo "<p>➡️ <a href='inspect_menu_images.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Inspect menu images</a></p>";
    echo "<p>➡️ <a href='index.php' style='color:#27ae60;font-weight:bold;text-decoration:none'>Return to Home</a></p>";

} catch (Exception $e) {
    echo '<div style="color:#c33;background:#fee;border:1px solid #fcc;padding:10px;border-radius:6px;">';
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
