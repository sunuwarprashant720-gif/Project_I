<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connect.php");

/* 🔐 PROTECT PAGE */
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneCraft Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(90deg, #faf5ff 0%, #fff1f8 40%, #eef4ff 100%);
    min-height: 100vh;
    margin: 0;
    display: flex;
}
</style>

<div class="container">

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo-wrapper">
        <img src="Assets/music-2.svg" class="logo">
        <p class="logo-title">TuneCraft</p>
    </div>

    <p class="logo-description">Make music, have fun!</p>

    <nav class="menu-nav">
        <ul class="menu">

            <div class="menu-item-container">
                <li class="active">
                    <a href="home.php">
                        <img src="./Assets/house.svg"> Dashboard
                    </a>
                </li>
            </div>

            <div class="menu-item-container">
                <li>
                    <a href="music-note-editor.html">
                        <img src="./Assets/music-2-black.svg"> Music Editor
                    </a>
                </li>
            </div>

            <div class="menu-item-container">
                <li>
                    <a href="learn.html">
                        <img src="./Assets/book.svg"> Learn
                    </a>
                </li>
            </div>

        </ul>
    </nav>

    <!-- ✅ LOGOUT BUTTON (SECURE + STYLED) -->
<form action="logout.php" method="POST">
    <button type="submit" name="logout" class="logout-btn">
        <img src="./Assets/log-out.svg" alt="">
        Logout
    </button>
</form>



</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

<h1>
Welcome
<?php
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo htmlspecialchars($row['username']);
}
?>
!!
</h1>

<h2>Creating something new everyday!</h2>

<button class="new-composition-btn">+ New composition</button>

<h3 class="recent-title">Recent projects:</h3>

<div class="projects">
    <div class="project-card">
        <div class="icon-box"><img src="Assets/music.svg"></div>
        <p>Symphony No. 1</p>
    </div>

    <div class="project-card">
        <div class="icon-box"><img src="Assets/music.svg"></div>
        <p>Happy Melody</p>
    </div>

    <div class="project-card">
        <div class="icon-box"><img src="Assets/music.svg"></div>
        <p>Practice Session</p>
    </div>

    <div class="project-card">
        <div class="icon-box"><img src="Assets/music.svg"></div>
        <p>Jazz Improv</p>
    </div>
</div>

</main>

<div class="bg-illustration-container">
    <img src="./Assets/bgimg2updated.jpg" class="bg-illustration">
</div>

</div>
</body>
</html>
