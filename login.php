<?php
	if (session_status() == PHP_SESSION_NONE) {
    	session_start();
	}
	if ($_SESSION['logged_in']==true) {
		header('Location: index.php');
	}	
	$con = mysqli_connect("webpagesdb.it.auth.gr:3306","thesis2021","Thesis2021*","thesis2021"); 
	mysqli_set_charset($con,"utf8");
	if($con === false){
		die("ERROR: Could not connect. " . mysqli_connect_error());
	}
	$error="";
	if(isset($_POST['submit'])){
		$email= $_POST['email'];
		$psw = $_POST['psw'];
		if(empty($_POST['email']) or empty($_POST['psw'])){
			$error="Fill in Username and Password";
		}
		if($error==""){
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
			else{
				$error="Wrong combination of Username and Password";
			}
		}
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
	 <div class="w3-bar w3-blue w3-border showBar">
        <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
        <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
        <a href="contact.html" class="w3-bar-item w3-button">Contact</a>
        <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
        <a href="register.html" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
        <a href="login.html" class="w3-bar-item w3-button w3-teal w3-right"><b>Login</b></a>
    </div>

	 <div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
		 <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
		 <a href="index.html" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
		 <a href="write.html" class="w3-bar-item w3-button">Write GDD</a>
		 <a href="contact.html" class="w3-bar-item w3-button">Contact</a>
		 <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
		 <a href="register.html" class="w3-bar-item w3-button w3-teal w3-right">Register</a>
		 <a href="login.html" class="w3-bar-item w3-button w3-teal w3-right"><b>Login</b></a>
	 </div>

	 <button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="container">
	<input type="text" placeholder="Enter Email" name="email" required>
	<input type="password" placeholder="Enter Password" id="psw" name="psw" required>

	<input id="labelShow" class="w3-check w3-margin-top" type="checkbox" onclick="showPassword('psw')">
	<label for="labelShow">Show password</label><br><br>

	<button type="submit">Login</button>
	<a href="register.html">Don't have an account?</a>
</div>


<?php 
	if($error!=""){
		echo '<script language="javascript">';
		echo 'alert("',$error,'")';
		echo '</script>';
	}
	?>
</body>
</html>
