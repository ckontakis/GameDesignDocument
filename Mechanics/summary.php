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
 * Getting the id of Assets to connect elements (e.g music kind, track) with the assets of the document.
 * If there is a problem with the execution of queries we redirect user to write page.
 */

// finding the id of game_summary table
if($resultSummary = $conn->query("SELECT ID from game_summary WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultSummary->num_rows === 1){
        $rowSummary = $resultSummary->fetch_assoc();

        if(isset($rowSummary['ID'])){
            $gameSummaryId = $rowSummary['ID'];
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
$queryLoadSummary = "SELECT name, concept, genre, audience, system, type, setting, software, game_code from game_summary WHERE ID='$gameSummaryId';";
$resLoadSummary = $conn->query($queryLoadSummary);

if($resLoadSummary->num_rows === 1){
    $rowLoadSummary = $resLoadSummary->fetch_assoc();
    $gameSummaryName= $rowLoadSummary['name'];
    $gameSummaryConcept= $rowLoadSummary['concept'];
    $gameSummaryGenre= $rowLoadSummary['genre'];
    $gameSummaryAudience= $rowLoadSummary['audience'];
    $gameSummarySystem= $rowLoadSummary['system'];
    $gameSummaryType= $rowLoadSummary['type'];
    $gameSummarySetting= $rowLoadSummary['setting'];
    $gameSummarySoftware= $rowLoadSummary['software'];
    $gameSummaryCode= $rowLoadSummary['game_code'];
}


/*
 * Actions when user submits the summary of the game
 */
$successUpdateSummary = false; // variable to show success message
$somethingWrongSummary = false; // variable to show failure message
if(isset($_POST["summarySubmit"])){
    if(isset($gameSummaryId)){
        $describeName = $_POST["nameGame"]; // getting the text with post method
        $describeConcept = $_POST["concept"]; 

        if(isset($_POST['checkGenre'])){
            $describeGenre = (array)$_POST["checkGenre"];
            $chkGen=implode(',',$describeGenre);
        }
        else{
            $chkGen=NULL;
        }
        

        if(isset($_POST['checkSystem'])){
            $describeSystem = (array)$_POST["checkSystem"];
            $chkSys=implode(',',$describeSystem);
        }
        else{
            $chkSys=NULL;
        }
        

        if(isset($_POST['checkType'])){
            $describeType = (array)$_POST["checkType"];
            $chkT=implode(',',$describeType);
        }
        else{
            $chkT=NULL;
        }
        
        
        $describeAudience = $_POST["targetAudience"];
        $describeSetting = $_POST["settingGame"]; 
        $describeSoftware = $_POST["programmingLang"]; 
        
        if (isset($_POST["myCode"])) {
            $describeCode=$_POST["myCode"];
        }
        else{
            if (isset($gameSummaryCode)){
                $describeCode=$gameSummaryCode;
            }else{
                $describeCode=NULL;
            }
        }
       
        // query to update the database array
        $updateSummaryQuery = "UPDATE game_summary SET name='$describeName', concept='$describeConcept', genre='$chkGen', audience='$describeAudience', system='$chkSys', type='$chkT', setting='$describeSetting', software='$describeSoftware', game_code='$describeCode' WHERE ID='$gameSummaryId';";

        if($conn->query($updateSummaryQuery)){ // if query executed successfully
            $successUpdateSummary = true;
            
            $gameSummaryName= $describeName;
            $gameSummaryConcept= $describeConcept;
            $gameSummaryGenre= $chkGen;
            $gameSummaryAudience= $describeAudience;
            $gameSummarySystem= $chkSys;
            $gameSummaryType= $chkT;
            $gameSummarySetting= $describeSetting;
            $gameSummarySoftware= $describeSoftware;
            $gameSummaryCode= $describeCode;
        }else{
            $somethingWrongSummary = true;
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
	<title>Create Game Summary</title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../Images/favicon-new.ico">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/main.css">
	<script src="../JavaScript/WorldBuilding.js"></script>
    <script src="../JavaScript/Main.js"></script>

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
    <a href="summary.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Game Summary</a>
</div>
	

	<div class="w3-container w3-blue panelInFormWorld">
	    <h3 class="headerPanel">Create summary of the game</h3>
	</div>

	<form action="" method="post" class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding" >

		<label for="nameGame">Type the name of the game</label>
    	<textarea class="w3-input w3-border w3-margin-top" rows="1" type="text" id="nameGame" placeholder="Type the name of the game.." name="nameGame"><?php if(isset($gameSummaryName)) echo $gameSummaryName; ?></textarea><br>

	   	<label for="concept">Create Game Concept</label>
	    <textarea class="w3-input w3-border w3-margin-top" style="resize: none" rows="2" type="text" id="concept" placeholder="Type the concept of the game.." name="concept"><?php if(isset($gameSummaryConcept)) echo $gameSummaryConcept; ?></textarea><br>
        
        <label>Specify Game Genre</label><br>
		<div class="w3-container">
        <div class="w3-container">
            <button class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission" type="button"
                    id="chooseButton" onclick="showAndHide('checkGenre','fontChoose')">
                Choose one or more genres <i id="fontChoose" class="fa fa-plus"></i></button>
        </div>

            <?php
            
                $selectedGen[] = explode(',',$gameSummaryGenre);
                    foreach($selectedGen as $value) {
                        
            ?>
		    
		    <div class="w3-container w3-left-align w3-animate-opacity checkboxesPosition" id="checkGenre">
		      <?php 
                if(in_array("action",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="action" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="action">';?>
              <label for="action">Action</label><br>

		      <?php 
                if(in_array("action_adventure",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="action_adventure" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="action_adventure">';?>
              <label for="action_adventure">Action-Adventure</label><br>

		      <?php 
                if(in_array("adventure",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="adventure" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="adventure">';?>
              <label for="adventure">Adventure</label><br>
		      
		      <?php 
                if(in_array("fighting",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="fighting" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="fighting">';?>
              <label for="fighting">Fighting</label><br>
		      
		      <?php 
                if(in_array("platformer",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="platformer" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="platformer">';?>
              <label for="platformer">Platformer</label><br>
		      
		      <?php 
                if(in_array("role_playing",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="role_playing" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="role_playing">';?>
              <label for="role_playing">Role-playing</label><br>
		      
		      <?php 
                if(in_array("simulation",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="simulation" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="simulation">';?>
              <label for="simulation">Simulation</label><br>
		      
		      <?php 
                if(in_array("puzzle",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="puzzle" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="puzzle">';?>
              <label for="puzzle">Puzzle</label><br>
		      
		      <?php 
                if(in_array("rhythm",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="rhythm" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="rhythm">';?>
              <label for="rhythm">Rhythm</label><br>
		      
		      <?php 
                if(in_array("horror",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="horror" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="horror">';?>
              <label for="horror">Horror</label><br>
		     
		      <?php 
                if(in_array("fps",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="fps" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="fps">';?>
              <label for="fps">FPS</label><br>
		      
		      <?php 
                if(in_array("strategy",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="strategy" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="strategy">';?>
              <label for="strategy">Strategy</label><br>
		      
		      <?php 
                if(in_array("sports",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="sports" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="sports">';?>
              <label for="sports">Sports</label><br>
		      
		      <?php 
                if(in_array("mmo",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="mmo" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="mmo">';?>
              <label for="mmo">MMO</label><br>
		      
		      <?php 
                if(in_array("gacha",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="gacha" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="gacha">';?>
              <label for="gacha">Gacha</label><br>

              <?php 
                if(in_array("other",$value))echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="other" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkGenre[]" value="other">';?>
              <label for="other">Other</label><br>
     		</div>
            <?php
            
            }
            
        ?>
    	</div><br/>
   

	    <label for="targetAudience">Choose Target Audience</label>
        <?php
            if(isset($gameSummaryAudience)){
                $selected = $gameSummaryAudience;
            }
            else{
                $selected= NULL;
            }
        ?>
	    <select id="targetAudience" name="targetAudience">
	    	<option <?php if($selected == 'baby'){echo("selected");}?> value="baby">3+</option>
    		<option <?php if($selected == 'child'){echo("selected");}?> value="child">7+</option>
    		<option <?php if($selected == 'youngteen'){echo("selected");}?> value="youngteen">12+</option>
    		<option <?php if($selected == 'lateteen'){echo("selected");}?> value="lateteen">16+</option>
    		<option <?php if($selected == 'adult'){echo("selected");}?> value="adult">18+</option>
	    </select> 
	    <br><br>

	   <label>Specify Target System</label><br>
	   <div class="w3-container">
        <div class="w3-container">
            <button class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission" type="button"
                    id="chooseButtonSystem" onclick="showAndHide('checkSystem','fontChooseSystem')">
                Choose one or more target systems <i id="fontChooseSystem" class="fa fa-plus"></i></button>
        </div>

        <?php
            
                $selectedSys[] = explode(',',$gameSummarySystem);
                    foreach($selectedSys as $value) {
                        
        ?>


        <div class="w3-container w3-left-align w3-animate-opacity checkboxesPosition" id="checkSystem">

            <?php 
            if(in_array("pc",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="pc" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="pc">';?>
            <label for="pc">PC</label><br>

            <?php 
            if(in_array("mobile",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="mobile" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="mobile">'; ?>
            <label for="mobile">Mobile</label><br>

            <?php 
            if(in_array("ps5",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="ps5" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="ps5">'; ?>
            <label for="ps5">PlayStation 5</label><br>

            <?php 
            if(in_array("ps4",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="ps4" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="ps4">'; ?>
            <label for="ps4">PlayStation 4</label><br>

            <?php 
            if(in_array("xbox",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="xbox" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="xbox">'; ?>
            <label for="xbox">Xbox Series X/S</label><br>

            <?php 
            if(in_array("xboxOne",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="xboxOne" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="xboxOne">';?>
            <label for="xboxOne">Xbox One</label><br> 

            <?php 
            if(in_array("nintendoSwitch",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="nintendoSwitch" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="nintendoSwitch">'; ?>
            <label for="nintendoSwitch">Nintendo Switch</label><br>

            <?php 
            if(in_array("nintendo3ds",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="nintendo3ds" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="nintendo3ds">'; ?>
            <label for="nintendo3ds">Nintendo 3DS</label><br>

            <?php 
            if(in_array("playVita",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="playVita" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="playVita">'; ?>
            <label for="playVita">PlayStation Vita</label><br>

            <?php 
            if(in_array("wii",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="wii" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="wii">';?>
            <label for="wii">Wii U</label><br> 

            <?php 
            if(in_array("other",$value))echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="other" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkSystem[]" value="other">'; ?>
            <label for="other">Other</label><br>

     		</div>
            <?php
            }
        ?>
    	</div><br/>

		<label>Specify Game Type</label><br>
	    <div class="w3-container">
        <div class="w3-container">
            <button class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission" type="button"
                    id="chooseButtonSystem" onclick="showAndHide('checkType','fontChooseType')">
                Choose one or more game types <i id="fontChooseType" class="fa fa-plus"></i></button>
        </div>

        <?php
            
                $selectedTyp[] = explode(',',$gameSummaryType);
                    foreach($selectedTyp as $value) {
                        
        ?>
        <div class="w3-container w3-left-align w3-animate-opacity checkboxesPosition" id="checkType">

              <?php 
              if(in_array("beatemup",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="beatemup" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="beatemup">';?>
              <label for="beatemup">Beat-em Up</label><br>

		      <?php 
              if(in_array("hacknslash",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="hacknslash" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="hacknslash">';?>
              <label for="hacknslash">Hack'n Slash</label><br>

		      <?php 
              if(in_array("stealth",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="stealth" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="stealth">';?>
              <label for="stealth">Stealth</label><br>

		      <?php 
              if(in_array("survival",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="survival" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="survival">';?>
              <label for="survival">Survival</label><br>

		      <?php 
              if(in_array("metroidvania",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="metroidvania" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="metroidvania">';?>
              <label for="metroidvania">Metroidvania</label><br>

		      <?php 
              if(in_array("textadventure",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="textadventure" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="textadventure">';?>
              <label for="textadventure">Text Adventure</label><br>

		      <?php 
              if(in_array("graphicadventure",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="graphicadventure" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="graphicadventure">';?>
              <label for="graphicadventure">Graphic Adventure</label><br>

		      <?php 
              if(in_array("visualnovel",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="visualnovel" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="visualnovel">';?>
              <label for="visualnovel">Visual Novels</label><br>

		      <?php 
              if(in_array("interactivemovie",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="interactivemovie" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="interactivemovie">';?>
              <label for="interactivemovie">Interactive Movie</label><br>

		      <?php 
              if(in_array("rpg",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="rpg" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="rpg">';?>
              <label for="rpg">RPG</label><br>

		      <?php 
              if(in_array("roguelike",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="roguelike" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="roguelike">';?>
              <label for="roguelike">Rouguelike</label><br>

		      <?php 
              if(in_array("tacticalrole",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="tacticalrole" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="tacticalrole">';?>
              <label for="tacticalrole">Tactical RPG</label><br>

		      <?php 
              if(in_array("sandboxrpg",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="sandboxrpg" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="sandboxrpg">';?>
              <label for="sandboxrpg">Sandbox RPG</label><br>

		      <?php 
              if(in_array("realtimestrategy",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="realtimestrategy" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="realtimestrategy">';?>
              <label for="realtimestrategy">Real-time Strategy</label><br>

		      <?php 
              if(in_array("realtimecombat",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="realtimecombat" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="realtimecombat">';?>
              <label for="realtimecombat">Real-time Combat</label><br>

		      <?php 
              if(in_array("turnbased",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="turnbased" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="turnbased">';?>
              <label for="turnbased">Turn Based</label><br>

		      <?php 
              if(in_array("towerdefence",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="towerdefence" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="towerdefence">';?>
              <label for="towerdefence">Tower Defence</label><br>

		      <?php 
              if(in_array("competitive",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="competitive" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="competitive">';?>
              <label for="competitive">Competitive</label><br>

		      <?php 
              if(in_array("trivia",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="trivia" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="trivia">';?>
              <label for="trivia">Trivia</label><br>

		      <?php 
              if(in_array("party",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="party" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="party">';?>
              <label for="party">Party</label><br>

              <?php 
              if(in_array("other",$value))echo '<input class="w3-check" type="checkbox" name="checkType[]" value="other" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkType[]" value="other">';?>
              <label for="other">Other</label><br>
     		</div>

             <?php
            
            }
          
        ?>
    	</div><br/>

	    <label for="settingGame">Create Setting</label>
	    <textarea class="w3-input w3-border w3-margin-top" style="resize: none" rows="2" type="text" placeholder="Type the setting of the game.." id="settingGame" name="settingGame"><?php if(isset($gameSummarySetting)) echo $gameSummarySetting; ?></textarea><br>

	    <label for="programmingLang">What software was used to make this game</label>
    	<textarea class="w3-input w3-border w3-margin-top" rows="2" type="text" placeholder="Type what programming languages/softwares were used in the game.." id="programmingLang" name="programmingLang"><?php if(isset($gameSummarySoftware)) echo $gameSummarySoftware; ?></textarea><br>

        <label for="myCode">Game Code Link</label>
        <textarea class="w3-input w3-border w3-margin-top" style="resize: none" rows="1" type="text" placeholder="Type the link for the code repository of the game.." id="myCode" name="myCode"><?php if (isset($gameSummaryCode)) echo $gameSummaryCode; ?></textarea><br>
	    

	    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit" name="summarySubmit" value="Submit">
	</form>

<!--- A connection to mech of mechanics that says that the user can continue with editing the assets -->
    <div class="w3-container continueAssets">
    <h3 style="">Continue with editing Mechanics of Mechanics Section</h3>
    <?php echo "<a href=\"mech.php?id=$idOfDocument\" class=\"w3-bar-item w3-button w3-margin-top transmission w3-text-blue w3-border w3-xxlarge w3-round w3-hover-blue\">
        Mechanics <i class=\"fa fa-angle-double-right\"></i></a>"?>
</div>


</body>
</html>
