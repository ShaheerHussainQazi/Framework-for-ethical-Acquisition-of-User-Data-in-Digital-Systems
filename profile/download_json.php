<?php
$uid = $_GET['uid'] ?? '';
if (!$uid) {
    die("UID missing.");
}

$host = "localhost";
$db_user = "root";
$db_pass = "@Pass2410";
$dbname = "user_preferences_db";

$conn = new mysqli($host, $db_user, $db_pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Getting profile_id from UID
$stmt = $conn->prepare("SELECT profile_id FROM profiles WHERE profile_uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$stmt->bind_result($profile_id);
if (!$stmt->fetch()) {
    die("Invalid UID.");
}
$stmt->close();

// Fetching all categories
$categories = [];
$result = $conn->query("SELECT category_id, name, parent_id FROM categories");
while ($row = $result->fetch_assoc()) {
    $categories[$row['category_id']] = [
        'name' => $row['name'],
        'parent_id' => $row['parent_id'],
        'children' => []
    ];
}
$result->free();

// Build tree structure
$tree = [];
foreach ($categories as $id => &$cat) {
    if ($cat['parent_id'] === null) {
        $tree[$id] = &$cat;
    } else {
        $categories[$cat['parent_id']]['children'][$id] = &$cat;
    }
}

// Fetching preferences (likes/dislikes/neutral)
$preferences = [];
$stmt = $conn->prepare("SELECT category_id, is_interested FROM profile_preferences WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $preferences[$row['category_id']] = $row['is_interested'];  // 0, 1, or NULL
}
$stmt->close();

// Recursive JSON builder
function buildJsonTreeFull($node, $preferences) {
    $entry = [];

    foreach ($node as $id => $cat) {
        $state_raw = $preferences[$id] ?? null;
        $state = match($state_raw) {
            0 => "likes",
            1 => "dislikes",
            default => "neutral"
        };

        $children = buildJsonTreeFull($cat['children'], $preferences);
        if (!empty($children)) {
            $entry[$cat['name']] = [
                "preference" => $state,
                "subcategories" => $children
            ];
        } else {
            $entry[$cat['name']] = $state;
        }
    }

    return $entry;
}

$jsonOutput = buildJsonTreeFull($tree, $preferences);

// Output as downloadable JSON file
header('Content-Type: application/json');
header("Content-Disposition: attachment; filename=profile_{$uid}.json");
echo json_encode($jsonOutput, JSON_PRETTY_PRINT);
exit;
