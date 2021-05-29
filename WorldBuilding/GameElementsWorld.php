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
 * Getting the id of Game Elements to connect elements (e.g character, location) with game elements of the document.
 * If there is a problem with the execution of queries we redirect user to write page.
 */

// finding the id of world_building table
if($resultInfoWorld = $conn->query("SELECT ID from world_building WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultInfoWorld->num_rows === 1){
        $rowInfoWorld = $resultInfoWorld->fetch_assoc();

        if(isset($rowInfoWorld['ID'])){
            $worldBuildingId = $rowInfoWorld['ID'];
            // finding the id of game elements table
            if($resultInfoGameElements = $conn->query("SELECT ID FROM game_elements WHERE WORLD_BUILDING_ID = '$worldBuildingId';")){
                if($resultInfoGameElements->num_rows === 1){
                    $rowInfoGameElements = $resultInfoGameElements->fetch_assoc();

                    if(isset($rowInfoGameElements['ID'])){
                        $gameElementsId = $rowInfoGameElements['ID']; // setting the id of game elements
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
    <title>Game Elements - GDD Maker</title>
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
    <a href="GameElementsWorld.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Game Elements</a>
</div>

<div class="w3-container w3-blue panelInFormWorld">
    <h3 class="headerPanel">Fill the characteristics for game elements</h3>
</div>

<!--- Content of characters modal -->
<div id="characters-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" class="w3-container" style="text-align: center;">
                <span onclick="hideElement('characters-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a character</h3><br>

            <label for="imgChar" class="w3-margin-top" id="labelImChar">Choose an image of the character</label><br>
            <input type="file" id="imgChar" class="w3-margin-top" name="imgChar" accept="image/*"><br><br>

            <label for="charAdd" class="w3-margin-top">Write the name of the character *</label>
            <input class="w3-input w3-border w3-margin-top" type="text" id="charAdd" name="charAdd" required><br>

            <label for="charType" class="w3-margin-top">Write the type of the character *</label>
            <input class="w3-input w3-border w3-margin-top" type="text" id="charType" name="charType"
                   placeholder="e.g human, animal" required><br>

            <label for="charDescription">Describe the character</label>
            <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="charDescription"
                      name="charDescription"></textarea><br>
            <div class="w3-container w3-padding-16">
                <button class="w3-button w3-green transmission" id="saveCharacter" type="submit"
                        name="saveCharacter">Save</button>
            </div>
        </form>
    </div>
</div>

<form class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding">
    <label for="story">Describe the story of the game</label>
    <textarea class="w3-input w3-border w3-margin-top" rows="2" type="text" id="story" name="story"></textarea><br>

    <label for="characters">Add characters of the game</label>
    <button onclick="showElement('characters-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="characters" type="button" name="characters">
        <i class="fa fa-plus"></i></button><br><br>


    <label for="objects">Add objects that are in the game</label>
    <button onclick="showElement('objects-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="objects" type="button" name="characters">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="objects-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('objects-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add an object</h3><br>

                <label for="imgObject" class="w3-margin-top" id="labelImObj">Choose an image of the object</label><br>
                <input type="file" id="imgObject" class="w3-margin-top" name="imgObject" accept="image/*"><br><br>

                <label for="objName" class="w3-margin-top">Write the name of the object *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="objName" name="objName" required><br>

                <label for="typeOfObj" class="w3-margin-top">Write the type of the object *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="typeOfObj" name="typeOfObj"
                       placeholder="e.g table, car, gun" required><br>

                <label for="objDescription">Describe the object</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="objDescription"
                          name="objDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveObject" type="submit" name="saveObject">Save</button>
                </div>
            </div>
        </div>
    </div>

    <label for="locations">Add locations of the game</label>
    <button onclick="showElement('locations-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="locations" type="button" name="locations">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="locations-modal" class="w3-modal w3-padding-16">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('locations-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add a location</h3><br>

                <label for="imgLocation" class="w3-margin-top" id="labelImLoc">Choose an image of the location</label><br>
                <input type="file" id="imgLocation" class="w3-margin-top" name="imgLocation" accept="image/*"><br><br>

                <label for="locName" class="w3-margin-top">Write the name of the location *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="locName" name="locName" required><br>

                <label for="locDescription">Describe the location</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="locDescription"
                          name="locDescription"></textarea><br>

                <label>Add objects that are at the location</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableObjects">
                    <tr>
                        <th>Objects</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Object 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj1" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj2" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj3" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label>Add characters that are at the location</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableCharacters">
                    <tr>
                        <th>Characters</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Character 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char1" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char2" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char3" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveLocation" type="submit" name="saveLocation">Save</button>
                </div>
            </div>
        </div>
    </div>

    <label for="dialogs">Add dialogs between characters of the game</label>
    <button onclick="showElement('dialogs-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="dialogs" type="button" name="dialogs">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="dialogs-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('dialogs-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add a dialog</h3><br>

                <label for="selectFromChar">Choose a character that talks to others *</label>
                <select class="w3-select w3-border w3-margin-top" id="selectFromChar" name="selectFromChar" required>
                    <option value="" disabled selected>Choose a character</option>
                    <option value="1">Character 1</option>
                    <option value="2">Character 2</option>
                    <option value="3">Character 3</option>
                </select><br><br>

                <label>Choose a character or more that the character above talks to</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableCharacters-dialogs">
                    <tr>
                        <th>Characters</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Character 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char1-dialogs" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char2-dialogs" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char3-dialogs" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label for="dialogText">Write the dialog *</label>
                <textarea id="dialogText" class="w3-input w3-border w3-margin-top" rows="3" type="text"
                          name="dialogText" required></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveDialog" type="submit" name="saveDialog">Save</button>
                </div>
            </div>
        </div>
    </div>

    <label for="scenes">Add scenes of the game</label>
    <button onclick="showElement('scenes-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="scenes" type="button" name="scenes">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="scenes-modal" class="w3-modal w3-padding-16">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('scenes-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add a scene</h3><br>

                <label for="sceneName" class="w3-margin-top">Write the name of the scene *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="sceneName" name="sceneName" required><br>

                <label for="sceneDescription">Describe the scene</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="sceneDescription"
                          name="sceneDescription"></textarea><br>

                <label>Add characters that take part in the scene</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableCharacters-scenes">
                    <tr>
                        <th>Characters</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Character 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char1-scene" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char2-scene" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="char3-scene" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label>Add locations of the scene</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLocations-scenes">
                    <tr>
                        <th>Locations</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Location 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="loc1-scene" type="button"
                                    name="btnAddLoc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Location 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="loc2-scene" type="button"
                                    name="btnAddLoc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Location 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="loc3-scene" type="button"
                                    name="btnAddLoc"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label>Add objects of the scene</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableObjects-scenes">
                    <tr>
                        <th>Object</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Object 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj1-scene" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj2-scene" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="obj3-scene" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>
                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveScene" type="submit" name="saveScene">Save</button>
                </div>
            </div>
        </div>
    </div>

    <label for="objectives">Add an objective of the game</label>
    <button onclick="showElement('objectives-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="objectives" type="button" name="objectives">
        <i class="fa fa-plus"></i></button><br><br>

    <div id="objectives-modal" class="w3-modal w3-padding-16">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <span onclick="hideElement('objectives-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
                <h3 class="headerForModal">Add an objective</h3><br>

                <label for="objTitle" class="w3-margin-top">Write the title of the objective *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="objTitle" name="objTitle" required><br>

                <label for="objectiveDescription">Describe the objective</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="objectiveDescription"
                          name="objDescription"></textarea><br>

                <label>Add scenes of the objective</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableScenes">
                    <tr>
                        <th>Scenes</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Scene 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="scene1" type="button"
                                    name="btnAddScene"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Scene 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="scene2" type="button"
                                    name="btnAddScene"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Scene 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="scene3" type="button"
                                    name="btnAddScene"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label>Add other characters that take part in the objective</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableCharactersObjective">
                    <tr>
                        <th>Characters</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Character 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="charObj1" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="charObj2" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Character 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="charObj3" type="button"
                                    name="btnAddChar"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <label>Add other objects that are used for the objective</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableObjectsObjectives">
                    <tr>
                        <th>Objects</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Object 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="objObjective1" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="objObjective2" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Object 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="objObjective3" type="button"
                                    name="btnAddObj"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveObjective" type="submit" name="saveObjective">Save</button>
                </div>
            </div>
        </div>
    </div>

    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="button" value="Submit">
</form>
</body>
</html>