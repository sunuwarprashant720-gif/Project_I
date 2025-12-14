<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | TuneCraft</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="signup-container">
  <div class="signup-wrapper">

    <div class="signup-logo-wrapper">
      <img src="Assets/music-2.svg" alt="Logo" class="logo">
    </div>

    <p class="signup-subtitle">Join TuneCraft!</p>
    <p class="signup-description">
      Create your account and start making music today.
    </p>

    <!-- SIGNUP FORM -->
    <form class="signup-form"
          name="myForm"
          action="submit.php"
          method="POST"
          onsubmit="return validateForm();">

      <!-- Username -->
      <div class="signup-field">
        <label class="signup-label">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="signup-input"
          placeholder="Your username"
          required
        >
        <small class="error" id="usernameError"></small>
      </div>

      <!-- Email -->
      <div class="signup-field">
        <label class="signup-label">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="signup-input"
          placeholder="you@example.com"
          required
        >
        <small class="error" id="emailError"></small>
      </div>

      <!-- Password -->
      <div class="signup-field">
        <label class="signup-label">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="signup-input"
          placeholder="••••••••"
          required
        >
        <small class="error" id="passwordError"></small>
      </div>

      <!-- Confirm Password -->
      <div class="signup-field">
        <label class="signup-label">Confirm Password</label>
        <input
          type="password"
          id="confirm_password"
          class="signup-input"
          placeholder="••••••••"
          required
        >
        <small class="error" id="confirmPasswordError"></small>
      </div>

      <!-- Submit -->
      <input
        type="submit"
        value="Sign Up"
        name="signUp"
        class="signup-button"
      >

    </form>

    <!-- Footer -->
    <div class="signup-footer">
      <p>
        Already have an account?
        <a class="signup-footer-link" href="login.php">Login here</a>
      </p>
    </div>

  </div>
</div>

<script src="script.js"></script>
</body>
</html>
