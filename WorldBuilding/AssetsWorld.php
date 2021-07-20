<?php

require '../connect.php'; // connecting to database
$conn = $_SESSION["conn"]; // variable that connected to database

// If user is not logged in then we redirect user to login page
if(!isset($_SESSION['logged_in'])){
    header("Location:../login.php");
}

$idOfPerson = $_SESSION['id']; // getting the id of user if is logged in

/*
 Getting the id of the document with the GET method for the Game Elements page. If there is no id of document we
 redirect user to write page
*/
if(isset($_GET['id'])){
    $idOfDocument = $_GET['id']; // gets id of document
}else{
    header("Location:../write.php"); // redirects user to write page
}

/*
 * Getting the name of the document
 */
$resultNameDoc = mysqli_query($conn, "SELECT name FROM document WHERE ID='$idOfDocument';");
$rowDocName = $resultNameDoc->fetch_assoc();
$nameOfDoc = $rowDocName["name"];

/*
 * Checking if user does not have access to the document that is typing at the url. If user does not have access
 * we redirect user to write page
 */
if($resultAccessDoc = $conn->query("SELECT * from person_edits_document WHERE PERSON_ID = '$idOfPerson' AND DOCUMENT_ID = '$idOfDocument' 
                                      AND status_of_invitation = 'accepted';")){
    if($resultAccessDoc->num_rows === 0){
        header('Location:../write.php');
    }
}else{
    header("Location:../write.php");
}

/*
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
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
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
        <button class="w3-button"><b>Profile</b> <i class="fa fa-user-circle"></i></button>
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

<form class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding">
    <label for="musicDescription">Describe the music of the game</label>
    <textarea class="w3-input w3-border w3-margin-top" rows="2" type="text" id="musicDescription"
              name="musicDescription"></textarea><br>

    <label for="musicType">Add a kind of music that the game has</label>

    <button onclick="showElement('music-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="musicType" type="button"
            name="musicType"><i class="fa fa-plus"></i></button><br><br>

    <div id="music-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('music-modal')"
                      class="w3-button w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add a kind of music</h3><br>

                <label for="musicTypeName">Name of music kind *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTypeName" name="musicTypeName" required><br>

                <label for="charTypeDescription">Describe the reason why you chose to use this kind of music in the game</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="charTypeDescription"
                          name="charTypeDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMusicType" type="submit"
                            name="saveMusicType">Save</button>
                </div>
            </div>
        </div>
    </div>

    <label for="musicTrack">Add a track of music that plays in the game</label>

    <button onclick="showElement('musicTrack-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="musicTrack" type="button" name="musicTrack">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="musicTrack-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('musicTrack-modal')"
                      class="w3-button w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add a music track</h3><br>

                <label for="musicTrackName">Name of track *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTrackName" name="musicTrackName" required><br>

                <label>Choose a kind or more that the track belongs</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="musicTypeObjects">
                    <tr>
                        <th>Kind of music</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Kind 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj1" type="button"
                                    name="btnAddMuc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Kind 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj2" type="button"
                                    name="btnAddMuc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Kind 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj3" type="button"
                                    name="btnAddMuc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label for="musicTrackMaker">Creators of track</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="musicTrackMaker" name="musicTrackMaker"><br>


                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMusicTrack" type="submit"
                            name="saveMusicTrack">Save</button>
                </div>
            </div>
        </div>
    </div>

    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="button" value="Submit">
</form>
</body>
</html>
