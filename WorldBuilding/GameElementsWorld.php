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

/*
 * Getting the value of story describe to load text
 */
$queryLoadStory = "SELECT story_describe from game_elements WHERE ID='$gameElementsId';";
$resLoadStory = $conn->query($queryLoadStory);

if($resLoadStory->num_rows === 1){
    $rowLoadStory = $resLoadStory->fetch_assoc();
    $gameStoryValue = $rowLoadStory['story_describe'];
}


/*
 * Actions when user submits the story of the game
 */
$successUpdateStory = false; // variable to show success message
$somethingWrongStory = false; // variable to show failure message
if(isset($_POST["mainSubmit"])){
    if(isset($gameElementsId)){
        $describeStory = $_POST["story"]; // getting the text with post method
        // query to update the database array
        $updateStoryQuery = "UPDATE game_elements SET story_describe='$describeStory' WHERE ID='$gameElementsId';";

        if($conn->query($updateStoryQuery)){ // if query executed successfully
            $successUpdateStory = true;
            $gameStoryValue = $describeStory;
            header('Location:./GameElementsWorld.php?id=' . $idOfDocument);
        }else{
            $somethingWrongStory = true;
        }
    }
}

$docRoot = $_SERVER["DOCUMENT_ROOT"]; // the path for the root of document

/*
 * Actions when user adds a character
 */
if (isset($_POST["saveCharacter"])) {
    $nameOfChar = test_data($_POST["charName"]); // getting the name of the character
    $charType = test_data($_POST["charType"]); // getting the type of the character
    $charDescription = test_data($_POST["charDescription"]); // getting the description of the character

    $uploadedImage = false;

    if ($_FILES["imgChar"]["name"] !== "") {
        $filename = $_FILES["imgChar"]["name"];
        $tempname = $_FILES["imgChar"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $uploadedImage = true;
            $image_id = mysqli_insert_id($conn);

            // query to add a new character in game_character table  with image
            $queryAddChar = "INSERT INTO game_character (GAME_ELEMENTS_ID, IMAGE_ID, name, type_char, describe_char) 
                     VALUES ('$gameElementsId', '$image_id' ,'$nameOfChar', '$charType', '$charDescription');";

            //executing the query
            if($conn->query($queryAddChar)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add character')</script>"; // else we show an error message
            }
        }else{
            echo "<script>alert('Error: cannot upload image of character')</script>"; // else we show an error message
        }
    }else{
        // query to add a new character in game_character table without image
        $queryAddChar = "INSERT INTO game_character (GAME_ELEMENTS_ID, name, type_char, describe_char) 
                     VALUES ('$gameElementsId' ,'$nameOfChar', '$charType', '$charDescription');";

        //executing the query
        if($conn->query($queryAddChar)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add character')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user updates information for an character
 */
if(isset($_POST["editCharacter"])){
    $idOfChar = $_POST["keyIdChar"];
    $nameOfChar = test_data($_POST["charName"]); // getting the name of the character
    $charType = test_data($_POST["charType"]); // getting the type of the character
    $charDescription = test_data($_POST["charDescription"]); // getting the description of the character

    // If user submits a picture for the character
    if ($_FILES["imgCharEdit"]["name"] !== "") {
        $filename = $_FILES["imgCharEdit"]["name"]; // getting the filename of the image
        $tempname = $_FILES["imgCharEdit"]["tmp_name"];
        // the url to add the new image
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/".$filename;

        // Finding if character has a submitted picture
        $queryFindIfCharHasPicture = "SELECT IMAGE_ID FROM game_character WHERE ID = '$idOfChar';";
        $resultFindPicture = $conn->query($queryFindIfCharHasPicture);

        if ($rowFindPicture = $resultFindPicture->fetch_assoc()) {
            if (isset($rowFindPicture["IMAGE_ID"])) {
                $idOfPictureToDel = $rowFindPicture["IMAGE_ID"]; // getting the id of the image row

                // getting the filename for the selected row from table image
                $resultFindFilenameOfPic = $conn->query("SELECT filename FROM image WHERE ID = '$idOfPictureToDel';");
                if ($rowFilename = $resultFindFilenameOfPic->fetch_assoc()) {
                    // the url of the image that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/".$rowFilename["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new image to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of character's image
                        if ($conn->query("UPDATE image SET filename='$filename' WHERE ID='$idOfPictureToDel';")) {
                            // query to update other information of the character
                            $queryUpdateChar = "UPDATE game_character SET name='$nameOfChar', type_char='$charType', describe_char='$charDescription' WHERE ID='$idOfChar';";

                            //executing the query
                            if ($conn->query($queryUpdateChar)) {
                                header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
                            }
                        }
                    }
                }
            } else {
                // actions if user adds first time image
                if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
                    $uploadedImage = true;
                    $image_id = mysqli_insert_id($conn);

                    $queryUpdateCharWithImage = "UPDATE game_character SET IMAGE_ID='$image_id' WHERE ID='$idOfChar';";
                    if ($conn->query($queryUpdateCharWithImage)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
                    }
                }
            }
        }
    } else {
        // query to update information about the character
        $queryUpdateCharacter = "UPDATE game_character SET name='$nameOfChar', type_char='$charType', describe_char='$charDescription'
                             WHERE ID='$idOfChar';";
        if($conn->query($queryUpdateCharacter)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user deletes a character
 */
if(isset($_POST["deleteCharacter"])){
    $idOfCharToDelete = $_POST["keyIdChar"];

    // Finding if character has a submitted picture
    $queryFindIfCharHasPicture = "SELECT IMAGE_ID FROM game_character WHERE ID = '$idOfCharToDelete';";
    $resultFindPicture = $conn->query($queryFindIfCharHasPicture);

    // If there is a picture we delete it
    if ($rowDelPic = $resultFindPicture->fetch_assoc()) {
        $idOfPictureToDel = $rowDelPic["IMAGE_ID"];

        $queryFindFilenameOfPic = "SELECT filename FROM image WHERE ID='$idOfPictureToDel'";
        $resultFindFilename = $conn->query($queryFindFilenameOfPic);

        if ($rowFindFilename = $resultFindFilename->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    }

    $queryDeleteChar = "DELETE FROM game_character WHERE ID='$idOfCharToDelete';";
    if($conn->query($queryDeleteChar)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete character')</script>";
    }
}

/*
 * Actions when user adds an object
 */
if (isset($_POST["saveObject"])) {
    $nameOfObj = test_data($_POST["objName"]);
    $typeOfObj = test_data($_POST["typeOfObj"]);
    $descriptionOfObj = test_data($_POST["objDescription"]);

    $uploadedImage = false;

    if ($_FILES["imgObject"]["name"] !== "") {
        $filename = $_FILES["imgObject"]["name"];
        $tempname = $_FILES["imgObject"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $uploadedImage = true;
            $image_id = mysqli_insert_id($conn);

            // query to add a new object in game_object table with image
            $queryAddObj = "INSERT INTO game_object (GAME_ELEMENTS_ID, IMAGE_ID, name, type_obj, describe_obj) 
                     VALUES ('$gameElementsId', '$image_id', '$nameOfObj', '$typeOfObj', '$descriptionOfObj');";

            //executing the query
            if($conn->query($queryAddObj)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                //echo "<script>alert('Error: cannot add object')</script>"; // else we show an error message
                echo "Error: " . $queryAddObj . "<br>" . $conn->error;
            }
        }else{
            echo "<script>alert('Error: cannot upload image of object')</script>"; // else we show an error message
        }
    }else{
        // query to add a new object in game_object table without image
        $queryAddObj = "INSERT INTO game_object (GAME_ELEMENTS_ID, name, type_obj, describe_obj) 
                     VALUES ('$gameElementsId', '$nameOfObj', '$typeOfObj', '$descriptionOfObj');";

        //executing the query
        if($conn->query($queryAddObj)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add object')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user updates information for an object
 */
if(isset($_POST["editObject"])){
    $idOfObj = $_POST["keyIdObj"];
    $nameOfObj = test_data($_POST["objName"]); // getting the name of the object
    $objType = test_data($_POST["objType"]); // getting the type of the object
    $objDescription = test_data($_POST["objDescription"]); // getting the description of the object

    if ($_FILES["imgObj"]["name"] !== "") {
        $filename = $_FILES["imgObj"]["name"];
        $tempname = $_FILES["imgObj"]["tmp_name"];
        // the url to add the new image
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/".$filename;

        // Finding if object has a submitted image
        $queryFindIfObjHasPicture = "SELECT IMAGE_ID FROM game_object WHERE ID = '$idOfObj';";
        $resultFindPicture = $conn->query($queryFindIfObjHasPicture);

        if ($rowFindPicture = $resultFindPicture->fetch_assoc()) {
            if (isset($rowFindPicture["IMAGE_ID"])) {
                $idOfPictureToDel = $rowFindPicture["IMAGE_ID"]; // getting the id of the image row

                // getting the filename for the selected row from table image
                $resultFindFilenameOfPic = $conn->query("SELECT filename FROM image WHERE ID = '$idOfPictureToDel';");
                if ($rowFilename = $resultFindFilenameOfPic->fetch_assoc()) {
                    // the url of the image that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/".$rowFilename["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new image to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of object's image
                        if ($conn->query("UPDATE image SET filename='$filename' WHERE ID='$idOfPictureToDel';")) {
                            // query to update other information of the object
                            $queryUpdateObj = "UPDATE game_object SET name='$nameOfObj', type_obj='$objType', describe_obj='$objDescription'
                                                WHERE ID='$idOfObj';";

                            //executing the query
                            if ($conn->query($queryUpdateObj)) {
                                //header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update the object')</script>"; // else we show an error message
                            }
                        }
                    }
                }
            } else {
                // actions if user adds first time an image
                if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
                    $uploadedImage = true;
                    $image_id = mysqli_insert_id($conn);

                    $queryUpdateObjWithImage = "UPDATE game_object SET IMAGE_ID='$image_id' WHERE ID='$idOfObj';";
                    //executing the query
                    if ($conn->query($queryUpdateObjWithImage)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot add object')</script>"; // else we show an error message
                        //echo "Error: " . $queryAddObj . "<br>" . $conn->error;
                    }
                }
            }
        }
    } else {
        $queryUpdateObject = "UPDATE game_object SET name='$nameOfObj', type_obj='$objType', describe_obj='$objDescription'
                             WHERE ID='$idOfObj';";
        if($conn->query($queryUpdateObject)){
            //header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update the object')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user deletes an object
 */
if(isset($_POST["deleteObject"])){
    $idOfObjToDelete = $_POST["keyIdObj"];

    // Finding if object has a submitted picture
    $queryFindIfObjHasPicture = "SELECT IMAGE_ID FROM game_object WHERE ID = '$idOfObjToDelete';";
    $resultFindPicture = $conn->query($queryFindIfObjHasPicture);

    // If there is a picture we delete it
    if ($rowDelPic = $resultFindPicture->fetch_assoc()) {
        $idOfPictureToDel = $rowDelPic["IMAGE_ID"];

        $queryFindFilenameOfPic = "SELECT filename FROM image WHERE ID='$idOfPictureToDel'";
        $resultFindFilename = $conn->query($queryFindFilenameOfPic);

        if ($rowFindFilename = $resultFindFilename->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    }

    $queryDeleteObj = "DELETE FROM game_object WHERE ID='$idOfObjToDelete';";
    if($conn->query($queryDeleteObj)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete object')</script>";
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
    <title>Game Elements - GDD Maker</title>
    <link rel="icon" href="../Images/favicon-new.ico">

    <script src="../JavaScript/Main.js"></script>
    <script src="../JavaScript/WorldBuilding.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="../css/main.css">

<body>

<!--- Bar for big screens -->
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

<!--- Side bar for small screens -->
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

<!--- Button to show side bar on click -->
<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="w3-container pathPosition">
    <a href="../write.php" class="w3-hover-text-blue">Write GDD</a>
    <i class="fa fa-angle-double-right"></i>
    <span><?php echo $nameOfDoc ?></span>
    <i class="fa fa-angle-double-right"></i>
    <a href="GameElementsWorld.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Game Elements</a>
</div>

<!--- Panel of the form -->
<div class="w3-container w3-blue panelInFormWorld">
    <h3 class="headerPanel">Fill the characteristics for game elements</h3>
</div>

<!--- Content of characters modal -->
<div id="characters-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                <span onclick="hideElement('characters-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a character</h3><br>

            <label for="imgChar<?php echo $gameElementsId; ?>" class="w3-margin-top">Choose an image of the character</label><br>
            <input type="file" id="imgChar<?php echo $gameElementsId; ?>" class="w3-margin-top" name="imgChar" accept="image/*"><br><br>

            <label for="charName<?php echo $gameElementsId; ?>" class="w3-margin-top">Write the name of the character *</label>
            <input class="w3-input w3-border w3-margin-top" type="text" id="charName<?php echo $gameElementsId; ?>" name="charName" required><br>

            <label for="charType<?php echo $gameElementsId; ?>" class="w3-margin-top">Write the type of the character *</label>
            <input class="w3-input w3-border w3-margin-top" type="text" id="charType<?php echo $gameElementsId; ?>" name="charType"
                   placeholder="e.g human, animal" required><br>

            <label for="charDescription<?php echo $gameElementsId; ?>">Describe the character</label>
            <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="charDescription<?php echo $gameElementsId; ?>"
                      name="charDescription"></textarea><br>
            <div class="w3-container w3-padding-16">
                <button class="w3-button w3-green transmission" type="submit" name="saveCharacter">Save</button>
            </div>
        </form>
    </div>
</div>

<!--- Modal for objects -->
<div id="objects-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('objects-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add an object</h3><br>

            <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                <label for="imgObject<?php echo $gameElementsId; ?>" class="w3-margin-top" id="labelImObj">Choose an image of the object</label><br>
                <input type="file" id="imgObject<?php echo $gameElementsId; ?>" class="w3-margin-top" name="imgObject" accept="image/*"><br><br>

                <label for="objName<?php echo $gameElementsId; ?>" class="w3-margin-top">Write the name of the object *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="objName<?php echo $gameElementsId; ?>" name="objName" required><br>

                <label for="typeOfObj<?php echo $gameElementsId; ?>" class="w3-margin-top">Write the type of the object *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="typeOfObj<?php echo $gameElementsId; ?>" name="typeOfObj"
                       placeholder="e.g table, car, gun" required><br>

                <label for="objDescription<?php echo $gameElementsId; ?>">Describe the object</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="objDescription<?php echo $gameElementsId; ?>"
                          name="objDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveObject" type="submit" name="saveObject">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--- Modal for locations -->
<div id="locations-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('locations-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a location</h3><br>

            <form method="post" action="" class="w3-container" style="text-align: center;">
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
            </form>
        </div>
    </div>
</div>

<!--- Modal for dialogs -->
<div id="dialogs-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('dialogs-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a dialog</h3><br>

            <form method="post" action="" class="w3-container" style="text-align: center;">
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
            </form>
        </div>
    </div>
</div>

<!--- Modal for scenes -->
<div id="scenes-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('scenes-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a scene</h3><br>

            <form method="post" action="" class="w3-container" style="text-align: center;">
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
            </form>
        </div>
    </div>
</div>

<!--- Modal for objectives -->
<div id="objectives-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('objectives-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add an objective</h3><br>

            <form method="post" action="" class="w3-container" style="text-align: center;">
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
            </form>
        </div>
    </div>
</div>


<?php
    // query to load all characters
    $queryLoadAllCharacters = "SELECT * FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllCharacters = mysqli_query($conn, $queryLoadAllCharacters); // executing the query

    while($rowLoadChar = $resultLoadAllCharacters->fetch_assoc()){
        $idOfChar = $rowLoadChar["ID"];
        $nameOfChar = $rowLoadChar["name"];
        $typeOfChar = $rowLoadChar["type_char"];
        $charDescribe = $rowLoadChar["describe_char"];
        $idOfImage = $rowLoadChar["IMAGE_ID"];

        if(isset($idOfImage)){
            $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

            if($rowImage = $resultImage->fetch_assoc()){
                $imgFilenameChar = $rowImage["filename"];
            }
        }

        echo "<div id=\"characters-modal-edit$idOfChar\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('characters-modal-edit$idOfChar')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit character $nameOfChar</h3><br>";

            if(isset($imgFilenameChar)){
                echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/$imgFilenameChar' alt='Image of character' style='width: 50%; height: auto;'><br><br>";
            }

            echo "<label for=\"imgCharEdit$idOfChar\" class=\"w3-margin-top\" id=\"labelImChar\">Choose an image of the character</label><br>
            <input type=\"file\" id=\"imgCharEdit$idOfChar\" class=\"w3-margin-top\" name=\"imgCharEdit\" accept=\"image/*\"><br><br>
            
            <input type=\"hidden\"  name=\"keyIdChar\" value=\"$idOfChar\" />

            <label for=\"charNameEdit$idOfChar\" class=\"w3-margin-top\">Write the name of the character *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"charNameEdit$idOfChar\" value=\"$nameOfChar\" name=\"charName\" required><br>

            <label for=\"charTypeEdit$idOfChar\" class=\"w3-margin-top\">Write the type of the character *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"charTypeEdit$idOfChar\" name=\"charType\"
                   placeholder=\"e.g human, animal\" value=\"$typeOfChar\" required><br>

            <label for=\"charDescriptionEdit$idOfChar\">Describe the character</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"charDescriptionEdit$idOfChar\"
                      name=\"charDescription\">$charDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editCharacter\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

// query to load all objects
$queryLoadAllObjects = "SELECT * FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';";
$resultLoadAllObjects = mysqli_query($conn, $queryLoadAllObjects); // executing the query

while($rowLoadObj = $resultLoadAllObjects->fetch_assoc()){
    $idOfObj = $rowLoadObj["ID"];
    $nameOfObj = $rowLoadObj["name"];
    $typeOfObj = $rowLoadObj["type_obj"];
    $objDescribe = $rowLoadObj["describe_obj"];

    if (isset($rowLoadObj["IMAGE_ID"])) {
        $idOfImage = $rowLoadObj["IMAGE_ID"];

        $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

        if($rowImage = $resultImage->fetch_assoc()){
            $imgFilenameObj = $rowImage["filename"];
        }
    }

    echo "<div id=\"objects-modal-edit$idOfObj\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\"  enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('objects-modal-edit$idOfObj')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit object $nameOfObj</h3><br>";

    if(isset($imgFilenameObj)){
        echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/$imgFilenameObj' alt='Image of object' style='width: 50%; height: auto;'><br><br>";
    }

    echo "<label for=\"imgObjEdit$idOfObj\" class=\"w3-margin-top\" id=\"labelImObj\">Choose an image of the object</label><br>
            <input type=\"file\" id=\"imgObjEdit$idOfObj\" class=\"w3-margin-top\" name=\"imgObj\" accept=\"image/*\"><br><br>
            
            <input type=\"hidden\"  name=\"keyIdObj\" value=\"$idOfObj\" />

            <label for=\"objNameEdit$idOfObj\" class=\"w3-margin-top\">Write the name of the object *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"objNameEdit$idOfObj\" value=\"$nameOfObj\" name=\"objName\" required><br>

            <label for=\"objTypeEdit$idOfObj\" class=\"w3-margin-top\">Write the type of the object *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"objTypeEdit$idOfObj\" name=\"objType\"
                   placeholder=\"e.g table, car, gun\" value=\"$typeOfObj\" required><br>

            <label for=\"objDescriptionEdit$idOfObj\">Describe the object</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"objDescriptionEdit$idOfObj\"
                      name=\"objDescription\">$objDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editObject\">Save</button>
            </div>
        </form>
    </div>
</div>";
}
?>

<!--- The form of game elements where user can add characters, objects, etc -->
<form class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding" method="post"
      action="">
    <label for="story">Describe the story of the game</label>
    <textarea class="w3-input w3-border w3-margin-top" rows="2" type="text" id="story" name="story"><?php if(isset($gameStoryValue)) echo $gameStoryValue; ?></textarea><br>

    <label for="characters">Add characters of the game</label>
    <button onclick="showElement('characters-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="characters" type="button" name="characters">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadCharacters">
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>

        <?php
            $queryLoadAllCharactersV2 = "SELECT * FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
            //$resultLoadAllCharactersV2 = $conn->query($queryLoadAllCharactersV2); // executing the query
            $resultLoadAllCharactersV2 = mysqli_query($conn, $queryLoadAllCharactersV2); // executing the query

            // Loading all entered characters
            while($rowLoadChar = $resultLoadAllCharactersV2->fetch_assoc()){
                $idOfCharLoad = $rowLoadChar["ID"];
                $nameCharLoad = $rowLoadChar["name"];

                echo "<tr><td>" . $nameCharLoad . "</td><td>" . $rowLoadChar["type_char"] .
                    "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('characters-modal-edit$idOfCharLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the character $nameCharLoad')\" type=\"submit\"
                                    name=\"deleteCharacter\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdChar\" value=\"$idOfCharLoad\" /></tr>";
            }
        ?>
    </table><br>

    <label for="objects">Add objects that are in the game</label>
    <button onclick="showElement('objects-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="objects" type="button" name="characters">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadObjects">
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryLoadAllObjectsV2 = "SELECT * FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllObjectsV2 = mysqli_query($conn, $queryLoadAllObjectsV2); // executing the query

        // Loading all entered objects
        while($rowLoadObj = $resultLoadAllObjectsV2->fetch_assoc()){
            $idOfObjLoad = $rowLoadObj["ID"];
            $nameObjLoad = $rowLoadObj["name"];

            echo "<tr><td>" . $nameObjLoad . "</td><td>" . $rowLoadObj["type_obj"] .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('objects-modal-edit$idOfObjLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the object $nameObjLoad')\" type=\"submit\"
                                    name=\"deleteObject\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdObj\" value=\"$idOfObjLoad\" /></tr>";
        }
        ?>
    </table><br>


    <label for="locations">Add locations of the game</label>
    <button onclick="showElement('locations-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="locations" type="button" name="locations">
        <i class="fa fa-plus"></i></button><br><br>


    <label for="dialogs">Add dialogs between characters of the game</label>
    <button onclick="showElement('dialogs-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="dialogs" type="button" name="dialogs">
        <i class="fa fa-plus"></i></button><br><br>


    <label for="scenes">Add scenes of the game</label>
    <button onclick="showElement('scenes-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="scenes" type="button" name="scenes">
        <i class="fa fa-plus"></i></button><br><br>

    <label for="objectives">Add an objective of the game</label>
    <button onclick="showElement('objectives-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="objectives" type="button" name="objectives">
        <i class="fa fa-plus"></i></button><br><br>

    <!--- A message to inform the user that updated the story of the game successfully -->
    <div class="w3-panel w3-green" <?php if($successUpdateStory) {
        echo 'style="display: block"';
    }else{
        echo 'style="display: none"';
    }?>>
        <p>You have successfully updated the story of the game!</p>
    </div>

    <!--- A message to inform the user that there was an error and didn't update the story of the game -->
    <div class="w3-panel w3-red" <?php if($somethingWrongStory) {
        echo 'style="display: block"';
    }else{
        echo 'style="display: none"';
    }?>>
        <p>Something went wrong. Unable to update the story of the game.</p>
    </div>

    <!--- Submit button for the form -->
    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit" name="mainSubmit" value="Submit">
</form>

<!--- A connection to assets of world building that says that the user can continue with editing the assets -->
<div class="w3-container continueAssets">
    <h3 style="">Continue with editing Assets of World Building</h3>
    <?php echo "<a href=\"AssetsWorld.php?id=$idOfDocument\" class=\"w3-bar-item w3-button w3-margin-top transmission w3-text-blue w3-border w3-xxlarge w3-round w3-hover-blue\">
        Assets <i class=\"fa fa-angle-double-right\"></i></a>"?>
</div>
</body>
</html>