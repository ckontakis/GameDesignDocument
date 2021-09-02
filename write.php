<?php

require 'connect.php'; // connecting to database
$con = $_SESSION["conn"]; // variable that connected to database

/*
 * If user is not connected we redirect user to write page
 */
if (!isset($_SESSION['logged_in'])) {
    header('Location: write-login.php');
}

$person_ID = $_SESSION["id"]; // Getting the id of user

$duplicateDoc = false; // boolean variable to show and hide error message for duplicate documents
$genericErrDoc = false; // boolean variable to show and hide generic error message for documents

/*
 * Actions when user creates a new document
 */
if (isset($_POST['saveDocument'])) {
    $name = $_POST['nameDocument'];
    if (empty($_POST['nameDocument'])) {
        $err = "Fill the name field for the document";
    } else {

        $query = "INSERT INTO document (name) 
                VALUES ('$name')";

        if (mysqli_query($con, $query)) {

            $document_last_id = mysqli_insert_id($con);

            if (!$con->query("INSERT INTO world_building (DOCUMENT_ID) VALUES ('$document_last_id');")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $world_building_last_id = mysqli_insert_id($con);

            if (!$con->query("INSERT INTO mechanics (DOCUMENT_ID, combat) 
                        VALUES ('$document_last_id',NULL);")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $mechanics_last_id = mysqli_insert_id($con);

            if (!$con->query("INSERT INTO game_summary (DOCUMENT_ID,name, concept, genre, audience, system, type, setting, software, game_code) 
                        VALUES ('$document_last_id',NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $summary_last_id = mysqli_insert_id($con);
            
            if (!$con->query("INSERT INTO physics (MECH_ID,environment, weather, climate, humidity, gravity, lethality, simulations, particles, ragdoll) 
                        VALUES ('$mechanics_last_id',NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $physics_last_id = mysqli_insert_id($con);

            // Creating game elements row
            if (!$con->query("INSERT INTO game_elements (WORLD_BUILDING_ID,story_describe) VALUES ('$world_building_last_id',NULL);")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $game_elements_last_id = mysqli_insert_id($con);

            // Creating assets row
            if (!$con->query("INSERT INTO assets (WORLD_BUILDING_ID,describe_music) VALUES ('$world_building_last_id',NULL);")) {
                echo "Error: " . "<br>" . $con->error;
            }
            $assets_last_id = mysqli_insert_id($con);

            $query = "INSERT INTO person_edits_document (PERSON_ID, DOCUMENT_ID, status_of_invitation, isAdmin) 
                VALUES ('$person_ID', '$document_last_id', 'accepted', '1')";

            if (!mysqli_query($con, $query)) {
                echo "Error: " . $query . "<br>" . $con->error;
            }

            $docRoot = $_SERVER["DOCUMENT_ROOT"];

            mkdir("$docRoot/ImagesFromUsers-GDD/$name/WorldBuilding/Characters", 0755 ,true);
            mkdir("$docRoot/ImagesFromUsers-GDD/$name/WorldBuilding/Objects", 0755 ,true);
            mkdir("$docRoot/ImagesFromUsers-GDD/$name/WorldBuilding/Locations", 0755 ,true);

            header('Location:write.php');

        }else{
            // we are checking if there is already a document with that name
            $resultAllDocs = mysqli_query($con, "SELECT name FROM document;");

            while($rowDocs = $resultAllDocs->fetch_assoc()){
                if($rowDocs["name"] === $name){
                    $duplicateDoc = true;
                }
            }

            if(!$duplicateDoc){ // if there is not duplicate document we show a generic error message
                $genericErrDoc = true;
            }
        }

    }
}

/*
 * Actions when user deletes a document
 */
if (isset($_POST['deleteDocument'])) {
    $idOfDocumentToDel = $_POST['keyIdDocument'];
    $queryDeleteDocument = "DELETE FROM document WHERE ID='$idOfDocumentToDel';";

    if ($con->query($queryDeleteDocument)) {
        header('Location:write.php');
    } else {
        echo "<script>alert('Something went wrong. Cannot delete document.')</script>";
        echo "Error: " . $queryDeleteDocument . "<br>" . $con->error;
    }
}

if (isset($_POST['addEditor'])) {
    $emailInvite = test_data($_POST['emailTeamMember']);
    $docInvite = $_POST['keyDocID'];
    if (isset($_POST['adminCheck'])) {
        $adminInv = '1';
    } else {
        $adminInv = '0';
    }

    // finding id of person to invite

    $queryToFindIdPerson = "SELECT ID FROM person WHERE email = '$emailInvite'";
    $resultFindPerson = $con->query($queryToFindIdPerson);

    $rowFindPerson = $resultFindPerson->fetch_assoc();
    if (isset($rowFindPerson['ID'])) {
        $idOfPersonToInvite = $rowFindPerson['ID'];
        $query = "INSERT INTO person_edits_document (PERSON_ID, DOCUMENT_ID,isAdmin) VALUES ('$idOfPersonToInvite','$docInvite','$adminInv')";
        if (mysqli_query($con, $query)) {
            echo "<script>alert('Invitation succeeded!')</script>";
        } else {
            echo "<script>alert('Invitation failed.')</script>";
        }
    } else {
        echo "<script>alert('Invitation failed: The given email does not match with any user.')</script>";
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
    <title>Write GDD - GDD Maker</title>
    <link rel="icon" href="Images/favicon-new.ico">
    <script src="JavaScript/Main.js"></script>
    <script src="JavaScript/WorldBuilding.js"></script>
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

<button class="w3-button w3-blue w3-xlarge showSideBar" onclick="showElement('sideBar')"><i class="fa fa-bars"></i>
</button>

<div class="container writePageContent">
    <button class="w3-btn w3-round w3-border w3-border-blue w3-hover-blue transmission" type="button"
            onclick="showElement('newDocument-modal')">Create a new Game Design Document
    </button>
    <br><br>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="text-align: center;">
        <div id="newDocument-modal" class="w3-modal">
            <div class="w3-modal-content w3-animate-zoom">
                <div class="w3-container">
                        <span onclick="hideElement('newDocument-modal')" class="w3-button
                        w3-display-topright w3-hover-red"><i class="fa fa-close"></i></span>
                    <h3 class="headerForModal">Create a document</h3><br>

                    <label for="nameDocument" class="w3-margin-top" id="labelNameDocument">Type the name of the document
                        *</label><br>
                    <input class="w3-input w3-border w3-margin-top" type="text" id="nameDocument" name="nameDocument"
                           required><br>

                    <div class="w3-container w3-padding-16">
                        <button class="w3-button w3-green transmission" id="saveDocument" type="submit"
                                name="saveDocument">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <?php
    $query = "SELECT DOCUMENT_ID FROM person_edits_document WHERE PERSON_ID ='$person_ID' AND status_of_invitation='accepted' ORDER BY DOCUMENT_ID ASC";
    $resultDocForeign = mysqli_query($con, $query);

    ?>
    <table class="w3-table w3-border w3-centered w3-striped w3-margin-top" id="tableGDD">
        <tr>
            <th>Game Design Document</th>
            <th>Edit</th>
            <th>Delete</th>
            <th>Print</th>
            <th>Teams</th>
        </tr>

        <?php
        if (mysqli_num_rows($resultDocForeign) >= 1) {
            while ($rowDocForeign = mysqli_fetch_assoc($resultDocForeign)) {
                $doc_id = $rowDocForeign['DOCUMENT_ID'];
                $query = "SELECT * FROM document WHERE ID ='$doc_id' ORDER BY ID ASC";
                $resultDocument = mysqli_query($con, $query);
                if (mysqli_num_rows($resultDocument) == 1) {
                    $rowDocument = $resultDocument->fetch_assoc();

                    ?>
                    <tr>
                        <td><?php echo $rowDocument["name"] ?></td>
                        <td>
                            <div class="w3-dropdown-hover">
                                <button class="w3-button w3-round w3-border w3-border-black transmission"
                                        id="edit<?php echo $rowDocument['ID'] ?>" type="button"
                                        name="btnEdit<?php echo $rowDocument['ID'] ?>"><i class="fa fa-edit"></i>
                                </button>
                                <div class="w3-dropdown-content w3-bar-block w3-border">
                                    <?php echo '<a href="Mechanics/summary.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-border-bottom w3-center transmission">Summary</a>'; ?>
                                    <button class="w3-bar-item w3-button w3-center transmission"
                                            onclick="showCategories('mechCat<?php echo $rowDocument['ID'] ?>', 'mechCatDown<?php echo $rowDocument['ID'] ?>')">
                                        Mechanics <i id="mechCatDown<?php echo $rowDocument['ID'] ?>"
                                                     class="fa fa-chevron-down"></i></button>
                                    <div id="mechCat<?php echo $rowDocument['ID'] ?>" class="catButton">
                                        <?php echo '<a href="Mechanics/mech.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Mechanics</a>'; ?>
                                        <?php echo '<a href="Mechanics/gameplay.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Gameplay</a>'; ?>
                                        <?php echo '<a href="Mechanics/GuiMenus.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Menus and Gui</a>'; ?>
                                    </div>
                                    <button class="w3-bar-item w3-button w3-border-top w3-center transmission"
                                            onclick="showCategories('worldCat<?php echo $rowDocument['ID'] ?>', 'worldCatDown<?php echo $rowDocument['ID'] ?>')">
                                        World Building <i id="worldCatDown<?php echo $rowDocument['ID'] ?>"
                                                          class="fa fa-chevron-down"></i></button>
                                    <div id="worldCat<?php echo $rowDocument['ID'] ?>" class="catButton">
                                        <?php echo '<a href="WorldBuilding/GameElementsWorld.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Game Elements</a>'; ?>
                                        <?php echo '<a href="WorldBuilding/AssetsWorld.php?id=' . $rowDocument['ID'] . '" class="w3-bar-item w3-button w3-center transmission">Assets</a>'; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <button class="w3-button w3-round w3-border w3-border-black transmission"
                                    <?php
                                    $nameOfDocument = $rowDocument['name'];
                                    echo "onclick=\"return  confirm('Are you sure that you want to delete $nameOfDocument document?')\"";
                                    ?>
                                        type="submit" name="deleteDocument"><i class="fa fa-trash"></i></button>
                                <input type="hidden" name="keyIdDocument" value="<?php echo $rowDocument['ID']; ?>"/>
                            </form>

                        </td>
                        <td>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <a href="print.php?id=<?php echo $rowDocument['ID']; ?>" class="w3-button w3-round w3-border w3-border-black transmission" type="submit"
                                        id="download"><i class="fa fa-print"></i></a>
                                <input type="hidden" name="keyIdDocument" value="<?php echo $rowDocument['ID']; ?>"/>
                            </form>
                        </td>

                        <td>
                            <button class="w3-button w3-round w3-border w3-border-black transmission"
                                    onclick="showElement('<?php echo $rowDocument['ID']; ?>')" type="button"><i
                                        class="fa fa-users"></i></button>
                        </td>
                    </tr>

                    <?php
                }
            }
        }
        ?>
    </table>
    <br>

    <div class="w3-panel w3-red" <?php if($duplicateDoc) {
        echo 'style="display: block"';
    }else{
        echo 'style="display: none"';
    }?>>
        <p>There is already a document with that name. Please name your document with another name.</p>
    </div>

    <div class="w3-panel w3-red" <?php if($genericErrDoc) {
        echo 'style="display: block"';
    }else{
        echo 'style="display: none"';
    }?>>
        <p>Error: cannot create a new document.</p>
    </div>


    <?php
    $queryFindAllIdOfDoc = "SELECT * FROM person_edits_document WHERE PERSON_ID='$person_ID' AND status_of_invitation='accepted';";
    $resultDocTeam = $con->query($queryFindAllIdOfDoc);

    while ($rowResDocTeam = $resultDocTeam->fetch_assoc()) {
    $idOfDocumentTeams = $rowResDocTeam['DOCUMENT_ID'];

    echo "<div id=\"$idOfDocumentTeams\" class=\"w3-modal\">
            <div class=\"w3-modal-content w3-animate-zoom\" style=\"text-align: center;\">
                <div class=\"w3-container\">
                            <span onclick=\"hideElement('$idOfDocumentTeams')\" class=\"w3-button
                            w3-display-topright w3-hover-red\"><i class=\"fa fa-close\"></i></span>
    
                    <h3 class=\"headerForModal\">Edit teams and users that can edit the document</h3><br>";
    ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

        <?php
        echo "<input type=\"hidden\"  name=\"keyDocID\"  value=\"$idOfDocumentTeams\" />
    
        <label for=\"emailTeamMember$idOfDocumentTeams\" class=\"w3-margin-top\" id=\"labelEmailMem$idOfDocumentTeams\">Invite a person to edit the document</label><br>
    
        <input style=\"display: block;\" class=\"w3-input w3-border w3-margin-top\" type=\"email\"
               placeholder=\"Type the email of the person that you want to invite\"
               id=\"emailTeamMember$idOfDocumentTeams\" name=\"emailTeamMember\" required>";

        if ($rowResDocTeam['isAdmin'] === '1') {
            echo "<input class=\"w3-check w3-margin-top\" id=\"adminCheck$idOfDocumentTeams\" name=\"adminCheck\" type=\"checkbox\">
        <label for=\"adminCheck$idOfDocumentTeams\">Admin of document</label><br>";
        }


        echo "<button type=\"submit\" name=\"addEditor\" class=\"w3-button w3-border w3-margin-top w3-green transmission\">
            Add</button><br><br>
            
        <label>Editors of the document</label>
    
        <table class=\"w3-table w3-border w3-centered w3-striped w3-margin-top\">
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Invitation</th>
            </tr>";

        $queryAllEditors = "SELECT * FROM person_edits_document WHERE DOCUMENT_ID='$idOfDocumentTeams' 
                                      AND (status_of_invitation='accepted' OR status_of_invitation='pending');";
        $resultEditors = $con->query($queryAllEditors);

        while ($rowEditors = $resultEditors->fetch_assoc()) {
            $idOfEditor = $rowEditors['PERSON_ID'];

            $queryFindInfoOfEditor = "SELECT name, surname, email FROM person WHERE ID='$idOfEditor';";
            $resultInfoEditors = $con->query($queryFindInfoOfEditor);
            $rowInfoEditor = $resultInfoEditors->fetch_assoc();

            echo "<tr> <td>" . $rowInfoEditor["name"] . "</td>";
            echo " <td>" . $rowInfoEditor["surname"] . "</td>";
            echo " <td>" . $rowInfoEditor["email"] . "</td>";

            if ($rowEditors["isAdmin"] === '1') {
                echo " <td><span class='w3-text-green'>Yes</span></td>";
            } else {
                echo " <td><span class='w3-text-red'>No</span></td>";
            }

            if ($rowEditors["status_of_invitation"] === 'accepted') {
                echo " <td><span class='w3-text-green'>Accepted</span></td> </tr>";
            } else {
                echo " <td><span class='w3-text-orange'>Pending</span></td> </tr>";
            }
        }
        echo "</table><br>
    
        <label>Teams that can edit the document</label>
        <table class=\"w3-table w3-border w3-centered w3-striped w3-margin-top\">
            <tr>
                <th>Teams</th>
                <th>Add</th>
            </tr>
            <tr>
                <td>Team 1</td>
                <td><button class=\"w3-button w3-green w3-circle transmission\" type=\"button\" name=\"btnAddTeam\"><i class=\"fa fa-plus\"></i></button></td>
            </tr>
        </table><br>
        </form>
    </div>
    </div>
    </div>";
        }
        ?>
</div>
</body>
</html>
