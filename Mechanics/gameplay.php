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
    
    $sceneDescription = test_data($_POST["sceneDescription"]); // getting the description of the character

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
                     VALUES ('$gameMechanicsId', '$cutsceneName', '$sceneDescription', '$image_id');";

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
                     VALUES ('$gameMechanicsId' ,'$cutsceneName', '$sceneDescription');";

        //executing the query
        if($conn->query($queryAddCutscene)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot add cutscene')</script>"; // else we show an error message
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
<div id="cutscenes-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('cutscenes-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a cutscene</h3><br>

            <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">

            	<label for="cutsceneFile" class="w3-margin-top">Choose cutscene file</label><br>
            	<input type="file" id="cutsceneFile" class="w3-margin-top" name="cutsceneFile" accept="audio/*,video/*,image/*"><br><br>
                
                <label for="cutsceneName" class="w3-margin-top">Write the name of the cutscenes *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="cutsceneName" name="cutsceneName" required><br>

                <label for="cutsceneDescription">Describe the cutscenes</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="cutsceneDescription"
                          name="cutsceneDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveCutscene" type="submit" name="saveCutscene">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--- Modal for menus -->
<div id="menu-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('menu-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a menu</h3><br>

            <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">

            	<label for="menuFile" class="w3-margin-top">Choose menu file</label><br>
            	<input type="file" id="menuFile" class="w3-margin-top" name="menuFile" accept="image/*"><br><br>
                
                <label for="menuName" class="w3-margin-top">Write the name of the menu *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="menuName" name="menuName" required><br>

                <label for="menuDescription">Describe the menu</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="menuDescription"
                          name="menuDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveMenu" type="submit" name="saveMenu">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--- Modal for gui -->
<div id="gui-modal" class="w3-modal w3-padding-16">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container">
                <span onclick="hideElement('gui-modal')" class="w3-button w3-display-topright w3-hover-red">
                    <i class="fa fa-close"></i></span>
            <h3 class="headerForModal">Add a gui</h3><br>

            <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">

            	<label for="guiFile" class="w3-margin-top">Choose gui file</label><br>
            	<input type="file" id="guiFile" class="w3-margin-top" name="guiFile" accept="audio/*,video/*,image/*"><br><br>
                
                <label for="guiName" class="w3-margin-top">Write the name of the gui *</label>
                <input class="w3-input w3-border w3-margin-top" type="text" id="guiName" name="guiName" required><br>

                <label for="guiDescription">Describe the gui</label>
                <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="guiDescription"
                          name="guiDescription"></textarea><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveGui" type="submit" name="saveGui">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

                if(isset($idOfImage)){
                    $resultImage = $conn->query("SELECT filename FROM image WHERE ID='$idOfImage';");

                    if($rowImage = $resultImage->fetch_assoc()){
                        $imgFilenameCut = $rowImage["filename"];
                    }
                }

                echo "<tr><td>" . $nameOfCutsceneLoad . "</td><td>" . $cutsceneDescriptionLoad .
                "</td><td><a href='/ImagesFromUsers-GDD/. $nameOfDoc ./WorldBuilding/Locations/. $imgFilenameCut .' download>Download</a></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the rule $nameOfCutsceneLoad')\" type=\"submit\"
                                    name=\"deleteCutscene\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdCutscene\" value=\"$idOfCutsceneLoad\" /></form></tr>";

            }
        ?>
        </table><br>

	    
			<p class="rule Rcontainer">Intro: <a href="../Images/ratfren.jpg" download>Download</a></p>
	    	<p class="rule Rcontainer">Outro: <a href="../Images/ratfren.jpg" download>Download</a></p>

    	<label for="menu">Add Game Menu:</label>
    	<button onclick="showElement('menu-modal')" class="w3-button w3-circle w3-border
    	w3-border-blue w3-hover-blue w3-margin-left transmission" id="menu" type="button" name="menu">
        <i class="fa fa-plus"></i></button><br><br>

        <label for="gui">Add GUI Menu:</label>
    	<button onclick="showElement('gui-modal')" class="w3-button w3-circle w3-border
    	w3-border-blue w3-hover-blue w3-margin-left transmission" id="gui" type="button" name="gui">
        <i class="fa fa-plus"></i></button><br><br>

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
