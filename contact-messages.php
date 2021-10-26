<?php
require 'connect.php';

$conn = $_SESSION["conn"]; // variable that connected to database

if(!isset($_SESSION['logged_in'])){
    header("Location:index.php");
}

$idOfPerson = $_SESSION['id']; // id of person that is logged in

// checking if person is administrator

$queryPersonIsAdminOfSite = "SELECT administrator FROM person WHERE ID = '$idOfPerson';";
$personIsAdminOfSiteRes = $conn->query($queryPersonIsAdminOfSite);
$rowPersonIsAdminOfSite = $personIsAdminOfSiteRes->fetch_assoc();

$personIsAdminOfSite = $rowPersonIsAdminOfSite['administrator'];

if($personIsAdminOfSite !== '1'){
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Messages - GDD Maker</title>
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
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button w3-indigo"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings <i class="fa fa-cog"></i></a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout <i class="fa fa-sign-out"></i></a>
        </div>
    </div>
</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button w3-indigo"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings <i class="fa fa-cog"></i></a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout <i class="fa fa-sign-out"></i></a>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="w3-container w3-border w3-padding-16 personalInfo">
    <div class="w3-container w3-center w3-left w3-border-right w3-border-bottom w3-padding-16">
        <button id="buttonPersonalInfo" class="w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission"
                onclick="window.location.href = 'profile.php'">
            Personal Information</button><br><br>
        <button id="buttonTeamsInvites" class="w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission"
                onclick="window.location.href = 'invites-teams.php'">
            Invites and Teams</button><br><br>
        <button id="buttonTeamsInvites" class="w3-button w3-border w3-round w3-blue w3-border-blue w3-hover-blue transmission">
            Contact Messages</button>
    </div>

    <div class="w3-container contact-messages">
        <h3>Messages</h3>
        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableCharacters-dialogs">
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date</th>
            </tr>

            <?php
            $allMessagesResult = $conn->query("SELECT * FROM contact");

            while($row = $allMessagesResult->fetch_assoc()){
                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>" . "<td>" . $row['surname'] . "</td>" . "<td>" . $row['email'] . "</td>"
                . "<td>" . $row['message'] . "</td>" . "<td>" . $row['date'] . "</td>";
            }
            ?>

        </table>
    </div>
</div>
</body>
</html>
