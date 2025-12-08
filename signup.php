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
        <div class = "signup-field">
        <label for="username" class= "signup-label">Username:</label>
        <input type="text" id="username" name="username" class="signup-input" placeholder="Your username"><br>
        </div>
        
        <div class = "signup-field">
            <label for="email" class= "signup-label ">Email:</label>
            <input type="email" id="email" name="email" class="signup-input"    placeholder="you@example.com"><br>
        </div>
        
        <div class = "signup-field">
            <label for="password" class= "signup-label">Password:</label>
            <input type="password" id="password" name="password" class="signup-input" placeholder="••••••••"><br>
        </div>
        
        <div class = "signup-field">
            <label for="confirm_password" class = "signup-label">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="signup-input" placeholder="••••••••"><br>
        </div>

        <input type="submit" value="SignUp" name= "signUp"class = "signup-button" location='login.php'>
    </form>
    <div class  ="signup-footer">
    <p>Already have an account? <a class = "signup-footer-link" href="login.php" >Login here</a></p>
    </div>
    </div>
    </div>

    <script  src = "script.js">
function validateForm() {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Check empty fields
    if (!username) {
        alert('Username is required.');
        return false;
    }

    // Username must start with a letter
    const usernamePattern = /^[A-Za-z][A-Za-z0-9_]*$/;
    if (!usernamePattern.test(username)) {
        alert('Username must start with a letter and contain only letters, numbers, or underscores.');
        return false;
    }

    if (!email) {
        alert('Email is required.');
        return false;
    }

    // Email format validation
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address.');
        return false;
    }

    if (!password) {
        alert('Password is required.');
        return false;
    }

    if (!confirmPassword) {
        alert('Please confirm your password.');
        return false;
    }

    // Password match validation
    if (password !== confirmPassword) {
        alert('Passwords do not match.');
        return false;
    }

    // Optional: password strength check (at least 6 characters)
    if (password.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }

    return true; // All validations passed
}
</script>


    
</body>
</html>