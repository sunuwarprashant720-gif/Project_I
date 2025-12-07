<?php
$host  = "localhost";
$user = "root";
$pass = "";
$db = "tunecraft";

$conn = new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
echo "Failed to connect database".$conn->connect_error;
}
?>