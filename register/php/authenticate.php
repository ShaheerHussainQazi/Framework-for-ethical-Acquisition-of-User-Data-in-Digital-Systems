<?php
// DB connection details (my actual DB credentials)
$host = 'localhost';
$db_username = 'root';          // using my real MySQL username here
$db_password = '@Pass2410';              // my MySQL password
$dbname = 'user_preferences_db';

// Getting the form data
$form_username = $_POST['username'] ?? '';
$form_password = $_POST['password'] ?? '';

// Basic input validation
if (empty($form_username) || empty($form_password)) {
    die("Please enter both username and password.");
}

// Connecting to MySQL
$conn = mysqli_connect($host, $db_username, $db_password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Checking if the username is already exists
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $form_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo '<script>alert("Username already exists."); window.location.href = "/register/register.html";</script>';
    exit();
}

// Hashing the password
$hashed_password = password_hash($form_password, PASSWORD_DEFAULT);

// Inserting the new user
$insert_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$insert_stmt = mysqli_prepare($conn, $insert_sql);
mysqli_stmt_bind_param($insert_stmt, "ss", $form_username, $hashed_password);

if (mysqli_stmt_execute($insert_stmt)) {
    session_start();
    $_SESSION["loggedin"] = true;
    $_SESSION["username"] = $form_username;
    header("Location: /dashboard/dashboard.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}

// Closing the connection
mysqli_close($conn);
?>
