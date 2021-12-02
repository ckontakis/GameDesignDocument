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
        $describeStory = test_data($_POST["story"]); // getting the text with post method
        // query to update the database array
        $updateStoryQuery = "UPDATE game_elements SET story_describe='$describeStory' WHERE ID='$gameElementsId';";

        if($conn->query($updateStoryQuery)){ // if query executed successfully
            $successUpdateStory = true;
            $gameStoryValue = $describeStory;
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
    $charState = test_data($_POST["charState"]); // getting the character's state
    $charSpeed = test_data($_POST["charSpeed"]); // getting the character's speed

    $uploadedImage = false;

    if ($_FILES["imgChar"]["name"] !== "") {
        $filename = $_FILES["imgChar"]["name"];
        $tempname = $_FILES["imgChar"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Characters/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $uploadedImage = true;
            $image_id = mysqli_insert_id($conn);

            // query to add a new character in game_character table  with image
            $queryAddChar = "INSERT INTO game_character (GAME_ELEMENTS_ID, IMAGE_ID, name, type_char, describe_char, state, speed) 
                     VALUES ('$gameElementsId', '$image_id' ,'$nameOfChar', '$charType', '$charDescription', '$charState', '$charSpeed');";

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
        $queryAddChar = "INSERT INTO game_character (GAME_ELEMENTS_ID, name, type_char, describe_char, state, speed) 
                     VALUES ('$gameElementsId' ,'$nameOfChar', '$charType', '$charDescription', '$charState', '$charSpeed');";

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
    $charState = test_data($_POST["charState"]); // getting the character's state
    $charSpeed = test_data($_POST["charSpeed"]); // getting the character's speed

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
                            $queryUpdateChar = "UPDATE game_character SET name='$nameOfChar', type_char='$charType', describe_char='$charDescription', state='$charState', speed='$charSpeed' WHERE ID='$idOfChar';";

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
        $queryUpdateCharacter = "UPDATE game_character SET name='$nameOfChar', type_char='$charType', 
                          describe_char='$charDescription', state='$charState', speed='$charSpeed'
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

    if ($_FILES["imgObject"]["name"] !== "") {
        $filename = $_FILES["imgObject"]["name"];
        $tempname = $_FILES["imgObject"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Objects/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $image_id = mysqli_insert_id($conn);

            // query to add a new object in game_object table with image
            $queryAddObj = "INSERT INTO game_object (GAME_ELEMENTS_ID, IMAGE_ID, name, type_obj, describe_obj) 
                     VALUES ('$gameElementsId', '$image_id', '$nameOfObj', '$typeOfObj', '$descriptionOfObj');";

            //executing the query
            if($conn->query($queryAddObj)){
                header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add object')</script>"; // else we show an error message
                //echo "Error: " . $queryAddObj . "<br>" . $conn->error;
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
 * Actions when user adds a location
 */
if (isset($_POST["saveLocation"])) {
   $nameOfLoc = test_data($_POST["locName"]);
   $descriptionOfLoc = test_data($_POST["locDescription"]);

    if ($_FILES["imgLocation"]["name"] !== "") {
        $filename = $_FILES["imgLocation"]["name"];
        $tempname = $_FILES["imgLocation"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/" . $filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $image_id = mysqli_insert_id($conn);

            // query to add a new location in game_location table with image
            $queryAddLoc = "INSERT INTO game_location (GAME_ELEMENTS_ID, IMAGE_ID, name, describe_loc) 
                     VALUES ('$gameElementsId', '$image_id', '$nameOfLoc', '$descriptionOfLoc');";

            //executing the query
            if($conn->query($queryAddLoc)){
                header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add location')</script>"; // else we show an error message
                //echo "Error: " . $queryAddObj . "<br>" . $conn->error;
            }
        }else{
            echo "<script>alert('Error: cannot upload image of location')</script>"; // else we show an error message
        }
    } else {
        // query to add a new location in game_location table without image
        $queryAddLoc = "INSERT INTO game_location (GAME_ELEMENTS_ID, name, describe_loc) 
                     VALUES ('$gameElementsId', '$nameOfLoc', '$descriptionOfLoc');";

        //executing the query
        if($conn->query($queryAddLoc)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add location')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user updates information for a location
 */
if(isset($_POST["editLocation"])){
    $idOfLoc = $_POST["keyIdLoc"];
    $nameOfLoc = test_data($_POST["locName"]); // getting the name of the location
    $locDescription = test_data($_POST["locDescription"]); // getting the description of the location

    if ($_FILES["imgLoc"]["name"] !== "") {
        $filename = $_FILES["imgLoc"]["name"];
        $tempname = $_FILES["imgLoc"]["tmp_name"];
        // the url to add the new image
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/".$filename;

        // Finding if location has a submitted image
        $queryFindIfLocHasPicture = "SELECT IMAGE_ID FROM game_location WHERE ID = '$idOfLoc';";
        $resultFindPicture = $conn->query($queryFindIfLocHasPicture);

        if ($rowFindPicture = $resultFindPicture->fetch_assoc()) {
            if (isset($rowFindPicture["IMAGE_ID"])) {
                $idOfPictureToDel = $rowFindPicture["IMAGE_ID"]; // getting the id of the image row

                // getting the filename for the selected row from table image
                $resultFindFilenameOfPic = $conn->query("SELECT filename FROM image WHERE ID = '$idOfPictureToDel';");
                if ($rowFilename = $resultFindFilenameOfPic->fetch_assoc()) {
                    // the url of the image that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/".$rowFilename["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new image to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of location's image
                        if ($conn->query("UPDATE image SET filename='$filename' WHERE ID='$idOfPictureToDel';")) {
                            // query to update other information of the location
                            $queryUpdateLoc = "UPDATE game_location SET name='$nameOfLoc', describe_loc='$locDescription'
                                                WHERE ID='$idOfLoc';";

                            //executing the query
                            if ($conn->query($queryUpdateLoc)) {
                                header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update the location')</script>"; // else we show an error message
                            }
                        }
                    }
                }
            } else {
                // actions if user adds first time an image
                if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
                    $image_id = mysqli_insert_id($conn);

                    $queryUpdateLocWithImage = "UPDATE game_location SET IMAGE_ID='$image_id' WHERE ID='$idOfLoc';";
                    //executing the query
                    if ($conn->query($queryUpdateLocWithImage)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot update the location')</script>"; // else we show an error message
                        //echo "Error: " . $queryAddObj . "<br>" . $conn->error;
                    }
                }
            }
        }
    } else {
        $queryUpdateLocation = "UPDATE game_location SET name='$nameOfLoc', describe_loc='$locDescription'
                             WHERE ID='$idOfLoc';";
        if($conn->query($queryUpdateLocation)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update the location')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user deletes a location
 */
if(isset($_POST["deleteLocation"])){
    $idOfLocToDelete = $_POST["keyIdLoc"];

    // Finding if location has a submitted picture
    $queryFindIfLocHasPicture = "SELECT IMAGE_ID FROM game_location WHERE ID = '$idOfLocToDelete';";
    $resultFindPicture = $conn->query($queryFindIfLocHasPicture);

    // If there is a picture we delete it
    if ($rowDelPic = $resultFindPicture->fetch_assoc()) {
        $idOfPictureToDel = $rowDelPic["IMAGE_ID"];

        $queryFindFilenameOfPic = "SELECT filename FROM image WHERE ID='$idOfPictureToDel'";
        $resultFindFilename = $conn->query($queryFindFilenameOfPic);

        if ($rowFindFilename = $resultFindFilename->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    }

    $queryDeleteLoc = "DELETE FROM game_location WHERE ID='$idOfLocToDelete';";
    if($conn->query($queryDeleteLoc)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete location')</script>";
    }
}

/**
 * Actions when user adds an object of a location
 */
if (isset($_POST["btnAddObjOfLoc"])) {
    $idOfLoc = $_POST["locId"]; // getting the id of the location
    $idOfObj = $_POST["objId"]; // getting the id of the object

    // query to add the object of the location
    $queryToAddObjectToLoc = "INSERT INTO game_location_has_game_object (GAME_LOCATION_ID, GAME_OBJECT_ID) 
                              VALUES ('$idOfLoc', '$idOfObj')";
    if ($conn->query($queryToAddObjectToLoc)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add object of the location')</script>";
    }
}

/**
 * Actions when user removes an object from a location
 */
if (isset($_POST["delObjOfLoc"])) {
    $idOfLoc = $_POST["locId"]; // getting the id of the location
    $idOfObj = $_POST["objId"]; // getting the id of the object

    // query to remove an object from a location
    $queryToDelObjFromLoc = "DELETE FROM game_location_has_game_object WHERE GAME_LOCATION_ID = '$idOfLoc' AND 
                             GAME_OBJECT_ID = '$idOfObj';";

    if ($conn->query($queryToDelObjFromLoc)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove the object from the location')</script>";
    }
}

/**
 * Actions when user adds a character of a location
 */
if (isset($_POST["btnAddCharOfLoc"])) {
    $idOfLoc = $_POST["locId"]; // getting the id of the location
    $idOfChar = $_POST["charId"]; // getting the id of the character

    // query to add the character of the location
    $queryToAddCharacterToLoc = "INSERT INTO game_location_has_game_character (GAME_LOCATION_ID, GAME_CHARACTER_ID) 
                              VALUES ('$idOfLoc', '$idOfChar')";
    if ($conn->query($queryToAddCharacterToLoc)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add character of the location')</script>";
    }
}

/**
 * Actions when user removes a character from a location
 */
if (isset($_POST["delCharOfLoc"])) {
    $idOfLoc = $_POST["locId"]; // getting the id of the location
    $idOfChar = $_POST["charId"]; // getting the id of the character

    // query to remove a character from a location
    $queryToDelCharFromLoc = "DELETE FROM game_location_has_game_character WHERE GAME_LOCATION_ID = '$idOfLoc' AND 
                             GAME_CHARACTER_ID = '$idOfChar';";

    if ($conn->query($queryToDelCharFromLoc)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove the character from the location')</script>";
    }
}

/**
 * Actions when user adds a scene
 */
if (isset($_POST["saveScene"])) {
    $sceneName = test_data($_POST["sceneName"]); // getting the name of the scene
    $sceneDescription = test_data($_POST["sceneDescription"]); // getting the description of the scene

    $queryToAddScene = "INSERT INTO scene (GAME_ELEMENTS_ID, name, describe_scene) 
        VALUES ('$gameElementsId', '$sceneName', '$sceneDescription')";

    if ($conn->query($queryToAddScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add scene')</script>";
    }
}

/**
 * Actions when user edits a scene
 */
if (isset($_POST["editScene"])) {
    $idOfScene = $_POST["keyIdScene"];
    $nameOfScene = test_data($_POST["sceneName"]);
    $descriptionOfScene = test_data($_POST["sceneDescription"]);

    $queryToEditScene = "UPDATE scene SET name='$nameOfScene', describe_scene='$descriptionOfScene' 
                         WHERE ID='$idOfScene'";

    if ($conn->query($queryToEditScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else {
        echo "<script>alert('Error: cannot edit scene')</script>";
    }
}

/**
 * Actions when user deletes a scene
 */
if (isset($_POST["deleteScene"])) {
    $idOfScene = $_POST["keyIdScene"];

    if ($conn->query("DELETE FROM scene WHERE ID='$idOfScene'")) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else {
        echo "<script>alert('Error: cannot edit scene')</script>";
    }
}

/**
 * Actions when user adds a character of a scene
 */
if (isset($_POST["btnAddCharOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfChar = $_POST["charId"]; // getting the id of the character

    // query to add the character of the scene
    $queryToAddCharacterToScene = "INSERT INTO game_character_has_scene (GAME_CHARACTER_ID, SCENE_ID) 
                              VALUES ('$idOfChar', '$idOfScene')";
    if ($conn->query($queryToAddCharacterToScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add character of the scene')</script>";
    }
}

/**
 * Actions when user removes a character from a scene
 */
if (isset($_POST["delCharOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfChar = $_POST["charId"]; // getting the id of the character

    // query to remove a character from a scene
    $queryToDelCharFromScene = "DELETE FROM game_character_has_scene WHERE SCENE_ID = '$idOfScene' AND 
                             GAME_CHARACTER_ID = '$idOfChar';";

    if ($conn->query($queryToDelCharFromScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove the character from the scene')</script>";
    }
}

/**
 * Actions when user adds an object of a scene
 */
if (isset($_POST["btnAddObjOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfObj = $_POST["objId"]; // getting the id of the object

    // query to add the object of the scene
    $queryToAddObjectToScene = "INSERT INTO game_object_has_scene (GAME_OBJECT_ID, SCENE_ID) 
                              VALUES ('$idOfObj', '$idOfScene')";
    if ($conn->query($queryToAddObjectToScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add object of the scene')</script>";
    }
}

/**
 * Actions when user removes an object from a scene
 */
if (isset($_POST["delObjOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfObj = $_POST["objId"]; // getting the id of the object

    // query to remove an object from a scene
    $queryToDelObjFromScene = "DELETE FROM game_object_has_scene WHERE SCENE_ID = '$idOfScene' AND 
                             GAME_OBJECT_ID = '$idOfObj';";

    if ($conn->query($queryToDelObjFromScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove the object from the scene')</script>";
    }
}

/**
 * Actions when user adds a location of a scene
 */
if (isset($_POST["btnAddLocOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfLoc = $_POST["locId"]; // getting the id of the location

    // query to add the location of the scene
    $queryToAddLocationToScene = "INSERT INTO game_location_has_scene (GAME_LOCATION_ID, SCENE_ID) 
                              VALUES ('$idOfLoc', '$idOfScene')";
    if ($conn->query($queryToAddLocationToScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add location of the scene')</script>";
    }
}

/**
 * Actions when user removes a location from a scene
 */
if (isset($_POST["delLocOfScene"])) {
    $idOfScene = $_POST["sceneId"]; // getting the id of the scene
    $idOfLoc = $_POST["locId"]; // getting the id of the location

    // query to remove a location from a scene
    $queryToDelLocFromScene = "DELETE FROM game_location_has_scene WHERE SCENE_ID = '$idOfScene' AND 
                             GAME_LOCATION_ID = '$idOfLoc';";

    if ($conn->query($queryToDelLocFromScene)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove the location from the scene')</script>";
    }
}

/**
 * Actions when user adds a new objective
 */
if (isset($_POST["saveObjective"])) {
    $titleOfObjective = test_data($_POST["objTitle"]);
    $descriptionOfObjective = test_data($_POST["objDescription"]);

    $queryToAddObjective = "INSERT INTO game_objective (GAME_ELEMENTS_ID, title, description) 
                            VALUES ('$gameElementsId', '$titleOfObjective', '$descriptionOfObjective')";

    if ($conn->query($queryToAddObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add objective')</script>";
    }
}

/**
 * Actions when user deletes an objective
 */
if (isset($_POST["deleteObjective"])) {
    $idOfObjectiveToDel = $_POST["keyIdObjective"];

    if ($conn->query("DELETE FROM game_objective WHERE ID='$idOfObjectiveToDel'")) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete the objective')</script>";
    }
}

/**
 * Actions when user edits an objective
 */
if (isset($_POST["editObjective"])) {
    $idOfObjectiveToEdit = $_POST["keyIdObjective"];
    $titleOfObjective = test_data($_POST["objectiveTitle"]);
    $descriptionOfObjective = test_data($_POST["objectiveDescription"]);

    $queryToUpdateObjective = "UPDATE game_objective SET title='$titleOfObjective', description='$descriptionOfObjective'
                               WHERE ID='$idOfObjectiveToEdit';";

    if ($conn->query($queryToUpdateObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot update the objective')</script>";
    }
}

/**
 * Actions when user adds a character to an objective
 */
if (isset($_POST["btnAddCharOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfChar = $_POST["charId"];

    $queryToAddCharacterToObjective = "INSERT INTO game_objective_has_game_character (GAME_OBJECTIVE_ID, GAME_CHARACTER_ID)
                                       VALUES ('$idOfObjective', '$idOfChar')";

    if ($conn->query($queryToAddCharacterToObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add character to the objective')</script>";
    }
}

/**
 * Actions when user deletes a character from an objective
 */
if (isset($_POST["delCharOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfChar = $_POST["charId"];

    $queryToDeleteCharFromObjective = "DELETE FROM game_objective_has_game_character WHERE GAME_OBJECTIVE_ID='$idOfObjective' 
                                        AND GAME_CHARACTER_ID='$idOfChar';";

    if ($conn->query($queryToDeleteCharFromObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete character from the objective')</script>";
    }
}

/**
 * Actions when user adds an object to an objective
 */
if (isset($_POST["btnAddObjOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfObject = $_POST["objId"];

    $queryToAddObjectToObjective = "INSERT INTO game_objective_has_game_object (GAME_OBJECTIVE_ID, GAME_OBJECT_ID) 
                                    VALUES ('$idOfObjective', '$idOfObject');";

    if ($conn->query($queryToAddObjectToObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add object to the objective')</script>";
    }
}

/**
 * Actions when user removes an object from an objective
 */
if (isset($_POST["delObjOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfObject = $_POST["objId"];

    $queryToRemoveObjectFromObjective = "DELETE FROM game_objective_has_game_object WHERE GAME_OBJECTIVE_ID='$idOfObjective'
                                         AND GAME_OBJECT_ID='$idOfObject';";

    if ($conn->query($queryToRemoveObjectFromObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete object from the objective')</script>";
    }
}

/**
 * Actions when user adds a scene to an objective
 */
if (isset($_POST["btnAddSceneOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfScene = $_POST["sceneId"];

    $queryToAddSceneToObjective = "INSERT INTO game_objective_has_scene (GAME_OBJECTIVE_ID, SCENE_ID) 
                                    VALUES ('$idOfObjective', '$idOfScene');";

    if ($conn->query($queryToAddSceneToObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add scene to the objective')</script>";
    }
}

/**
 * Actions when user removes a scene from an objective
 */
if (isset($_POST["delSceneOfObjective"])) {
    $idOfObjective = $_POST["objectiveId"];
    $idOfScene = $_POST["sceneId"];

    $queryToRemoveSceneFromObjective = "DELETE FROM game_objective_has_scene WHERE GAME_OBJECTIVE_ID='$idOfObjective'
                                         AND SCENE_ID='$idOfScene';";

    if ($conn->query($queryToRemoveSceneFromObjective)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete scene from the objective')</script>";
    }
}

/**
 * Actions when user adds a dialog
 */
if (isset($_POST["saveDialog"])) {
    $charTalksId = $_POST["selectFromChar"];
    $dialogName = test_data($_POST["dialogName"]);
    $dialogText = test_data($_POST["dialogText"]);

    $queryToAddDialog = "INSERT INTO game_dialog (GAME_ELEMENTS_ID, GAME_CHARACTER_TALKS, name, text) VALUES ('$gameElementsId', '$charTalksId', '$dialogName', '$dialogText');";

    if ($conn->query($queryToAddDialog)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add dialog')</script>";
    }
}

/**
 * Actions when user edits a dialog
 */
if (isset($_POST["editDialog"])) {
    $idOfDialog = $_POST["keyIdDialog"];
    $nameOfDialog = test_data($_POST["dialogName"]);
    $textOfDialog = test_data($_POST["dialogText"]);
    $talkerChar = $_POST["selectFromChar"];

    $queryToUpdateDialog = "UPDATE game_dialog SET name='$nameOfDialog', GAME_CHARACTER_TALKS='$talkerChar', text='$textOfDialog' WHERE ID='$idOfDialog';";

    if ($conn->query($queryToUpdateDialog)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot update dialog')</script>";
    }
}

/**
* Actions when user deletes a dialog
 */
if (isset($_POST["deleteDialog"])) {
    $idOfDialogToDel = $_POST["keyIdDialog"];

    $queryToDeleteDialog = "DELETE FROM game_dialog WHERE ID='$idOfDialogToDel';";

    if ($conn->query($queryToDeleteDialog)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot delete dialog')</script>";
    }
}

/**
 * Actions when user adds a character that listens a dialog
 */
if (isset($_POST["btnAddListenCharOfDialog"])) {
    $characterTalksId = $_POST["characterTalksId"];
    $characterListensId = $_POST["characterListensId"];
    $dialogId = $_POST["dialogId"];

    $queryToAddCharListenDialog = "INSERT INTO character_dialogs_character (CHARACTER_TALKS_ID, CHARACTER_LISTENS_ID, DIALOG_ID)
                                    VALUES ('$characterTalksId', '$characterListensId', '$dialogId')";

    if ($conn->query($queryToAddCharListenDialog)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot add character that listens the dialog')</script>";
    }
}
/**
 * Actions when user removes a character that listens a dialog
 */
if (isset($_POST["delCharListensFromDialog"])) {
    $characterTalksId = $_POST["characterTalksId"];
    $characterListensId = $_POST["characterListensId"];
    $dialogId = $_POST["dialogId"];

    $queryToRemoveCharThatListenDialog = "DELETE FROM character_dialogs_character WHERE CHARACTER_TALKS_ID='$characterTalksId'
                                           AND CHARACTER_LISTENS_ID='$characterListensId' AND DIALOG_ID='$dialogId';";

    if ($conn->query($queryToRemoveCharThatListenDialog)) {
        header("Refresh:0"); // if query is executed successfully we refresh the page
    } else {
        echo "<script>alert('Error: cannot remove character that listens the dialog')</script>";
    }
}


/*
 * Function to filter data.
 */
function test_data($data)
{
    return htmlspecialchars(addslashes(stripslashes($data)));
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
            <a href="../profile.php" class="w3-bar-item w3-button">Settings <i class="fa fa-cog"></i></a>
            <a href="../logout.php" class="w3-bar-item w3-button">Logout <i class="fa fa-sign-out"></i></a>
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
            <a href="../profile.php" class="w3-bar-item w3-button">Settings <i class=\"fa fa-cog\"></i></a>
            <a href="../logout.php" class="w3-bar-item w3-button">Logout <i class=\"fa fa-sign-out\"></i></a>
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
    <div class="w3-modal-content w3-animate-zoom w3-margin-bottom">
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
            <label for="charState<?php echo $gameElementsId; ?>">Describe the character's state</label>
            <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="charState<?php echo $gameElementsId; ?>"
                      name="charState"></textarea><br>
            <label for="charSpeed<?php echo $gameElementsId; ?>">Describe the character's speed</label>
            <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="charSpeed<?php echo $gameElementsId; ?>"
                      name="charSpeed"></textarea><br>
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

            <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                <label for="imgLocation" class="w3-margin-top" id="labelImLoc">Choose an image of the location</label><br>
                <input type="file" id="imgLocation" class="w3-margin-top" name="imgLocation" accept="image/*"><br><br>

                <label for="locName" class="w3-margin-top">Write the name of the location *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="locName" name="locName" required><br>

                <label for="locDescription">Describe the location</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="locDescription"
                          name="locDescription"></textarea><br>

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
                <label for="dialogName" class="w3-margin-top">Write the name of the dialog *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="dialogName" name="dialogName" required><br>

                <label for="selectFromChar">Choose a character that talks to others *</label>
                <select class="w3-select w3-border w3-margin-top" id="selectFromChar" name="selectFromChar" required>
                    <option value="" disabled selected>Choose a character</option>
                    <?php
                    // query to load all characters
                    $queryLoadAllCharactersForDialogs = "SELECT * FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
                    $resultLoadAllCharactersForDialogs = mysqli_query($conn, $queryLoadAllCharactersForDialogs); // executing the query

                    while($rowLoadChar = $resultLoadAllCharactersForDialogs->fetch_assoc()) {
                        $idOfChar = $rowLoadChar["ID"];
                        $nameOfChar = $rowLoadChar["name"];
                        echo "<option value=\"$idOfChar\">$nameOfChar</option>";
                    }
                    ?>
                </select><br><br>

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
        $charState = $rowLoadChar["state"];
        $charSpeed = $rowLoadChar["speed"];
        $idOfImage = $rowLoadChar["IMAGE_ID"];

        if (isset($idOfImage)){
            $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

            if($rowImage = $resultImage->fetch_assoc()){
                $imgFilenameChar = $rowImage["filename"];
            }
        } else {
            $imgFilenameChar = null;
        }

        echo "<div id=\"characters-modal-edit$idOfChar\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('characters-modal-edit$idOfChar')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit character <b>$nameOfChar</b></h3><br>";

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
                      
            <label for=\"charStateEdit$idOfChar\">Describe the character's state</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"charStateEdit$idOfChar\"
                      name=\"charState\">$charState</textarea><br>
                      
            <label for=\"charSpeedEdit$idOfChar\">Describe the character's speed</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"charSpeedEdit$idOfChar\"
                      name=\"charSpeed\">$charSpeed</textarea><br>  
                              
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
    } else {
        $imgFilenameObj = null;
    }

    echo "<div id=\"objects-modal-edit$idOfObj\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\"  enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('objects-modal-edit$idOfObj')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit object <b>$nameOfObj</b></h3><br>";

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

// query to load all locations
$queryLoadAllLocations = "SELECT * FROM game_location WHERE GAME_ELEMENTS_ID='$gameElementsId';";
$resultLoadAllLocations = mysqli_query($conn, $queryLoadAllLocations); // executing the query

while($rowLoadLoc = $resultLoadAllLocations->fetch_assoc()){
    $idOfLoc = $rowLoadLoc["ID"];
    $nameOfLoc = $rowLoadLoc["name"];
    $locDescribe = $rowLoadLoc["describe_loc"];

    if (isset($rowLoadLoc["IMAGE_ID"])) {
        $idOfImage = $rowLoadLoc["IMAGE_ID"];

        $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

        if($rowImage = $resultImage->fetch_assoc()){
            $imgFilenameLoc = $rowImage["filename"];
        }
    } else {
        $imgFilenameLoc = null;
    }

    echo "<div id=\"locations-modal-edit$idOfLoc\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\"  enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('locations-modal-edit$idOfLoc')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit location <b>$nameOfLoc</b></h3><br>";

    if(isset($imgFilenameLoc)){
        echo "<img src='/ImagesFromUsers-GDD/$nameOfDoc/WorldBuilding/Locations/$imgFilenameLoc' alt='Image of location' style='width: 50%; height: auto;'><br><br>";
    }

    echo "<label for=\"imgLocEdit$idOfLoc\" class=\"w3-margin-top\" id=\"labelImLoc\">Choose an image of the location</label><br>
            <input type=\"file\" id=\"imgLocEdit$idOfLoc\" class=\"w3-margin-top\" name=\"imgLoc\" accept=\"image/*\"><br><br>
            
            <input type=\"hidden\"  name=\"keyIdLoc\" value=\"$idOfLoc\" />

            <label for=\"locNameEdit$idOfLoc\" class=\"w3-margin-top\">Write the name of the location *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"locNameEdit$idOfLoc\" value=\"$nameOfLoc\" name=\"locName\" required><br>

            <label for=\"locDescriptionEdit$idOfLoc\">Describe the location</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"locDescriptionEdit$idOfLoc\"
                      name=\"locDescription\">$locDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editLocation\">Save</button>
            </div>
        </form>
    </div>
</div>";

    echo "<div id=\"locations-add-objects$idOfLoc\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
                <span onclick=\"hideElement('locations-add-objects$idOfLoc')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Add objects of the location <b>$nameOfLoc</b></h3><br>";
    ?>

    <table class="w3-table w3-border w3-centered w3-striped">
                    <tr>
                        <th>Objects</th>
                        <th>Add/Remove</th>
                    </tr>
                    <?php
                    // query to load all objects
                    $queryLoadAllObjectsForLocs = "SELECT ID, name FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';";
                    $resultLoadAllObjectsForLocs = mysqli_query($conn, $queryLoadAllObjectsForLocs); // executing the query

                    while ($rowLoadObjForLoc = $resultLoadAllObjectsForLocs->fetch_assoc()) {
                        $rowIdObj = $rowLoadObjForLoc["ID"];
                        $rowNameObj = $rowLoadObjForLoc["name"];

                        $queryToCheckIfObjectIsAdded = "SELECT * FROM game_location_has_game_object WHERE 
                                                        GAME_LOCATION_ID='$idOfLoc' AND GAME_OBJECT_ID='$rowIdObj';";
                        $resultCheckIfObjIsAdded = mysqli_query($conn, $queryToCheckIfObjectIsAdded);

                        if ($resultCheckIfObjIsAdded->num_rows === 0) {
                            echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfLoc\" type=\"submit\" name=\"btnAddObjOfLoc\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"locId\" value=\"$idOfLoc\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
                        } else {
                            echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfLoc\" type=\"submit\" name=\"delObjOfLoc\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"locId\" value=\"$idOfLoc\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
                        }
                    }
                    ?>
    </table><br>
    <?php echo "</div></div>" ?>

    <?php
    echo "<div id=\"locations-add-characters$idOfLoc\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('locations-add-characters$idOfLoc')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add characters of the location <b>$nameOfLoc</b></h3><br>";
    ?>

    <table class="w3-table w3-border w3-centered w3-striped">
        <tr>
            <th>Characters</th>
            <th>Add/Remove</th>
        </tr>
        <?php
        // query to load all characters
        $queryLoadAllCharactersForLocs = "SELECT ID, name FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllCharactersForLocs = mysqli_query($conn, $queryLoadAllCharactersForLocs); // executing the query

        while ($rowLoadCharForLoc = $resultLoadAllCharactersForLocs->fetch_assoc()) {
            $rowIdChar = $rowLoadCharForLoc["ID"];
            $rowNameChar = $rowLoadCharForLoc["name"];

            $queryToCheckIfCharacterIsAdded = "SELECT * FROM game_location_has_game_character WHERE 
                                                        GAME_LOCATION_ID='$idOfLoc' AND GAME_CHARACTER_ID='$rowIdChar';";
            $resultCheckIfCharIsAdded = mysqli_query($conn, $queryToCheckIfCharacterIsAdded);

            if ($resultCheckIfCharIsAdded->num_rows === 0) {
                echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfLoc\" type=\"submit\" name=\"btnAddCharOfLoc\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"locId\" value=\"$idOfLoc\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
            } else {
                echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfLoc\" type=\"submit\" name=\"delCharOfLoc\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"locId\" value=\"$idOfLoc\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
            }
        }
        ?>
    </table><br>
    <?php echo "</div></div>" ?>
<?php
}

// query to load all scenes
$queryLoadAllScenes = "SELECT * FROM scene WHERE GAME_ELEMENTS_ID='$gameElementsId';";
$resultLoadAllScenes = mysqli_query($conn, $queryLoadAllScenes); // executing the query

while($rowLoadScene = $resultLoadAllScenes->fetch_assoc()){
    $idOfScene = $rowLoadScene["ID"];
    $nameOfScene = $rowLoadScene["name"];
    $descriptionOfScene = $rowLoadScene["describe_scene"];

    echo "<div id=\"scenes-modal-edit$idOfScene\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('scenes-modal-edit$idOfScene')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit scene <b>$nameOfScene</b></h3><br>";

    echo "<label for=\"sceneNameEdit$idOfScene\" class=\"w3-margin-top\">Write the name of the scene *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"sceneNameEdit$idOfScene\" value=\"$nameOfScene\" name=\"sceneName\" required><br>
            <input type=\"hidden\"  name=\"keyIdScene\" value=\"$idOfScene\" />
            <label for=\"sceneDescriptionEdit$idOfScene\">Describe the scene</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"sceneDescriptionEdit$idOfScene\"
                      name=\"sceneDescription\">$descriptionOfScene</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editScene\">Save</button>
            </div>
        </form>
    </div>
</div>";

    echo "<div id=\"scenes-add-characters$idOfScene\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('scenes-add-characters$idOfScene')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add characters of the scene <b>$nameOfScene</b></h3><br>";
    ?>

<table class="w3-table w3-border w3-centered w3-striped">
    <tr>
        <th>Characters</th>
        <th>Add/Remove</th>
    </tr>
    <?php
    // query to load all characters
    $queryLoadAllCharactersForScenes = "SELECT ID, name FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    $resultLoadAllCharactersForScenes = mysqli_query($conn, $queryLoadAllCharactersForScenes); // executing the query

    while ($rowLoadCharForScenes = $resultLoadAllCharactersForScenes->fetch_assoc()) {
        $rowIdChar = $rowLoadCharForScenes["ID"];
        $rowNameChar = $rowLoadCharForScenes["name"];

        $queryToCheckIfCharacterIsAdded = "SELECT * FROM game_character_has_scene WHERE 
                                                        SCENE_ID='$idOfScene' AND GAME_CHARACTER_ID='$rowIdChar';";
        $resultCheckIfCharIsAdded = mysqli_query($conn, $queryToCheckIfCharacterIsAdded);

        if ($resultCheckIfCharIsAdded->num_rows === 0) {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"btnAddCharOfScene\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
        } else {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"delCharOfScene\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
        }
    }
    ?>
</table><br>
<?php echo "</div></div>"; ?>

    <?php echo "<div id=\"scenes-add-objects$idOfScene\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('scenes-add-objects$idOfScene')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add objects of the scene <b>$nameOfScene</b></h3><br>";
                                                                                         ?>

    <table class="w3-table w3-border w3-centered w3-striped">
        <tr>
            <th>Objects</th>
            <th>Add/Remove</th>
        </tr>
        <?php
        // query to load all objects
        $queryLoadAllObjectsForScenes = "SELECT ID, name FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllObjectsForScenes = mysqli_query($conn, $queryLoadAllObjectsForScenes); // executing the query

        while ($rowLoadObjForScenes = $resultLoadAllObjectsForScenes->fetch_assoc()) {
            $rowIdObj = $rowLoadObjForScenes["ID"];
            $rowNameObj = $rowLoadObjForScenes["name"];

            $queryToCheckIfObjIsAdded = "SELECT * FROM game_object_has_scene WHERE 
                                                        SCENE_ID='$idOfScene' AND GAME_OBJECT_ID='$rowIdObj';";
            $resultCheckIfObjIsAdded = mysqli_query($conn, $queryToCheckIfObjIsAdded);

            if ($resultCheckIfObjIsAdded->num_rows === 0) {
                echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"btnAddObjOfScene\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
            } else {
                echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"delObjOfScene\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
            }
        }
        ?>
    </table><br>
    <?php echo "</div></div>"; ?>

    <?php echo "<div id=\"scenes-add-locations$idOfScene\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('scenes-add-locations$idOfScene')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add locations of the scene <b>$nameOfScene</b></h3><br>";
    ?>

    <table class="w3-table w3-border w3-centered w3-striped">
        <tr>
            <th>Locations</th>
            <th>Add/Remove</th>
        </tr>
        <?php
        // query to load all locations
        $queryLoadAllLocationsForScenes = "SELECT ID, name FROM game_location WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllLocationsForScenes = mysqli_query($conn, $queryLoadAllLocationsForScenes); // executing the query

        while ($rowLoadLocForScenes = $resultLoadAllLocationsForScenes->fetch_assoc()) {
            $rowIdLoc = $rowLoadLocForScenes["ID"];
            $rowNameLoc = $rowLoadLocForScenes["name"];

            $queryToCheckIfLocIsAdded = "SELECT * FROM game_location_has_scene WHERE 
                                                        SCENE_ID='$idOfScene' AND GAME_LOCATION_ID='$rowIdLoc';";
            $resultCheckIfLocIsAdded = mysqli_query($conn, $queryToCheckIfLocIsAdded);

            if ($resultCheckIfLocIsAdded->num_rows === 0) {
                echo "<tr>
                        <td>$rowNameLoc</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"btnAddLocOfScene\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"locId\" value=\"$rowIdLoc\"/></form></td>
                    </tr>";
            } else {
                echo "<tr>
                        <td>$rowNameLoc</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfScene\" type=\"submit\" name=\"delLocOfScene\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$idOfScene\"/>
                        <input type=\"hidden\" name=\"locId\" value=\"$rowIdLoc\"/></form></td>
                    </tr>";
            }
        }
        ?>
    </table><br>
    <?php echo "</div></div>";
}
?>

    <?php
    // query to load all objectives
    $queryLoadAllObjectives = "SELECT * FROM game_objective WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    $resultLoadAllObjectives = mysqli_query($conn, $queryLoadAllObjectives); // executing the query

    while($rowLoadObjective = $resultLoadAllObjectives->fetch_assoc()) {
        $idOfObjective = $rowLoadObjective["ID"];
        $titleOfObjective = $rowLoadObjective["title"];
        $descriptionOfObjective = $rowLoadObjective["description"];

        echo "<div id=\"objectives-modal-edit$idOfObjective\" class=\"w3-modal w3-padding-16\">
        <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" class=\"w3-container\" style=\"text-align: center;\">
        <span onclick=\"hideElement('objectives-modal-edit$idOfObjective')\" class=\"w3-button w3-display-topright w3-hover-red\">
        <i class=\"fa fa-close\"></i></span>
        <h3 class=\"headerForModal\">Edit objective <b>$titleOfObjective</b></h3><br>";

            echo "<label for=\"objectiveTitleEdit$idOfObjective\" class=\"w3-margin-top\">Write the title of the objective *</label>
        <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"objectiveTitleEdit$idOfObjective\" value=\"$titleOfObjective\" name=\"objectiveTitle\" required><br>
        <input type=\"hidden\"  name=\"keyIdObjective\" value=\"$idOfObjective\" />
        <label for=\"objectiveDescriptionEdit$idOfObjective\">Describe the objective</label>
        <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"objectiveDescriptionEdit$idOfObjective\"
                                                           name=\"objectiveDescription\">$descriptionOfObjective</textarea><br>
        <div class=\"w3-container w3-padding-16\">
        <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editObjective\">Save</button>
        </div>
        </form>
        </div>
        </div>";

    echo "<div id=\"objectives-add-characters$idOfObjective\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('objectives-add-characters$idOfObjective')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add characters of the objective <b>$titleOfObjective</b></h3><br>";
    ?>

<table class="w3-table w3-border w3-centered w3-striped">
    <tr>
        <th>Characters</th>
        <th>Add/Remove</th>
    </tr>
    <?php
    // query to load all characters
    $queryLoadAllCharactersForObjectives = "SELECT ID, name FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    $resultLoadAllCharactersForObjectives = mysqli_query($conn, $queryLoadAllCharactersForObjectives); // executing the query

    while ($rowLoadCharForObjectives = $resultLoadAllCharactersForObjectives->fetch_assoc()) {
        $rowIdChar = $rowLoadCharForObjectives["ID"];
        $rowNameChar = $rowLoadCharForObjectives["name"];

        $queryToCheckIfCharacterIsAdded = "SELECT * FROM game_objective_has_game_character WHERE 
                                                        GAME_OBJECTIVE_ID='$idOfObjective' AND GAME_CHARACTER_ID='$rowIdChar';";
        $resultCheckIfCharIsAdded = mysqli_query($conn, $queryToCheckIfCharacterIsAdded);

        if ($resultCheckIfCharIsAdded->num_rows === 0) {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"btnAddCharOfObjective\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
        } else {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"delCharOfObjective\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"charId\" value=\"$rowIdChar\"/></form></td>
                    </tr>";
        }
    }
    ?>
</table><br>
<?php echo "</div></div>"; ?>

        <?php echo "<div id=\"objectives-add-objects$idOfObjective\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('objectives-add-objects$idOfObjective')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add objects of the objective <b>$titleOfObjective</b></h3><br>";
        ?>

        <table class="w3-table w3-border w3-centered w3-striped">
            <tr>
                <th>Objects</th>
                <th>Add/Remove</th>
            </tr>
            <?php
            // query to load all objects
            $queryLoadAllObjectsForObjectives = "SELECT ID, name FROM game_object WHERE GAME_ELEMENTS_ID='$gameElementsId';";
            $resultLoadAllObjectsForObjectives = mysqli_query($conn, $queryLoadAllObjectsForObjectives); // executing the query

            while ($rowLoadObjForObjectives = $resultLoadAllObjectsForObjectives->fetch_assoc()) {
                $rowIdObj = $rowLoadObjForObjectives["ID"];
                $rowNameObj = $rowLoadObjForObjectives["name"];

                $queryToCheckIfObjIsAdded = "SELECT * FROM game_objective_has_game_object WHERE 
                                                        GAME_OBJECTIVE_ID='$idOfObjective' AND GAME_OBJECT_ID='$rowIdObj';";
                $resultCheckIfObjIsAdded = mysqli_query($conn, $queryToCheckIfObjIsAdded);

                if ($resultCheckIfObjIsAdded->num_rows === 0) {
                    echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"btnAddObjOfObjective\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
                } else {
                    echo "<tr>
                        <td>$rowNameObj</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"delObjOfObjective\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"objId\" value=\"$rowIdObj\"/></form></td>
                    </tr>";
                }
            }
            ?>
        </table><br>
        <?php echo "</div></div>"; ?>

        <?php echo "<div id=\"objectives-add-scenes$idOfObjective\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('objectives-add-scenes$idOfObjective')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add scenes of the objective <b>$titleOfObjective</b></h3><br>";
        ?>

        <table class="w3-table w3-border w3-centered w3-striped">
            <tr>
                <th>Scenes</th>
                <th>Add/Remove</th>
            </tr>
            <?php
            // query to load all scenes
            $queryLoadAllScenesForObjectives = "SELECT ID, name FROM scene WHERE GAME_ELEMENTS_ID='$gameElementsId';";
            $resultLoadAllScenesForObjectives = mysqli_query($conn, $queryLoadAllScenesForObjectives); // executing the query

            while ($rowLoadSceneForObjectives = $resultLoadAllScenesForObjectives->fetch_assoc()) {
                $rowIdScene = $rowLoadSceneForObjectives["ID"];
                $rowNameScene = $rowLoadSceneForObjectives["name"];

                $queryToCheckIfSceneIsAdded = "SELECT * FROM game_objective_has_scene WHERE 
                                                        GAME_OBJECTIVE_ID='$idOfObjective' AND SCENE_ID='$rowIdScene';";
                $resultCheckIfSceneIsAdded = mysqli_query($conn, $queryToCheckIfSceneIsAdded);

                if ($resultCheckIfSceneIsAdded->num_rows === 0) {
                    echo "<tr>
                        <td>$rowNameScene</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"btnAddSceneOfObjective\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$rowIdScene\"/></form></td>
                    </tr>";
                } else {
                    echo "<tr>
                        <td>$rowNameScene</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfObjective\" type=\"submit\" name=\"delSceneOfObjective\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"objectiveId\" value=\"$idOfObjective\"/>
                        <input type=\"hidden\" name=\"sceneId\" value=\"$rowIdScene\"/></form></td>
                    </tr>";
                }
            }
            ?>
        </table><br>
        <?php echo "</div></div>"; ?>
<?php
    }
?>

<?php
    // query to load all dialogs
    $queryLoadAllDialogs = "SELECT * FROM game_dialog WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    $resultLoadAllDialogs = mysqli_query($conn, $queryLoadAllDialogs); // executing the query

    while($rowLoadDialogs = $resultLoadAllDialogs->fetch_assoc()) {
        $idOfDialog = $rowLoadDialogs["ID"];
        $characterTalksId = $rowLoadDialogs["GAME_CHARACTER_TALKS"];
        $nameOfDialog = $rowLoadDialogs["name"];
        $textOfDialog = $rowLoadDialogs["text"];

        echo "<div id=\"dialogs-modal-edit$idOfDialog\" class=\"w3-modal w3-padding-16\">
        <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" class=\"w3-container\" style=\"text-align: center;\">
        <span onclick=\"hideElement('dialogs-modal-edit$idOfDialog')\" class=\"w3-button w3-display-topright w3-hover-red\">
        <i class=\"fa fa-close\"></i></span>
        <h3 class=\"headerForModal\">Edit dialog <b>$nameOfDialog</b></h3><br>";

            echo "<label for=\"dialogNameEdit$idOfDialog\" class=\"w3-margin-top\">Write the name of the dialog *</label>
        <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"dialogNameEdit$idOfDialog\" value=\"$nameOfDialog\" name=\"dialogName\" required><br>
        <input type=\"hidden\"  name=\"keyIdDialog\" value=\"$idOfDialog\" />
        
        <label for=\"selectFromChar$idOfDialog\">Choose a character that talks to others *</label>
                <select class=\"w3-select w3-border w3-margin-top\" id=\"selectFromChar$idOfDialog\" name=\"selectFromChar\" required>
                    <option value=\"\" disabled>Choose a character</option>";

                    // query to load all characters
                    $queryLoadAllCharactersForDialogs = "SELECT * FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
                    $resultLoadAllCharactersForDialogs = mysqli_query($conn, $queryLoadAllCharactersForDialogs); // executing the query

                    while($rowLoadChar = $resultLoadAllCharactersForDialogs->fetch_assoc()) {
                        $idOfChar = $rowLoadChar["ID"];
                        $nameOfChar = $rowLoadChar["name"];
                        if ($characterTalksId === $idOfChar) {
                            echo "<option value=\"$idOfChar\" selected>$nameOfChar</option>";
                        } else {
                            echo "<option value=\"$idOfChar\">$nameOfChar</option>";
                        }
                    }

echo "</select><br><br>

        <label for=\"textDialogEdit$idOfDialog\">Write the dialog *</label>
        <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"textDialogEdit$idOfDialog\"
                                                           name=\"dialogText\">$textOfDialog</textarea><br>
        <div class=\"w3-container w3-padding-16\">
        <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editDialog\">Save</button>
        </div>
        </form>
        </div>
        </div>";

echo "<div id=\"dialogs-add-characters$idOfDialog\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom w3-padding-16\" style=\"text-align: center;\">
    <span onclick=\"hideElement('dialogs-add-characters$idOfDialog')\" class=\"w3-button w3-display-topright w3-hover-red\">
    <i class=\"fa fa-close\"></i></span>
    <h3 class=\"headerForModal\">Add characters that listen the dialog <b>$nameOfDialog</b></h3><br>";
?>

<table class="w3-table w3-border w3-centered w3-striped">
    <tr>
        <th>Characters</th>
        <th>Add/Remove</th>
    </tr>
    <?php
    // query to load all characters
    $queryLoadAllCharactersForDialogsV2 = "SELECT ID, name FROM game_character WHERE GAME_ELEMENTS_ID='$gameElementsId';";
    $resultLoadAllCharactersForDialogsV2 = mysqli_query($conn, $queryLoadAllCharactersForDialogsV2); // executing the query

    while ($rowLoadCharForDialogs = $resultLoadAllCharactersForDialogsV2->fetch_assoc()) {
        $rowIdChar = $rowLoadCharForDialogs["ID"];
        $rowNameChar = $rowLoadCharForDialogs["name"];

        $queryToCheckIfCharacterIsAdded = "SELECT * FROM character_dialogs_character WHERE 
                                                        CHARACTER_TALKS_ID='$characterTalksId' AND 
                                                CHARACTER_LISTENS_ID='$rowIdChar' AND DIALOG_ID='$idOfDialog';";
        $resultCheckIfCharIsAdded = mysqli_query($conn, $queryToCheckIfCharacterIsAdded);

        if ($resultCheckIfCharIsAdded->num_rows === 0) {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-green w3-circle transmission\" 
                        id=\"$idOfDialog\" type=\"submit\" name=\"btnAddListenCharOfDialog\"><i class=\"fa fa-plus\"></i></button>
                        <input type=\"hidden\" name=\"characterTalksId\" value=\"$characterTalksId\"/>
                        <input type=\"hidden\" name=\"characterListensId\" value=\"$rowIdChar\"/>
                        <input type=\"hidden\" name=\"dialogId\" value=\"$idOfDialog\"/></form></td>
                        
                    </tr>";
        } else {
            echo "<tr>
                        <td>$rowNameChar</td>
                        <td><form method=\"post\" action=\"\"><button class=\"w3-button w3-red w3-circle transmission\" 
                        id=\"$idOfDialog\" type=\"submit\" name=\"delCharListensFromDialog\"><i class=\"fa fa-minus\"></i></button>
                        <input type=\"hidden\" name=\"characterTalksId\" value=\"$characterTalksId\"/>
                        <input type=\"hidden\" name=\"characterListensId\" value=\"$rowIdChar\"/>
                        <input type=\"hidden\" name=\"dialogId\" value=\"$idOfDialog\"/></form></td>
                    </tr>";
        }
    }
    ?>
</table><br>
<?php echo "</div></div>"; ?>
<?php
    }
?>


<!--- The form of game elements where user can add characters, objects, etc -->
<div class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding">
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
                $nameCharLoadForConfirm = addslashes($rowLoadChar["name"]);

                echo "<tr><td>" . $nameCharLoad . "</td><td>" . $rowLoadChar["type_char"] .
                    "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('characters-modal-edit$idOfCharLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the character $nameCharLoadForConfirm')\" type=\"submit\"
                                    name=\"deleteCharacter\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdChar\" value=\"$idOfCharLoad\" /></form></tr>";
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
            $nameObjLoadForConfirm = addslashes($rowLoadObj["name"]);

            echo "<tr><td>" . $nameObjLoad . "</td><td>" . $rowLoadObj["type_obj"] .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('objects-modal-edit$idOfObjLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the object $nameObjLoadForConfirm')\" type=\"submit\"
                                    name=\"deleteObject\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdObj\" value=\"$idOfObjLoad\" /></form></tr>";
        }
        ?>
    </table><br>


    <label for="locations">Add locations of the game</label>
    <button onclick="showElement('locations-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="locations" type="button" name="locations">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadLocations">
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Add characters</th>
            <th>Add objects</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryLoadAllLocationsV2 = "SELECT * FROM game_location WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllLocationsV2 = mysqli_query($conn, $queryLoadAllLocationsV2); // executing the query

        // Loading all entered locations
        while($rowLoadLoc = $resultLoadAllLocationsV2->fetch_assoc()){
            $idOfLocLoad = $rowLoadLoc["ID"];
            $nameLocLoad = $rowLoadLoc["name"];
            $nameLocLoadForConfirm = addslashes($rowLoadLoc["name"]);

            echo "<tr><td>" . $nameLocLoad . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('locations-modal-edit$idOfLocLoad')\"><i class=\"fa fa-edit\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('locations-add-characters$idOfLocLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('locations-add-objects$idOfLocLoad')\"><i class=\"fa fa-plus\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button
                    w3-border transmission\" onclick=\"return confirm('Are you sure that you want to delete the location $nameLocLoadForConfirm')\" type=\"submit\"
                    name=\"deleteLocation\"><i class=\"fa fa-trash\"></i></button></td><input type=\"hidden\"  name=\"keyIdLoc\"
                    value=\"$idOfLocLoad\" /></form></tr>";
        }
        ?>
    </table><br>


    <label for="dialogs">Add dialogs between characters of the game</label>
    <button onclick="showElement('dialogs-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="dialogs" type="button" name="dialogs">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadDialogs">
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Add characters that listen</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryLoadAllDialogsV2 = "SELECT * FROM game_dialog WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllDialogsV2 = mysqli_query($conn, $queryLoadAllDialogsV2); // executing the query

        // Loading all entered dialogs
        while($rowLoadDialog = $resultLoadAllDialogsV2->fetch_assoc()){
            $idOfDialogLoad = $rowLoadDialog["ID"];
            $nameDialogLoad = $rowLoadDialog["name"];
            $nameDialogLoadForConfirm = addslashes($rowLoadDialog["name"]);

            echo "<tr><td>" . $nameDialogLoad . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('dialogs-modal-edit$idOfDialogLoad')\"><i class=\"fa fa-edit\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('dialogs-add-characters$idOfDialogLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button
                    w3-border transmission\" onclick=\"return confirm('Are you sure that you want to delete the dialog $nameDialogLoadForConfirm')\" type=\"submit\"
                    name=\"deleteDialog\"><i class=\"fa fa-trash\"></i></button></td><input type=\"hidden\" name=\"keyIdDialog\"
                    value=\"$idOfDialogLoad\" /></form></tr>";
        }
        ?>
    </table><br>

    <label for="scenes">Add scenes of the game</label>
    <button onclick="showElement('scenes-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="scenes" type="button" name="scenes">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadScenes">
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Add characters</th>
            <th>Add objects</th>
            <th>Add locations</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryLoadAllScenesV2 = "SELECT * FROM scene WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllScenesV2 = mysqli_query($conn, $queryLoadAllScenesV2); // executing the query

        // Loading all entered scenes
        while($rowLoadScene = $resultLoadAllScenesV2->fetch_assoc()){
            $idOfSceneLoad = $rowLoadScene["ID"];
            $nameSceneLoad = $rowLoadScene["name"];
            $nameSceneLoadForConfirm = addslashes($rowLoadScene["name"]);

            echo "<tr><td>" . $nameSceneLoad . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('scenes-modal-edit$idOfSceneLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('scenes-add-characters$idOfSceneLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('scenes-add-objects$idOfSceneLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('scenes-add-locations$idOfSceneLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the scene $nameSceneLoadForConfirm')\" type=\"submit\"
                                    name=\"deleteScene\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdScene\" value=\"$idOfSceneLoad\" /></form></tr>";
        }
        ?>
    </table><br>

    <label for="objectives">Add an objective of the game</label>
    <button onclick="showElement('objectives-modal')" class="w3-button w3-circle w3-border
    w3-border-blue w3-hover-blue w3-margin-left transmission" id="objectives" type="button" name="objectives">
        <i class="fa fa-plus"></i></button><br><br>

    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadObjectives">
        <tr>
            <th>Title</th>
            <th>Edit</th>
            <th>Add characters</th>
            <th>Add objects</th>
            <th>Add scenes</th>
            <th>Delete</th>
        </tr>

        <?php
        $queryLoadAllObjectivesV2 = "SELECT * FROM game_objective WHERE GAME_ELEMENTS_ID='$gameElementsId';";
        $resultLoadAllObjectivesV2 = mysqli_query($conn, $queryLoadAllObjectivesV2); // executing the query

        // Loading all entered objectives
        while($rowLoadObjective = $resultLoadAllObjectivesV2->fetch_assoc()){
            $idOfObjectiveLoad = $rowLoadObjective["ID"];
            $titleObjectiveLoad = $rowLoadObjective["title"];
            $titleObjectiveLoadForConfirm = addslashes($rowLoadObjective["title"]);

            echo "<tr><td>" . $titleObjectiveLoad . "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('objectives-modal-edit$idOfObjectiveLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('objectives-add-characters$idOfObjectiveLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('objectives-add-objects$idOfObjectiveLoad')\"><i class=\"fa fa-plus\"></i></button></td><td><button class=\"w3-button w3-border transmission\" type=\"button\" 
                    onclick=\"showElement('objectives-add-scenes$idOfObjectiveLoad')\"><i class=\"fa fa-plus\"></i></button></td><form method=\"post\" action=\"\"><td><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the objective $titleObjectiveLoadForConfirm')\" type=\"submit\"
                                    name=\"deleteObjective\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdObjective\" value=\"$idOfObjectiveLoad\" /></form></tr>";
        }
        ?>
    </table><br>

    <!--- Submit button for the form -->
    <form method="post" action="">
        <label for="story">Describe the story of the game</label>
        <textarea class="w3-input w3-border w3-margin-top" rows="2" type="text" id="story" name="story"><?php if(isset($gameStoryValue)) echo $gameStoryValue; ?></textarea><br>

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

        <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit" name="mainSubmit" value="Submit">
    </form>
</div>

<!--- A connection to assets of world building that says that the user can continue with editing the assets -->
<div class="w3-container continueAssets">
    <h3 style="">Continue with editing Assets of World Building</h3>
    <?php echo "<a href=\"AssetsWorld.php?id=$idOfDocument\" class=\"w3-bar-item w3-button w3-margin-top transmission w3-text-blue w3-border w3-xxlarge w3-round w3-hover-blue\">
        Assets <i class=\"fa fa-angle-double-right\"></i></a>"?>
</div>
</body>
</html>