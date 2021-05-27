<?php

require 'connect.php';
$con = $_SESSION["conn"]; // variable that connected to database
//require 'redirect.php';
$person_ID=$_SESSION["id"];

if (!isset($_SESSION['logged_in'])) {
    header('Location: write-login.php');
}

if (isset($_POST['saveDocument'])){
    echo "done";
    $name=$_POST['nameDocument'];
    if(empty($_POST['nameDocument']))
    {
        $err="Συμπληρώστε όλα τα πεδία";
    }
    else{
        // Creating game elements row
        if(!$con->query("INSERT INTO game_elements (story_describe) VALUES (NULL);")){
            echo "Error: " . "<br>" . $con->error;
        }
        $game_elements_last_id = mysqli_insert_id($con);

        // Creating assets row
        if(!$con->query("INSERT INTO assets (describe_music) VALUES (NULL);")){
            echo "Error: " . "<br>" . $con->error;
        }
        $assets_last_id = mysqli_insert_id($con);

        if(!$con->query("INSERT INTO world_building (ASSETS_ID, GAME_ELEMENTS_ID) VALUES ('$assets_last_id', '$game_elements_last_id');")){
            echo "Error: " . "<br>" . $con->error;
        }
        $world_building_last_id = mysqli_insert_id($con);

        if(!$con->query("INSERT INTO game_summary (name, concept, genre, audience, system, type, setting, software, game_code) 
                        VALUES (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);")){
            echo "Error: " . "<br>" . $con->error;
        }
        $summary_last_id = mysqli_insert_id($con);


        if(!$con->query("INSERT INTO mechanics (combat) 
                        VALUES (NULL);")){
            echo "Error: " . "<br>" . $con->error;
        }
        $mechanics_last_id = mysqli_insert_id($con);

        $query="INSERT INTO document (name, WORLD_BUILDING_ID, SUMMARY_ID, MECHANICS_ID) 
                VALUES ('$name', '$world_building_last_id', '$summary_last_id', '$mechanics_last_id')";
        
        if(!mysqli_query($con, $query)){
           echo "Error: " . $query . "<br>" . $con->error;
        }

        $document_last_id = mysqli_insert_id($con);

        $query="INSERT INTO person_edits_document (PERSON_ID, DOCUMENT_ID, status_of_invitation) 
                VALUES ('$person_ID', '$document_last_id', 'accepted')";

        if(!mysqli_query($con, $query)){
            echo "Error: " . $query . "<br>" . $con->error;
        }
        
        header('Location:write.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Write GDD - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
</head>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/main.css">
<body>
<div class="w3-bar w3-blue showBar">
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button w3-indigo"><b>Write GDD</b></a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout</a>
        </div>
    </div>
</div>

<div class="w3-sidebar w3-blue w3-bar-block w3-border-right w3-animate-left" id="sideBar" style="display: none;">
    <button onclick="hideElement('sideBar')" class="w3-bar-item w3-large">Close <i class="fa fa-close"></i></button>
    <a href="index.php" class="w3-bar-item w3-button"><img src="Images/favicon-new.ico" alt="logo"> Start Page</a>
    <a href="write.php" class="w3-bar-item w3-button w3-indigo"><b>Write GDD</b></a>
    <a href="contact.php" class="w3-bar-item w3-button">Contact</a>
    <a href="#" class="w3-bar-item w3-button">Frequently Asked Questions</a>
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button">Profile <i class="fa fa-user-circle"></i></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a href="profile.php" class="w3-bar-item w3-button">Settings</a>
            <a href="logout.php" class="w3-bar-item w3-button">Logout</a>
        </div>
    </div>
</div>

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i></button>

<div class="container writePageContent">
    <button class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="button"
            onclick="showElement('newDocument-modal')">Create a new Game Design Document</button><br><br>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="text-align: center;">
        <div id="newDocument-modal" class="w3-modal">
            <div class="w3-modal-content w3-animate-zoom">
                <div class="w3-container">
                        <span onclick="hideElement('newDocument-modal')" class="w3-button
                        w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Create a document</h3><br>

                    <label for="nameDocument" class="w3-margin-top" id="labelNameDocument">Type the name of the document *</label><br>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="nameDocument" name="nameDocument" required><br>

                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" id="saveDocument" type="submit" name="saveDocument">
                            Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <?php
        $query = "SELECT DOCUMENT_ID FROM person_edits_document WHERE PERSON_ID ='$person_ID' AND status_of_invitation='accepted'    ORDER BY DOCUMENT_ID ASC";
        $resultDocForeign = mysqli_query($con, $query);

    ?>
    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableGDD">
        <tr>
            <th>Game Design Document</th>
            <th>Edit</th>
            <th>Delete</th>
            <th>Download</th>
            <th>Teams</th>
        </tr>

        <?php
            if(mysqli_num_rows ( $resultDocForeign)>=1){
                while ($rowDocForeign = mysqli_fetch_assoc($resultDocForeign)) {
                    $doc_id=$rowDocForeign['DOCUMENT_ID'];
                    $query = "SELECT * FROM document WHERE ID ='$doc_id' ORDER BY ID ASC";
                    $resultDocument = mysqli_query($con, $query);
                    if(mysqli_num_rows ( $resultDocument )==1){
                            $rowDocument = $resultDocument->fetch_assoc();

                        ?>
        <tr>
            <input type="hidden"  name="keyIdPerson"  value="<?php echo $person_ID; ?>" />
            <td><?php echo $rowDocument["name"]?></td>
            <td>
                <div class="w3-dropdown-hover">
                    <button class="w3-button w3-round w3-border w3-border-black transmission" id="edit<?php echo $rowDocument['ID']?>" type="button"
                            name="btnEdit<?php echo $rowDocument['ID']?>"><i class="fa fa-edit"></i></button>
                    <div class="w3-dropdown-content w3-bar-block w3-border">
                        <?php echo '<a href="Mechanics/summary.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-border-bottom w3-center transmission">Summary</a>';?>
                        <button class="w3-bar-item w3-button w3-center transmission"
                                onclick="showCategories('mechCat<?php echo $rowDocument['ID']?>', 'mechCatDown<?php echo $rowDocument['ID']?>')">
                            Mechanics <i id="mechCatDown<?php echo $rowDocument['ID']?>" class="fa fa-chevron-down"></i></button>
                        <div id="mechCat<?php echo $rowDocument['ID']?>" class="catButton">
                            <?php echo '<a href="Mechanics/mech.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Mechanics</a>';?>
                            <?php echo '<a href="Mechanics/gameplay.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Gameplay</a>';?>
                            <?php echo '<a href="Mechanics/GuiMenusi.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Menus and Gui</a>';?>
                        </div>
                        <button class="w3-bar-item w3-button w3-border-top w3-center transmission"
                                onclick="showCategories('worldCat<?php echo $rowDocument['ID']?>', 'worldCatDown<?php echo $rowDocument['ID']?>')">
                            World Building <i id="worldCatDown<?php echo $rowDocument['ID']?>" class="fa fa-chevron-down"></i></button>
                        <div id="worldCat<?php echo $rowDocument['ID']?>" class="catButton">
                            <?php echo '<a href="WorldBuilding/GameElementsWorld.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Game Elements</a>';?>
                            <?php echo '<a href="WorldBuilding/AssetsWorld.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Assets</a>';?>
                        </div>
                    </div>
                </div>
            </td>
            <td><button class="w3-button w3-round w3-border w3-border-black transmission" id="remove1" onclick="confirmDelete('Are you sure you want to delete this document?')" type="button" name="btnRemove1"><i class="fa fa-trash"></i></button></td>
            <td><button class="w3-button w3-round w3-border w3-border-black transmission" id="down1" type="button" name="down1"><i class="fa fa-download"></i></button></td>
            <td><button class="w3-button w3-round w3-border w3-border-black transmission" id="teams1" onclick="showElement('teams-modal')" type="button" name="teams1"><i class="fa fa-users"></i></button></td>
        </tr>
        <?php
                }
            }
        }
        ?>
    </table><br>

    <div id="teams-modal" class="w3-modal">
        <div class="w3-modal-content w3-animate-zoom" style="text-align: center;">
            <div class="w3-container">
                        <span onclick="hideElement('teams-modal')" class="w3-button
                        w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>

                <h3 class="headerForModal">Edit teams and users that can edit the document</h3><br>

                <label for="emailTDocumentEditor" class="w3-margin-top" id="labelTEmailDocumentEditor">Invite more people to edit the document</label><br>

                <input class="w3-input w3-border w3-margin-top inputEmailMember" type="email"
                       placeholder="Type the email of the person that you want to invite"
                       id="emailTDocumentEditor" name="emailTDocumentEditor">
                <button class="w3-button w3-border w3-margin-top w3-border-blue w3-hover-blue transmission"
                        id="addTEditor" type="button" name="addTEditor" style="display: inline-block;">
                    <i class="fa fa-plus"></i></button><br><br>

                <label>Editors of the document</label>

                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableEditors1">
                    <tr>
                        <th>Name</th>
                        <th>Delete</th>
                    </tr>
                    <tr>
                        <td>Kostas</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="editor1" type="button" name="btnRemoveEditor"><i class="fa fa-minus"></i></button></td>
                    </tr>
                </table><br>

                <label>Teams that can edit the document</label>
                <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableTeams1">
                    <tr>
                        <th>Teams</th>
                        <th>Add</th>
                    </tr>
                    <tr>
                        <td>Team 1</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="teamT1" type="button" name="btnAddTeam"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Team 2</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="teamT2" type="button" name="btnAddTeam"><i class="fa fa-plus"></i></button></td>
                    </tr>
                    <tr>
                        <td>Team 3</td>
                        <td><button class="w3-button w3-green w3-circle transmission" id="teamT3" type="button" name="btnAddTeam"><i class="fa fa-plus"></i></button></td>
                    </tr>
                </table><br>

                <div class="w3-container w3-padding-16">
                    <button class="w3-button w3-green transmission" id="saveTeam" type="submit" name="saveDocument">
                        Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
