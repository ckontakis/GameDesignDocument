<?php

require 'connect.php';
$conn = $_SESSION["conn"]; // variable that connected to database

if (!isset($_SESSION['logged_in'])) {
    header("Location:login.php");
}

$idOfPerson = $_SESSION['id'];

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

    $nameOfTeamFinal = $findNameOfTeam['name'];
    array_push($allTeamsNames, $nameOfTeamFinal);
}

if (isset($_POST['saveCreateTeam'])) {
    $nameOfTeam = test_data($_POST['nameTeam']);
    $nameLen = strlen($nameOfTeam) <= 50;

    if ($nameLen) {
        $query = "INSERT INTO team (name) VALUES ('$nameOfTeam');";

        if ($conn->query($query) === TRUE) {
            $showDivSuccess = TRUE;

            // finding the id of the added team
            $queryIdOfTeam = "SELECT id FROM team WHERE name = '$nameOfTeam';";
            $idOfTeamRes = $conn->query($queryIdOfTeam);

            $rowIdOfTeam = $idOfTeamRes->fetch_assoc();
            $teamId = $rowIdOfTeam['id'];

            $queryAddPerson = "INSERT INTO person_is_in_team (PERSON_ID, TEAM_ID, status_of_invitation, isAdmin)
VALUES ('$idOfPerson', '$teamId', 'accepted', '1');";

            if ($conn->query($queryAddPerson) === FALSE) {
                $showDivSomethingWrong = TRUE;
            } else {
                header("Refresh:0");
            }
        } else {
            $queryDuplicateTeam = "SELECT ID FROM team WHERE name = '$nameOfTeam';";
            $checkTeam = $conn->query($queryDuplicateTeam);

            if ($checkTeam->num_rows === 1) { // checking if there is already a team with that name
                $showDivDuplicateTeam = TRUE;
            } else {
                $showDivSomethingWrong = TRUE;
            }
        }
    }
}

$emailInviteLen = TRUE;
if (isset($_POST['saveInvitePerson'])) {
    $emailInvite = test_data($_POST['emailTeamMember']);
    $teamToInvite = $_POST['keyTeam'];

    $invitePerson = TRUE;

    // finding id of person to invite

    $queryToFindIdPerson = "SELECT ID FROM person WHERE email = '$emailInvite'";
    $resultFindPerson = $conn->query($queryToFindIdPerson);

    $rowFindPerson = $resultFindPerson->fetch_assoc();
    if (isset($rowFindPerson['ID'])) {
        $idOfPersonToInvite = $rowFindPerson['ID'];
    } else {
        $invitePerson = FALSE;
        echo "<script>alert('Invitation failed: The given email does not match with any user.')</script>";
    }


    // finding id of team

    $queryToFindTeam = "SELECT ID FROM team WHERE name = '$teamToInvite'";
    $resultFindTeam = $conn->query($queryToFindTeam);

    $rowFindTeam = $resultFindTeam->fetch_assoc();
    $idOfTeamToInvite = $rowFindTeam['ID'];

    // checking if person is already invited
    if ($invitePerson) {
        $queryToCheckIsInvited = "SELECT PERSON_ID, TEAM_ID FROM person_is_in_team WHERE PERSON_ID = '$idOfPersonToInvite' AND TEAM_ID = '$idOfTeamToInvite'";
        $resultCheckInvited = $conn->query($queryToCheckIsInvited);

        $rowCheckIsInvited = $resultCheckInvited->fetch_assoc();
        if (isset($rowCheckIsInvited['PERSON_ID'])) {
            $invitePerson = FALSE;
            echo "<script>alert('Invitation failed: The user is already member of the team or is invited to become member of the team')</script>";
        }
    }


    $emailInviteLen = strlen($emailInvite) <= 200;

    if (!$emailInviteLen) {
        echo "<script>alert('Invitation failed: The maximum length of email is 200 characters.')</script>";
    }

    $isAdmin = '0';

    if (isset($_POST['adminCheck'])) {
        $isAdmin = '1';
    }

    if ($emailInviteLen && $invitePerson) {
        $queryInviteMember = "INSERT INTO person_is_in_team (PERSON_ID, TEAM_ID, isAdmin) VALUES 
        ('$idOfPersonToInvite', '$idOfTeamToInvite', '$isAdmin')";

        if ($conn->query($queryInviteMember) === FALSE) {
            echo "<script>alert('Invitation failed: please check your information.')</script>";
        }
    }
}

// Actions for button submit to accept the invite of team

if (isset($_POST['buttonAcceptInviteTeam'])) {
    $idOfTeamPost = $_POST['keyIdTeam'];

    $queryAcceptInvite = "UPDATE person_is_in_team SET status_of_invitation = 'accepted' WHERE PERSON_ID = '$idOfPerson' AND TEAM_ID = '$idOfTeamPost'";

    if ($conn->query($queryAcceptInvite)) {
        header("Refresh:0");
    }
}

// Actions for button submit to reject the invite of team

if (isset($_POST['buttonRejectInviteTeam'])) {
    $idOfTeamPost = $_POST['keyIdTeam'];

    $queryRejectInvite = "DELETE FROM person_is_in_team WHERE PERSON_ID = '$idOfPerson' AND TEAM_ID = '$idOfTeamPost';";

    if ($conn->query($queryRejectInvite)) {
        header("Refresh:0");
    }
}

// Actions when admin deletes a team

if (isset($_POST['deleteTeam'])) {
    $idOfTeamToDelete = $_POST['keyIdTeam'];
    if ($conn->query("DELETE FROM person_is_in_team WHERE TEAM_ID = '$idOfTeamToDelete';")) {
        //if($conn->query("DELETE FROM team_edits_document WHERE TEAM_ID = '$idOfTeamToDelete'")){
        if ($conn->query("DELETE FROM team WHERE ID = '$idOfTeamToDelete';")) {
            header("Refresh:0");
        }
    }
}

/*
 * Actions when user accepts an invite to edit a document
 */
if(isset($_POST["buttonAcceptInviteDocument"])){
    $idOfDocument = $_POST["keyIdDoc"];

    $queryAcceptInvDoc = "UPDATE person_edits_document SET status_of_invitation='accepted' 
WHERE PERSON_ID='$idOfPerson' AND DOCUMENT_ID='$idOfDocument';";
    if($conn->query("$queryAcceptInvDoc")){
        header("Refresh:0");
    }
}

/*
 * Actions when user rejects an invite to edit a document
 */
if(isset($_POST["buttonDeclineInviteDocument"])){
    $idOfDocument = $_POST["keyIdDoc"];

    $queryDeclineInvDoc = "DELETE FROM person_edits_document WHERE PERSON_ID='$idOfPerson' AND DOCUMENT_ID='$idOfDocument';";
    if($conn->query("$queryDeclineInvDoc")){
        header("Refresh:0");
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
    <title>Invites & Teams - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">

    <script>
        /*
        Script to show or hide the create team elements when page loads.
        */
        window.onload = function () {
            let showCreateTeam = localStorage.getItem('formCreateTeam');

            let x = document.getElementById('formCreateTeam');
            let elButton = document.getElementById('fontCreateTeam');

            if (showCreateTeam === 'true') {
                x.style.display = 'block';
                elButton.className = 'fa fa-minus';
            } else {
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
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout</a>
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
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout</a>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i>
</button>

<?php

/*
 * Checking if logged in user is administrator of the website
 */
$queryPersonIsAdminOfSite = "SELECT administrator FROM person WHERE ID = '$idOfPerson';";
$personIsAdminOfSiteRes = $conn->query($queryPersonIsAdminOfSite);
$rowPersonIsAdminOfSite = $personIsAdminOfSiteRes->fetch_assoc();

$personIsAdminOfSite = $rowPersonIsAdminOfSite['administrator'];
?>

<div class="w3-container w3-border w3-padding-16 personalInfo">
    <div class="w3-container w3-center w3-left w3-border-right w3-border-bottom w3-padding-16">
        <button id="buttonPersonalInfo" class="w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission"
                onclick="window.location.href = 'profile.php'">
            Personal Information
        </button>
        <br><br>
        <button id="buttonTeamsInvites"
                class="w3-button w3-border w3-round w3-border-blue w3-blue w3-hover-blue transmission">
            Invites and Teams
        </button>
        <?php
        if ($personIsAdminOfSite === '1') {
            echo "<br><br>";
            echo "<button id=\"buttonContactMess\" class=\"w3-button w3-border w3-round w3-border-blue w3-hover-blue transmission\"
                onclick=\"window.location.href = 'contact-messages.php'\">
            Contact Messages</button>";
        }
        ?>
    </div>

    <div class="w3-container teams-invites" id="invitesTeams">
        <h3 class="w3-border-bottom">Invites</h3><br>

        <?php
        $queryFindAllTeamInvites = "SELECT TEAM_ID FROM person_is_in_team WHERE PERSON_ID = '$idOfPerson' AND status_of_invitation = 'pending';";
        $findAllTeamInvitesResult = $conn->query($queryFindAllTeamInvites);

        while ($rowTeamInvites = $findAllTeamInvitesResult->fetch_assoc()){
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
              class="w3-container w3-border w3-padding-16">
            <?php
            $idOfTeamInvite = $rowTeamInvites['TEAM_ID'];
            $findNameOfTeamInvite = $conn->query("SELECT name FROM team WHERE ID = '$idOfTeamInvite'");
            $rowNameOfTeamInvite = $findNameOfTeamInvite->fetch_assoc();
            $nameOfTeamInvite = $rowNameOfTeamInvite['name'];

            echo "<label>$nameOfTeamInvite team is inviting you to join the team</label><br>
            <button type=\"submit\" name=\"buttonAcceptInviteTeam\"
                    class=\"w3-button w3-margin-top w3-green transmission\">Accept</button>
            <button type=\"submit\" name=\"buttonRejectInviteTeam\"
                    class=\"w3-button w3-margin-top w3-red transmission\">Reject</button>
                    <input type=\"hidden\"  name=\"keyIdPerson\" value=\"$idOfPerson\" />
                    <input type=\"hidden\"  name=\"keyIdTeam\" value=\"$idOfTeamInvite\" />
            </form><br>";
            }
            ?>

            <!--- Loading all invites that user has to edit documents -->


                <?php
                $queryFindAllDocumentInvites = "SELECT DOCUMENT_ID from person_edits_document WHERE PERSON_ID='$idOfPerson' 
                                                    AND status_of_invitation='pending';";
                $resFindAllDocInvites = $conn->query($queryFindAllDocumentInvites);
                while($rowDocInvites = $resFindAllDocInvites->fetch_assoc()){
                    ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                      class="w3-container w3-border w3-padding-16">
                  <?php
                    $idOfDocInv = $rowDocInvites['DOCUMENT_ID'];
                    $nameOfDocInviteRes = $conn->query("SELECT name from document WHERE ID='$idOfDocInv';");

                    if($nameOfDocInviteRes->num_rows === 1){
                        $rowNameDoc = $nameOfDocInviteRes->fetch_assoc();
                        $nameOfDocInvite = $rowNameDoc['name'];

                        echo "<label>An administrator is inviting you to edit $nameOfDocInvite document</label><br>
                    <button name=\"buttonAcceptInviteDocument\" type=\"submit\" class=\"w3-button w3-margin-top w3-green transmission\">Accept
                    </button>
                    <button name=\"buttonDeclineInviteDocument\" type=\"submit\" class=\"w3-button w3-margin-top w3-red transmission\">Decline
                    </button>
                    <input type=\"hidden\"  name=\"keyIdPerson\" value=\"$idOfPerson\" />
                    <input type=\"hidden\"  name=\"keyIdDoc\" value=\"$idOfDocInv\" /></form>";
                    }
                }
                ?>


            <h3 class="w3-border-bottom">Teams</h3><br>

            <button id="createNewTeam" onclick="showAndHide('formCreateTeam', 'fontCreateTeam')"
                    class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission">Create a new team
                <i id="fontCreateTeam" class="fa fa-plus"></i></button>

            <?php
            for ($i = 0;
            $i < count($allTeamsNames);
            $i++){

            // finding the id of team that clicked to open the modal
            $idOfTeamModalResult = $conn->query("SELECT id FROM team WHERE name = '$allTeamsNames[$i]'");
            $rowIdOfTeamModal = $idOfTeamModalResult->fetch_assoc();

            if (isset($rowIdOfTeamModal['id'])) {
                $idOfTeamModal = $rowIdOfTeamModal['id'];
            } else {
                continue;
            }

            //Getting all members of the team that accepted tha invitation or the invitation is on pending status
            $queryFindMembersIds = "SELECT PERSON_ID, isAdmin, status_of_invitation FROM person_is_in_team WHERE 
                                                       TEAM_ID = '$idOfTeamModal'
                                                   AND (status_of_invitation = 'accepted' OR status_of_invitation = 'pending');";
            $resultFindMembersIds = $conn->query($queryFindMembersIds); ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                  class="w3-container inputProfile" id="invitePersonForm">
                <?php
                echo "<div id=\"$allTeamsNames[$i]-modal\" class=\"w3-modal\">
            <div class=\"w3-modal-content w3-animate-zoom\">
                <div class=\"w3-container\">
                <span onclick=\"hideElement('$allTeamsNames[$i]-modal')\" class=\"w3-button w3-display-topright w3-hover-red\">
                    <i class=\"fa fa-close\"></i></span>
                  
                    <h3 class=\"headerForModal\">Invite a person</h3><br>

                    <label for=\"emailTeamMember$allTeamsNames[$i]\" class=\"w3-margin-top\" id=\"labelEmailMem\">Invite a person to your team</label><br>

                    <input style=\"display: block;\" class=\"w3-input w3-border w3-margin-top\" type=\"email\"
                           placeholder=\"Type the email of the person that you want to invite\"
                           id=\"emailTeamMember$allTeamsNames[$i]\" name=\"emailTeamMember\" required>";

                // checking if signed in person is admin of team to add the checkbox for admin option.

                $queryFindIfPersonIsAdmin = "SELECT isAdmin FROM person_is_in_team WHERE PERSON_ID = '$idOfPerson' AND TEAM_ID = '$idOfTeamModal';";
                $findIfPersonAdmin = $conn->query($queryFindIfPersonIsAdmin);
                $findIfPersonAdminRes = $findIfPersonAdmin->fetch_assoc();

                if ($findIfPersonAdminRes['isAdmin'] === '1') {
                    echo "<input class=\"w3-check w3-margin-top\" id=\"adminCheck\" name=\"adminCheck\" type=\"checkbox\">
                    <label for=\"adminCheck\">Admin of team</label><br><br>";
                }

                echo "<input type=\"hidden\"  name=\"keyTeam\" value=\"$allTeamsNames[$i]\" />
                    
                    <h3>Members</h3>
                    <table class=\"w3-table w3-border w3-centered w3-striped w3-margin-top\" id=\"tableInvitedPeople\">
                        <tr>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th>Invitation</th>
                        </tr> ";

                // adding all members in the table
                while ($row = $resultFindMembersIds->fetch_assoc()) {
                    $memberId = $row['PERSON_ID'];
                    $queryFindNameOfMember = "SELECT name, surname, email FROM person WHERE id = '$memberId'";

                    $resultMemberInfo = $conn->query($queryFindNameOfMember);

                    while ($rowInfo = $resultMemberInfo->fetch_assoc()) {
                        $nameMem = $rowInfo['name'];
                        $surnameMem = $rowInfo['surname'];
                        $emailMem = $rowInfo['email'];

                        echo "<tr> <td>$nameMem</td> <td>$surnameMem</td> <td>$emailMem</td>
                                <td>";
                        if ($row['isAdmin']) echo '<span class="w3-text-green">Yes</span>'; else echo '<span class="w3-text-red">No</span>';
                        echo "</td> ";
                        echo "<td>";
                        if ($row['status_of_invitation'] === 'accepted') {
                            //echo "<p class='w3-text-green'>Accepted</p>";
                            echo "<span class='w3-text-green'>Accepted</span>";
                        } else {
                            echo "<span class='w3-text-orange'>Pending</span>";
                        }
                        echo "</td> </tr>";
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
                <form class="w3-margin-top" method="post"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="w3-container w3-border w3-padding-16 w3-animate-opacity" id="formCreateTeam">

                        <label for="nameTeam" class="w3-margin-top" id="labelNameTeam">Type the name of the team
                            *</label>
                        <input class="w3-input w3-border w3-margin-top" type="text" id="nameTeam" name="nameTeam"
                               required><br>

                        <div class="w3-panel w3-green" <?php if ($showDivSuccess) {
                            echo 'style="display: block"';
                        } else {
                            echo 'style="display: none"';
                        } ?>>
                            <p>You have successfully created a team!</p>
                        </div>

                        <div class="w3-panel w3-red" <?php if (!$nameLen) {
                            echo 'style="display: block"';
                        } else {
                            echo 'style="display: none"';
                        } ?>>
                            <p>The maximum length of name is 50 characters.</p>
                        </div>

                        <div class="w3-panel w3-red" <?php if ($showDivDuplicateTeam) {
                            echo 'style="display: block"';
                        } else {
                            echo 'style="display: none"';
                        } ?>>
                            <p>There is already a team with that name.</p>
                        </div>

                        <div class="w3-panel w3-red" <?php if ($showDivSomethingWrong) {
                            echo 'style="display: block"';
                        } else {
                            echo 'style="display: none"';
                        } ?>>
                            <p>Something went wrong. Please check your information.</p>
                        </div>

                        <div class="w3-container w3-padding-16">
                            <button class="w3-button w3-green transmission" id="saveCreateTeam" type="submit"
                                    name="saveCreateTeam">
                                Save
                            </button>
                        </div>
                    </div>
                </form>

                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableTeams">
                    <tr>
                        <th>Team</th>
                        <th>Members</th>
                        <th>Delete</th>
                    </tr>

                    <?php

                    for ($i = 0;
                    $i < count($allTeamsNames);
                    $i++) {

                    // finding the id of team
                    $idOfTeamTable = $conn->query("SELECT id FROM team WHERE name = '$allTeamsNames[$i]'");

                    if ($rowIdOfTeamTable = $idOfTeamTable->fetch_assoc()) {
                        $idOfTeam = $rowIdOfTeamTable['id'];
                    } else {
                        continue;
                    }


                    // checking if signed in person is admin of team to add delete button.

                    $queryFindIfPersonIsAdminTable = "SELECT isAdmin FROM person_is_in_team WHERE PERSON_ID = '$idOfPerson' AND TEAM_ID = '$idOfTeam';";
                    $findIfPersonAdminTable = $conn->query($queryFindIfPersonIsAdminTable);
                    $findIfPersonAdminTableRes = $findIfPersonAdminTable->fetch_assoc();

                    echo "<tr> " . "<td>$allTeamsNames[$i]</td> ";
                    echo "<td><button class=\"w3-button w3-border transmission\" onclick=\"showElement('$allTeamsNames[$i]-modal')\" id=\"$allTeamsNames[$i]\" type=\"button\"
                                    name=\"$allTeamsNames[$i]\"><i class=\"fa fa-users\"></i></button></td>";
                    if ($findIfPersonAdminTableRes['isAdmin'] === '1'){
                    ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <?php
                        echo "<td><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete $allTeamsNames[$i] team?')\" type=\"submit\"
                                    name=\"deleteTeam\"><i class=\"fa fa-trash\"></i></button>
                                    <input type=\"hidden\"  name=\"keyIdTeam\" value=\"$idOfTeam\" /></td>";

                        } else {
                            echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\"
                                    name=\"deleteTeamDisabled\" disabled><i class=\"fa fa-trash\"></i></button></td>";
                        }
                        echo "</form>";
                        echo "</tr>";
                        }
                        ?>
                </table>
                <br>
    </div>
</div>
</body>
</html>
