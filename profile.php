<?php

require 'connect.php'; // connecting to database
$conn = $_SESSION["conn"]; // variable that connected to database

// loading previous data fields
$queryData = "SELECT name, surname, email FROM person WHERE ID = '1';";
$data = $conn->query($queryData);

$prevName = $prevSurname = $prevEmail = "";

if($data->num_rows === 1){
    $row = $data->fetch_assoc();

    $prevName = $row["name"];
    $prevSurname = $row["surname"];
    $prevEmail = $row["email"];
}

$name = $surname = $email = $newPass = $confNewPass = "";

$showDivSuccess = $showDivDuplicateEmail = $showDivSomethingWrong = $passIsDiff = $updateEmail = FALSE;
$nameLen = $surnameLen = $emailLen = $passwordLen = TRUE;

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = test_data($_POST["userName"]);
    $surname = test_data($_POST["userSurname"]);
    $email = test_data($_POST["userEmail"]);
    $password = test_data($_POST["newPass"]);
    $confNewPass = test_data($_POST["confirmPass"]);

    $nameLen = strlen($name) <= 30; // checking length of name
    $surnameLen = strlen($surname) <= 30; // checking length of surname
    $emailLen = strlen($email) <= 200; // checking length of email
    $passwordLen = strlen($password) <= 50; // checking length of password

    $passIsDiff = $password !== $confNewPass; // checking if passwords are same or not

    if($nameLen && $surnameLen && $emailLen && $passwordLen && !$passIsDiff){
        $query = "UPDATE person SET  name = '$name', surname = '$surname'";

        // if the email in field is different from the previous we are adding it to the query
        if($prevEmail !== $email){
            $query = $query . ", email = '$email'";
            $updateEmail = TRUE;
        }

        // if the password is not blank we are adding it to the query
        if($password !== ""){
            $query = $query . ", psw = '$password'";
        }
        $query = $query . " WHERE ID = '1';";

        if($conn->query($query) === TRUE){
            $showDivSuccess = TRUE;

            $prevName = $name;
            $prevSurname = $surname;
            if($updateEmail) $prevEmail = $email;
        }else{
            $queryDuplicateEmail = "SELECT ID FROM Person WHERE email = '$email' AND ID <> '1';";
            $checkEmail = $conn->query($queryDuplicateEmail);

            if($checkEmail->num_rows === 1){ // checking if there is already an account with that email
                $showDivDuplicateEmail = TRUE;
            }else{
                echo "Error: " . $query . "<br>" . $conn->error;
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
    <title>Profile - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">

    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">

<body id="body">

<div class="w3-bar w3-blue showBar">
    <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.html" class="w3-bar-item w3-button">Settings</a>
            <button class="w3-bar-item w3-button">Logout</button>
        </div>
    </div>
</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.html" class="w3-bar-item w3-button">Settings</a>
            <button class="w3-bar-item w3-button">Logout</button>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="w3-container w3-border w3-padding-16 personalInfo">
    <div class="w3-container w3-center w3-left w3-border-right w3-border-bottom w3-padding-16">
        <button id="buttonPersonalInfo" class="w3-button w3-border w3-round w3-blue w3-border-blue w3-hover-blue transmission">
            Personal information</button><br><br>
        <button id="buttonTeamsInvites" class="w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission"
                onclick="window.location.href = 'invites-teams.php'">
            Invites and Teams</button>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container inputProfile" id="personalInfo">
        <label for="chooseFile" id="labelImUser">Choose an image for your profile</label><br><br>
        <input type="file" id="imgUser" class="w3-margin-top" name="imgUser" style="display: none;" accept="image/*">
        <input type="button" id="chooseFile" class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission"
               value="Choose a file" onclick="document.getElementById('imgUser').click();" /><br><br>

        <label for="userName" id="userNameLabel">Name</label>
        <input class="w3-input w3-border w3-margin-top" id="userName" name="userName" value="<?php echo $prevName?>" type="text" required><br>

        <label for="userSurname" id="userSurnameLabel">Surname</label>
        <input class="w3-input w3-border w3-margin-top" id="userSurname" name="userSurname" value="<?php echo $prevSurname?>" type="text" required><br>

        <label for="userEmail" id="userEmailLabel">Email</label>
        <input class="w3-input w3-border w3-margin-top" id="userEmail" name="userEmail" value="<?php echo $prevEmail?>" type="email" required><br>

        <label for="newPass" id="newPassLabel">New password</label>
        <input class="w3-input w3-border w3-margin-top" id="newPass" name="newPass" type="password"><br>

        <label for="confirmPass" id="confirmPassLabel">Confirm the new password</label>
        <input class="w3-input w3-border w3-margin-top" id="confirmPass" name="confirmPass" type="password"><br>

        <div class="w3-panel w3-green" <?php if($showDivSuccess === TRUE) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>You have successfully updated your personal information!</p>
        </div>

        <div class="w3-panel w3-red" <?php if($passIsDiff) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>The passwords in password field and confirm password field must be the same.</p>
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

        <input class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission" type="submit" value="Submit">
    </form>

</div>
</body>
</html>
