<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up form</title>
    <link rel="stylesheet" href="style.css">
    <!-- ?<script src="script.js"></script> -->
</head>
<body>

    <div class="signup-container">
        <div class="signup-wrapper">
        <div class="signup-logo-wrapper">
        <img src="Assets/music-2.svg" alt="Logo" class="logo"></div>
    
    <p class ="signup-subtitle">Join TuneCraft!</p>
    <p class="signup-description">Create your account and start making music today.</p>
    <form class= "signup-form" name="myForm" action="submit.php" method="post" onsubmit="return validateForm()"> 
     <div class="signup-field">
    <label class="signup-label">Username:</label>
    <input type="text" id="username" class="signup-input" placeholder="Your username">
    <small class="error" id="usernameError"></small>
</div>

<div class="signup-field">
    <label class="signup-label">Email:</label>
    <input type="email" id="email" class="signup-input" placeholder="you@example.com">
    <small class="error" id="emailError"></small>
</div>

<div class="signup-field">
    <label class="signup-label">Password:</label>
    <input type="password" id="password" class="signup-input" placeholder="••••••••">
    <small class="error" id="passwordError"></small>
</div>

<div class="signup-field">
    <label class="signup-label">Confirm Password:</label>
    <input type="password" id="confirm_password" class="signup-input" placeholder="••••••••">
    <small class="error" id="confirmPasswordError"></small>
</div>


        <input type="submit" value="SignUp" name= "signUp"class = "signup-button" location='login.php'>
    </form>
    <div class  ="signup-footer">
    <p>Already have an account? <a class = "signup-footer-link" href="login.php" >Login here</a></p>
    </div>
    </div>
    </div>

<script  src = "script.js"></script>


    
</body>
</html>