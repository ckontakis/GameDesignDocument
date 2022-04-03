<?php
require 'connect.php'; // connecting to database
$con = $_SESSION["conn"]; // variable that connected to database

if(isset($_SESSION['logged_in'])){
    header('Location:index.php');
}

$showError = false;

if(isset($_POST['submit'])){
    $email = test_data($_POST['email']);
    $psw = test_data($_POST['psw']);

    $query = "SELECT * FROM person WHERE email = '$email' AND psw= '$psw' ";
    $result = mysqli_query ( $con, $query );
    $row = mysqli_fetch_assoc($result);

    if (mysqli_num_rows ( $result )>=1) {
        session_start();
        $_SESSION['id']=$row['ID'];
        mysqli_close($con);
        header('Location: index.php');
        $_SESSION['logged_in'] = true;
    }
    else {
        $showError = true;
    }
}

/*
 * Function to filter data.
 */
function test_data($data)
{
    return htmlspecialchars(stripslashes($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
	<title>Login - GDD Maker</title>
	<link rel="icon" href="Images/favicon-new.ico">
	<script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="./css/loginstyle.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="./css/main.css">
	
<body>
	<!--- Bar for big screens -->
	 <div class="w3-bar w3-blue w3-border showBar">
        <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
        <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Login</b></a>
    </div>
    <!--- Side bar for small screens -->
	 <div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
		 <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
		 <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
		 <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
		 <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
		 <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
		 <a href="register.php" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
		 <a href="login.php" class="w3-bar-item w3-button w3-teal w3-right"><b>Login</b></a>
	 </div>

	 <button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<!--- Panel of the login form -->
<div class="w3-card w3-border contactRegisterPanel w3-margin-bottom">
    <div class="w3-container w3-blue">
        <h3 class="headerPanel">Login</h3>
    </div>

    <!--- Login form -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
          class="w3-container w3-padding-16 contactRegisterForm">

           <!--- Label for input of email -->
        <label for="email">Email *</label>
        <!--- Required input text for email -->
        <input class="w3-input w3-border" id="email" name="email" placeholder="Enter Email" type="email" required><br>

        <!--- Label for input of email -->
        <label for="psw">Password *</label>
        <!--- Required input text for password -->
        <input class="w3-input w3-border" id="psw" name="psw" placeholder="Enter Password" type="password" required>

        <!--- Checkbox to show and hide password -->
        <input id="labelShow" class="w3-check" type="checkbox" onclick="showPassword('psw')">
        <label for="labelShow">Show password</label><br><br>

        <div class="w3-panel w3-red" <?php /* if credentials are wrong we show an error message */
        if ($showError) {
            echo 'style="display: block"';
        } else {
            echo 'style="display: none"';
        } ?>>
            <p>Wrong combination of username and password.</p>
        </div>


        <!--- Button to submit the form -->
        <input class="w3-button w3-green transmission" type="submit" name="submit" value="Login">
    </form>
</div>
</body>
</html>
