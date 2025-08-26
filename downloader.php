<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uid = $_POST["uid"] ?? '';

    if (!$uid) {
        $error = "Please enter a valid Profile ID.";
    } else {
        // Redirect to download_json.php with uid as GET param
        header("Location: /profile/download_json.php?uid=" . urlencode($uid));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Preferences by Profile ID</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/downloader.css" />
</head>
<body>
    <div class="downloader-container">
        <h2>Download Preference JSON</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="uid" placeholder="Enter Profile UID" required>
            <button type="submit">Download JSON</button>
        </form>
    </div>
</body>
</html>
