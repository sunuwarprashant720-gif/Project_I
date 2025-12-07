<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
     <div class="login-container">
        <div class="login-wrapper">
        <div class="login-logo-wrapper">
        <img src="Assets/music-2.svg" alt="Logo" class="logo"></div>
    
    <h2 class  ="login-subtitle">Welcome Back!</h2>
    <p class = "login-description">Login to continue your musical journey</p>
     <form class="login-form" action = "submit.php" method = "POST">
        
        <!-- Email Field -->
        <div class="login-field">
          <label for="email" class = "login-label">Email</label>
          <input 
            id="email"
            type="email"
            placeholder="you@example.com"
            class="login-input"
            name="email"
            required
          />
        </div>

        <!-- Password Field -->
        <div class="login-field">
          <label for="password" class = "login-label">Password</label>
          <input 
            id="password"
            type="password"
            placeholder="••••••••"
            class="login-input"
            name="password"
            required
          /><br>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit"
          class="login-button" name = "login"
        >
          Login
        </button>
      </form>

      <!-- Footer -->
       <div class  ="login-footer">
    <p>Don't have an account? <a class = "login-footer-link" href="signup.php" >Sign up</a></p>
      </p>
    </div>
  </div>
</div>



</body>
</html>