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

// finding the id of mechanics table
if($resultInfoMechanics = $conn->query("SELECT ID from mechanics WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultInfoMechanics->num_rows === 1){
        $rowInfoMechanics = $resultInfoMechanics->fetch_assoc();

        if(isset($rowInfoMechanics['ID'])){
            $gameMechanicsId = $rowInfoMechanics['ID'];
            
        }else{
            header("Location:../write.php");
        }
    }else{
        header("Location:../write.php");
    }
}

$docRoot = $_SERVER["DOCUMENT_ROOT"]; // the path for the root of document

/*
 * Actions when user adds a cutscene
 */
if (isset($_POST["saveCutscene"])) {
    $cutsceneName = test_data($_POST["cutsceneName"]); // getting the name of the character
    
    $cutsceneDescription = test_data($_POST["cutsceneDescription"]); // getting the description of the character

    $uploadedImage = false;

    if ($_FILES["cutsceneFile"]["name"] !== "") {
        $filename = $_FILES["cutsceneFile"]["name"];
        $tempname = $_FILES["cutsceneFile"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Cutscenes/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $uploadedImage = true;
            $image_id = mysqli_insert_id($conn);

            // query to add a new cutscene in cutscenes table  with image
            $queryAddCutscene = "INSERT INTO cutscenes (MECH_ID, name, description ,file_id) 
                     VALUES ('$gameMechanicsId', '$cutsceneName', '$cutsceneDescription', '$image_id');";

            //executing the query
            if($conn->query($queryAddCutscene)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add cutscene')</script>"; // else we show an error message
            }
        }else{
            echo "<script>alert('Error: cannot upload image of cutscene')</script>"; // else we show an error message
        }
    }else{
        // query to add a new cutscene in cutscenes table without image
        $queryAddCutscene = "INSERT INTO cutscenes (MECH_ID, name, description) 
                     VALUES ('$gameMechanicsId' ,'$cutsceneName', '$cutsceneDescription');";

        //executing the query
        if($conn->query($queryAddCutscene)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add cutscene')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user adds a menu
 */
if (isset($_POST["saveMenu"])) {
    $menuName = test_data($_POST["menuName"]); // getting the name of the character
    
    $menuDescription = test_data($_POST["menuDescription"]); // getting the description of the character

    $uploadedFile = false;

    if ($_FILES["menuFile"]["name"] !== "") {
        $filename = $_FILES["menuFile"]["name"];
        $tempname = $_FILES["menuFile"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Menus/".$filename;

        if ( move_uploaded_file($tempname, $folder)) {
           
            // query to add a new menu in menus table  with menu
            $queryAddMenu = "INSERT INTO menus (MECH_ID, name, description, type, filename ) 
                     VALUES ('$gameMechanicsId', '$menuName', '$menuDescription','Menu', '$filename');";

            //executing the query
            if($conn->query($queryAddMenu)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add menu')</script>"; // else we show an error message
            }
        }else{
            echo "<script>alert('Error: cannot upload menu of menu')</script>"; // else we show an error message
        }
    }else{
        // query to add a new menu in menus table without menu
        $queryAddMenu = "INSERT INTO menus (MECH_ID, name, description, type, filename) 
                     VALUES ('$gameMechanicsId' ,'$menuName', '$menuDescription', 'Menu', NULL);";

        //executing the query
        if($conn->query($queryAddMenu)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add menu')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user adds a gui
 */
if (isset($_POST["saveGui"])) {
    $guiName = test_data($_POST["guiName"]); // getting the name of the character
    
    $guiDescription = test_data($_POST["guiDescription"]); // getting the description of the character

    $uploadedFile = false;

    if ($_FILES["guiFile"]["name"] !== "") {
        $filename = $_FILES["guiFile"]["name"];
        $tempname = $_FILES["guiFile"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Menus/".$filename;

        if ( move_uploaded_file($tempname, $folder)) {
           
            // query to add a new gui in menus table  with gui
            $queryAddGui = "INSERT INTO menus (MECH_ID, name, description, type, filename ) 
                     VALUES ('$gameMechanicsId', '$guiName', '$guiDescription','GUI', '$filename');";

            //executing the query
            if($conn->query($queryAddGui)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add GUI')</script>"; // else we show an error message
            }
        }else{
            echo "<script>alert('Error: cannot upload menu of GUI')</script>"; // else we show an error message
        }
    }else{
        // query to add a new gui in menus table without gui
        $queryAddGui = "INSERT INTO menus (MECH_ID, name, description, type, filename) 
                     VALUES ('$gameMechanicsId' ,'$guiName', '$guiDescription', 'GUI', NULL);";

        //executing the query
        if($conn->query($queryAddGui)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add GUI')</script>"; // else we show an error message
        }
    }
}


/*
 * Actions when user adds a event
 */
if (isset($_POST["saveEvent"])) {
    $nameOfEvent = test_data($_POST["eventName"]); // getting the name of the event
    $eventDescription = test_data($_POST["eventDescription"]); // getting the description of the event
    $eventType = test_data($_POST["eventType"]); // getting the description of the event
    $eventPrerequisite = test_data($_POST["eventPrerequisite"]); // getting the description of the event
    $eventResult = test_data($_POST["eventResult"]); // getting the description of the event
    // query to add a new event in game_character table without image
    $queryAddEvent = "INSERT INTO events (MECH_ID, name, description, type, prerequisite, result) VALUES ('$gameMechanicsId' ,'$nameOfEvent', '$eventDescription', '$eventType', '$eventPrerequisite', '$eventResult');";

    //executing the query
    if($conn->query($queryAddEvent)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add event')</script>"; // else we show an error message
    }
}


/*
 * Actions when user adds a level layout
 */
if (isset($_POST["saveLevel"])) {
    $levelName = test_data($_POST["levelName"]); // getting the name of the character
    
    $levelDescription = test_data($_POST["levelDescription"]); // getting the description of the character

    $levelPrecondition = test_data($_POST["levelPrecondition"]); // getting the description of the character
    
    $levelGoal = test_data($_POST["levelGoal"]); // getting the description of the character
    
    $levelUnlocks = test_data($_POST["levelUnlocks"]); // getting the description of the character  

    $uploadedImage = false;

    if ($_FILES["levelFile"]["name"] !== "") {
        $filename = $_FILES["levelFile"]["name"];
        $tempname = $_FILES["levelFile"]["tmp_name"];
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Levels/".$filename;

        if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
            $uploadedImage = true;
            $image_id = mysqli_insert_id($conn);

            // query to add a new level layout in levels table  with image
            $queryAddLevel = "INSERT INTO levels (MECH_ID, name, description ,image_id, precondition, goal, unlocks) 
                     VALUES ('$gameMechanicsId', '$levelName', '$levelDescription', '$image_id', '$levelPrecondition', '$levelGoal', '$levelUnlocks');";

            //executing the query
            if($conn->query($queryAddLevel)){
                //header("Refresh:0"); // if query is executed successfully we refresh the page
            }else{
                echo "<script>alert('Error: cannot add level layout')</script>"; // else we show an error message
            }
        }else{
            echo "<script>alert('Error: cannot upload image of level layout')</script>"; // else we show an error message
        }
    }else{
        // query to add a new level layout in levels table without image
        $queryAddLevel = "INSERT INTO levels (MECH_ID, name, description , precondition, goal, unlocks) 
                     VALUES ('$gameMechanicsId', '$levelName', '$levelDescription', '$levelPrecondition', '$levelGoal', '$levelUnlocks');";

        //executing the query
        if($conn->query($queryAddLevel)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add level layout')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user deletes a cutscene
 */
if(isset($_POST["deleteCutscene"])){
    $idOfCutsceneToDelete = $_POST["keyIdCutscene"];

    // Finding if cutscene has a submitted picture
    $queryFindIfCutsceneHasPicture = "SELECT file_id FROM cutscenes WHERE ID = '$idOfCutsceneToDelete';";
    $resultFindFile = $conn->query($queryFindIfCutsceneHasPicture);

    // If there is a picture we delete it
    if ($rowDelFile = $resultFindFile->fetch_assoc()) {
        $idOfFileToDel = $rowDelFile["file_id"];

        $queryFindFilenameOfFile = "SELECT filename FROM image WHERE ID='$idOfFileToDel'";
        $resultFindFilename = $conn->query($queryFindFilenameOfFile);

        if ($rowFindFilename = $resultFindFilename->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Cutscenes/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    }

    $queryDeleteCutscene = "DELETE FROM cutscenes WHERE ID='$idOfCutsceneToDelete';";
    if($conn->query($queryDeleteCutscene)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete cutscene')</script>";
    }
}

/*
 * Actions when user deletes a menu
 */
if(isset($_POST["deleteMenu"])){
    $idOfMenuToDelete = $_POST["keyIdMenu"];

    // Finding if menu has a submitted picture
    $queryFindIfMenuHasFile = "SELECT filename FROM menus WHERE ID = '$idOfMenuToDelete';";
    $resultFindFile = $conn->query($queryFindIfMenuHasFile);

    // If there is a picture we delete it
    

        if ($rowFindFilename = $resultFindFile->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Menus/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    

    $queryDeleteMenu = "DELETE FROM menus WHERE ID='$idOfMenuToDelete';";
    if($conn->query($queryDeleteMenu)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete menu')</script>";
    }
}

/*
 * Actions when user deletes a event
 */
if(isset($_POST["deleteEvent"])){
    $idOfEventToDelete = $_POST["keyIdEvent"];

    $queryDeleteEvent = "DELETE FROM events WHERE ID='$idOfEventToDelete';";
    if($conn->query($queryDeleteEvent)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete event')</script>";
    }
}

/*
 * Actions when user deletes a level
 */
if(isset($_POST["deleteLevel"])){
    $idOfLevelToDelete = $_POST["keyIdCutscene"];

    // Finding if level has a submitted picture
    $queryFindIfLevelHasPicture = "SELECT image_id FROM levels WHERE ID = '$idOfLevelToDelete';";
    $resultFindFile = $conn->query($queryFindIfLevelHasPicture);

    // If there is a picture we delete it
    if ($rowDelFile = $resultFindFile->fetch_assoc()) {
        $idOfFileToDel = $rowDelFile["image_id"];

        $queryFindFilenameOfFile = "SELECT filename FROM image WHERE ID='$idOfFileToDel'";
        $resultFindFilename = $conn->query($queryFindFilenameOfFile);

        if ($rowFindFilename = $resultFindFilename->fetch_assoc()) {
            $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Levels/".$rowFindFilename["filename"];
            unlink($filenameUrlDel); // deletes the picture
        }
    }

    $queryDeleteLevel = "DELETE FROM levels WHERE ID='$idOfLevelToDelete';";
    if($conn->query($queryDeleteLevel)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete level')</script>";
    }
}


/*
 * Actions when user updates information for an cutscene
 */
if(isset($_POST["editCutscene"])){
    $idOfCutscene = $_POST["keyIdCutscene"];
    $nameOfCutscene = test_data($_POST["cutsceneName"]); // getting the name of the cutscene
    $cutsceneDescription = test_data($_POST["cutsceneDescription"]); // getting the description of the cutscene

    // If user submits a picture for the cutscene
    if ($_FILES["imgCutsceneEdit"]["name"] !== "") {
        $filename = $_FILES["imgCutsceneEdit"]["name"]; // getting the filename of the file
        $tempname = $_FILES["imgCutsceneEdit"]["tmp_name"];
        // the url to add the new file
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Cutscenes/".$filename;

        // Finding if cutscene has a submitted picture
        $queryFindIfCharHasFile = "SELECT file_id FROM cutscenes WHERE ID = '$idOfCutscene';";
        $resultFindFile = $conn->query($queryFindIfCharHasFile);

        if ($rowFindFile = $resultFindFile->fetch_assoc()) {
            if (isset($rowFindFile["file_id"])) {
                $idOfFileToDel = $rowFindFile["file_id"]; // getting the id of the file row

                // getting the filename for the selected row from table file
                $resultFindFilenameOfFile = $conn->query("SELECT filename FROM image WHERE ID = '$idOfFileToDel';");
                if ($rowFilename = $resultFindFilenameOfFile->fetch_assoc()) {
                    // the url of the file that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Cutscenes/".$rowFilename["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new file to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of cutscene's file
                        if ($conn->query("UPDATE image SET filename='$filename' WHERE ID='$idOfFileToDel';")) {
                            // query to update other information of the cutscene
                            $queryUpdateCutscene = "UPDATE cutscenes SET name='$nameOfCutscene', description='$cutsceneDescription' WHERE ID='$idOfCutscene';";

                            //executing the query
                            if ($conn->query($queryUpdateCutscene)) {
                                header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update cutscene')</script>"; // else we show an error message
                            }
                        }
                    }
                }
            } else {
                // actions if user adds first time image
                if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
                    $uploadedImage = true;
                    $file_id = mysqli_insert_id($conn);

                    $queryUpdateCutsceneWithImage = "UPDATE cutscenes SET file_id='$file_id' WHERE ID='$idOfCutscene';";
                    if ($conn->query($queryUpdateCutsceneWithImage)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
                    }
                }
            }
        }
    } else {
        // query to update information about the character
        $queryUpdateCutscene = "UPDATE cutscenes SET name='$nameOfCutscene', description='$cutsceneDescription'
                             WHERE ID='$idOfCutscene';";
        if($conn->query($queryUpdateCutscene)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
        }
    }
}


/*
 * Actions when user updates information for an menu
 */
if(isset($_POST["editMenu"])){
    $idOfMenu = $_POST["keyIdMenu"];
    $nameOfMenu = test_data($_POST["menuName"]); // getting the name of the menu
    $menuDescription = test_data($_POST["menuDescription"]); // getting the description of the menu

    // If user submits a picture for the menu
    if ($_FILES["fileMenuEdit"]["name"] !== "") {
        $filename = $_FILES["fileMenuEdit"]["name"]; // getting the filename of the file
        $tempname = $_FILES["fileMenuEdit"]["tmp_name"];
        // the url to add the new file
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Menus/".$filename;

        // Finding if menu has a submitted picture
        $queryFindIfMenuHasFile = "SELECT filename FROM menus WHERE ID = '$idOfMenu';";
        $resultFindFile = $conn->query($queryFindIfMenuHasFile);

        if ($rowFindFile = $resultFindFile->fetch_assoc()) {
            if (isset($rowFindFile["filename"])) {
               

                // getting the filename for the selected row from table file
                
                
                    // the url of the file that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Menus/".$rowFindFile["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new file to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of menu's file
                        if ($conn->query("UPDATE menus SET filename='$filename' WHERE ID='$idOfMenu';")) {
                            // query to update other information of the menu
                            $queryUpdateMenu = "UPDATE menus SET name='$nameOfMenu', description='$menuDescription' WHERE ID='$idOfMenu';";

                            //executing the query
                            if ($conn->query($queryUpdateMenu)) {
                                header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update menu')</script>"; // else we show an error message
                            }
                        }
                    }
                
            } else {
                // actions if user adds first time image
                if ( move_uploaded_file($tempname, $folder)) {
                    $uploadedFile = true;
                    $file_id = mysqli_insert_id($conn);

                    $queryUpdateMenuWithFile = "UPDATE menus SET filename='$filename' WHERE ID='$idOfMenu';";
                    if ($conn->query($queryUpdateMenuWithFile)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot update menu')</script>"; // else we show an error message
                    }
                }
            }
        }
    } else {
        // query to update information about the menu
        $queryUpdateMenu = "UPDATE menus SET name='$nameOfMenu', description='$menuDescription'
                             WHERE ID='$idOfMenu';";
        if($conn->query($queryUpdateMenu)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update menu')</script>"; // else we show an error message
        }
    }
}

/*
 * Actions when user updates information for a event
 */
if(isset($_POST["editEvent"])){
    $idOfEvent = $_POST["keyIdEvent"];
    $nameOfEvent = test_data($_POST["eventName"]); // getting the name of the character
    $eventDescription = test_data($_POST["eventDescription"]); // getting the description of the character
    $eventType = test_data($_POST["eventType"]); // getting the description of the character
    $eventPrerequisite = test_data($_POST["eventPrerequisite"]); // getting the description of the character
    $eventResult = test_data($_POST["eventResult"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateEvent = "UPDATE events SET name='$nameOfEvent', description ='$eventDescription', type ='$eventType', prerequisite ='$eventPrerequisite', result ='$eventResult'
                             WHERE ID='$idOfEvent';";
        if($conn->query($queryUpdateEvent)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update event')</script>"; // else we show an error message
        }
    }



/*
 * Actions when user updates information for an level
 */
if(isset($_POST["editLevel"])){
    $idOfLevel = $_POST["keyIdLevel"];
    $nameOfLevel = test_data($_POST["levelName"]); // getting the name of the level
    $levelDescription = test_data($_POST["levelDescription"]); // getting the description of the level
    $levelPrecondition = test_data($_POST["levelPrecondition"]); // getting the description of the character
    $levelGoal = test_data($_POST["levelGoal"]); // getting the description of the character
    $levelUnlocks = test_data($_POST["levelUnlocks"]); // getting the description of the character 

    // If user submits a picture for the level
    if ($_FILES["imgLevelEdit"]["name"] !== "") {
        $filename = $_FILES["imgLevelEdit"]["name"]; // getting the filename of the file
        $tempname = $_FILES["imgLevelEdit"]["tmp_name"];
        // the url to add the new file
        $folder = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Levels/".$filename;

        // Finding if level has a submitted picture
        $queryFindIfLevelHasFile = "SELECT image_id FROM levels WHERE ID = '$idOfCutscene';";
        $resultFindFile = $conn->query($queryFindIfLevelHasFile);

        if ($rowFindFile = $resultFindFile->fetch_assoc()) {
            if (isset($rowFindFile["image_id"])) {
                $idOfFileToDel = $rowFindFile["image_id"]; // getting the id of the file row

                // getting the filename for the selected row from table file
                $resultFindFilenameOfFile = $conn->query("SELECT filename FROM image WHERE ID = '$idOfFileToDel';");
                if ($rowFilename = $resultFindFilenameOfFile->fetch_assoc()) {
                    // the url of the file that we want to delete
                    $filenameUrlDel = "$docRoot/ImagesFromUsers-GDD/$nameOfDoc/Mechanics/Levels/".$rowFilename["filename"];
                    unlink($filenameUrlDel); // deletes the picture

                    // moving the new file to the correct path
                    if (move_uploaded_file($tempname, $folder)) {
                        // query to update the filename of level's file
                        if ($conn->query("UPDATE image SET filename='$filename' WHERE ID='$idOfFileToDel';")) {
                            // query to update other information of the level
                            $queryUpdateLevel = "UPDATE levels SET name='$nameOfLevel', description='$levelDescription', precondition='$levelPrecondition', goal='$levelGoal', unlocks='$levelUnlocks'  WHERE ID='$idOfLevel';";

                            //executing the query
                            if ($conn->query($queryUpdateLevel)) {
                                header("Refresh:0"); // if query is executed successfully we refresh the page
                            } else {
                                echo "<script>alert('Error: cannot update level')</script>"; // else we show an error message
                            }
                        }
                    }
                }
            } else {
                // actions if user adds first time image
                if (mysqli_query($conn, "INSERT INTO image (filename) VALUES ('$filename');") && move_uploaded_file($tempname, $folder)) {
                    $uploadedImage = true;
                    $image_id = mysqli_insert_id($conn);

                    $queryUpdateLevelWithImage = "UPDATE levels SET image_id='$image_id' WHERE ID='$idOfLevel';";
                    if ($conn->query($queryUpdateLevelWithImage)) {
                        header("Refresh:0"); // if query is executed successfully we refresh the page
                    } else {
                        echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
                    }
                }
            }
        }
    } else {
        // query to update information about the character
        $queryUpdateLevel = "UPDATE levels SET name='$nameOfLevel', description='$levelDescription', precondition='$levelPrecondition', goal='$levelGoal', unlocks='$levelUnlocks'
                             WHERE ID='$idOfLevel';";
        if($conn->query($queryUpdateLevel)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update character')</script>"; // else we show an error message
        }
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
<html>
<head>
	<title>Explain Gameplay Elements</title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../Images/favicon-new.ico">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/main.css">
	<script src="../JavaScript/WorldBuilding.js"></script>
    <script src="../JavaScript/Main.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
</head>
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
	    <a href="gameplay.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Gameplay Mechanics</a>
	</div>

	<div class="w3-container w3-blue panelInFormWorld">
	    <h3 class="headerPanel">Explain Gameplay Elements</h3>
	</div>


	

    	<!--- Modal for cutscenes -->
<div id="cutscenes-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
        
                <span onclick="hideElement('cutscenes-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a cutscene</h3><br>

            

                <label for="cutsceneFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Choose a file for the cutscene</label><br>
            	<input type="file" id="cutsceneFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top" name="cutsceneFile" accept="audio/*,video/*,image/*"><br><br>
                
                <label for="cutsceneName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the cutscene *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="cutsceneName<?php echo $gameMechanicsId; ?>" name="cutsceneName" required><br>

                <label for="cutsceneDescription<?php echo $gameMechanicsId; ?>">Describe the cutscene</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="cutsceneDescription<?php echo $gameMechanicsId; ?>"name="cutsceneDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveCutscene" type="submit" name="saveCutscene">Save</button>
                </div>
            </form>
        </div>
    </div>


    <!--- Modal for levels -->
<div id="levels-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
        
                <span onclick="hideElement('levels-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a level</h3><br>

            

                <label for="levelFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Choose an image for the level layout</label><br>
                <input type="file" id="levelFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top" name="levelFile" accept="image/*"><br><br>
                
                <label for="levelName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the level *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="levelName<?php echo $gameMechanicsId; ?>" name="levelName" required><br>

                <label for="levelDescription<?php echo $gameMechanicsId; ?>">Describe the level</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="levelDescription<?php echo $gameMechanicsId; ?>"name="levelDescription"></textarea><br>

                <label for="levelPrecondition<?php echo $gameMechanicsId; ?>">Describe the preconditions to unlock this level</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="levelPrecondition<?php echo $gameMechanicsId; ?>"name="levelPrecondition"></textarea><br>

                <label for="levelGoal<?php echo $gameMechanicsId; ?>">Describe the level goals</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="levelGoal<?php echo $gameMechanicsId; ?>"name="levelGoal"></textarea><br>

                <label for="levelUnlocks<?php echo $gameMechanicsId; ?>">Describe what completing the level unlocks</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="levelUnlocks<?php echo $gameMechanicsId; ?>"name="levelUnlocks"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveLevel" type="submit" name="saveLevel">Save</button>
                </div>
            </form>
        </div>
    </div>


    <!--- Modal for events -->
<div id="events-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
        
                <span onclick="hideElement('events-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add an event</h3><br>

                
                <label for="eventName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the event *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="eventName<?php echo $gameMechanicsId; ?>" name="eventName" required><br>

                <label for="eventDescription<?php echo $gameMechanicsId; ?>">Describe the event</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="eventDescription<?php echo $gameMechanicsId; ?>"name="eventDescription"></textarea><br>

                <label for="eventType<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the type of the event (eg. Quest, Random Event, Limited Time etc)</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="eventType<?php echo $gameMechanicsId; ?>" name="eventType" required><br>

                <label for="eventPrerequisite<?php echo $gameMechanicsId; ?>">Describe the event prerequisites to unlock it</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="eventPrerequisite<?php echo $gameMechanicsId; ?>"name="eventPrerequisite"></textarea><br>

                <label for="eventResult<?php echo $gameMechanicsId; ?>">Describe the event results/rewards</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="eventResult<?php echo $gameMechanicsId; ?>"name="eventResult"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveEvent" type="submit" name="saveEvent">Save</button>
                </div>
            </form>
        </div>
    </div>


<!--- Modal for menus -->
<div id="menus-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
        
                <span onclick="hideElement('menus-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a menu</h3><br>

            

                <label for="menuFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Choose a file for the menu<br>(you can use the story flowchart of the design tool to design menus/GUIs)</label><br>
                <input type="file" id="menuFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top" name="menuFile" ><br><br>
                
                <label for="menuName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the menu *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="menuName<?php echo $gameMechanicsId; ?>" name="menuName" required><br>

                <label for="menuDescription<?php echo $gameMechanicsId; ?>">Describe the menu</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="menuDescription<?php echo $gameMechanicsId; ?>"name="menuDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMenu" type="submit" name="saveMenu">Save</button>
                </div>
            </form>
        </div>
    </div>


<!--- Modal for gui -->
<div id="gui-modal" class="w3-modal">
    <div class="w3-modal-content w3-animate-zoom">
        <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
        
                <span onclick="hideElement('gui-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a gui</h3><br>

            

                <label for="guiFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Choose a file for the gui<br>(you can use the story flowchart of the design tool to design menus/GUIs)</label><br>
                <input type="file" id="guiFile<?php echo $gameMechanicsId; ?>" class="w3-margin-top" name="guiFile" ><br><br>
                
                <label for="guiName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the gui *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="guiName<?php echo $gameMechanicsId; ?>" name="guiName" required><br>

                <label for="guiDescription<?php echo $gameMechanicsId; ?>">Describe the gui</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="guiDescription<?php echo $gameMechanicsId; ?>"name="guiDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveGui" type="submit" name="saveGui">Save</button>
                </div>
            </form>
        </div>
    </div>


<?php
    // query to load all characters
    $queryLoadAllCutscenes = "SELECT * FROM cutscenes WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllCutscenes = mysqli_query($conn, $queryLoadAllCutscenes); // executing the query

    while($rowLoadCutscene = $resultLoadAllCutscenes->fetch_assoc()){
        $idOfCutscene = $rowLoadCutscene["ID"];
        $nameOfCutscene = $rowLoadCutscene["name"];
        
        $cutsceneDescribe = $rowLoadCutscene["description"];
        $idOfFile = $rowLoadCutscene["file_id"];
        $fileFilenameCutscene=NULL;

        if(isset($idOfFile)){
            $resultFile = $conn->query("SELECT filename FROM image WHERE ID='$idOfFile';");

            if($rowFile = $resultFile->fetch_assoc()){
                $fileFilenameCutscene = $rowFile["filename"];
            }
        }

        echo "<div id=\"cutscenes-modal-edit$idOfCutscene\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('cutscenes-modal-edit$idOfCutscene')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit cutscene <b>$nameOfCutscene</b></h3><br>";

            if(isset($fileFilenameCutscene)){
                echo "<p><b>File in use</b>:</p><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Cutscenes/".$fileFilenameCutscene."' download>Download</a><br>";
            }

            echo "<label for=\"imgCutsceneEdit$idOfCutscene\" class=\"w3-margin-top\" id=\"labelFileCutscene\">Choose a file for the cutscene</label><br>
            <input type=\"file\" id=\"imgCutsceneEdit$idOfCutscene\" class=\"w3-margin-top\" name=\"imgCutsceneEdit\" accept=\"audio/*,video/*,image/*\"><br><br>
            
            <input type=\"hidden\"  name=\"keyIdCutscene\" value=\"$idOfCutscene\" />

            <label for=\"cutsceneNameEdit$idOfCutscene\" class=\"w3-margin-top\">Write the name of the cutscene *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"cutsceneNameEdit$idOfCutscene\" value=\"$nameOfCutscene\" name=\"cutsceneName\" required><br>

            <label for=\"cutsceneDescriptionEdit$idOfCutscene\">Describe the cutscene</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"cutsceneDescriptionEdit$idOfCutscene\"
                      name=\"cutsceneDescription\">$cutsceneDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editCutscene\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }


    // query to load all characters
    $queryLoadAllLevels = "SELECT * FROM levels WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllLevels = mysqli_query($conn, $queryLoadAllLevels); // executing the query

    while($rowLoadLevel = $resultLoadAllLevels->fetch_assoc()){
        $idOfLevel = $rowLoadLevel["ID"];
        $nameOfLevel = $rowLoadLevel["name"];
        $levelDescribe = $rowLoadLevel["description"];
        $levelPrecondition = $rowLoadLevel["precondition"];
        $levelGoal = $rowLoadLevel["goal"];
        $levelUnlocks = $rowLoadLevel["unlocks"];
        $idOfFile = $rowLoadLevel["image_id"];
        $fileFilenameLevel=NULL;

        if(isset($idOfFile)){
            $resultFile = $conn->query("SELECT filename FROM image WHERE ID='$idOfFile';");

            if($rowFile = $resultFile->fetch_assoc()){
                $fileFilenameLevel = $rowFile["filename"];
            }
        }

        echo "<div id=\"levels-modal-edit$idOfLevel\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('levels-modal-edit$idOfLevel')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit level <b>$nameOfLevel</b></h3><br>";

            if(isset($fileFilenameLevel)){
                echo "<p><b>File in use</b>:</p><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Levels/".$fileFilenameLevel."' download>Download</a><br>";
            }

            echo "<label for=\"imgLevelEdit$idOfLevel\" class=\"w3-margin-top\" id=\"labelFileLevel\">Choose an image for the level</label><br>
            <input type=\"file\" id=\"imgLevelEdit$idOfLevel\" class=\"w3-margin-top\" name=\"imgLevelEdit\" accept=\"image/*\"><br><br>
            
            <input type=\"hidden\"  name=\"keyIdLevel\" value=\"$idOfLevel\" />

            <label for=\"levelNameEdit$idOfLevel\" class=\"w3-margin-top\">Write the name of the level *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"levelNameEdit$idOfLevel\" value=\"$nameOfLevel\" name=\"levelName\" required><br>

            <label for=\"levelDescriptionEdit$idOfLevel\">Describe the level</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"levelDescriptionEdit$idOfLevel\"
                      name=\"levelDescription\">$levelDescribe</textarea><br>

            <label for=\"levelPreconditionEdit$idOfLevel\">Describe the preconditions to unlock this level</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"levelPreconditionEdit$idOfLevel\"
                      name=\"levelPrecondition\">$levelPrecondition</textarea><br>


            <label for=\"levelGoalEdit$idOfLevel\">Describe the level goals</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"levelGoalEdit$idOfLevel\"
                      name=\"levelGoal\">$levelGoal</textarea><br>

            <label for=\"levelUnlocksEdit$idOfLevel\">Describe what completing the level unlocks</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"levelUnlocksEdit$idOfLevel\"
                      name=\"levelUnlocks\">$levelUnlocks</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editLevel\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    // query to load all events
    $queryLoadAllEvents = "SELECT * FROM events WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllEvents = mysqli_query($conn, $queryLoadAllEvents); // executing the query

    while($rowLoadEvent = $resultLoadAllEvents->fetch_assoc()){
        $idOfEvent = $rowLoadEvent["ID"];
        $eventName = $rowLoadEvent["name"];
        $eventDescribe = $rowLoadEvent["description"];
        $eventType = $rowLoadEvent["type"];
        $eventPrerequisite = $rowLoadEvent["prerequisite"];
        $eventResult = $rowLoadEvent["result"];
        

        echo "<div id=\"events-modal-edit$idOfEvent\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('events-modal-edit$idOfEvent')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit event <b>$eventName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdEvent\" value=\"$idOfEvent\" />

            <label for=\"eventNameEdit$idOfEvent\" class=\"w3-margin-top\">Write the name of the event*</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"eventNameEdit$idOfEvent\" value=\"$eventName\" name=\"eventName\" required><br>

        

            <label for=\"eventDescriptionEdit$idOfEvent\">Describe the event description</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"eventDescriptionEdit$idOfEvent\"
                      name=\"eventDescription\">$eventDescribe</textarea><br>


            <label for=\"eventTypeEdit$idOfEvent\" class=\"w3-margin-top\">Write the type of the event*</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"eventTypeEdit$idOfEvent\" value=\"$eventType\" name=\"eventType\" required><br>

            <label for=\"eventPrerequisiteEdit$idOfEvent\">Describe the event prerequisites</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"eventPrerequisiteEdit$idOfEvent\"
                      name=\"eventPrerequisite\">$eventPrerequisite</textarea><br>

            <label for=\"eventResultEdit$idOfEvent\">Describe the event results/rewards</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"eventResultEdit$idOfEvent\"
                      name=\"eventResult\">$eventResult</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editEvent\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }



    // query to load all characters
    $queryLoadAllMenus = "SELECT * FROM menus WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllMenus = mysqli_query($conn, $queryLoadAllMenus); // executing the query

    while($rowLoadMenu = $resultLoadAllMenus->fetch_assoc()){
        $idOfMenu = $rowLoadMenu["ID"];
        $nameOfMenu = $rowLoadMenu["name"];
        
        $menuDescribe = $rowLoadMenu["description"];
        $fileFilenameMenu = $rowLoadMenu["filename"];
        


        echo "<div id=\"menus-modal-edit$idOfMenu\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('menus-modal-edit$idOfMenu')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit menu <b>$nameOfMenu</b></h3><br>";

            if(isset($fileFilenameMenu)){
                echo "<p><b>File in use</b>:</p><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Menus/".$fileFilenameMenu."' download>Download</a><br>";
            }

            echo "<label for=\"fileMenuEdit$idOfMenu\" class=\"w3-margin-top\" id=\"labelFileMenu\">Choose a file for the menu<br>(you can use the story flowchart of the design tool to design menus/GUIs)</label><br>
            <input type=\"file\" id=\"fileMenuEdit$idOfMenu\" class=\"w3-margin-top\" name=\"fileMenuEdit\" ><br><br>
            
            <input type=\"hidden\"  name=\"keyIdMenu\" value=\"$idOfMenu\" />

            <label for=\"menuNameEdit$idOfMenu\" class=\"w3-margin-top\">Write the name of the menu *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"menuNameEdit$idOfMenu\" value=\"$nameOfMenu\" name=\"menuName\" required><br>

            <label for=\"menuDescriptionEdit$idOfMenu\">Describe the menu</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"menuDescriptionEdit$idOfMenu\"
                      name=\"menuDescription\">$menuDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editMenu\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>

	<form action="" method="post" enctype="multipart/form-data" class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding" >
	   	

		<label for="cutscenes">Add Intro,Cutscenes etc.:</label>
    	<button onclick="showElement('cutscenes-modal')" class="w3-button w3-circle w3-border
    	w3-border-blue w3-hover-blue w3-margin-left transmission" id="cutscenes" type="button" name="cutscenes">
        <i class="fa fa-plus"></i></button><br><br>

        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadCutscenes">
        <tr>
            <th>Cutscene Name</th>
            <th>Description</th>
            <th>File</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllRulesV2= "SELECT * FROM cutscenes WHERE MECH_ID=$gameMechanicsId ORDER BY ID ASC;";

            $resultLoadAllRulesV2= mysqli_query($conn,$queryLoadAllRulesV2);

            while ($rowLoadCutscene = $resultLoadAllRulesV2->fetch_assoc()) {
                $idOfCutsceneLoad = $rowLoadCutscene["ID"];
                $nameOfCutsceneLoad = $rowLoadCutscene["name"];
                $cutsceneDescriptionLoad = $rowLoadCutscene["description"];
                $idOfImage = $rowLoadCutscene["file_id"];
                $imgFilenameCut = NULL;
                echo "<tr><td>" . $nameOfCutsceneLoad . "</td><td>" . $cutsceneDescriptionLoad ."</td>";
                if(isset($idOfImage)){
                    $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

                    if($rowImage = $resultImage->fetch_assoc()){
                        $imgFilenameCut = $rowImage["filename"];
                    }
                }

                if(isset($imgFilenameCut)){
                    echo "<td><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Cutscenes/".$imgFilenameCut."' download>Download</a></td>";
                }else{
                    echo "<td><p>No available file for this cutscene</p></td>";
                }
                echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('cutscenes-modal-edit$idOfCutsceneLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the cutscene $nameOfCutsceneLoad')\" type=\"submit\"
                                    name=\"deleteCutscene\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdCutscene\" value=\"$idOfCutsceneLoad\" /></form></tr>";

            }
        ?>
        </table><br>


        <label for="levels">Add Game Levels:</label>
        <button onclick="showElement('levels-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="levels" type="button" name="levels">
        <i class="fa fa-plus"></i></button><br><br>

        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadLevels">
        <tr>
            <th>Level Name</th>
            <th>Description</th>
            <th>Precondition</th>
            <th>Goal</th>
            <th>Unlocks</th>
            <th>File</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllLevelsV2= "SELECT * FROM levels WHERE MECH_ID=$gameMechanicsId ORDER BY ID ASC;";

            $resultLoadAllLevelsV2= mysqli_query($conn,$queryLoadAllLevelsV2);

            while ($rowLoadLevel = $resultLoadAllLevelsV2->fetch_assoc()) {
                $idOfLevelLoad = $rowLoadLevel["ID"];
                $nameOfLevelLoad = $rowLoadLevel["name"];
                $levelDescriptionLoad = $rowLoadLevel["description"];
                $levelPreconditionLoad = $rowLoadLevel["precondition"];
                $levelGoalLoad = $rowLoadLevel["goal"];
                $levelUnlocksLoad = $rowLoadLevel["unlocks"];
                $idOfImage = $rowLoadLevel["image_id"];
                $imgFilenameLvl = NULL;
                echo "<tr><td>" . $nameOfLevelLoad . "</td><td>" . $levelDescriptionLoad ."</td><td>". $levelPreconditionLoad ."</td><td>". $levelGoalLoad ."</td><td>". $levelUnlocksLoad."</td>" ;
                if(isset($idOfImage)){
                    $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

                    if($rowImage = $resultImage->fetch_assoc()){
                        $imgFilenameLvl = $rowImage["filename"];
                    }
                }

                if(isset($imgFilenameLvl)){
                    echo "<td><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Levels/".$imgFilenameLvl."' download>Download</a></td>";
                }else{
                    echo "<td><p>No available file for this level</p></td>";
                }
                echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('levels-modal-edit$idOfLevelLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the level $nameOfLevelLoad')\" type=\"submit\"
                                    name=\"deleteLevel\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdLevel\" value=\"$idOfLevelLoad\" /></form></tr>";

            }
        ?>
        </table><br>


        <label for="events">Add Game Events, Quests etc.:</label>
        <button onclick="showElement('events-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="events" type="button" name="events">
        <i class="fa fa-plus"></i></button><br><br>

        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadCutscenes">
        <tr>
            <th>Event Name</th>
            <th>Description</th>
            <th>Type</th>
            <th>Prerequisite</th>
            <th>Result/Reward</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllEventsV2= "SELECT * FROM events WHERE MECH_ID=$gameMechanicsId ORDER BY ID ASC;";

            $resultLoadAllEventsV2= mysqli_query($conn,$queryLoadAllEventsV2);

            while ($rowLoadEvent = $resultLoadAllEventsV2->fetch_assoc()) {
                $idOfEventLoad = $rowLoadEvent["ID"];
                $nameOfEventLoad = $rowLoadEvent["name"];
                $eventDescriptionLoad = $rowLoadEvent["description"];
                $eventTypeLoad = $rowLoadEvent["type"];
                $eventPrerequisiteLoad = $rowLoadEvent["prerequisite"];
                $eventResultLoad = $rowLoadEvent["result"];
                
                echo "<tr><td>" . $nameOfEventLoad . "</td><td>" . $eventDescriptionLoad . "</td><td>" . $eventTypeLoad . "</td><td>" . $eventPrerequisiteLoad . "</td><td>" . $eventResultLoad . "</td>" ;
                
                echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('events-modal-edit$idOfEventLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the cutscene $nameOfEventLoad')\" type=\"submit\"
                                    name=\"deleteEvent\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdEvent\" value=\"$idOfEventLoad\" /></form></tr>";

            }
        ?>
        </table><br>

	    
			<!--<p class="rule Rcontainer">Intro: <a href="../Images/ratfren.jpg" download>Download</a></p>
	    	<p class="rule Rcontainer">Outro: <a href="../Images/ratfren.jpg" download>Download</a></p>-->

    	<label for="menus">Add Game Menu:</label>
    	<button onclick="showElement('menus-modal')" class="w3-button w3-circle w3-border
    	w3-border-blue w3-hover-blue w3-margin-left transmission" id="menus" type="button" name="menus">
        <i class="fa fa-plus"></i></button><br><br>


        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadMenus">
        <tr>
            <th>Menu Name</th>
            <th>Description</th>
            <th>File</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllMenusV2= "SELECT * FROM menus WHERE MECH_ID=$gameMechanicsId AND type='Menu' ORDER BY ID ASC;";

            $resultLoadAllMenusV2= mysqli_query($conn,$queryLoadAllMenusV2);

            while ($rowLoadMenu = $resultLoadAllMenusV2->fetch_assoc()) {
                $idOfMenuLoad = $rowLoadMenu["ID"];
                $nameOfMenuLoad = $rowLoadMenu["name"];
                $menuDescriptionLoad = $rowLoadMenu["description"];
                $imgFilenameMenu = $rowLoadMenu["filename"];
                
                echo "<tr><td>" . $nameOfMenuLoad . "</td><td>" . $menuDescriptionLoad ."</td>";
                

                if(isset($imgFilenameMenu)){
                    echo "<td><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Menus/".$imgFilenameMenu."' download>Download</a></td>";
                }else{
                    echo "<td><p>No available file for this menu</p></td>";
                }
                echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('menus-modal-edit$idOfMenuLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the menu $nameOfMenuLoad')\" type=\"submit\"
                                    name=\"deleteMenu\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdMenu\" value=\"$idOfMenuLoad\" /></form></tr>";

            }
        ?>
        </table><br>

        <label for="gui">Add GUI Menu:</label>
    	<button onclick="showElement('gui-modal')" class="w3-button w3-circle w3-border
    	w3-border-blue w3-hover-blue w3-margin-left transmission" id="gui" type="button" name="gui">
        <i class="fa fa-plus"></i></button><br><br>


        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadMenus">
        <tr>
            <th>GUI Name</th>
            <th>Description</th>
            <th>File</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllGuisV2= "SELECT * FROM menus WHERE MECH_ID=$gameMechanicsId AND type='GUI' ORDER BY ID ASC;";

            $resultLoadAllGuisV2= mysqli_query($conn,$queryLoadAllGuisV2);

            while ($rowLoadGui = $resultLoadAllGuisV2->fetch_assoc()) {
                $idOfGuiLoad = $rowLoadGui["ID"];
                $nameOfGuiLoad = $rowLoadGui["name"];
                $guiDescriptionLoad = $rowLoadGui["description"];
                $imgFilenameGui = $rowLoadGui["filename"];
                
                echo "<tr><td>" . $nameOfGuiLoad . "</td><td>" . $guiDescriptionLoad ."</td>";
                

                if(isset($imgFilenameGui)){
                    echo "<td><a href='/ImagesFromUsers-GDD/".$nameOfDoc."/Mechanics/Menus/".$imgFilenameGui."' download>Download</a></td>";
                }else{
                    echo "<td><p>No available file for this menu</p></td>";
                }
                echo "<td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('menus-modal-edit$idOfGuiLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td><td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the menu $nameOfGuiLoad')\" type=\"submit\"
                                    name=\"deleteMenu\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdMenu\" value=\"$idOfGuiLoad\" /></form></tr>";

            }
        ?>
        </table><br>

	    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue" type="submit" value="Submit">
	</form>


<script type="text/javascript">
		var coll = document.getElementsByClassName("collapsible");
		var i;

		for (i = 0; i < coll.length; i++) {
		  coll[i].addEventListener("click", function() {
		    this.classList.toggle("active");
		    var content = this.nextElementSibling;
		    if (content.style.display === "block") {
		      content.style.display = "none";
		    } else {
		      content.style.display = "block";
		    }
		  });
		}
	</script>
</body>
</html>
