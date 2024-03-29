<?php

require 'connect.php';
$conn = $_SESSION["conn"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Start Page - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">

<body>
<div class="w3-bar w3-blue showBar">
    <a href="index.php" class="w3-bar-item w3-button w3-indigo"><img src="Images/favicon-new.ico" alt="logo"><b> Start Page</b></a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    // if user is logged in we show settings button and logout button
    if(isset($_SESSION["logged_in"])){
        echo "<div class=\"w3-dropdown-hover w3-right\">
        <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
        <div class=\"w3-dropdown-content w3-bar-block w3-border\">
            <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings <i class=\"fa fa-cog\"></i></a>
            <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout <i class=\"fa fa-sign-out\"></i></a>
        </div>
    </div>";
    }else{
        // else we show register and login button
        echo "<a href=\"register.php\" class=\"w3-bar-item w3-button w3-teal w3-right\">Register</a>
              <a href=\"login.php\" class=\"w3-bar-item w3-button w3-teal w3-right\">Login</a>";
    }
    ?>

</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.php" class="w3-bar-item w3-button w3-indigo"><img src="Images/favicon-new.ico" alt="logo"><b> Start Page</b></a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    // if user is logged in we show settings button and logout button
    if(isset($_SESSION["logged_in"])){
        echo "<div class=\"w3-dropdown-hover w3-right\">
        <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
        <div class=\"w3-dropdown-content w3-bar-block w3-border\">
            <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings <i class=\"fa fa-cog\"></i></a>
            <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout <i class=\"fa fa-sign-out\"></i></a>
        </div>
    </div>";
    }else{
        // else we show register and login button
        echo "<a href=\"register.php\" class=\"w3-bar-item w3-button w3-teal w3-right\">Register</a>
              <a href=\"login.php\" class=\"w3-bar-item w3-button w3-teal w3-right\">Login</a>";
    }
    ?>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>
<h2 class="headers w3-border w3-hover-border-blue w3-round">Create your own Game Design Document now!</h2>
<p class="textInMain">This page is created from two students that study at Aristotle University of Thessaloniki.
    With this page you can create your own game design document for your game. If you want to create a game design document you
    have to create an account and login with it. Also, you can create a team that can edit a game design document and you can
    invite more people to edit your document. There are four categories when you edit a document: 1. Summary of game, 2. Mechanics of game,
    3. World building of game and 4. Umbra. If you have questions you can contact with us at
    <a href="contact.php" class="w3-hover-text-blue">contact page</a>.
</p>

<img class="image" src="Images/logo.png" alt="logo">
</body>
</html>