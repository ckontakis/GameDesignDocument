<?php

// Starting session if it is not created already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if user is logged in we redirect user to write page
if(isset($_SESSION["logged_in"])){
    header("Location:write.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Write GDD - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<body>
<!--- Bar for big screens -->
<div class="w3-bar w3-blue showBar">
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"><b> Start Page</b></a>
    <a href="write.php" class="w3-bar-item w3-button w3-indigo">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
    <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
</div>

<!--- Side bar for small screens -->
<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"><b> Start Page</b></a>
    <a href="write.php" class="w3-bar-item w3-button w3-indigo">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
    <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
</div>

<!--- Button to show side bar on click -->
<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<!--- A message that says user has to be logged in to create or edit a document
and a button that redirects user to login page -->
<div class="w3-container writeLogin">
    <h1 style="">You have to login to create or edit a document</h1>
    <a href="login.php" class="w3-bar-item w3-button transmission w3-text-blue w3-border w3-xxxlarge w3-round w3-hover-blue" style="margin-top: 4%">Login</a>
</div>

</body>
</html>