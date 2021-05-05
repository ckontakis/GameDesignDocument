<?php

require 'connect.php';
$conn = $_SESSION["conn"]; // variable that connected to database


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
        <button id="buttonPersonalInfo" class="w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission"
                onclick="window.location.href = 'profile.php'">
            Personal information</button><br><br>
        <button id="buttonTeamsInvites" class="w3-button w3-border w3-round w3-border-blue w3-blue w3-hover-blue transmission">
            Invites and Teams</button>
    </div>

    <div class="w3-container teams-invites" id="invitesTeams">
        <h3 class="w3-border-bottom">Invites</h3><br>
        <div class="w3-container w3-border w3-padding-16">
            <label id="labelInviteDoc">User is inviting you to edit a document</label><br>
            <button id="buttonAcceptInviteDoc" class="w3-button w3-margin-top w3-green transmission">Accept</button>
            <button id="buttonDeclineInviteDoc" class="w3-button w3-margin-top w3-red transmission">Decline</button>
        </div>
        <h3 class="w3-border-bottom">Teams</h3><br>
        <div class="w3-container w3-border w3-padding-16">
            <label id="labelInviteTeam">Test team is inviting you to join the team</label><br>
            <button id="buttonAcceptInviteTeam" class="w3-button w3-margin-top w3-green transmission">Accept</button>
            <button id="buttonDeclineInviteTeam" class="w3-button w3-margin-top w3-red transmission">Decline</button>
        </div><br>

        <button id="createNewTeam" onclick="document.getElementById('newTeam-modal').style.display='block'"
                class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission">Create a new team</button>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div id="newTeam-modal" class="w3-modal">
                <div class="w3-modal-content w3-animate-zoom">
                    <div class="w3-container">
                        <span onclick="document.getElementById('newTeam-modal').style.display='none'" class="w3-button
                        w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
                        <h3 class="headerForModal">Create a team</h3><br>

                        <label for="nameTeam" class="w3-margin-top" id="labelNameTeam">Type the name of the team *</label><br>
                        <input class="w3-input w3-border w3-margin-top" type="text" id="nameTeam" name="nameTeam" required><br>

                        <label for="emailTeamMember" class="w3-margin-top" id="labelEmailMem">Invite a person to your team</label><br>

                        <input class="w3-input w3-border w3-margin-top inputEmailMember" type="email"
                               placeholder="Type the email of the person that you want to invite"
                               id="emailTeamMember" name="emailTeamMember">
                        <button class="w3-button w3-border w3-margin-top w3-border-blue w3-hover-blue transmission"
                                id="addMember" type="button" name="addMember" style="display: inline-block;">
                            <i class="fa fa-plus"></i></button><br><br>

                        <div class="w3-container w3-padding-16">
                            <button class="w3-button w3-green transmission" id="saveObject" type="submit" name="saveObject">
                                Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
