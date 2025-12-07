<?php

session_start();
include'connect.php';
if(isset($_POST['signUp'])){
    $userName  =$_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    $checkEmail = "SELECT * FROM users where email = '$email'";
    $result = $conn->query($checkEmail);
    if($result->num_rows>0){
        echo "Email Address already exists!";
    }
    else{
        $insertQuery = "INSERT INTO users(username,email,password)
        values('$userName','$email','$password')";
        if($conn->query($insertQuery)==TRUE){
            header('location:index.php');
        }
        else{
            echo "Error:".$conn->error;
        }
    }
}

if(isset($_POST['login'])){
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);
if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])){
        $_SESSION['email'] = $row['email'];
        header("Location:home.php");
        exit();
    } else {
        echo "Incorrect Password";
    }
} else {
    echo "Email not found";
}
}
?>