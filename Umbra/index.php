<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="SideMenu.css">
    <link rel="stylesheet" type="text/css" href="bootstrap.css">
    <link href="https://fonts.googleapis.com/css?family=Gloria+Hallelujah|Source+Sans+Pro:400i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Bangers" rel="stylesheet">
    <script src="jquery-3.2.0.min.js"></script>
    <script type="text/javascript" src="coreApp.js"></script>
    <script type="text/javascript" src="tabManagment.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-Side menu icons-->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="img/network/favicon.png"/>
    <title>Umbra: The Game Scenario Designer</title>


</head>
<body onload="mainTab();">
<?php include("menu.php") ?>


<!-- HOME SCREEN ASSETS -->


<h1>Umbra: The Game Scenario Designer</h1>
<div style="position: fixed;
    font-family: 'Bangers', cursive;
    font-size: 2em;
    letter-spacing: 1px;
    text-shadow: 2px 1px rgb(206, 207, 212);
    opacity: 0.7;
    left: 28%;
    top: 60%;">Design your own Interactive Game Scenarios and evaluate them.</div>
<div id="buttons">
    <button class="button  save" id="saveData" onclick="sevaga();" href="#" ><span>Save Project</span></button>
    <button class="button  arrow2" id="newData" onclick='insertName("load");'><span>New Story Project</span></button>
    <button class="button  load" type="file"  class="button" id="loadData" onclick="loadaga();"><span> Load Project</span></button>
    <input type="file" id="upload" />
</div>
<div class="namesPane" id="newProjectPane">
    <span>New Project Name</span>
    <br>
    <table style="margin: auto">
        <tr><td> <input id="name" type="text" name="name" value="New Project"></td></tr>

    </table>
    <input type="button" value="save" onclick="insertName('save');" id="nodeSaveButton" />
    <input type="button" value="cancel" onclick="insertName('cancel');" id="nodeCancelButton" />
</div>
<div class="namesPane" id="newCharPane">
    <span>New Character Model Name</span>
    <br>
    <table style="margin: auto">
        <tr><td> <input id="cname" type="text" name="name" value="New Project"></td></tr>

    </table>
    <input type="button" value="save" onclick="insertCharName('save');" id="CnodeSaveButton" />
    <input type="button" value="cancel" onclick="insertCharName('cancel');" id="CnodeCancelButton" />
</div>
<div class="namesPane" id="newStoryPane">
    <span>New Story Flow Chart Name</span>
    <br>
    <table style="margin: auto">
        <tr><td> <input id="sfname" type="text" name="name" value="New Project"></td></tr>

    </table>
    <input type="button" value="save" onclick="insertFlowName('save');" id="sFnodeSaveButton" />
    <input type="button" value="cancel" onclick="insertFlowName('cancel');" id="sFnodeCancelButton" />
</div>
<footer>
    <!--p>Made by: <br> Nick Baf (Bafatakis)</p-->
    <p>Made with <img src="img/network/heart.png" alt="love"  style="width:15px;height:15px;"> by:<br> Nick Baf(Bafatakis) in SKG.</p>
</footer>
</body>
</html>
