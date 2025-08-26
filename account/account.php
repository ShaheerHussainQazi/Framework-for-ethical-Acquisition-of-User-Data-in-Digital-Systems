<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /index.html");
    exit;
}

$username = $_SESSION["username"];
$user_id = null;

$host = "localhost";
$db_user = "root";
$db_pass = "@Pass2410";
$dbname = "user_preferences_db";

$conn = new mysqli($host, $db_user, $db_pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching user_id
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Handling form submissions
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["change_username"])) {
        $new_username = trim($_POST["new_username"]);
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_username, $user_id);
        if ($stmt->execute()) {
            $_SESSION["username"] = $new_username;
            $msg = "Username updated successfully.";
        } else {
            $msg = "Username update failed.";
        }
        $stmt->close();
    }

    if (isset($_POST["change_password"])) {
        $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_password, $user_id);
        if ($stmt->execute()) {
            $msg = "Password updated successfully.";
        } else {
            $msg = "Password update failed.";
        }
        $stmt->close();
    }

    if (isset($_POST["delete_account"])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            session_destroy();
            header("Location: /index.html");
            exit;
        } else {
            $msg = "Account deletion failed.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
    <link rel="stylesheet" href="/account/assets/css/account.css">
</head>
<body>
<div class="account-container">
    <h2>Account Settings</h2>
    <?php if ($msg): ?>
        <p class="msg"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post">
        <h3>Change Username</h3>
        <input type="text" name="new_username" placeholder="New Username" required>
        <button type="submit" name="change_username">Update Username</button>
    </form>

    <form method="post">
        <h3>Change Password</h3>
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit" name="change_password">Update Password</button>
    </form>

    <form method="post" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
        <h3>Delete Account</h3>
        <button type="submit" name="delete_account" class="danger">Delete My Account</button>
    </form>

    <a href="/dashboard/dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>
