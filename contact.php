<?php

require 'connect.php'; // connecting to database

$conn = $_SESSION["conn"]; // variable that connected to database

$namePrev = $surnamePrev = $emailPrev = ""; // Initializing data of user

// Getting data of user if user is logged in
if(isset($_SESSION['id'])){
    $idOfUser = $_SESSION['id'];
    $userInfoRes = $conn->query("SELECT name, surname, email FROM person WHERE ID = '$idOfUser'");

    if($row = $userInfoRes->fetch_assoc()){
        $namePrev = $row['name'];
        $surnamePrev = $row['surname'];
        $emailPrev = $row['email'];
    }
}

$name = $surname = $email = $message = ""; // Initializing variables that will get values from POST method

// Initializing variables to show success or error messages when user submits the form
$showDivSuccess = $showDivSomethingWrong = FALSE;
$nameLen = $surnameLen = $emailLen = $messageLen = TRUE;

// Actions when user submits the form
if(isset($_POST['submit'])) {

    /* Getting the name, surname, email and message from the form with the POST method
     and testing them with test_data function
    */
    $name = test_data($_POST["firstName"]);
    $surname = test_data($_POST["lastName"]);
    $email = test_data($_POST["email"]);
    $message = test_data($_POST["message"]);

    // Checking the length of fields
    $nameLen = strlen($name) <= 30;
    $surnameLen = strlen($surname) <= 30;
    $emailLen = strlen($email) <= 200;
    $messageLen = strlen($email) <= 200;

    // Actions if length of variables are accepted
    if($nameLen && $surnameLen && $emailLen && $messageLen){
        $dateToSubmit = date("Y-m-d"); // Getting the date

        // Query that inserts a row in contact array with the given data
        $query = "INSERT INTO contact (name, surname, email, message, date) VALUES ('$name', '$surname', '$email', '$message', '$dateToSubmit')";

        // Actions if query is executed
        if($conn->query($query) === TRUE){
            $showDivSuccess = TRUE; // Setting the variable showDivSuccess to true to show the appropriate message
        }else{
            $showDivSomethingWrong = TRUE; // Setting the variable showDivSomethingWrong to true to show the appropriate message
        }
    }
}

/*
 * Function to filter data.
 */
function test_data($data){
    return htmlspecialchars(stripslashes($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<body>
<div class="w3-bar w3-blue showBar">
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button w3-indigo"><b>Contact</b></a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    // if user is logged in we show settings button and logout button
    if(isset($_SESSION["logged_in"])){
        echo "<div class=\"w3-dropdown-hover w3-right\">
        <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
        <div class=\"w3-dropdown-content w3-bar-block w3-border\">
            <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings</a>
            <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout</a>
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
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button w3-indigo"><b>Contact</b></a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    // if user is logged in we show settings button and logout button
    if(isset($_SESSION["logged_in"])){
        echo "<div class=\"w3-dropdown-hover w3-right\">
        <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
        <div class=\"w3-dropdown-content w3-bar-block w3-border\">
            <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings</a>
            <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout</a>
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

<div class="w3-card w3-border contactRegisterPanel">
    <div class="w3-container w3-blue">
        <h3 class="headerPanel">Contact with us</h3>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container w3-padding-16 contactRegisterForm">
        <label for="firstName">Name *</label>
        <input class="w3-input w3-border" id="firstName" name="firstName"
               value="<?php if(isset($namePrev)) echo $namePrev?>" type="text" required><br>

        <label for="lastName">Surname *</label>
        <input class="w3-input w3-border" id="lastName" name="lastName" type="text"
               value="<?php if(isset($surnamePrev)) echo $surnamePrev?>" required><br>

        <label for="email">Email *</label>
        <input class="w3-input w3-border" id="email" name="email" type="email"
               value="<?php if(isset($emailPrev)) echo $emailPrev?>" required><br>

        <label for="message">Message *</label>
        <textarea class="w3-input w3-border" rows="3" type="text" id="message" name="message" required></textarea><br>

        <div class="w3-panel w3-green" <?php if($showDivSuccess === TRUE) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>Thank you for your message. We will contact with you as soon as possible.</p>
        </div>

        <div class="w3-panel w3-red" <?php if(!$nameLen) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>The maximum length of name is 30 characters.</p>
        </div>

        <div class="w3-panel w3-red" <?php if(!$surnameLen) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>The maximum length of surname is 30 characters.</p>
        </div>

        <div class="w3-panel w3-red" <?php if(!$emailLen) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>The maximum length of email is 200 characters.</p>
        </div>

        <div class="w3-panel w3-red" <?php if(!$messageLen) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>The maximum length of message is 300 characters.</p>
        </div>

        <div class="w3-panel w3-red" <?php if($showDivSomethingWrong) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>Something went wrong. Please check your information.</p>
        </div>

        <input class="w3-button w3-green transmission" type="submit" name="submit" value="Submit">
    </form>
</div>
</body>
</html>