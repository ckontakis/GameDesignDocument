<?php

require 'connect.php';
$conn = $_SESSION["conn"]; // variable that connected to database

$idOfPerson = '1';

$showModal = FALSE;
$clickedModalIdTeam = "";

$nameOfTeam = "";
$nameLen = TRUE;
$showDivSuccess = $showDivDuplicateTeam = $showDivSomethingWrong = FALSE;


// load teams
$queryLoadTeams = "SELECT TEAM_ID FROM person_is_in_team WHERE PERSON_ID = '$idOfPerson' AND status_of_invitation = 'accepted';";
$teamsIdResult = $conn->query($queryLoadTeams);

//$teamIdForModals = $teamsIdResult; // copying team ids to create modals
$allTeamsNames = array();

while ($row = $teamsIdResult->fetch_assoc()) {
    $teamIdFindName = $row['TEAM_ID'];
    $queryFindNameTeam = "SELECT name FROM team WHERE ID = '$teamIdFindName';";

    $nameTeamResult = $conn->query($queryFindNameTeam);
    $findNameOfTeam = $nameTeamResult->fetch_assoc();

    $nameOfTeamFinal =  $findNameOfTeam['name'];
    array_push($allTeamsNames, $nameOfTeamFinal);
}


if(isset($_POST['saveCreateTeam'])){
    $nameOfTeam = test_data($_POST['nameTeam']);
    $nameLen = strlen($nameOfTeam) <= 50;

    if($nameLen){
        $query = "INSERT INTO team (name) VALUES ('$nameOfTeam');";

        if($conn->query($query) === TRUE){
            $showDivSuccess = TRUE;

            // finding the id of the added team
            $queryIdOfTeam = "SELECT id FROM team WHERE name = '$nameOfTeam';";
            $idOfTeamRes = $conn->query($queryIdOfTeam);

            $rowIdOfTeam = $idOfTeamRes->fetch_assoc();
            $teamId = $rowIdOfTeam['id'];

            $queryAddPerson = "INSERT INTO person_is_in_team (PERSON_ID, TEAM_ID, status_of_invitation, isAdmin)
VALUES ('$idOfPerson', '$teamId', 'accepted', '1');";

            if($conn->query($queryAddPerson) === FALSE){
                $showDivSomethingWrong = TRUE;
            }else{
                header("Refresh:0");
            }
        }else{
            $queryDuplicateTeam = "SELECT ID FROM team WHERE name = '$nameOfTeam';";
            $checkTeam = $conn->query($queryDuplicateTeam);

            if($checkTeam->num_rows === 1){ // checking if there is already a team with that name
                $showDivDuplicateTeam = TRUE;
            }else{
                $showDivSomethingWrong = TRUE;
            }
        }
    }
}

if(isset($_POST['saveInvitePerson'])){
    $emailInvite = test_data($_POST['emailTeamMember']);
    $teamToInvite = $_POST['keyTeam'];

    // finding id of person to invite

    $queryToFindIdPerson = "SELECT ID FROM person WHERE email = '$emailInvite'";
    $resultFindPerson = $conn->query($queryToFindIdPerson);

    $rowFindPerson = $resultFindPerson->fetch_assoc();
    $idOfPersonToInvite = $rowFindPerson['ID'];

    // finding id of team

    $queryToFindTeam = "SELECT ID FROM team WHERE name = '$teamToInvite'";
    $resultFindTeam = $conn->query($queryToFindTeam);

    $rowFindTeam = $resultFindTeam->fetch_assoc();
    $idOfTeamToInvite = $rowFindTeam['ID'];

    $emailInviteLen = strlen($emailInvite) <= 200;

    $isAdmin = '0';

    if(isset($_POST['adminCheck'])){
        $isAdmin = '1';
    }

    if($emailInviteLen){
        $queryInviteMember = "INSERT INTO person_is_in_team (PERSON_ID, TEAM_ID, isAdmin) VALUES 
        ('$idOfPersonToInvite', '$idOfTeamToInvite', '$isAdmin')";

        if($conn->query($queryInviteMember) === FALSE){
            echo "Something went wrong.";
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
    <title>Invites & Teams - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">

    <script>
        /*
        Script to show or hide the create team elements when page loads.
        */
        window.onload = function(){
            let showCreateTeam = localStorage.getItem('formCreateTeam');

            let x = document.getElementById('formCreateTeam');
            let elButton = document.getElementById('fontCreateTeam');

            if(showCreateTeam === 'true'){
                x.style.display = 'block';
                elButton.className = 'fa fa-minus';
            }else{
                x.style.display = 'none';
                elButton.className = 'fa fa-plus';
            }
        }

    </script>

    <script src="JavaScript/Main.js"></script>
    <script src="JavaScript/WorldBuilding.js"></script>
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
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="invites-teams.php" class="w3-bar-item w3-button">Settings</a>
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
            <a href="invites-teams.php" class="w3-bar-item w3-button">Settings</a>
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
        </div><br>

        <div class="w3-container w3-border w3-padding-16">
            <label id="labelInviteTeam">Test team is inviting you to join the team</label><br>
            <button id="buttonAcceptInviteTeam" class="w3-button w3-margin-top w3-green transmission">Accept</button>
            <button id="buttonDeclineInviteTeam" class="w3-button w3-margin-top w3-red transmission">Decline</button>
        </div>

        <h3 class="w3-border-bottom">Teams</h3><br>

        <button id="createNewTeam" onclick="showAndHide('formCreateTeam', 'fontCreateTeam')"
                class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission">Create a new team
            <i id="fontCreateTeam" class="fa fa-plus"></i></button><br><br>


        <?php
        for($i=0; $i < count($allTeamsNames) ; $i++){

            $queryFindMembersIds = "SELECT PERSON_ID, isAdmin FROM person_is_in_team WHERE TEAM_ID = (SELECT id FROM team WHERE name = '$allTeamsNames[$i]')";
            $resultFindMembersIds = $conn->query($queryFindMembersIds);?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container inputProfile" id="invitePersonForm">
                <?php
            echo "<div id=\"$allTeamsNames[$i]-modal\" class=\"w3-modal\">
            <div class=\"w3-modal-content w3-animate-zoom\">
                <div class=\"w3-container\">
                <span onclick=\"hideElement('$allTeamsNames[$i]-modal')\" class=\"w3-button w3-display-topright w3-hover-red\">
                    <i class=\"fa fa-close\"></i></span>
                  
                    <h3 class=\"headerForModal\">Invite a person</h3><br>

                    <label for=\"emailTeamMember\" class=\"w3-margin-top\" id=\"labelEmailMem\">Invite a person to your team</label><br>

                    <input style=\"display: block;\" class=\"w3-input w3-border w3-margin-top\" type=\"email\"
                           placeholder=\"Type the email of the person that you want to invite\"
                           id=\"emailTeamMember\" name=\"emailTeamMember\" required>

                    <input class=\"w3-check w3-margin-top\" id=\"adminCheck\" name=\"adminCheck\" type=\"checkbox\">
                    <label for=\"adminCheck\">Admin of team</label><br><br>
                    
                    <input type=\"hidden\"  name=\"keyTeam\" value=\"$allTeamsNames[$i]\" />
                    
                    <h3>Members</h3>
                    <table class=\"w3-table w3-border w3-centered w3-striped w3-margin-top\" id=\"tableInvitedPeople\">
                        <tr>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Email</th>
                            <th>Admin</th>
                        </tr> ";

                        while ($row = $resultFindMembersIds->fetch_assoc()) {
                            $memberId = $row['PERSON_ID'];
                            $queryFindNameOfMember = "SELECT name, surname, email FROM person WHERE id = '$memberId'";

                            $resultMemberInfo = $conn->query($queryFindNameOfMember);

                            while($rowInfo = $resultMemberInfo->fetch_assoc()){
                                $nameMem = $rowInfo['name'];
                                $surnameMem = $rowInfo['surname'];
                                $emailMem = $rowInfo['email'];

                                echo "<tr> <td>$nameMem</td> <td>$surnameMem</td> <td>$emailMem</td>
                                <td>";
                                if($row['isAdmin']) echo 'Yes'; else echo 'No';
                                echo "</td></tr> ";
                            }
                        }

                    echo "</table><br>

                    <div class=\"w3-container w3-padding-16\">
                        <button class=\"w3-button w3-green transmission\" id=\"saveInvitePerson\" type=\"submit\" name=\"saveInvitePerson\">Save</button>
                    </div>
                   </form>
                </div>
            </div>
        </div>";
        }
        ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="w3-container w3-border w3-padding-16 w3-animate-opacity" id="formCreateTeam">

                <label for="nameTeam" class="w3-margin-top" id="labelNameTeam">Type the name of the team *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="nameTeam" name="nameTeam" required><br>

                <div class="w3-panel w3-green" <?php if($showDivSuccess) {
                    echo 'style="display: block"';
                }else{
                    echo 'style="display: none"';
                }?>>
                    <p>You have successfully created a team!</p>
                </div>

                <div class="w3-panel w3-red" <?php if(!$nameLen) {
                    echo 'style="display: block"';
                }else{
                    echo 'style="display: none"';
                }?>>
                    <p>The maximum length of name is 50 characters.</p>
                </div>

                <div class="w3-panel w3-red" <?php if($showDivDuplicateTeam) {
                    echo 'style="display: block"';
                }else{
                    echo 'style="display: none"';
                }?>>
                    <p>There is already a team with that name.</p>
                </div>

                <div class="w3-panel w3-red" <?php if($showDivSomethingWrong) {
                    echo 'style="display: block"';
                }else{
                    echo 'style="display: none"';
                }?>>
                    <p>Something went wrong. Please check your information.</p>
                </div>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveCreateTeam" type="submit" name="saveCreateTeam">
                        Save</button>
                </div>
            </div>
        </form>

        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableTeams">
            <tr>
                <th>Team</th>
                <th>Members</th>
            </tr>

            <?php

            for($i = 0; $i < count($allTeamsNames) ; $i++) {

                echo "<tr> " . "<td>$allTeamsNames[$i]</td> ";
                echo "<td><button class=\"w3-button w3-border transmission\" onclick=\"showElement('$allTeamsNames[$i]-modal')\" id=\"$allTeamsNames[$i]\" type=\"button\"
                                    name=\"$allTeamsNames[$i]\"><i class=\"fa fa-users\"></i></button></td> </tr>";
            }

            ?>
        </table><br>

    </div>
</div>
</body>
</html>
