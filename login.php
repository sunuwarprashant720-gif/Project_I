<?php
session_start();

// If already logged in, redirect to home
if (isset($_SESSION['email'])) {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TuneCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
  <div class="login-wrapper">

    <div class="login-logo-wrapper">
      <img src="Assets/music-2.svg" alt="Logo" class="logo">
    </div>

    <h2 class="login-subtitle">Welcome Back!</h2>
    <p class="login-description">
      Login to continue your musical journey
    </p>

    <!-- LOGIN FORM -->
    <form class="login-form" action="submit.php" method="POST">

      <!-- Email -->
      <div class="login-field">
        <label class="login-label">Email</label>
        <input
          type="email"
          name="email"
          class="login-input"
          placeholder="you@example.com"
          required
        >
      </div>

      <!-- Password -->
      <div class="login-field">
        <label class="login-label">Password</label>
        <input
          type="password"
          name="password"
          class="login-input"
          placeholder="••••••••"
          required
        >
      </div><br>

      <!-- Submit -->
      <button
        type="submit"
        name="login"
        class="login-button"
      >
        Login
      </button>

    </form>

    <!-- Footer -->
    <div class="login-footer">
      <p>
        Don't have an account?
        <a class="login-footer-link" href="signup.php">Sign up</a>
      </p>
    </div>

  </div>
</div>

</body>
</html>
