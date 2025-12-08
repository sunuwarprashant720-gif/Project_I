
const username = document.getElementById("username");
const email = document.getElementById("email");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

function showError(id, message) {
    document.getElementById(id).textContent = message;
}

function clearError(id) {
    document.getElementById(id).textContent = "";
}

// LIVE VALIDATION
username.addEventListener("input", () => {
    if (!username.value.trim()) {
        showError("usernameError", "Username is required");
    } 
    else if (!isNaN(username.value[0])) {
        showError("usernameError", "Username must start with a letter");
    }
    else {
        clearError("usernameError");
    }
});

email.addEventListener("input", () => {
    if (!email.value.includes("@") || !email.value.includes(".")) {
        showError("emailError", "Enter a valid email");
    } else {
        clearError("emailError");
    }
});

password.addEventListener("input", () => {
    if (password.value.length < 6) {
        showError("passwordError", "At least 6 characters");
    } else {
        clearError("passwordError");
    }
});

confirmPassword.addEventListener("input", () => {
    if (confirmPassword.value !== password.value) {
        showError("confirmPasswordError", "Passwords do not match");
    } else {
        clearError("confirmPasswordError");
    }
});

// FORM VALIDATION ON SUBMIT
function validateForm() {

    // Trigger each validation once more
    username.dispatchEvent(new Event("input"));
    email.dispatchEvent(new Event("input"));
    password.dispatchEvent(new Event("input"));
    confirmPassword.dispatchEvent(new Event("input"));

    // If any error message exists, block submit
    if (
        document.getElementById("usernameError").textContent ||
        document.getElementById("emailError").textContent ||
        document.getElementById("passwordError").textContent ||
        document.getElementById("confirmPasswordError").textContent
    ) {
        return false;
    }

    return true; // Form can be submitted
}
