<?php

require 'connect.php'; // connecting to database

$conn = $_SESSION["conn"]; // variable that connected to database

/*
 * if user is logged in we redirect user to start page
 */
if(isset($_SESSION['logged_in'])){
    header("Location:index.php");
}

// Initializing variables that we save data from the post method
$name = $surname = $email = $password = "";

// Initializing variables to show success or error messages when user submits the form
$showDivSuccess = $showDivDuplicateEmail = $showDivSomethingWrong = FALSE;
$nameLen = $surnameLen = $emailLen = $passwordLen = TRUE;

// Actions when user submits the form to register
if(isset($_POST['submit'])){
    // Getting data from the post method, testing data and setting to variables
    $name = test_data($_POST["firstName"]);
    $surname = test_data($_POST["lastName"]);
    $email = test_data($_POST["email"]);
    $password = test_data($_POST["password"]);

    $nameLen = strlen($name) <= 30; // checking length of name
    $surnameLen = strlen($surname) <= 30; // checking length of surname
    $emailLen = strlen($email) <= 200; // checking length of email
    $passwordLen = strlen($password) <= 50; // checking length of password

    // Actions if length of variables are accepted
    if($nameLen && $surnameLen && $emailLen && $passwordLen){
        // query to add a user to person table in database
        $query = "INSERT INTO person (name, surname, email, psw) VALUES ('$name', '$surname', '$email', '$password')";

        // Executing query and if it returns true we set variable showDivSuccess true to show a success message
        if($conn->query($query) === TRUE){
            $showDivSuccess = TRUE;
            echo mysqli_insert_id($conn);
        }else{
            // if query fails we check if there is a duplicate registered user
            $queryDuplicateEmail = "SELECT ID FROM Person WHERE email = '$email'";
            $checkEmail = $conn->query($queryDuplicateEmail);

            if($checkEmail->num_rows === 1){ // if there is duplicate registered user we show an error for duplicate email
                $showDivDuplicateEmail = TRUE;
            }else{
                $showDivSomethingWrong = TRUE; // if there is not a duplicate email we show a general error message
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

$conn->close(); // closing the connection of database
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
    <!--- Bar for big screens -->
    <div class="w3-bar w3-blue showBar">
        <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Register</b></a>
        <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
    </div>

    <!--- Side bar for small screens -->
    <div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
        <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
        <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Register</b></a>
        <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right">Login</a>
    </div>

    <!--- Button to show side bar on click -->
    <button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

    <!--- Panel of the register form -->
    <div class="w3-card w3-border contactRegisterPanel">
        <div class="w3-container w3-blue">
            <h3 class="headerPanel">Register</h3>
        </div>

        <!--- Register form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container w3-padding-16 contactRegisterForm">
            <!--- Required input text for name -->
            <input class="w3-input w3-border w3-margin-top" id="firstName" name="firstName" type="text" placeholder="Name *" required><br>

            <!--- Required input text for surname  -->
            <input class="w3-input w3-border w3-margin-top" id="lastName" name="lastName" type="text" placeholder="Surname *" required><br>

            <!--- Required input text for email -->
            <input class="w3-input w3-border w3-margin-top" id="email" name="email" type="email" placeholder="Email *" required><br>

            <!--- Required input text for password -->
            <input class="w3-input w3-border w3-margin-top" id="password" name="password" type="password" placeholder="Password *" required>

            <!--- Checkbox to show and hide password -->
            <input id="labelShow" class="w3-check w3-margin-top" type="checkbox" onclick="showPassword('password')">
            <label for="labelShow">Show password</label><br><br>

            <div class="w3-panel w3-green" <?php /* if variable showDivSuccess is true we show a success message */
            if($showDivSuccess === TRUE) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>You have successfully registered!</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if the length of name is bigger than 30 characters we show an error message */
            if(!$nameLen) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>The maximum length of name is 30 characters.</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if the length of surname is bigger than 30 characters we show an error message */
            if(!$surnameLen) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>The maximum length of surname is 30 characters.</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if the length of email is bigger than 200 characters we show an error message */
            if(!$emailLen) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>The maximum length of email is 200 characters.</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if the length of password is bigger than 50 characters we show an error message */
            if(!$passwordLen) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>The maximum length of password is 50 characters.</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if the variable showDivDuplicateEmail is true we show an error for duplicate email */
            if($showDivDuplicateEmail) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>There is already an account with that email.</p>
            </div>

            <div class="w3-panel w3-red" <?php /* if registration is failed and there is no duplicate email we show a general error message */
            if($showDivSomethingWrong) {
                echo 'style="display: block"';
            }else{
                echo 'style="display: none"';
            }?>>
                <p>Something went wrong. Please check your information.</p>
            </div>

            <!--- Button to submit the form -->
            <input class="w3-button w3-green transmission" type="submit" name="submit" value="Register">
        </form>
    </div>
</body>
</html>
