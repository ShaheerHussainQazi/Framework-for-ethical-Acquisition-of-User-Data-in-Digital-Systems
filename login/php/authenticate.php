<?php
// DB connection credentials
$host = 'localhost';
$db_username = 'root';
$db_password = '@Pass2410';
$dbname = 'user_preferences_db';

// Starting session
session_start();

// Getting login form input
$form_username = $_POST['username'] ?? '';
$form_password = $_POST['password'] ?? '';

// Checking if input is empty
if (empty($form_username) || empty($form_password)) {
    die("Please enter both username and password.");
}

// Connecting to MySQL
$conn = mysqli_connect($host, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Preparing SQL to fetch user by username
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $form_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Checking if user exists
if ($row = mysqli_fetch_assoc($result)) {
    // Verifying password
    if (password_verify($form_password, $row['password'])) {
        // Password is correct, starting session
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $form_username;
        $_SESSION["user_id"] = $row["user_id"];  // You'll need this for profiles
        header("Location: /dashboard/dashboard.php");
        exit();
    } else {
        echo '<script>alert("Invalid password."); window.location.href = "/login/login.html";</script>';
        exit();
    }
} else {
    echo '<script>alert("User not found."); window.location.href = "/login/login.html";</script>';
    exit();
}

// Closing connection
mysqli_close($conn);
?>
