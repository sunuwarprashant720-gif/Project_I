// Get input fields
const username = document.getElementById("username");
const email = document.getElementById("email");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

// Error helpers
function showError(id, message) {
    document.getElementById(id).textContent = message;
}

function clearError(id) {
    document.getElementById(id).textContent = "";
}

// Regex patterns
const usernameRegex = /^[A-Za-z][A-Za-z0-9_]{2,15}$/;
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;

// Username validation
function validateUsername() {
    if (!username.value.trim()) {
        showError("usernameError", "Username is required");
        return false;
    }
    if (!usernameRegex.test(username.value)) {
        showError(
            "usernameError",
            "Username must start with a letter and be 3–16 characters"
        );
        return false;
    }
    clearError("usernameError");
    return true;
}

// Email validation
function validateEmail() {
    if (!email.value.trim()) {
        showError("emailError", "Email is required");
        return false;
    }
    if (!emailRegex.test(email.value)) {
        showError("emailError", "Enter a valid email address");
        return false;
    }
    clearError("emailError");
    return true;
}

// Password validation
function validatePassword() {
    if (!password.value) {
        showError("passwordError", "Password is required");
        return false;
    }
    if (!passwordRegex.test(password.value)) {
        showError(
            "passwordError",
            "Password must be at least 6 characters and include a number"
        );
        return false;
    }
    clearError("passwordError");
    return true;
}

// Confirm password validation
function validateConfirmPassword() {
    if (!confirmPassword.value) {
        showError("confirmPasswordError", "Please confirm your password");
        return false;
    }
    if (confirmPassword.value !== password.value) {
        showError("confirmPasswordError", "Passwords do not match");
        return false;
    }
    clearError("confirmPasswordError");
    return true;
}

// Live validation
username.addEventListener("input", validateUsername);
email.addEventListener("input", validateEmail);
password.addEventListener("input", () => {
    validatePassword();
    validateConfirmPassword();
});
confirmPassword.addEventListener("input", validateConfirmPassword);

// Form submit validation
function validateForm() {
    const isUsernameValid = validateUsername();
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    const isConfirmValid = validateConfirmPassword();

    return (
        isUsernameValid &&
        isEmailValid &&
        isPasswordValid &&
        isConfirmValid
    );
}
