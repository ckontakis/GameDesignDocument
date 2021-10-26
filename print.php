<?php

require 'connect.php';
$conn = $_SESSION["conn"];

// If user is not logged in then we redirect user to login page
if(!isset($_SESSION['logged_in'])){
    header("Location:../login.php");
}

$idOfPerson = $_SESSION['id']; // getting the id of user if is logged in

/*
 Getting the id of the document with the GET method for the Assets page. If there is no id of document we
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

/*
 * Getting the id of the summary table
 */
$resultIdSummary = $conn->query("SELECT ID FROM game_summary WHERE DOCUMENT_ID='$idOfDocument';");
if ($resultIdSummary->num_rows === 1) {
    $rowIdSummary = $resultIdSummary->fetch_assoc();
    $summaryId = $rowIdSummary["ID"];
} else {
    header("Location:../write.php");
}

/*
 * Getting the id of World Building, Game Elements and Assets to print the content of these sections to the page.
 * If there is a problem with the execution of queries we redirect user to write page.
 */

// finding the id of world_building table
if($resultInfoWorld = $conn->query("SELECT ID from world_building WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultInfoWorld->num_rows === 1){
        $rowInfoWorld = $resultInfoWorld->fetch_assoc();

        if(isset($rowInfoWorld['ID'])){
            $worldBuildingId = $rowInfoWorld['ID'];
            // finding the id of game elements table
            if($resultInfoGameElements = $conn->query("SELECT ID, story_describe FROM game_elements WHERE WORLD_BUILDING_ID = '$worldBuildingId';")){
                if($resultInfoGameElements->num_rows === 1){
                    $rowInfoGameElements = $resultInfoGameElements->fetch_assoc();

                    if(isset($rowInfoGameElements['ID'])){
                        $gameElementsId = $rowInfoGameElements['ID']; // setting the id of game elements
                    }else{
                        header("Location:../write.php");
                    }

                    if (isset($rowInfoGameElements['story_describe'])) {
                        $storyDescribe = $rowInfoGameElements['story_describe'];
                    }
                }
            }else{
                header("Location:../write.php");
            }

            // finding the id of assets table
            if($resultInfoAssets = $conn->query("SELECT ID, describe_music FROM assets WHERE WORLD_BUILDING_ID = '$worldBuildingId';")){
                if($resultInfoAssets->num_rows === 1){
                    $rowInfoAssets = $resultInfoAssets->fetch_assoc();

                    if(isset($rowInfoAssets['ID'])){
                        $assetsId = $rowInfoAssets['ID']; // setting the id of assets
                        $descriptionOfMusicAssets = $rowInfoAssets["describe_music"];
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
    <title>Print - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/print.css">

<body>
<div class="w3-bar w3-blue showBar" id="bar">
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    echo "<div class=\"w3-dropdown-hover w3-right\">
    <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
    <div class=\"w3-dropdown-content w3-bar-block w3-border\">
        <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings <i class=\"fa fa-cog\"></i></a>
        <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout</a>
    </div>
    </div>";

    ?>
</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button">Write GDD</a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <?php
    echo "<div class=\"w3-dropdown-hover w3-right\">
    <button class=\"w3-button\">Profile <i class=\"fa fa-user-circle\"></i></button>
    <div class=\"w3-dropdown-content w3-bar-block w3-border\">
        <a href=\"profile.php\" class=\"w3-bar-item w3-button\">Settings <i class=\"fa fa-cog\"></i></a>
        <a href=\"logout.php\" class=\"w3-bar-item w3-button\">Logout</a>
    </div>
    </div>";
    ?>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" id="showSideBarButton" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="printButton" id="printButton">
<button class="w3-btn w3-large w3-center w3-round w3-border w3-border-blue w3-hover-blue transmission"
        onclick="window.print()">Print <i class="fa fa-print"></i></button>
</div>
<h2 class="headerForPrint">Game Design Document for the game <b><?php echo $nameOfDoc?></b></h2>

<div class="contentForPrint">
    <h3 class="sections">Summary</h3>

    <?php
    /*
     * Loading all information about the summary of the game.
     */
    $resultInfoSummary = $conn->query("SELECT * FROM game_summary WHERE ID='$summaryId';");
    $rowInfoSummary = $resultInfoSummary->fetch_assoc();

    $nameOfTheGame = $rowInfoSummary["name"];
    $conceptOfTheGame = $rowInfoSummary["concept"];
    $genreOfTheGame = $rowInfoSummary["genre"];
    $audienceOfTheGame = $rowInfoSummary["audience"];
    $systemOfTheGame = $rowInfoSummary["system"];
    $typeOfTheGame = $rowInfoSummary["type"];
    $settingOfTheGame = $rowInfoSummary["setting"];
    $softwareOfTheGame = $rowInfoSummary["software"];
    $gameCodeOfTheGame = $rowInfoSummary["game_code"];

    // Printing the name of the game
    if (isset($nameOfTheGame) && $nameOfTheGame !== "") {
        echo "<p><b>Name:</b> $nameOfTheGame</p>";
    }

    // Printing the concept of the game
    if (isset($conceptOfTheGame) && $conceptOfTheGame !== "") {
        echo "<p><b>Concept:</b> $conceptOfTheGame</p>";
    }

    // Printing the genre of the game
    if (isset($genreOfTheGame) && $genreOfTheGame !== "") {
        $genres = explode(",", $genreOfTheGame);

        echo "<p><b>Genres</b></p>";
        echo "<ul>";
        foreach ($genres as $genre) {
            if ($genre === "action") {
                echo "<li>Action</li>";
            } else if ($genre === "action_adventure") {
                echo "<li>Action-Adventure</li>";
            } else if ($genre === "adventure") {
                echo "<li>Adventure</li>";
            } else if ($genre === "fighting") {
                echo "<li>Fighting</li>";
            } else if ($genre === "platformer") {
                echo "<li>Platformer</li>";
            } else if ($genre === "role_playing") {
                echo "<li>Role-playing</li>";
            } else if ($genre === "simulation") {
                echo "<li>Simulation</li>";
            } else if ($genre === "puzzle") {
                echo "<li>Puzzle</li>";
            } else if($genre === "rhythm") {
                echo "<li>Rhythm</li>";
            } else if ($genre === "horror") {
                echo "<li>Horror</li>";
            } else if ($genre === "fps") {
                echo "<li>FPS</li>";
            } else if ($genre === "strategy") {
                echo "<li>Strategy</li>";
            } else if ($genre === "sports") {
                echo "<li>Sports</li>";
            } else if ($genre === "mmo") {
                echo "<li>MMO</li>";
            } else if ($genre === "gacha") {
                echo "<li>Gacha</li>";
            } else if ($genre === "other") {
                echo "<li>Other</li>";
            } else {
                echo "<li>$genre</li>";
            }
        }
        echo "</ul>";
    }

    // Printing the audience of the game
    if (isset($audienceOfTheGame) && $audienceOfTheGame !== "") {
        echo "<p><b>Audience:</b> ";
        if ($audienceOfTheGame === "baby") {
            echo "3+";
        } else if ($audienceOfTheGame === "child") {
            echo "7+";
        } else if ($audienceOfTheGame === "youngteen") {
            echo "12+";
        } else if ($audienceOfTheGame === "lateteen") {
            echo "16+";
        } else if ($audienceOfTheGame === "adult") {
            echo "18+";
        }

        echo "</p>";
    }

    // Printing the systems of the game
    if (isset($systemOfTheGame) && $systemOfTheGame !== "") {
        echo "<p><b>Target systems</b></p>";

        echo "<ul>";
        $systems = explode(",", $systemOfTheGame);
        foreach ($systems as $system) {
            if ($system === "pc") {
                echo "<li>PC</li>";
            } else if ($system === "mobile") {
                echo "<li>Mobile</li>";
            } else if ($system === "ps5") {
                echo "<li>PlayStation 5</li>";
            } else if ($system === "ps4") {
                echo "<li>PlayStation 4</li>";
            } else if ($system === "xbox") {
                echo "<li>Xbox Series X/S</li>";
            } else if ($system === "xboxOne") {
                echo "<li>Xbox One</li>";
            } else if ($system === "nintendoSwitch") {
                echo "<li>Nintendo Switch</li>";
            } else if ($system === "nintendo3ds") {
                echo "<li>Nintendo 3DS</li>";
            } else if ($system === "playVita") {
                echo "<li>PlayStation Vita</li>";
            } else if ($system === "wii") {
                echo "<li>Wii U</li>";
            } else if ($system === "other") {
                echo "<li>Other</li>";
            } else {
                echo "<li>$system</li>";
            }
        }
        echo "</ul>";
    }

    // Printing the types of the game
    if (isset($typeOfTheGame) && $typeOfTheGame !== "") {
        echo "<p><b>Game types</b></p>";
        $types = explode(",", $typeOfTheGame);
        echo "<ul>";
        foreach ($types as $type) {
            if ($type === "beatemup") {
                echo "<li>Beat-em Up</li>";
            } else if ($type === "hacknslash") {
                echo "<li>Hack'n Slash</li>";
            } else if ($type === "stealth") {
                echo "<li>Stealth</li>";
            } else if ($type === "survival") {
                echo "<li>Survival</li>";
            } else if ($type === "metroidvania") {
                echo "<li>Metroidvania</li>";
            } else if ($type === "textadventure") {
                echo "<li>Text Adventure</li>";
            } else if ($type === "graphicadventure") {
                echo "<li>Graphic Adventure</li>";
            } else if ($type === "visualnovel") {
                echo "<li>Visual Novels</li>";
            } else if ($type === "interactivemovie") {
                echo "<li>Interactive Movie</li>";
            } else if ($type === "rpg") {
                echo "<li>RPG</li>";
            } else if ($type === "roguelike") {
                echo "<li>Roguelike</li>";
            } else if ($type === "tacticalrole") {
                echo "<li>Tactical RPG</li>";
            } else if ($type === "sandboxrpg") {
                echo "<li>Sandbox RPG</li>";
            } else if ($type === "realtimestrategy") {
                echo "<li>Real-time Strategy</li>";
            } else if ($type === "realtimecombat") {
                echo "<li>Real-time Combat</li>";
            } else if ($type === "turnbased") {
                echo "<li>Turn Based</li>";
            } else if ($type === "towerdefence") {
                echo "<li>Tower Defence</li>";
            } else if ($type === "competitive") {
                echo "<li>Competitive</li>";
            } else if ($type === "trivia") {
                echo "<li>Trivia</li>";
            } else if ($type === "party") {
                echo "<li>Party</li>";
            } else if ($type === "other") {
                echo "<li>Other</li>";
            } else {
                echo "<li>$type</li>";
            }
        }
        echo "</ul>";
    }

    // Printing the setting of the game
    if (isset($settingOfTheGame) && $settingOfTheGame !== "") {
        echo "<p><b>Setting:</b> $settingOfTheGame</p>";
    }

    // Printing the software of the game
    if (isset($softwareOfTheGame) && $softwareOfTheGame !== "") {
        echo "<p><b>Software:</b> $softwareOfTheGame</p>";
    }

    // Printing the game code of the game
    if (isset($gameCodeOfTheGame) && $gameCodeOfTheGame !== "") {
        echo "<p><b>Game code:</b> $gameCodeOfTheGame</p>";
    }

    ?>
    <h3 class="sections">Mechanics</h3>
    <h3 class="sections">World Building</h3>
    <h4 class="sections">Game Elements</h4>
    <?php
    if (isset($storyDescribe) && $storyDescribe !== "") {
        echo "<p><b>Description of story:</b> $storyDescribe</p>";
    }
    ?>
    <?php
    // Loading all character to print them at the page.
    $resultLoadAllCharacter = $conn->query("SELECT * FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';");
    if ($resultLoadAllCharacter->num_rows !== 0) {
        echo "<div><p><b>Characters</b></p>";
        while ($rowLoadChar = $resultLoadAllCharacter->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $nameOfChar = $rowLoadChar["name"];
            $typeOfChar = $rowLoadChar["type_char"];
            $descriptionOfChar = $rowLoadChar["describe_char"];
            $imageIdOfChar = $rowLoadChar["IMAGE_ID"];

            if(isset($imageIdOfChar)){
                $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$imageIdOfChar';");

                if($rowImage = $resultImage->fetch_assoc()){
                    $imgFilenameChar = $rowImage["filename"];
                }
            }

            echo "<p><b>Name:</b> $nameOfChar</p>";
            echo "<p><b>Type:</b> $typeOfChar</p>";
            if (isset($descriptionOfChar) && $descriptionOfChar !== "") {
                echo "<p><b>Description:</b> $descriptionOfChar</p>";
            }

            if (isset($imgFilenameChar)) {
                echo "<p><b>Image</b></p><br>";
                echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/$imgFilenameChar' 
                      alt='Image of character $nameOfChar' style='width: 300px; height: auto;'><br><br>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    // Loading all objects to print them at the page
    $resultLoadAllObjects = $conn->query("SELECT * FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';");

    if ($resultLoadAllObjects->num_rows !== 0) {
        echo "<div><p><b>Objects</b></p>";
        while ($rowLoadObj = $resultLoadAllObjects->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $nameOfObj = $rowLoadObj["name"];
            $typeOfObj = $rowLoadObj["type_obj"];
            $descriptionOfObj = $rowLoadObj["describe_obj"];
            $imageIdOfObj = $rowLoadObj["IMAGE_ID"];

            echo "<p><b>Name:</b> $nameOfObj</p>";
            echo "<p><b>Type:</b> $typeOfObj</p>";

            if (isset($descriptionOfObj) && $descriptionOfObj !== "") {
                echo "<p><b>Description:</b> $descriptionOfObj</p>";
            }

            if(isset($imageIdOfObj)){
                $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$imageIdOfObj';");

                if($rowImage = $resultImage->fetch_assoc()){
                    $imgFilenameObj = $rowImage["filename"];
                }
            }

            if (isset($imgFilenameObj)) {
                echo "<p><b>Image</b></p><br>";
                echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/$imgFilenameObj' 
                      alt='Image of object $nameOfObj' style='width: 300px; height: auto;'><br><br>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    // Loading all locations to print them at the page
    $resultLoadAllLocations = $conn->query("SELECT * FROM game_location WHERE GAME_ELEMENTS_ID='$gameElementsId';");

    if ($resultLoadAllLocations->num_rows !== 0) {
        echo "<div><p><b>Locations</b></p>";
        while ($rowLoadLoc = $resultLoadAllLocations->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $idOfLoc = $rowLoadLoc["ID"];
            $nameOfLoc = $rowLoadLoc["name"];
            $descriptionOfLoc = $rowLoadLoc["describe_loc"];
            $imageIdOfLoc = $rowLoadLoc["IMAGE_ID"];

            echo "<p><b>Name:</b> $nameOfLoc</p>";

            if (isset($descriptionOfLoc) && $descriptionOfLoc !== "") {
                echo "<p><b>Description:</b> $descriptionOfLoc</p>";
            }

            // Loading all characters of location
            $resultLoadAllCharactersOfLoc = $conn->query("SELECT * FROM game_location_has_game_character WHERE GAME_LOCATION_ID='$idOfLoc';");

            if ($resultLoadAllCharactersOfLoc->num_rows !== 0) {
                echo "<p><b>Characters of location</b></p>";
                echo "<ul>";
                while ($rowLoadCharOfLoc = $resultLoadAllCharactersOfLoc->fetch_assoc()) {
                    $characterOfLocId = $rowLoadCharOfLoc["GAME_CHARACTER_ID"];

                    $resultNameOfCharOfLoc = $conn->query("SELECT name FROM game_character WHERE ID='$characterOfLocId';");
                    $rowLoadNameOfCharOfLoc = $resultNameOfCharOfLoc->fetch_assoc();
                    $nameOfCharOfLoc = $rowLoadNameOfCharOfLoc["name"];
                    echo "<li>$nameOfCharOfLoc</li>";
                }
                echo "</ul>";
            }

            // Loading all objects of location
            $resultLoadAllObjectsOfLoc = $conn->query("SELECT * FROM game_location_has_game_object WHERE GAME_LOCATION_ID='$idOfLoc';");

            if ($resultLoadAllObjectsOfLoc->num_rows !== 0) {
                echo "<p><b>Objects of location</b></p>";
                echo "<ul>";
                while ($rowLoadObjOfLoc = $resultLoadAllObjectsOfLoc->fetch_assoc()) {
                    $objOfLocId = $rowLoadObjOfLoc["GAME_OBJECT_ID"];

                    $resultNameOfObjOfLoc = $conn->query("SELECT name FROM game_object WHERE ID='$objOfLocId';");
                    $rowLoadNameOfObjOfLoc = $resultNameOfObjOfLoc->fetch_assoc();
                    $nameOfObjOfLoc = $rowLoadNameOfObjOfLoc["name"];
                    echo "<li>$nameOfObjOfLoc</li>";
                }
                echo "</ul>";
            }

            if(isset($imageIdOfLoc)){
                $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$imageIdOfLoc';");

                if($rowImage = $resultImage->fetch_assoc()){
                    $imgFilenameLoc = $rowImage["filename"];
                }
            }

            if (isset($imgFilenameLoc)) {
                echo "<p><b>Image</b></p><br>";
                echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/$imgFilenameLoc' 
                      alt='Image of location $nameOfLoc' style='width: 300px; height: auto;'><br><br>";
            }

            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    // Loading all dialogs to print them at the page.
    $resultLoadAllDialogs = $conn->query("SELECT * FROM game_dialog WHERE GAME_ELEMENTS_ID='$gameElementsId';");

    if ($resultLoadAllDialogs->num_rows !== 0) {
        echo "<div><p><b>Dialogues</b></p>";
        while ($rowLoadDialog = $resultLoadAllDialogs->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";

            $idOfDialog = $rowLoadDialog["ID"];
            $gameCharTalksId = $rowLoadDialog["GAME_CHARACTER_TALKS"];
            $nameOfDialog = $rowLoadDialog["name"];
            $textOfDialog = $rowLoadDialog["text"];

            echo "<p><b>Name:</b> $nameOfDialog</p>";
            echo "<p><b>Dialogue text:</b> $textOfDialog</p>";

            $resultNameOfCharTalks = $conn->query("SELECT name FROM game_character WHERE ID='$gameCharTalksId';");
            $rowNameOfCharTalks = $resultNameOfCharTalks->fetch_assoc();
            $nameOfCharTalks = $rowNameOfCharTalks["name"];
            echo "<p><b>Character that says the dialogue text:</b> $nameOfCharTalks</p>";

            // Loading all listeners of the dialogue
            $resultListenersOfDialogue = $conn->query("SELECT * FROM character_dialogs_character WHERE CHARACTER_TALKS_ID='$gameCharTalksId';");

            if ($resultListenersOfDialogue->num_rows !== 0) {
                echo "<p><b>Characters that listen the dialogue</b></p>";
                echo "<ul>";
                while ($rowLoadListenersOfDialogue = $resultListenersOfDialogue->fetch_assoc()) {
                    $listenerCharId = $rowLoadListenersOfDialogue["CHARACTER_LISTENS_ID"];
                    // finding the name of the character that listens
                    $resultNameListenerChar = $conn->query("SELECT name FROM game_character WHERE ID='$listenerCharId';");
                    $rowNameListenerChar = $resultNameListenerChar->fetch_assoc();
                    $nameListenerChar = $rowNameListenerChar["name"];
                    echo "<li>$nameListenerChar</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    // Loading all scenes to print their data at the page.
    $resultLoadAllScenes = $conn->query("SELECT * FROM scene WHERE GAME_ELEMENTS_ID='$gameElementsId';");

    if ($resultLoadAllScenes->num_rows !== 0) {
        echo "<div><p><b>Scenes</b></p>";
        while ($rowLoadScene = $resultLoadAllScenes->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $idOfScene = $rowLoadScene["ID"];
            $nameOfScene = $rowLoadScene["name"];
            $descriptionOfScene = $rowLoadScene["describe_scene"];

            echo "<p><b>Name:</b> $nameOfScene</p>";
            if (isset($descriptionOfScene) && $descriptionOfScene !== "") {
                echo "<p><b>Description of scene:</b> $descriptionOfScene</p>";
            }

            // Loading all characters of the scene
            $resultCharsOfScene = $conn->query("SELECT * FROM game_character_has_scene WHERE SCENE_ID='$idOfScene';");
            if ($resultCharsOfScene->num_rows !== 0) {
                echo "<p><b>Characters that take part in the scene</b></p>";
                echo "<ul>";
                while ($rowCharOfScene = $resultCharsOfScene->fetch_assoc()) {
                    $idOfCharOfScene = $rowCharOfScene["GAME_CHARACTER_ID"];

                    $resultNameOfCharOfScene = $conn->query("SELECT name FROM game_character WHERE ID='$idOfCharOfScene';");
                    $rowNameOfCharOfScene = $resultNameOfCharOfScene->fetch_assoc();
                    $nameOfCharOfScene = $rowNameOfCharOfScene["name"];
                    echo "<li>$nameOfCharOfScene</li>";
                }
                echo "</ul>";
            }

            // Loading all objects of the scene
            $resultObjectsOfScene = $conn->query("SELECT * FROM game_object_has_scene WHERE SCENE_ID='$idOfScene';");
            if ($resultObjectsOfScene->num_rows !== 0) {
                echo "<p><b>Objects of scene</b></p>";
                echo "<ul>";
                while ($rowObjOfScene = $resultObjectsOfScene->fetch_assoc()) {
                    $idOfObjOfScene = $rowObjOfScene["GAME_OBJECT_ID"];

                    $resultNameOfObjOfScene = $conn->query("SELECT name FROM game_object WHERE ID='$idOfObjOfScene';");
                    $rowNameOfObjOfScene = $resultNameOfObjOfScene->fetch_assoc();
                    $nameOfObjOfScene = $rowNameOfObjOfScene["name"];
                    echo "<li>$nameOfObjOfScene</li>";
                }
                echo "</ul>";
            }

            // Loading all locations of the scene
            $resultLocationsOfScene = $conn->query("SELECT * FROM game_location_has_scene WHERE SCENE_ID='$idOfScene';");
            if ($resultLocationsOfScene->num_rows !== 0) {
                echo "<p><b>Locations of scene</b></p>";
                echo "<ul>";
                while ($rowLocOfScene = $resultLocationsOfScene->fetch_assoc()) {
                    $idOfLocOfScene = $rowLocOfScene["GAME_LOCATION_ID"];

                    $resultNameOfLocOfScene = $conn->query("SELECT name FROM game_location WHERE ID='$idOfLocOfScene';");
                    $rowNameOfLocOfScene = $resultNameOfLocOfScene->fetch_assoc();
                    $nameOfLocOfScene = $rowNameOfLocOfScene["name"];
                    echo "<li>$nameOfLocOfScene</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    $resultLoadAllObjectives = $conn->query("SELECT * FROM game_objective WHERE GAME_ELEMENTS_ID='$gameElementsId';");
    if ($resultLoadAllObjectives->num_rows !== 0) {
        echo "<div><p><b>Objectives</b></p>";
        while ($rowLoadObjective = $resultLoadAllObjectives->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";

            $idOfObjective = $rowLoadObjective["ID"];
            $titleOfObjective = $rowLoadObjective["title"];
            $descriptionOfObjective = $rowLoadObjective["description"];

            echo "<p><b>Title:</b> $titleOfObjective</p>";
            if (isset($descriptionOfObjective) && $descriptionOfObjective !== "") {
                echo "<p><b>Description:</b> $descriptionOfObjective</p>";
            }

            // Loading all characters of the objective
            $resultCharsOfObjective = $conn->query("SELECT * FROM game_objective_has_game_character WHERE GAME_OBJECTIVE_ID='$idOfObjective';");
            if ($resultCharsOfObjective->num_rows !== 0) {
                echo "<p><b>Characters that take part in the objective</b></p>";
                echo "<ul>";
                while ($rowCharOfObjective = $resultCharsOfObjective->fetch_assoc()) {
                    $idOfCharOfObjective = $rowCharOfObjective["GAME_CHARACTER_ID"];

                    $resultNameOfCharOfObjective = $conn->query("SELECT name FROM game_character WHERE ID='$idOfCharOfObjective';");
                    $rowNameOfCharOfObjective = $resultNameOfCharOfObjective->fetch_assoc();
                    $nameOfCharOfObjective = $rowNameOfCharOfObjective["name"];
                    echo "<li>$nameOfCharOfObjective</li>";
                }
                echo "</ul>";
            }

            // Loading all objects of the objective
            $resultObjectsOfObjective = $conn->query("SELECT * FROM game_objective_has_game_object WHERE GAME_OBJECTIVE_ID='$idOfObjective';");
            if ($resultObjectsOfObjective->num_rows !== 0) {
                echo "<p><b>Objects of the objective</b></p>";
                echo "<ul>";
                while ($rowObjectOfObjective = $resultObjectsOfObjective->fetch_assoc()) {
                    $idOfObjectOfObjective = $rowObjectOfObjective["GAME_OBJECT_ID"];

                    $resultNameOfObjectOfObjective = $conn->query("SELECT name FROM game_object WHERE ID='$idOfObjectOfObjective';");
                    $rowNameOfObjectOfObjective = $resultNameOfObjectOfObjective->fetch_assoc();
                    $nameOfObjectOfObjective = $rowNameOfObjectOfObjective["name"];
                    echo "<li>$nameOfObjectOfObjective</li>";
                }
                echo "</ul>";
            }

            // Loading all scenes of the objective
            $resultScenesOfObjective = $conn->query("SELECT * FROM game_objective_has_scene WHERE GAME_OBJECTIVE_ID='$idOfObjective';");
            if ($resultScenesOfObjective->num_rows !== 0) {
                echo "<p><b>Scenes of the objective</b></p>";
                echo "<ul>";
                while ($rowSceneOfObjective = $resultScenesOfObjective->fetch_assoc()) {
                    $idOfSceneOfObjective = $rowSceneOfObjective["SCENE_ID"];

                    $resultNameOfSceneOfObjective = $conn->query("SELECT name FROM scene WHERE ID='$idOfSceneOfObjective';");
                    $rowNameOfSceneOfObjective = $resultNameOfSceneOfObjective->fetch_assoc();
                    $nameOfSceneOfObjective = $rowNameOfSceneOfObjective["name"];
                    echo "<li>$nameOfSceneOfObjective</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <h4 class="sections">Assets</h4>
    <?php
    if (isset($descriptionOfMusicAssets) && $descriptionOfMusicAssets !== "") {
        echo "<p><b>Description of the music that game has:</b> $descriptionOfMusicAssets</p>";
    }

    // Loading all kinds of music that game has
    $resultKindsOfMusic = $conn->query("SELECT name, describe_reason FROM music_kind WHERE ASSETS_ID='$assetsId';");
    if ($resultKindsOfMusic->num_rows !== 0) {
        echo "<div><p><b>Kinds of music that game has</b></p>";
        while ($rowKindOfMusic = $resultKindsOfMusic->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $nameOfMusicKind = $rowKindOfMusic["name"];
            $describeReasonMusicKind = $rowKindOfMusic["describe_reason"];

            echo "<p><b>Name:</b> $nameOfMusicKind</p>";
            if (isset($describeReasonMusicKind) && $describeReasonMusicKind !== "") {
                echo "<p><b>Description of the reason why we chose this kind of music: </b> $describeReasonMusicKind</p>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <?php
    // Loading all music tracks
    $resultAllMusicTracks = $conn->query("SELECT ID, name, creators FROM track WHERE ASSETS_ID='$assetsId';");
    if ($resultAllMusicTracks->num_rows !== 0) {
        echo "<div><p><b>Music tracks</b></p>";
        while ($rowMusicTrack = $resultAllMusicTracks->fetch_assoc()) {
            echo "<div class='elementsInList lastInfoInElements'>";
            $idOfMusicTrack = $rowMusicTrack["ID"];
            $nameOfMusicTrack = $rowMusicTrack["name"];
            $creatorsOfMusicTrack = $rowMusicTrack["creators"];

            echo "<p><b>Name:</b> $nameOfMusicTrack</p>";
            if (isset($creatorsOfMusicTrack) && $creatorsOfMusicTrack !== "") {
                echo "<p><b>Creators:</b> $creatorsOfMusicTrack</p>";
            }

            // Loading all music kinds of the track
            $resultLoadMusicKindsOfTrack = $conn->query("SELECT MUSIC_KIND_ID FROM track_has_music_kind WHERE TRACK_ID='$idOfMusicTrack';");
            if ($resultLoadMusicKindsOfTrack->num_rows !== 0) {
                echo "<p><b>Kinds of music</b></p>";
                echo "<ul>";
                while ($rowMusicKindOfTrack = $resultLoadMusicKindsOfTrack->fetch_assoc()) {
                    $idOfMusicKindOfTrack = $rowMusicKindOfTrack["MUSIC_KIND_ID"];
                    $resultNameOfMusicKind = $conn->query("SELECT name FROM music_kind WHERE ID='$idOfMusicKindOfTrack';");
                    $rowNameOfMusicKind = $resultNameOfMusicKind->fetch_assoc();
                    $nameOfMusicKindOfTrack = $rowNameOfMusicKind["name"];

                    echo "<li>$nameOfMusicKindOfTrack</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>
</div>
</body>
</html>