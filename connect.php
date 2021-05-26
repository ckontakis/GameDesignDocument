<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$servername = "localhost";
$usernameDB = "root";
$passwordDB = "";
$dbname = "thesis2021";

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
mysqli_set_charset($conn, "utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$_SESSION["conn"] = $conn; // pass the variable that connected to database
