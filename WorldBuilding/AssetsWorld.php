<?php

require '../connect.php'; // connecting to database
$conn = $_SESSION["conn"]; // variable that connected to database

// If user is not logged in then we redirect user to login page
if(!isset($_SESSION['logged_in'])){
    header("Location:../login.php");
}

$idOfPerson = $_SESSION['id']; // getting the id of user if is logged in

/**
 Getting the id of the document with the GET method for the Assets page. If there is no id of document we
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
$resultNameDoc = mysqli_query($conn, "SELECT name FROM document WHERE ID='$idOfDocument';");
$rowDocName = $resultNameDoc->fetch_assoc();
$nameOfDoc = $rowDocName["name"];

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

/**
 * Getting the id of Assets to connect elements (e.g music kind, track) with the assets of the document.
 * If there is a problem with the execution of queries we redirect user to write page.
 */

// finding the id of world_building table
if($resultInfoWorld = $conn->query("SELECT ID from world_building WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultInfoWorld->num_rows === 1){
        $rowInfoWorld = $resultInfoWorld->fetch_assoc();

        if(isset($rowInfoWorld['ID'])){
            $worldBuildingId = $rowInfoWorld['ID'];
            // finding the id of assets table
            if($resultInfoAssets = $conn->query("SELECT ID FROM assets WHERE WORLD_BUILDING_ID = '$worldBuildingId';")){
                if($resultInfoAssets->num_rows === 1){
                    $rowInfoAssets = $resultInfoAssets->fetch_assoc();

                    if(isset($rowInfoAssets['ID'])){
                        $assetsId = $rowInfoAssets['ID']; // setting the id of assets to variable assetsId
                    }else{
                        header("Location:../write.php");
                    }
                }
            }else{
                header("Location:../write.php");
            }
        }else{
            header("Location:../write.php");
        }


    }else{
        header("Location:../write.php");
    }
}

/**
 * Actions to load the text of music description
 */
$queryToLoadDescriptionMusic = "SELECT describe_music FROM assets WHERE ID='$assetsId';";
$resultLoadDescriptionMusic = $conn->query($queryToLoadDescriptionMusic);

if ($resultLoadDescriptionMusic->num_rows === 1) {
    $rowLoadDescriptionMusic = $resultLoadDescriptionMusic->fetch_assoc();
    $descriptionOfMusicValue = $rowLoadDescriptionMusic["describe_music"];
}

$successUpdateMusic = false; // variable to show success message
$somethingWrongMusic = false; // variable to show failure message

/**
 * Actions when user saves the description of the music
 */
if (isset($_POST["saveAssets"])) {
    $descriptionOfMusic = test_data($_POST["musicDescription"]);

    $queryToUpdateAssets = "UPDATE assets SET describe_music='$descriptionOfMusic' WHERE ID='$assetsId';";

    if ($conn->query($queryToUpdateAssets)) {
        $descriptionOfMusicValue = $descriptionOfMusic;
        $successUpdateMusic = true;
    } else {
        $somethingWrongMusic = true;
    }
}

/**
 * Actions when user adds a music kind
 */
if (isset($_POST["saveMusicType"])) {
    $nameOfMusicKind = test_data($_POST["musicTypeName"]);
    $descriptionOfMusicKind = test_data($_POST["musicTypeDescription"]);

    $queryToAddAMusicKind = "INSERT INTO music_kind (ASSETS_ID, name, describe_reason) VALUES ('$assetsId','$nameOfMusicKind', '$descriptionOfMusicKind');";

    if ($conn->query($queryToAddAMusicKind)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music kind')</script>";
    }
}

/**
 * Actions when user edits a music kind
 */
if (isset($_POST["editMusicKind"])) {
    $idOfMusicKind = $_POST["keyIdMusicKind"];
    $nameOfMusicKind = test_data($_POST["musicTypeName"]);
    $descriptionOfMusicKind = test_data($_POST["musicTypeDescription"]);

    $queryToUpdateMusicKind = "UPDATE music_kind SET name='$nameOfMusicKind', describe_reason='$descriptionOfMusicKind' WHERE ID='$idOfMusicKind';";

    if ($conn->query($queryToUpdateMusicKind)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot update the kind of music')</script>";
    }
}

/**
 * Actions when user deletes a music kind
 */
if (isset($_POST["deleteMusicKind"])) {
    $idOfMusicKind = $_POST["keyIdMusicKind"];

    $queryToDeleteMusicKind = "DELETE FROM music_kind WHERE ID='$idOfMusicKind';";
    if ($conn->query($queryToDeleteMusicKind)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete the kind of music')</script>";
    }
}

/**
 * Actions when user adds a music track
 */
if (isset($_POST["saveMusicTrack"])) {
    $nameOfTrack = test_data($_POST["musicTrackName"]);
    $creatorsOfTrack = test_data($_POST["musicTrackMaker"]);

    // Query to add a music track to the table track
    $queryToAddMusicTrack = "INSERT INTO track (ASSETS_ID, name, creators) VALUES ('$assetsId', '$nameOfTrack', '$creatorsOfTrack');";

    if ($conn->query($queryToAddMusicTrack)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music track')</script>";
    }
}

/**
 * Actions when user edits a music track
 */
if (isset($_POST["editMusicTrack"])){
    $idOfMusicTrack = $_POST["keyIdMusicTrack"];
    $nameOfTrack = test_data($_POST["musicTrackName"]);
    $creatorsOfTrack = test_data($_POST["musicTrackMaker"]);

    $queryToUpdateMusicTrack = "UPDATE track SET name='$nameOfTrack', creators='$creatorsOfTrack' WHERE ID='$idOfMusicTrack';";

    if ($conn->query($queryToUpdateMusicTrack)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music track')</script>";
    }
}

/**
 * Actions when user deletes a music track
 */
if (isset($_POST["deleteMusicTrack"])) {
    $idOfMusicTrackToDel = $_POST["keyIdMusicTrack"];

    $queryToDeleteMusicTrack = "DELETE FROM track WHERE ID='$idOfMusicTrackToDel';";

    if ($conn->query($queryToDeleteMusicTrack)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music track')</script>";
    }
}

/**
 * Actions when user adds a kind of music to a track
 */
if (isset($_POST["btnAddKindOfTrack"])) {
    $trackId = $_POST["trackId"];
    $musicKindId = $_POST["musicKindId"];

    $queryToAddMusicKindToTrack = "INSERT INTO track_has_music_kind (TRACK_ID, MUSIC_KIND_ID) VALUES ('$trackId', '$musicKindId');";

    if ($conn->query($queryToAddMusicKindToTrack)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music track')</script>";
    }
}

/**
 * Actions when user deletes a music kind from a track
 */
if (isset($_POST["delKindOfTrack"])) {
    $trackId = $_POST["trackId"];
    $musicKindId = $_POST["musicKindId"];

    $queryToDeleteMusicKindFromTrack = "DELETE FROM track_has_music_kind WHERE TRACK_ID='$trackId' AND MUSIC_KIND_ID='$musicKindId';";

    if ($conn->query($queryToDeleteMusicKindFromTrack)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add music track')</script>";
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
    <title>Assets - GDD Maker</title>
    <link rel="icon" href="../Images/favicon-new.ico">
    <script src="../JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../css/main.css">

<body>

<div class="w3-bar w3-blue showBar">
    <a href="../index.php" class="w3-bar-item w3-button"><img src="../Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="../write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="../contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="../profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="../logout.php" class="w3-bar-item w3-button">Logout</a>
        </div>
    </div>
</div>


<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="../index.php" class="w3-bar-item w3-button"><img src="../Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="../write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="../contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="../profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="../logout.php" class="w3-bar-item w3-button">Logout</a>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="w3-container pathPosition">
    <a href="../write.php" class="w3-hover-text-blue">Write GDD</a>
    <i class="fa fa-angle-double-right"></i>
    <span><?php echo $nameOfDoc ?></span>
    <i class="fa fa-angle-double-right"></i>
    <a href="AssetsWorld.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Assets</a>
</div>

<div class="w3-container w3-blue panelInFormWorld">
    <h3 class="headerPanel">Fill the assets</h3>
</div>

<div id="music-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container w3-center">
                <span onclick="hideElement('music-modal')"
                      class="w3-button w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a kind of music</h3><br>
            <form method="post" action="">
                <label for="musicTypeName">Name of music kind *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTypeName" name="musicTypeName" required><br>

                <label for="musicTypeDescription">Describe the reason why you chose to use this kind of music in the game</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="musicTypeDescription"
                          name="musicTypeDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMusicType" type="submit"
                            name="saveMusicType">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="musicTrack-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container w3-center">
                <span onclick="hideElement('musicTrack-modal')"
                      class="w3-button w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a music track</h3><br>

            <form method="post" action="">
                <label for="musicTrackName">Name of track *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTrackName" name="musicTrackName" required><br>

                <label for="musicTrackMaker">Creators of track</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTrackMaker" name="musicTrackMaker"><br>


                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMusicTrack" type="submit"
                            name="saveMusicTrack">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$queryToLoadAllMusicKinds = "SELECT * FROM music_kind WHERE ASSETS_ID='$assetsId';";
$resultLoadAllMusicKinds = $conn->query($queryToLoadAllMusicKinds);

while ($rowLoadMusicKind = $resultLoadAllMusicKinds->fetch_assoc()) {
    $idOfMusicKind = $rowLoadMusicKind["ID"];
    $nameOfMusicKind = $rowLoadMusicKind["name"];
    $descriptionOfMusicKind = $rowLoadMusicKind["describe_reason"];

    echo "<div id=\"music_kind-modal-edit$idOfMusicKind\" class=\"w3-modal\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <div class=\"w3-container w3-center\">
                <span onclick=\"hideElement('music_kind-modal-edit$idOfMusicKind')\"
                      class=\"w3-button w3-display-topright w3-hover-red\"><i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit the kind of music <b>$nameOfMusicKind</b></h3><br>
            <form method=\"post\" action=\"\">
                <label for=\"musicTypeName$idOfMusicKind\">Name of music kind *</label>
                <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"musicTypeName$idOfMusicKind\" name=\"musicTypeName\" value=\"$nameOfMusicKind\" required><br>

                <input type=\"hidden\"  name=\"keyIdMusicKind\" value=\"$idOfMusicKind\" />
                
                <label for=\"musicTypeDescription$idOfMusicKind\">Describe the reason why you chose to use this kind of music in the game</label>
                <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"musicTypeDescription$idOfMusicKind\"
                          name=\"musicTypeDescription\">$descriptionOfMusicKind</textarea><br>

                <div class=\"w3-container w3-padding-16\">
                    <button class=\"w3-button w3-green transmission\" id=\"editMusicKind\" type=\"submit\"
                            name=\"editMusicKind\">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>";
}

$queryToLoadAllMusicTracks = "SELECT * FROM track WHERE ASSETS_ID='$assetsId';";
$resultLoadAllMusicTracks = $conn->query($queryToLoadAllMusicTracks);

while ($rowLoadMusicTrack = $resultLoadAllMusicTracks->fetch_assoc()) {
    $idOfMusicTrack = $rowLoadMusicTrack["ID"];
    $nameOfMusicTrack = $rowLoadMusicTrack["name"];
    $trackCreators = $rowLoadMusicTrack["creators"];

    echo "<div id=\"music_track-modal-edit$idOfMusicTrack\" class=\"w3-modal\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <div class=\"w3-container w3-center\">
                <span onclick=\"hideElement('music_track-modal-edit$idOfMusicTrack')\"
                      class=\"w3-button w3-display-topright w3-hover-red\"><i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit the music track <b>$nameOfMusicTrack</b></h3><br>
            <form method=\"post\" action=\"\">
                <label for=\"musicTrackName$idOfMusicTrack\">Name of track *</label>
                <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"musicTrackName$idOfMusicTrack\" value=\"$nameOfMusicTrack\" name=\"musicTrackName\" required><br>

                <input type=\"hidden\"  name=\"keyIdMusicTrack\" value=\"$idOfMusicTrack\" />
                
                <label for=\"musicTrackMaker$idOfMusicTrack\">Creators of track</label>
                <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"musicTrackMaker$idOfMusicTrack\" value=\"$trackCreators\" name=\"musicTrackMaker\"><br>

                <div class=\"w3-container w3-padding-16\">
                    <button class=\"w3-button w3-green transmission\" id=\"editMusicTrack$idOfMusicTrack\" type=\"submit\"
                            name=\"editMusicTrack\">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>";

echo "<div id=\"music_tracks-add-music_kind$idOfMusicTrack\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('music_tracks-add-music_kind$idOfMusicTrack')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Choose a kind or more that the track <b>$nameOfMusicTrack</b> belongs</h3><br>";
?>

<table class="w3-table w3-border w3-centered w3-striped">
    <tr>
        <th>Kinds</th>
        <th>Add/Remove</th>
    </tr>
    <?php
    // query to load all kinds
    $queryLoadAllKindsForTracks = "SELECT ID, name FROM music_kind WHERE ASSETS_ID='$assetsId';";
    $resultLoadAllKindsForTracks = mysqli_query($conn, $queryLoadAllKindsForTracks); // executing the query

    while ($rowLoadKindForTracks = $resultLoadAllKindsForTracks->fetch_assoc()) {
        $rowIdKind = $rowLoadKindForTracks["ID"];
        $rowNameKind = $rowLoadKindForTracks["name"];

        $queryToCheckIfKindIsAdded = "SELECT * FROM track_has_music_kind WHERE 
                                                        TRACK_ID='$idOfMusicTrack' AND 
                                                MUSIC_KIND_ID='$rowIdKind';";
        $resultCheckIfKindIsAdded = mysqli_query($conn, $queryToCheckIfKindIsAdded);

        if ($resultCheckIfKindIsAdded->num_rows === 0) {
            echo "<tr>
                        <td>$rowNameKind</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfMusicTrack\" type=\"submit\" name=\"btnAddKindOfTrack\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"trackId\" value=\"$idOfMusicTrack\"/>
                        <input type=\"hidden\" name=\"musicKindId\" value=\"$rowIdKind\"/></form></td>
                        
                    </tr>";
        } else {
            echo "<tr>
                        <td>$rowNameKind</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfMusicTrack\" type=\"submit\" name=\"delKindOfTrack\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"trackId\" value=\"$idOfMusicTrack\"/>
                        <input type=\"hidden\" name=\"musicKindId\" value=\"$rowIdKind\"/></form></td>
                    </tr>";
        }
    }
    ?>
</table><br>
<?php echo "</div></div>";
}
?>
<div class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding">
    <label for="musicType">Add a kind of music that the game has</label>

    <button onclick="showElement('music-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="musicType" type="button"
            name="musicType"><i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadMusicKinds">
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

    <?php
    $queryToLoadAllMusicKindsV2 = "SELECT * FROM music_kind WHERE ASSETS_ID='$assetsId';";
    $resultLoadAllMusicKindsV2 = $conn->query($queryToLoadAllMusicKindsV2);

    while ($rowLoadMusicKind = $resultLoadAllMusicKindsV2->fetch_assoc()) {
        $idOfMusicKind = $rowLoadMusicKind["ID"];
        $nameOfMusicKind = $rowLoadMusicKind["name"];

        echo "<tr><td>" . $nameOfMusicKind . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('music_kind-modal-edit$idOfMusicKind')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the kind of music $nameOfMusicKind')\" type=\"submit\"
                                    name=\"deleteMusicKind\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdMusicKind\" value=\"$idOfMusicKind\" /></form></tr>";
    }
    ?>

    </table><br>

    <label for="musicTrack">Add a track of music that plays in the game</label>

    <button onclick="showElement('musicTrack-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="musicTrack" type="button" name="musicTrack">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadMusicKinds">
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Add kind of music</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryToLoadAllMusicTracksV2 = "SELECT * FROM track WHERE ASSETS_ID='$assetsId';";
        $resultLoadAllMusicTracksV2 = $conn->query($queryToLoadAllMusicTracksV2);

        while ($rowLoadMusicTrack = $resultLoadAllMusicTracksV2->fetch_assoc()) {
            $idOfMusicTrack = $rowLoadMusicTrack["ID"];
            $nameOfMusicTrack = $rowLoadMusicTrack["name"];

            echo "<tr><td>" . $nameOfMusicTrack . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('music_track-modal-edit$idOfMusicTrack')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('music_tracks-add-music_kind$idOfMusicTrack')\"><i class=\"fa fa-plus\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the music track $nameOfMusicTrack')\" type=\"submit\"
                                    name=\"deleteMusicTrack\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdMusicTrack\" value=\"$idOfMusicTrack\" /></form></tr>";
        }
        ?>

    </table><br>

    <form method="post" action="">
        <label for="musicDescription">Describe the music of the game</label>
        <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="musicDescription"
                  name="musicDescription"><?php if(isset($descriptionOfMusicValue)) echo $descriptionOfMusicValue; ?></textarea><br>

        <!--- A message to inform the user that updated the music description of the game successfully -->
        <div class="w3-panel w3-green" <?php if($successUpdateMusic) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>You have successfully updated the music description of the game!</p>
        </div>

        <!--- A message to inform the user that there was an error and didn't update the music description of the game -->
        <div class="w3-panel w3-red" <?php if($somethingWrongMusic) {
            echo 'style="display: block"';
        }else{
            echo 'style="display: none"';
        }?>>
            <p>Something went wrong. Unable to update the music description of the game.</p>
        </div>

        <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit" name="saveAssets" value="Submit" />
    </form>
</div>
</body>
</html>
