<?php
session_start();
$servername = "webpagesdb.it.auth.gr:3306";
$usernameDB = "thesis2021";
$passwordDB = "Thesis2021*";
$dbname = "thesis2021";

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);
mysqli_set_charset($conn, "utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$_SESSION["conn"] = $conn;
