<?php

require 'connect.php';

$conn = $_SESSION["conn"]; // variable that connected to database

$name = $surname = $email = $password = "";

$showDivSuccess = $showDivDuplicateEmail = $showDivSomethingWrong = FALSE;
$nameLen = $surnameLen = $emailLen = $passwordLen = TRUE;

if(isset($_POST['submit'])){
    $name = test_data($_POST["firstName"]);
    $surname = test_data($_POST["lastName"]);
    $email = test_data($_POST["email"]);
    $password = test_data($_POST["password"]);

    $nameLen = strlen($name) <= 30; // checking length of name
    $surnameLen = strlen($surname) <= 30; // checking length of surname
    $emailLen = strlen($email) <= 200; // checking length of email
    $passwordLen = strlen($password) <= 50; // checking length of password

    if($nameLen && $surnameLen && $emailLen && $passwordLen){
        $query = "INSERT INTO person (name, surname, email, psw) VALUES ('$name', '$surname', '$email', '$password')";

        if($conn->query($query) === TRUE){
            $showDivSuccess = TRUE;
        }else{
            $queryDuplicateEmail = "SELECT ID FROM Person WHERE email = '$email'";
            $checkEmail = $conn->query($queryDuplicateEmail);

            if($checkEmail->num_rows === 1){ // checking if there is already an account with that email
                $showDivDuplicateEmail = TRUE;
            }else{
                $showDivSomethingWrong = TRUE;
            }
        }
    }
}

/*
 * Function to filter data.
 */
function test_data($data){
    return htmlspecialchars(stripslashes($data));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<body>
    <div class="w3-bar w3-blue showBar">
        <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Register</b></a>
        <a href="login.html" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
    </div>

    <div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
        <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
        <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Register</b></a>
        <a href="login.html" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
    </div>

    <button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

    <div class="w3-card w3-border contactRegisterPanel">
        <div class="w3-container w3-blue">
            <h3 class="headerPanel">Register</h3>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container w3-padding-16 contactRegisterForm">
            <input class="w3-input w3-border w3-margin-top" id="firstName" name="firstName" type="text" placeholder="Name *" required><br>

            <input class="w3-input w3-border w3-margin-top" id="lastName" name="lastName" type="text" placeholder="Surname *" required><br>

            <input class="w3-input w3-border w3-margin-top" id="email" name="email" type="email" placeholder="Email *" required><br>

            <input class="w3-input w3-border w3-margin-top" id="password" name="password" type="password" placeholder="Password *" required>

            <input id="labelShow" class="w3-check w3-margin-top" type="checkbox" onclick="showPassword('password')">
            <label for="labelShow">Show password</label><br><br>

            <div class="w3-panel w3-green" <?php if($showDivSuccess === TRUE) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>You have successfully registered!</p>
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

            <div class="w3-panel w3-red" <?php if(!$passwordLen) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>The maximum length of password is 50 characters.</p>
            </div>

            <div class="w3-panel w3-red" <?php if($showDivDuplicateEmail) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>There is already an account with that email.</p>
            </div>

            <div class="w3-panel w3-red" <?php if($showDivSomethingWrong) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>Something went wrong. Please check your information.</p>
            </div>

            <input class="w3-button w3-green transmission" type="submit" name="submit" value="Register">
        </form>
    </div>
</body>
</html>
