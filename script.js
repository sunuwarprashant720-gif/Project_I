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