<?php
session_start();
include("connect.php");

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$composition_id = $_GET['id'] ?? null;
if (!$composition_id) {
    header("Location: home.php");
    exit();
}

// Get user ID
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT user_id AS id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Get composition
$stmt = $conn->prepare("SELECT title, composer, data FROM compositions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $composition_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$composition = $result->fetch_assoc();

if (!$composition) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($composition['title']); ?> - TuneCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <!-- Same sidebar as home.php -->
        </aside>
        
        <main class="main-content">
            <h1><?php echo htmlspecialchars($composition['title']); ?></h1>
            <h3>by <?php echo htmlspecialchars($composition['composer']); ?></h3>
            
            <div id="composition-viewer">
                <p>This composition can be opened in the editor</p>
                <a href="music-note-editor.html?id=<?php echo $composition_id; ?>" class="edit-btn">Edit in Editor</a>
                <button onclick="window.location.href='home.php'" class="back-btn">Back to Dashboard</button>
            </div>
        </main>
    </div>
</body>
</html>