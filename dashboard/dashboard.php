<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /index.html");
    exit;
}

$username = $_SESSION["username"];
$host = "localhost";
$db_username = "root";
$db_password = "@Pass2410";
$dbname = "user_preferences_db";

$conn = mysqli_connect($host, $db_username, $db_password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$user_query = "SELECT user_id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $user_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$profile_query = "SELECT profile_name, profile_uid FROM profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $profile_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profiles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $profiles[] = $row;
}
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" type="text/css" href="/dashboard/assets/css/dashboard.css">
    <script src="/dashboard/assets/js/dashboard-script.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <button onclick="logout()" class="logout-btn">Logout</button>
            </div>

            <nav>
                <ul>
                    <li><a href="/account/account.php">Account Settings</a></li>
                </ul>

                <div class="profile-list-label">Profiles</div>
                <div class="profile-list">
                    <?php foreach ($profiles as $profile): ?>
                        <a class="profile-link" href="/profile/profile.php?uid=<?php echo $profile['profile_uid']; ?>">
                            <?php echo htmlspecialchars($profile['profile_name']); ?>
                        </a>
                    <?php endforeach; ?>
                    <a class="profile-link new-profile" href="/profile/newprofile.php?new=1">+ New Profile</a>
                    <a class="profile-link new-profile" href="#" onclick="openAIAssistant()">🤖 AI Profile Assistant</a>

                </div>
            </nav>
        </aside>

        <main class="main-panel">
            <div class="main-header">
                <h2>Your Profiles</h2>
            </div>
            <div class="profile-grid">
                <?php if (empty($profiles)): ?>
                    <p>You haven't created any profiles yet. Use the + New option to start.</p>
                <?php else: ?>
                    <?php foreach ($profiles as $profile): ?>
                        <div class="profile-card">
                            <h3><?php echo htmlspecialchars($profile['profile_name']); ?></h3>
                            <p><strong>ID:</strong> <?php echo $profile['profile_uid']; ?></p>
                            <a href="/profile/profile.php?uid=<?php echo $profile['profile_uid']; ?>" class="view-btn">View / Edit</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="aiModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>AI Profile Assistant</h3>
        <p>Describe your interests, and we'll create a profile for you:</p>
        <textarea id="userInputText" rows="5" placeholder="e.g. I love cooking, sci-fi movies, and productivity tools."></textarea>
        <br><br>
        <button onclick="generateAIProfile()">Generate Profile</button>
        <button onclick="closeAIAssistant()">Cancel</button>
        <p id="aiStatus" style="margin-top: 10px;"></p>
    </div>
    </div>

</body>
</html>
