<?php

session_start();
include'connect.php';
if (isset($_POST['signUp'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // SERVER VALIDATION
    if (empty($username) || empty($email) || empty($password)) {
        die("All fields are required");
    }

    if (!preg_match("/^[A-Za-z][A-Za-z0-9_]{2,15}$/", $username)) {
        die("Invalid username");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email");
    }

    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $password)) {
        die("Weak password");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // PREPARED STATEMENT (SECURE)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die("Email already exists");
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Registration failed";
    }
}

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("All fields required");
    }

    $stmt = $conn->prepare("SELECT email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Email not found');</script>";
    }
}


?>