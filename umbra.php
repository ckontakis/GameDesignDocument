<?php

require './connect.php'; // connecting to database
$conn = $_SESSION["conn"]; // variable that connected to database

// If user is not logged in then we redirect user to login page
if(!isset($_SESSION['logged_in'])){
    header("Location:../login.php");
}

$idOfPerson = $_SESSION['id']; // getting the id of user if is logged in

/**
 Getting the id of the document with the GET method for the Character model page. If there is no id of document we
 redirect user to write page
*/
if(isset($_GET['id'])){
    $idOfDocument = $_GET['id']; // gets id of document
}else{
    header("Location:../write.php"); // redirects user to write page
}

/**
 * Getting the name of the document
 */
$resultNameDoc = mysqli_query($conn, "SELECT name, UMBRA_FILE_ID FROM document WHERE ID='$idOfDocument';");
$rowDocName = $resultNameDoc->fetch_assoc();

$nameOfDoc = $rowDocName["name"];
$umbraFileId = $rowDocName["UMBRA_FILE_ID"];

/**
 * Checking if user does not have access to the document that is typing at the url. If user does not have access
 * we redirect user to write page
 */
if($resultAccessDoc = $conn->query("SELECT * from person_edits_document WHERE PERSON_ID = '$idOfPerson' AND DOCUMENT_ID = '$idOfDocument' 
                                      AND status_of_invitation = 'accepted';")){
    if($resultAccessDoc->num_rows === 0){
        // Getting all team ids that can edit the document
        $resultTeamsThatEditDoc = $conn->query("SELECT TEAM_ID FROM team_edits_document WHERE DOCUMENT_ID='$idOfDocument';");
        // If there are teams that can edit the document
        if ($resultTeamsThatEditDoc->num_rows > 0) {
            $personEditDoc = false;

            // Checking if person is member of a team that can edit the document
            while ($rowTeamEditDoc = $resultTeamsThatEditDoc->fetch_assoc()) {
                $idOfTeamThatEdits = $rowTeamEditDoc['TEAM_ID'];
                $checkIfUserIsInTeam = $conn->query("SELECT * FROM person_is_in_team WHERE PERSON_ID='$idOfPerson' 
                                  AND TEAM_ID='$idOfTeamThatEdits' AND status_of_invitation='accepted'");
                if ($checkIfUserIsInTeam->num_rows > 0) {
                    $personEditDoc = true;
                }
            }

            // If person is not member of some team that can edit the document we redirect the user to the write page
            if (!$personEditDoc) {
                header('Location:../write.php');
            }
        } else {
            header('Location:../write.php');
        }
    }
}else{
    header("Location:../write.php");
}

$docRoot = $_SERVER["DOCUMENT_ROOT"]; // the path for the root of document

/**
 * Actions when user submit to save changes.
 */
if (isset($_POST["saveCharacterModel"])) {
    if ($_FILES["umbra_file"]["name"] !== "") {
        $filename = $_FILES["umbra_file"]["name"];
        $tempname = $_FILES["umbra_file"]["tmp_name"];
        $folder = "$docRoot/Files-GDD/$nameOfDoc/Umbra/" . $filename;

        if (mysqli_query($conn, "INSERT INTO file (name) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $file_id = mysqli_insert_id($conn);
            if (mysqli_query($conn, "UPDATE document SET UMBRA_FILE_ID = '$file_id' WHERE ID = '$idOfDocument'")) {
                $successfulSubmit = true;

                // If there is a cookie for the umbra file of GDD, then we delete it
                if (isset($_COOKIE["{$nameOfDoc}_gdd_umbra"])) {
                    setcookie("{$nameOfDoc}_gdd_umbra", "", time() - 3600);
                }
            }
        } else {
            $submitWentWrong = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Umbra - GDD Maker</title>
    <link rel="icon" href="./Images/favicon-new.ico">
    <script src="./JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="./css/main.css">

<body>

<div class="w3-bar w3-blue showBar">
    <a href="./index.php" class="w3-bar-item w3-button"><img src="./Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="./write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="./contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="./profile.php" class="w3-bar-item w3-button">Settings <i class="fa fa-cog"></i></a>
            <a href="./logout.php" class="w3-bar-item w3-button">Logout <i class="fa fa-sign-out"></i></a>
        </div>
    </div>
</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="./index.php" class="w3-bar-item w3-button"><img src="./Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="./write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="./contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="./profile.php" class="w3-bar-item w3-button">Settings <i class="fa fa-cog\"></i></a>
            <a href="./logout.php" class="w3-bar-item w3-button">Logout <i class="fa fa-sign-out"></i></a>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="w3-container pathPosition">
    <a href="./write.php" class="w3-hover-text-blue">Write GDD</a>
    <i class="fa fa-angle-double-right"></i>
    <span><?php echo $nameOfDoc ?></span>
    <i class="fa fa-angle-double-right"></i>
    <a href="umbra.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Umbra</a>
</div>

<div class="w3-container w3-blue panelInFormWorld">
    <h3 class="headerPanel">Upload your Umbra file</h3>
</div>

<div class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding">
    <form method="post" enctype="multipart/form-data" action="">
        <label for="umbra_file" class="w3-margin-top">Choose the Umbra file of your project. Visit
            <a href="http://ntsiouma.webpages.auth.gr/tool" class="w3-hover-text-blue" target="_blank">Umbra</a> to create your project.</label><br>
        <input type="file" id="umbra_file" class="w3-margin-top" name="umbra_file" accept=".umbra"><br>

        <?php
        if ($umbraFileId) {
            $resultFileName = $conn->query("SELECT name FROM file WHERE ID='$umbraFileId';");
            $rowFilename = $resultFileName->fetch_assoc();

            if ($rowFilename) {
                $filename = $rowFilename["name"];
                echo "<p>Download submitted Umbra file: <a class='w3-hover-text-blue' href='/Files-GDD/$nameOfDoc/Umbra/$filename' download>$filename</a></p>";
            }
        }
        ?>

        <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit"
               name="saveCharacterModel" value="Submit" />

        <!--- A message to inform the user that the umbra file submitted successfully -->
        <div class="w3-panel w3-green" <?php if($successfulSubmit) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>You have successfully submitted the umbra file!</p>
        </div>

        <!--- A message to inform the user that the umbra file wasn't submitted successfully -->
        <div class="w3-panel w3-red" <?php if($submitWentWrong) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>Something went wrong. Cannot submit the umbra file.</p>
        </div>
    </form>
</div>
</body>
</html>