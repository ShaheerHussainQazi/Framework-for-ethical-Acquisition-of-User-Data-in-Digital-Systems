<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /index.html");
    exit;
}

$username = $_SESSION["username"];
$host = "localhost";
$db_user = "root";
$db_pass = "@Pass2410";
$dbname = "user_preferences_db";

$conn = new mysqli($host, $db_user, $db_pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = $_GET['uid'] ?? '';
if (!$uid) {
    die("Profile UID missing.");
}

// Getting profile_id and profile_name
$stmt = $conn->prepare("SELECT profile_id, profile_name FROM profiles WHERE profile_uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$stmt->bind_result($profile_id, $profile_name);
if (!$stmt->fetch()) {
    die("Profile not found.");
}
$stmt->close();

// Handling form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['delete_profile'])) {
        // Deleting profile and all related preferences
        $stmt = $conn->prepare("DELETE FROM profiles WHERE profile_id = ?");
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $stmt->close();

        header("Location: /dashboard/dashboard.php");
        exit;
    }

    // Handle normal Save action
    // Delete old preferences
    $stmt = $conn->prepare("DELETE FROM profile_preferences WHERE profile_id = ?");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $stmt->close();

    if (!empty($_POST["category_states"])) {
        foreach ($_POST["category_states"] as $cat_id => $state) {
            if ($state === "") continue; // skip neutral
            $is_interested = ($state === "like") ? 0 : 1;
            $stmt = $conn->prepare("INSERT INTO profile_preferences (profile_id, category_id, is_interested) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $profile_id, $cat_id, $is_interested);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: /dashboard/dashboard.php");
    exit;
}

// Fetch preferences
$preferences = [];
$stmt = $conn->prepare("SELECT category_id, is_interested FROM profile_preferences WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $preferences[$row['category_id']] = $row['is_interested'];
}
$stmt->close();

// Recursive category builder
function getCategories($conn, $preferences, $parent_id = null, $level = 0)
{
    $sql = "SELECT category_id, name FROM categories WHERE parent_id ";
    $sql .= is_null($parent_id) ? "IS NULL" : "= ?";
    $stmt = $conn->prepare($sql);
    if (!is_null($parent_id)) {
        $stmt->bind_param("i", $parent_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $output = "";
    while ($row = $result->fetch_assoc()) {
        $catId = $row['category_id'];
        $catName = htmlspecialchars($row['name']);
        $margin = $level * 20;

        $selected = $preferences[$catId] ?? null;
        $like_checked = ($selected === 0) ? "checked" : "";
        $dislike_checked = ($selected === 1) ? "checked" : "";
        $neutral_checked = ($selected === null) ? "checked" : "";

        $output .= "<div class='category-row' style='margin-left: {$margin}px;'>
            <span class='category-name'>{$catName}</span>
            <div class='toggle-buttons'>
                <input type='radio' id='like_$catId' name='category_states[$catId]' value='like' {$like_checked}>
                <label for='like_$catId' class='like-btn'>Like</label>

                <input type='radio' id='dislike_$catId' name='category_states[$catId]' value='dislike' {$dislike_checked}>
                <label for='dislike_$catId' class='dislike-btn'>Dislike</label>

                <input type='radio' id='neutral_$catId' name='category_states[$catId]' value='' {$neutral_checked}>
                <label for='neutral_$catId' class='neutral-btn'>Not Decided</label>
            </div>
        </div>";

        $output .= getCategories($conn, $preferences, $catId, $level + 1);
    }
    $stmt->close();
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - <?= htmlspecialchars($profile_name) ?></title>
    <link rel="stylesheet" type="text/css" href="/profile/assets/css/profile.css">
</head>
<body>
    <div class="container">
        <h1>Edit Profile: <?= htmlspecialchars($profile_name) ?></h1>
        <p>
            <strong>Profile UID:</strong> <?= htmlspecialchars($uid) ?>
        </p>

        <form method="GET" action="download_json.php">
            <input type="hidden" name="uid" value="<?= htmlspecialchars($uid) ?>">
            <button type="submit" class="download-btn">Download JSON</button>
        </form>


        <form method="POST">
            <h3>Update Your Interests:</h3>
            <div class="categories">
                <?= getCategories($conn, $preferences) ?>
            </div>

            <div class="buttons">
                <button type="submit" class="save-btn">Save</button>
                <a href="/dashboard/dashboard.php" class="cancel-btn">Cancel</a>
                <button type="submit" name="delete_profile" class="delete-btn" onclick="return confirm('Are you sure you want to delete this profile?');">Delete</button>
            </div>
        </form>
    </div>
</body>
</html>
