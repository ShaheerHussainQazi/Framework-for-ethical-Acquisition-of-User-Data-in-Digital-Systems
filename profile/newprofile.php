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

$conn = mysqli_connect($host, $db_user, $db_pass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Getting user_id
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $profile_name = $_POST["profile_name"] ?? "Untitled Profile";
    $profile_uid = generateUID();

    // Inserting profile
    $stmt = $conn->prepare("INSERT INTO profiles (user_id, profile_name, profile_uid) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $profile_name, $profile_uid);
    $stmt->execute();
    $new_profile_id = $stmt->insert_id;
    $stmt->close();

    // Handling preferences
    if (!empty($_POST["category_states"])) {
        foreach ($_POST["category_states"] as $cat_id => $state) {
            if ($state === "") continue; // skip if not decided
            $is_interested = ($state == "like") ? 0 : 1; // 0 = true, 1 = false
            $stmt = $conn->prepare("INSERT INTO profile_preferences (profile_id, category_id, is_interested) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $new_profile_id, $cat_id, $is_interested);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: /dashboard/dashboard.php");
    exit;
}

// Recursive category loader
function getCategories($conn, $parent_id = null, $level = 0)
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
        $margin = $level * 20;
        $catId = $row['category_id'];
        $catName = htmlspecialchars($row['name']);
        $output .= "<div class='category-row' style='margin-left: {$margin}px;'>
            <span class='category-name'>{$catName}</span>
            <div class='toggle-buttons'>
                <input type='radio' id='like_$catId' name='category_states[$catId]' value='like'>
                <label for='like_$catId' class='like-btn'>Like</label>

                <input type='radio' id='dislike_$catId' name='category_states[$catId]' value='dislike'>
                <label for='dislike_$catId' class='dislike-btn'>Dislike</label>

                <input type='radio' id='neutral_$catId' name='category_states[$catId]' value='' checked>
                <label for='neutral_$catId' class='neutral-btn'>Not Decided</label>
            </div>
        </div>";

        $output .= getCategories($conn, $catId, $level + 1);
    }
    $stmt->close();
    return $output;
}

// UID generator
function generateUID()
{
    return strtoupper(bin2hex(random_bytes(3))) . '-' .
           strtoupper(bin2hex(random_bytes(3))) . '-' .
           strtoupper(bin2hex(random_bytes(3)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Profile</title>
    <link rel="stylesheet" type="text/css" href="/profile/assets/css/newprofile.css">
</head>
<body>
    <div class="container">
        <h1>Create New Profile</h1>
        <form method="POST">
            <label for="profile_name">Profile Name:</label>
            <input type="text" name="profile_name" placeholder="e.g., Movie Fan, Reader, Gamer" required>

            <h3>Select Your Interests:</h3>
            <div class="categories">
                <?php echo getCategories($conn); ?>
            </div>

            <div class="buttons">
                <button type="submit" class="save-btn">Save</button>
                <a href="/dashboard/dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
