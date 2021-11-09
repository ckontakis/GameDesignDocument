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


// finding the id of mechanics table
if($resultInfoMechanics = $conn->query("SELECT ID from mechanics WHERE DOCUMENT_ID = '$idOfDocument';")){
    if($resultInfoMechanics->num_rows === 1){
        $rowInfoMechanics = $resultInfoMechanics->fetch_assoc();

        if(isset($rowInfoMechanics['ID'])){
            $gameMechanicsId = $rowInfoMechanics['ID'];
            // finding the id of physics table
            if($resultInfoPhysics = $conn->query("SELECT ID FROM physics WHERE MECH_ID = '$gameMechanicsId';")){
                if($resultInfoPhysics->num_rows === 1){
                    $rowInfoPhysics = $resultInfoPhysics->fetch_assoc();

                    if(isset($rowInfoPhysics['ID'])){
                        $gamePhysicsId = $rowInfoPhysics['ID']; // setting the id of game elements
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
$queryLoadMechanics = "SELECT combat, coop, difficulty from mechanics WHERE ID='$gameMechanicsId';";
$queryLoadPhysics = "SELECT environment, weather, climate, humidity, gravity, lethality, simulations, particles, ragdoll from physics WHERE MECH_ID='$gameMechanicsId';";
$resLoadMechanics = $conn->query($queryLoadMechanics);
$resLoadPhysics = $conn->query($queryLoadPhysics);

if(($resLoadMechanics->num_rows === 1) && ($resLoadPhysics->num_rows === 1)){
    $rowLoadMechanics = $resLoadMechanics->fetch_assoc();
    $rowLoadPhysics = $resLoadPhysics->fetch_assoc();

    $gameMechanicsCombat= $rowLoadMechanics['combat'];
    $gameMechanicsCoop= $rowLoadMechanics['coop'];
    $gameMechanicsDifficulty= $rowLoadMechanics['difficulty'];

    $gameMechanicsEnvironment= $rowLoadPhysics['environment'];
    $gameMechanicsWeather= $rowLoadPhysics['weather'];
    $gameMechanicsClimate= $rowLoadPhysics['climate'];
    $gameMechanicsHumidity= $rowLoadPhysics['humidity'];
    $gameMechanicsGravity= $rowLoadPhysics['gravity'];
    $gameMechanicsLethality= $rowLoadPhysics['lethality'];
    $gameMechanicsSimulations= $rowLoadPhysics['simulations'];
    $gameMechanicsParticles= $rowLoadPhysics['particles'];
    $gameMechanicsRagdoll= $rowLoadPhysics['ragdoll'];

    
}

/*
 * Actions when user submits the summary of the game
 */
$successUpdateMechanics = false; // variable to show success message
$somethingWrongMechanics = false; // variable to show failure message
if(isset($_POST["mechSubmit"])){
    if(isset($gameMechanicsId)){
        $describeCombat = $_POST["combat"]; // getting the text with post method
        $describeCoop = $_POST["coop"];

        if(isset($_POST['checkDifficulty'])){
            $describeDifficulty = (array)$_POST["checkDifficulty"];
            $chkDif=implode(',',$describeDifficulty);
        }
        else{
            $chkDif=NULL;
        }

        $describeEnvironment = $_POST["environment"];
        $describeWeather = $_POST["weather"];
        $describeClimate = $_POST["climate"];
        $describeHumidity = $_POST["humidity"];
        $describeGravity = $_POST["gravity"];
        $describeLethality = $_POST["lethality"];
        $describeSimulations = $_POST["simulations"];
		$describeParticles = $_POST["particles"];
        $describeRagdoll = $_POST["ragdoll"];
        
        // query to update the database array
        $updateMechanicsQuery = "UPDATE mechanics SET combat='$describeCombat', coop='$describeCoop', difficulty='$chkDif' WHERE ID='$gameMechanicsId';";
        $updatePhysicsQuery = "UPDATE physics SET environment='$describeEnvironment', weather='$describeWeather', climate='$describeClimate', humidity='$describeHumidity', gravity='$describeGravity', lethality='$describeLethality', simulations='$describeSimulations', particles='$describeParticles', ragdoll='$describeRagdoll'  WHERE MECH_ID='$gameMechanicsId';";

        if($conn->query($updateMechanicsQuery) ){ // if query executed successfully
            if ($conn->query($updatePhysicsQuery)) {
            	// code...
            

            $successUpdateMechanics = true;
            
            $gameMechanicsCombat= $describeCombat;
            $gameMechanicsCoop= $describeCoop;
            $gameMechanicsDifficulty= $chkDif;
            
            $gameMechanicsEnvironment= $describeEnvironment;
            $gameMechanicsWeather= $describeWeather;
            $gameMechanicsClimate= $describeClimate;
            $gameMechanicsHumidity= $describeHumidity;
            $gameMechanicsGravity= $describeGravity;
            $gameMechanicsLethality= $describeLethality;
            $gameMechanicsSimulations= $describeSimulations;
            $gameMechanicsParticles= $describeParticles;
            $gameMechanicsRagdoll= $describeRagdoll;
            }else{
            	$somethingWrongMechanics = true;
            }
            //header('mech.php?id=' . $idOfDocument);
        }else{
            $somethingWrongMechanics = true;
        }
    }
}


/*
 * Actions when user adds a rule
 */
if (isset($_POST["saveRule"])) {
    $nameOfRule = test_data($_POST["ruleName"]); // getting the name of the rule
    $ruleDescription = test_data($_POST["ruleDescription"]); // getting the description of the rule
    // query to add a new rule in game_character table without image
    $queryAddRule = "INSERT INTO rules (MECH_ID, name, description) VALUES ('$gameMechanicsId' ,'$nameOfRule', '$ruleDescription');";

    //executing the query
    if($conn->query($queryAddRule)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add rule')</script>"; // else we show an error message
    }
}


/*
 * Actions when user deletes a rule
 */
if(isset($_POST["deleteRule"])){
    $idOfRuleToDelete = $_POST["keyIdRule"];

    $queryDeleteRule = "DELETE FROM rules WHERE ID='$idOfRuleToDelete';";
    if($conn->query($queryDeleteRule)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete rule')</script>";
    }
}

/*
 * Actions when user updates information for a rule
 */
if(isset($_POST["editRule"])){
    $idOfRule = $_POST["keyIdRule"];
    $nameOfRule = test_data($_POST["ruleName"]); // getting the name of the character
    $ruleDescription = test_data($_POST["ruleDescription"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateRule = "UPDATE rules SET name='$nameOfRule', description ='$ruleDescription'
                             WHERE ID='$idOfRule';";
        if($conn->query($queryUpdateRule)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update rule')</script>"; // else we show an error message
        }
    }


/*
 * Actions when user adds a button
 */
if (isset($_POST["saveControls"])) {
    $nameOfButton = test_data($_POST["buttonName"]); // getting the name of the button
    $buttonDescription = test_data($_POST["buttonDescription"]); // getting the description of the character
    // query to add a new character in game_character table without image
    $queryAddButton= "INSERT INTO controls (MECH_ID, button, description) VALUES ('$gameMechanicsId' ,'$nameOfButton', '$buttonDescription');";

    //executing the query
    if($conn->query($queryAddButton)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add character')</script>"; // else we show an error message
    }
}

/*
 * Actions when user deletes a button
 */
if(isset($_POST["deleteControl"])){
    $idOfControlToDelete = $_POST["keyIdControl"];

    $queryDeleteControl = "DELETE FROM controls WHERE ID='$idOfControlToDelete';";
    if($conn->query($queryDeleteControl)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete button')</script>";
    }
}

/*
 * Actions when user updates information for a button 
 */
if(isset($_POST["editControl"])){
    $idOfButton = $_POST["keyIdControl"];
    $nameOfButton = test_data($_POST["buttonName"]); // getting the name of the character
    $buttonDescription = test_data($_POST["buttonDescription"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateControl = "UPDATE controls SET button='$nameOfButton', description ='$buttonDescription'
                             WHERE ID='$idOfButton';";
        if($conn->query($queryUpdateControl)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update button')</script>"; // else we show an error message
        }
    }


/*
 * Actions when user adds an action
 */
if (isset($_POST["saveAction"])) {
    $nameOfAction = test_data($_POST["actionName"]); // getting the name of the action
    $actionDescription = test_data($_POST["actionDescription"]); // getting the description of the action
    // query to add a new action in game_character table without image
    $queryAddAction = "INSERT INTO actions (MECH_ID, name, description) VALUES ('$gameMechanicsId' ,'$nameOfAction', '$actionDescription');";

    //executing the query
    if($conn->query($queryAddAction)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add action')</script>"; // else we show an error message
    }
}


/*
 * Actions when user deletes an action
 */
if(isset($_POST["deleteAction"])){
    $idOfActionToDelete = $_POST["keyIdAction"];

    $queryDeleteAction = "DELETE FROM actions WHERE ID='$idOfActionToDelete';";
    if($conn->query($queryDeleteAction)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete action')</script>";
    }
}

/*
 * Actions when user updates information for a action
 */
if(isset($_POST["editAction"])){
    $idOfAction = $_POST["keyIdAction"];
    $nameOfAction = test_data($_POST["actionName"]); // getting the name of the character
    $actionDescription = test_data($_POST["actionDescription"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateAction = "UPDATE actions SET name='$nameOfAction', description ='$actionDescription'
                             WHERE ID='$idOfAction';";
        if($conn->query($queryUpdateAction)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update action')</script>"; // else we show an error message
        }
    }


/*
 * Actions when user adds a skill
 */
if (isset($_POST["saveSkill"])) {
    $nameOfSkill = test_data($_POST["skillName"]); // getting the name of the skill
    $skillDescription = test_data($_POST["skillDescription"]); // getting the description of the skill
    // query to add a new skill in game_character table without image
    $queryAddSkill = "INSERT INTO skills (MECH_ID, name, description) VALUES ('$gameMechanicsId' ,'$nameOfSkill', '$skillDescription');";

    //executing the query
    if($conn->query($queryAddSkill)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add skill')</script>"; // else we show an error message
    }
}


/*
 * Actions when user deletes an skill
 */
if(isset($_POST["deleteSkill"])){
    $idOfSkillToDelete = $_POST["keyIdSkill"];

    $queryDeleteSkill = "DELETE FROM skills WHERE ID='$idOfSkillToDelete';";
    if($conn->query($queryDeleteSkill)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete skill')</script>";
    }
}

/*
 * Actions when user updates information for a skill
 */
if(isset($_POST["editSkill"])){
    $idOfSkill = $_POST["keyIdSkill"];
    $nameOfSkill = test_data($_POST["skillName"]); // getting the name of the character
    $skillDescription = test_data($_POST["skillDescription"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateSkill = "UPDATE skills SET name='$nameOfSkill', description ='$skillDescription'
                             WHERE ID='$idOfSkill';";
        if($conn->query($queryUpdateSkill)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update skill')</script>"; // else we show an error message
        }
    }



    /*
 * Actions when user adds an ability
 */
if (isset($_POST["saveAbility"])) {
    $nameOfAbility = test_data($_POST["abilityName"]); // getting the name of the ability
    $abilityDescription = test_data($_POST["abilityDescription"]); // getting the description of the ability
    // query to add a new ability in game_character table without image
    $queryAddAbility = "INSERT INTO abilities (MECH_ID, name, description) VALUES ('$gameMechanicsId' ,'$nameOfAbility', '$abilityDescription');";

    //executing the query
    if($conn->query($queryAddAbility)){
         header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot add ability')</script>"; // else we show an error message
    }
}


/*
 * Actions when user deletes an ability
 */
if(isset($_POST["deleteAbility"])){
    $idOfAbilityToDelete = $_POST["keyIdAbility"];

    $queryDeleteAbility = "DELETE FROM abilities WHERE ID='$idOfAbilityToDelete';";
    if($conn->query($queryDeleteAbility)){
        header("Refresh:0"); // if query is executed successfully we refresh the page
    }else{
        echo "<script>alert('Error: cannot delete skill')</script>";
    }
}

/*
 * Actions when user updates information for a ability
 */
if(isset($_POST["editAbility"])){
    $idOfAbility = $_POST["keyIdAbility"];
    $nameOfAbility = test_data($_POST["abilityName"]); // getting the name of the character
    $abilityDescription = test_data($_POST["abilityDescription"]); // getting the description of the character

   
        // query to update information about the character
        $queryUpdateAbility = "UPDATE abilities SET name='$nameOfAbility', description ='$abilityDescription'
                             WHERE ID='$idOfAbility';";
        if($conn->query($queryUpdateAbility)){
            header("Refresh:0"); // if query is executed successfully we refresh the page
        }else{
            echo "<script>alert('Error: cannot update ability')</script>"; // else we show an error message
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
	<title>Explain Game Mechanics</title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="../Images/favicon-new.ico">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/main.css">
	<script src="../JavaScript/WorldBuilding.js"></script>
    <script src="../JavaScript/Main.js"></script>

    <!--<style>
    body {
      background-image: url('../b031d004f21d25336fe79c9dec0eff45ff34d0aa.jpg');
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-size: 100% 100%;
    }
    </style>-->

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
	    <a href="mech.php?id=<?php if(isset($idOfDocument)) echo $idOfDocument ?>" class="w3-hover-text-blue">Game Mechanics</a>
	</div>


	<div class="w3-container w3-blue panelInFormWorld">
	    <h3 class="headerPanel">Explain Mechanics</h3>
	</div>
	
    <div id="rules-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                    <span onclick="hideElement('rules-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Add a Rule</h3><br>

                    <label for="ruleName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of rule*</label>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="ruleName<?php echo $gameMechanicsId;?>" name="ruleName" ><br>

                    <label for="ruleDescription<?php echo $gameMechanicsId; ?>">Describe the rule</label>
                    <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="ruleDescription<?php echo $gameMechanicsId; ?>" name="ruleDescription"></textarea><br>
                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" type="submit" name="saveRule">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    // query to load all rules
    $queryLoadAllRules = "SELECT * FROM rules WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllRules = mysqli_query($conn, $queryLoadAllRules); // executing the query

    while($rowLoadRule = $resultLoadAllRules->fetch_assoc()){
        $idOfRule = $rowLoadRule["ID"];
        $ruleName = $rowLoadRule["name"];
        $ruleDescribe = $rowLoadRule["description"];
        

        echo "<div id=\"rules-modal-edit$idOfRule\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('rules-modal-edit$idOfRule')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit rule <b>$ruleName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdRule\" value=\"$idOfRule\" />

            <label for=\"ruleNameEdit$idOfRule\" class=\"w3-margin-top\">Write the name of the rule *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"controlNameEdit$idOfRule\" value=\"$ruleName\" name=\"ruleName\" required><br>

        

            <label for=\"controlDescriptionEdit$idOfRule\">Describe the rule</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"ruleDescriptionEdit$idOfRule\"
                      name=\"ruleDescription\">$ruleDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editRule\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>



    <div id="controls-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                    <span onclick="hideElement('controls-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Controls Addition</h3><br>

                    <label for="buttonName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the button*</label>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="buttonName<?php echo $gameMechanicsId;?>" name="buttonName" ><br>

                    <label for="buttonDescription<?php echo $gameMechanicsId; ?>">Describe control button</label>
                    <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="buttonDescription<?php echo $gameMechanicsId; ?>" name="buttonDescription"></textarea><br>
                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" type="submit" name="saveControls">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    // query to load all controls
    $queryLoadAllControls = "SELECT * FROM controls WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllControls = mysqli_query($conn, $queryLoadAllControls); // executing the query

    while($rowLoadControl = $resultLoadAllControls->fetch_assoc()){
        $idOfControl = $rowLoadControl["ID"];
        $buttonName = $rowLoadControl["button"];
        $controlDescribe = $rowLoadControl["description"];
        

        echo "<div id=\"controls-modal-edit$idOfControl\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('controls-modal-edit$idOfControl')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit button <b>$buttonName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdControl\" value=\"$idOfControl\" />

            <label for=\"controlNameEdit$idOfControl\" class=\"w3-margin-top\">Write the name of the button *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"controlNameEdit$idOfControl\" value=\"$buttonName\" name=\"buttonName\" required><br>

        

            <label for=\"controlDescriptionEdit$idOfControl\">Describe the button control</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"controlDescriptionEdit$idOfControl\"
                      name=\"buttonDescription\">$controlDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editControl\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>


    <div id="actions-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                    <span onclick="hideElement('actions-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Actions Addition</h3><br>

                    <label for="actionName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the action*</label>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="buttonName<?php echo $gameMechanicsId;?>" name="actionName" ><br>

                    <label for="actionDescription<?php echo $gameMechanicsId; ?>">Describe action</label>
                    <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="actionDescription<?php echo $gameMechanicsId; ?>" name="actionDescription"></textarea><br>
                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" type="submit" name="saveAction">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    // query to load all controls
    $queryLoadAllActions = "SELECT * FROM actions WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllActions = mysqli_query($conn, $queryLoadAllActions); // executing the query

    while($rowLoadAction = $resultLoadAllActions->fetch_assoc()){
        $idOfAction = $rowLoadAction["ID"];
        $actionName = $rowLoadAction["name"];
        $actionDescribe = $rowLoadAction["description"];
        

        echo "<div id=\"actions-modal-edit$idOfAction\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('actions-modal-edit$idOfAction')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit action <b>$actionName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdAction\" value=\"$idOfAction\" />

            <label for=\"actionNameEdit$idOfAction\" class=\"w3-margin-top\">Write the name of the action *</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"actionNameEdit$idOfAction\" value=\"$actionName\" name=\"actionName\" required><br>

        

            <label for=\"actionDescriptionEdit$idOfAction\">Describe the action control</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"actionDescriptionEdit$idOfAction\"
                      name=\"actionDescription\">$actionDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editAction\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>


    <div id="skills-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                    <span onclick="hideElement('skills-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Skills Addition</h3><br>

                    <label for="skillName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the skill*</label>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="skillName<?php echo $gameMechanicsId;?>" name="skillName" ><br>

                    <label for="skillDescription<?php echo $gameMechanicsId; ?>">Describe skill effects</label>
                    <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="skillDescription<?php echo $gameMechanicsId; ?>" name="skillDescription"></textarea><br>
                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" type="submit" name="saveSkill">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    // query to load all skills
    $queryLoadAllSkills = "SELECT * FROM skills WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllSkills = mysqli_query($conn, $queryLoadAllSkills); // executing the query

    while($rowLoadSkill = $resultLoadAllSkills->fetch_assoc()){
        $idOfSkill = $rowLoadSkill["ID"];
        $skillName = $rowLoadSkill["name"];
        $skillDescribe = $rowLoadSkill["description"];
        

        echo "<div id=\"skills-modal-edit$idOfSkill\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('skills-modal-edit$idOfSkill')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit skill/ability <b>$skillName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdSkill\" value=\"$idOfSkill\" />

            <label for=\"skillNameEdit$idOfSkill\" class=\"w3-margin-top\">Write the name of the skill/ability*</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"skillNameEdit$idOfSkill\" value=\"$skillName\" name=\"skillName\" required><br>

        

            <label for=\"skillDescriptionEdit$idOfSkill\">Describe the skill/ability effects</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"skillDescriptionEdit$idOfSkill\"
                      name=\"skillDescription\">$skillDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editSkill\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>







    <div id="abilities-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom">
            <div class="w3-container">
                <form method="post" action="" enctype="multipart/form-data" class="w3-container" style="text-align: center;">
                    <span onclick="hideElement('abilities-modal')" class="w3-button w3-display-topright w3-hover-red">
                        <i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Abilities Addition</h3><br>

                    <label for="abilityName<?php echo $gameMechanicsId; ?>" class="w3-margin-top">Write the name of the ability*</label>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="abilityName<?php echo $gameMechanicsId;?>" name="abilityName" ><br>

                    <label for="abilityDescription<?php echo $gameMechanicsId; ?>">Describe ability effects</label>
                    <textarea class="w3-input w3-border w3-margin-top" rows="3" type="text" id="abilityDescription<?php echo $gameMechanicsId; ?>" name="abilityDescription"></textarea><br>
                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" type="submit" name="saveAbility">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    // query to load all abilities
    $queryLoadAllAbilities = "SELECT * FROM abilities WHERE MECH_ID='$gameMechanicsId';";
    //$resultLoadAllCharacters = $conn->query($queryLoadAllCharacters); // executing the query
    $resultLoadAllAbilities = mysqli_query($conn, $queryLoadAllAbilities); // executing the query

    while($rowLoadAbility = $resultLoadAllAbilities->fetch_assoc()){
        $idOfAbility = $rowLoadAbility["ID"];
        $abilityName = $rowLoadAbility["name"];
        $abilityDescribe = $rowLoadAbility["description"];
        

        echo "<div id=\"abilities-modal-edit$idOfAbility\" class=\"w3-modal w3-padding-16\">
    <div class=\"w3-modal-content w3-animate-zoom\">
        <form method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"w3-container\" style=\"text-align: center;\">
                <span onclick=\"hideElement('abilities-modal-edit$idOfAbility')\" class=\"w3-button w3-display-topright w3-hover-red\">
                        <i class=\"fa fa-close\"></i></span>
            <h3 class=\"headerForModal\">Edit ability <b>$abilityName</b></h3><br>";

            echo "
            
            <input type=\"hidden\"  name=\"keyIdAbility\" value=\"$idOfAbility\" />

            <label for=\"abilityNameEdit$idOfAbility\" class=\"w3-margin-top\">Write the name of the ability*</label>
            <input class=\"w3-input w3-border w3-margin-top\" type=\"text\" id=\"abilityNameEdit$idOfAbility\" value=\"$abilityName\" name=\"abilityName\" required><br>

        

            <label for=\"abilityDescriptionEdit$idOfAbility\">Describe the ability effects</label>
            <textarea class=\"w3-input w3-border w3-margin-top\" rows=\"3\" type=\"text\" id=\"abilityDescriptionEdit$idOfAbility\"
                      name=\"abilityDescription\">$abilityDescribe</textarea><br>
            <div class=\"w3-container w3-padding-16\">
                <button class=\"w3-button w3-green transmission\" type=\"submit\" name=\"editAbility\">Save</button>
            </div>
        </form>
    </div>
</div>";
    }

    ?>

    


	<form action="" method="post" enctype="multipart/form-data" class="w3-container w3-border w3-hover-shadow w3-padding-16 formWorldBuilding" >
	   	

        <label for="rules">Add Rules of the game</label>
        <button onclick="showElement('rules-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="rules" type="button" name="rules">
        <i class="fa fa-plus"></i></button><br><br>

        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadRules">
        <tr>
            <th>Rule Name</th>
            <th>Description</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllRulesV2= "SELECT ID, name, description FROM rules WHERE MECH_ID=$gameMechanicsId;";

            $resultLoadAllRulesV2= mysqli_query($conn,$queryLoadAllRulesV2);

            while ($rowLoadRule = $resultLoadAllRulesV2->fetch_assoc()) {
                                $idOfRuleLoad = $rowLoadRule["ID"];
                                $nameOfRuleLoad = $rowLoadRule["name"];
                                $ruleDescriptionLoad = $rowLoadRule["description"];

                echo "<tr><td>" . $nameOfRuleLoad . "</td><td>" . $ruleDescriptionLoad .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('rules-modal-edit$idOfRuleLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the rule $nameOfRuleLoad')\" type=\"submit\"
                                    name=\"deleteRule\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdRule\" value=\"$idOfRuleLoad\" /></form></tr>";

            }
        ?>
        </table><br>
	    	

	    <label for="combat">Explain Combat</label>
	    <textarea class="w3-input w3-border w3-margin-top" style="resize: none" rows="2" type="text" placeholder="Describe the combat of the game.." id="combat" name="combat"><?php if(isset($gameMechanicsCombat)) echo $gameMechanicsCombat; ?></textarea><br>

	    <label for="physics">Explain Physics</label><br><br>
	    <button type="button" class="collapsible">Physics</button>
		<div class="content physics">
		    <br><label for="environment">Choose Game Environment</label>
		    <?php
            	if(isset($gameMechanicsEnvironment)){
                	$selected = $gameMechanicsEnvironment;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="environment" name="environment">
		    	<option <?php if($selected == 'normal'){echo("selected");}?> value="normal">Normal</option>
		    	<option <?php if($selected == 'plains'){echo("selected");}?> value="plains">Plains</option>
		    	<option <?php if($selected == 'mountainous'){echo("selected");}?> value="mountainous">Mountainous</option>
		    	<option <?php if($selected == 'seaside'){echo("selected");}?> value="seaside">Seaside</option>
		    	<option <?php if($selected == 'city'){echo("selected");}?> value="city">City</option>
		    	<option <?php if($selected == 'countryside'){echo("selected");}?> value="countryside">Countryside</option>
		    	<option <?php if($selected == 'desert'){echo("selected");}?> value="desert">Desert</option>
		    	<option <?php if($selected == 'icy'){echo("selected");}?> value="icy">Icy</option>
		    	<option <?php if($selected == 'forest'){echo("selected");}?> value="forest">Forest</option>
		    	<option <?php if($selected == 'island'){echo("selected");}?> value="island">Island</option>
		    	<option <?php if($selected == 'swamp'){echo("selected");}?> value="swamp">Swamp</option>
		    	
		    </select> 
		    <br><br>

		    <label for="weather">Choose Environment Weather</label>
		    <?php
            	if(isset($gameMechanicsWeather)){
                	$selected = $gameMechanicsWeather;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="weather" name="weather">
		    	<option <?php if($selected == 'sunny'){echo("selected");}?> value="sunny">Sunny/Clear</option>
		    	<option <?php if($selected == 'partially'){echo("selected");}?> value="partially">Partially Cloudy</option>
		    	<option <?php if($selected == 'cloudy'){echo("selected");}?> value="cloudy">Cloudy</option>
		    	<option <?php if($selected == 'overcast'){echo("selected");}?> value="overcast">Overcast</option>
		    	<option <?php if($selected == 'rain'){echo("selected");}?> value="rain">Rain</option>
		    	<option <?php if($selected == 'drizzle'){echo("selected");}?> value="drizzle">Drizzle</option>
		    	<option <?php if($selected == 'snow'){echo("selected");}?> value="snow">Snow</option>
		    	<option <?php if($selected == 'stormy'){echo("selected");}?> value="stormy">Stormy</option>
		    	<option <?php if($selected == 'tornadoes'){echo("selected");}?> value="tornadoes">Tornadoes</option>
		    	<option <?php if($selected == 'thundersnows'){echo("selected");}?> value="thundersnows">Thundersnows</option>
		    	<option <?php if($selected == 'fog'){echo("selected");}?> value="fog">Fog</option>
		    	<option <?php if($selected == 'hurricanes'){echo("selected");}?> value="hurricanes">Hurricanes</option>
		    	<option <?php if($selected == 'sandstorms'){echo("selected");}?> value="sandstorms">Sandstorms</option>
		    </select> 
		    <br><br>

		    <label for="climate">Choose Environment Climate</label>
		    <?php
            	if(isset($gameMechanicsClimate)){
                	$selected = $gameMechanicsClimate;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="climate" name="climate">
		    	<option <?php if($selected == 'tropical'){echo("selected");}?> value="tropical">Tropical</option>
		    	<option <?php if($selected == 'dry'){echo("selected");}?> value="dry">Dry</option>
		    	<option <?php if($selected == 'temperate'){echo("selected");}?> value="temperate">Temperate</option>
		    	<option <?php if($selected == 'continental'){echo("selected");}?> value="continental">Continental</option>
		    	<option <?php if($selected == 'polar'){echo("selected");}?> value="polar">Polar</option>
		    	
		    </select> 
		    <br><br>

		    <label for="humidity">Choose Environment Humidity</label>
		    <?php
            	if(isset($gameMechanicsHumidity)){
                	$selected = $gameMechanicsHumidity;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="humidity" name="humidity">
		    	<option <?php if($selected == 'high'){echo("selected");}?> value="high">High</option>
		    	<option <?php if($selected == 'normal'){echo("selected");}?> value="normal">Normal</option>
		    	<option <?php if($selected == 'low'){echo("selected");}?> value="low">Low</option>
		    	
		    </select> 
		    <br><br>

		    <label for="gravity">Choose Environment Gravity</label>
		    <?php
            	if(isset($gameMechanicsGravity)){
                	$selected = $gameMechanicsGravity;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="gravity" name="gravity">
		    	<option <?php if($selected == 'high'){echo("selected");}?> value="high">High</option>
		    	<option <?php if($selected == 'normal'){echo("selected");}?> value="normal">Normal</option>
		    	<option <?php if($selected == 'low'){echo("selected");}?> value="low">Low</option>
		    	
		    </select> 
		    <br><br>

		    <label for="lethality">Choose Environment Lethality</label>
		    <?php
            	if(isset($gameMechanicsLethality)){
                	$selected = $gameMechanicsLethality;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="lethality" name="lethality">
		    	<option <?php if($selected == 'high'){echo("selected");}?> value="high">High</option>
		    	<option <?php if($selected == 'normal'){echo("selected");}?> value="normal">Normal</option>
		    	<option <?php if($selected == 'low'){echo("selected");}?> value="low">Low</option>
		    	<option <?php if($selected == 'none'){echo("selected");}?> value="none">None</option>
		    	
		    </select> 
		    <br><br>

		    <label for="simulations">Choose Physics Simulations</label>
		    <?php
            	if(isset($gameMechanicsSimulations)){
                	$selected = $gameMechanicsSimulations;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="simulations" name="simulations">
		    	<option <?php if($selected == 'rigid'){echo("selected");}?> value="rigid">Rigid body</option>
		    	<option <?php if($selected == 'soft'){echo("selected");}?> value="soft">Soft-body</option>
		    	
		    </select> 
		    <br><br>

		    <label for="particles">Choose Particle System</label>
		    <?php
            	if(isset($gameMechanicsParticles)){
                	$selected = $gameMechanicsParticles;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="particles" name="particles">
		    	<option <?php if($selected == 'high'){echo("selected");}?> value="high">High density</option>
		    	<option <?php if($selected == 'normal'){echo("selected");}?> value="normal">Normal density</option>
		    	<option <?php if($selected == 'low'){echo("selected");}?> value="low">Low density</option>
		    	<option <?php if($selected == 'off'){echo("selected");}?> value="off">No particles</option>
		    	
		    </select> 
		    <br><br>

		    <label for="ragdoll">Choose Ragdoll Physics</label>
		    <?php
            	if(isset($gameMechanicsRagdoll)){
                	$selected = $gameMechanicsRagdoll;
            	}
            	else{
                	$selected= NULL;
            	}
        	?>
		    <select id="ragdoll" name="ragdoll">
		    	<option <?php if($selected == 'on'){echo("selected");}?> value="on">On</option>
		    	<option <?php if($selected == 'off'){echo("selected");}?> value="off">Off</option>
		    	
		    </select>
	    </div> <br><br>

        <label for="coop">Explain Co-op</label>
        <textarea class="w3-input w3-border w3-margin-top" style="resize: none" rows="2" type="text" placeholder="If there is co-op option in the game, describe how it works.." id="coop" name="coop"><?php if(isset($gameMechanicsCoop)) echo $gameMechanicsCoop; ?></textarea><br>


        <label>Specify Game Difficulties</label><br>
        <div class="w3-container">
        <div class="w3-container">
            <button class="w3-button w3-border w3-border-blue w3-hover-blue w3-round transmission" type="button"
                    id="chooseButton" onclick="showAndHide('checkDifficulty','fontChoose')">
                Choose all available game difficulties <i id="fontChoose" class="fa fa-plus"></i></button>
        </div>

            <?php
            
                $selectedDif[] = explode(',',$gameMechanicsDifficulty);
                    foreach($selectedDif as $value) {
                        
            ?>
            
            <div class="w3-container w3-left-align w3-animate-opacity checkboxesPosition" id="checkDifficulty">
              <?php 
                if(in_array("easy",$value))echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="easy" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="easy">';?>
              <label for="easy">Easy</label><br>

              <?php 
                if(in_array("normal",$value))echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="normal" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="normal">';?>
              <label for="normal">Normal</label><br>

              <?php 
                if(in_array("hard",$value))echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="hard" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="hard">';?>
              <label for="hard">Hard</label><br>
              
              <?php 
                if(in_array("extreme",$value))echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="extreme" checked >'; else echo '<input class="w3-check" type="checkbox" name="checkDifficulty[]" value="extreme">';?>
              <label for="extreme">Extreme</label><br>

            </div>
            <?php
            
            }
            
        ?>
        </div><br/>





        <label for="controls">Add Controls</label>
        <button onclick="showElement('controls-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="controls" type="button" name="controls">
        <i class="fa fa-plus"></i></button><br><br>
	    
    	
        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadControls">
        <tr>
            <th>Button Name</th>
            <th>Description</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllControlsV2= "SELECT ID, button, description FROM controls WHERE MECH_ID=$gameMechanicsId;";

            $resultLoadAllControlsV2= mysqli_query($conn,$queryLoadAllControlsV2);

            while ($rowLoadControl = $resultLoadAllControlsV2->fetch_assoc()) {
                $idOfControlsLoad = $rowLoadControl["ID"];
                $buttonNameLoad = $rowLoadControl["button"];
                $buttonDescriptionLoad = $rowLoadControl["description"];

                echo "<tr><td>" . $buttonNameLoad . "</td><td>" . $buttonDescriptionLoad .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('controls-modal-edit$idOfControlsLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the button $buttonNameLoad')\" type=\"submit\"
                                    name=\"deleteControl\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdControl\" value=\"$idOfControlsLoad\" /></form></tr>";

            }
        ?>
        </table><br>


        <label for="actions">Add Actions</label>
        <button onclick="showElement('actions-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="actions" type="button" name="actions">
        <i class="fa fa-plus"></i></button><br><br>


        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadActions">
        <tr>
            <th>Action Name</th>
            <th>Description</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllActionsV2= "SELECT ID, name, description FROM actions WHERE MECH_ID=$gameMechanicsId;";

            $resultLoadAllActionsV2= mysqli_query($conn,$queryLoadAllActionsV2);

            while ($rowLoadAction = $resultLoadAllActionsV2->fetch_assoc()) {
                $idOfActionLoad = $rowLoadAction["ID"];
                $actionNameLoad = $rowLoadAction["name"];
                $actionDescriptionLoad = $rowLoadAction["description"];

                echo "<tr><td>" . $actionNameLoad . "</td><td>" . $actionDescriptionLoad .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('actions-modal-edit$idOfActionLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the action $actionNameLoad')\" type=\"submit\"
                                    name=\"deleteAction\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdAction\" value=\"$idOfActionLoad\" /></form></tr>";

            }
        ?>
        </table><br>


        <label for="skills">Add Skills</label>
        <button onclick="showElement('skills-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="skills" type="button" name="skills">
        <i class="fa fa-plus"></i></button><br><br>


        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadSkills">
        <tr>
            <th>Skill Name</th>
            <th>Effect</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllSkillsV2= "SELECT ID, name, description FROM skills WHERE MECH_ID=$gameMechanicsId;";

            $resultLoadAllSkillsV2= mysqli_query($conn,$queryLoadAllSkillsV2);

            while ($rowLoadSkill = $resultLoadAllSkillsV2->fetch_assoc()) {
                $idOfSkillLoad = $rowLoadSkill["ID"];
                $skillNameLoad = $rowLoadSkill["name"];
                $skillDescriptionLoad = $rowLoadSkill["description"];

                echo "<tr><td>" . $skillNameLoad . "</td><td>" . $skillDescriptionLoad .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('skills-modal-edit$idOfSkillLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the skill $skillNameLoad')\" type=\"submit\"
                                    name=\"deleteSkill\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdSkill\" value=\"$idOfSkillLoad\" /></form></tr>";

            }
        ?>
        </table><br>




        <label for="abilities">Add Abilities</label>
        <button onclick="showElement('abilities-modal')" class="w3-button w3-circle w3-border
        w3-border-blue w3-hover-blue w3-margin-left transmission" id="abilities" type="button" name="abilities">
        <i class="fa fa-plus"></i></button><br><br>


        <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableLoadAbilities">
        <tr>
            <th>Ability Name</th>
            <th>Effect</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>

        <?php 
            $queryLoadAllAbilitiesV2= "SELECT ID, name, description FROM abilities WHERE MECH_ID=$gameMechanicsId;";

            $resultLoadAllAbilitiesV2= mysqli_query($conn,$queryLoadAllAbilitiesV2);

            while ($rowLoadAbility = $resultLoadAllAbilitiesV2->fetch_assoc()) {
                $idOfAbilityLoad = $rowLoadAbility["ID"];
                $abilityNameLoad = $rowLoadAbility["name"];
                $abilityDescriptionLoad = $rowLoadAbility["description"];

                echo "<tr><td>" . $abilityNameLoad . "</td><td>" . $abilityDescriptionLoad .
                "</td><td><button class=\"w3-button w3-border transmission\" type=\"button\" onclick=\"showElement('abilities-modal-edit$idOfAbilityLoad')\">
                     <i class=\"fa fa-edit\"></i></button></td>" . "<td><form method=\"post\" action=\"\"><button class=\"w3-button w3-border transmission\" 
                          onclick=\"return confirm('Are you sure that you want to delete the ability $abilityNameLoad')\" type=\"submit\"
                                    name=\"deleteAbility\"><i class=\"fa fa-trash\"></i></button></td>
                                    <input type=\"hidden\"  name=\"keyIdAbility\" value=\"$idOfAbilityLoad\" /></form></tr>";

            }
        ?>
        </table><br>



	    <input class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="submit" name="mechSubmit" value="Submit">
	</form>

	<?php echo
	'<script type="text/javascript">
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
	</script>';?>
    

    
	<!--- A connection to mech of mechanics that says that the user can continue with editing the assets -->
    <div class="w3-container continueAssets">
    <h3 style="">Continue with editing Gameplay Elements of Mechanics Section</h3>
    <?php echo "<a href=\"gameplay.php?id=$idOfDocument\" class=\"w3-bar-item w3-button w3-margin-top transmission w3-text-blue w3-border w3-xxlarge w3-round w3-hover-blue\">
        Gameplay Elements <i class=\"fa fa-angle-double-right\"></i></a>"?>
	</div>
</body>
</html>
