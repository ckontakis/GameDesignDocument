<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://fonts.googleapis.com/css?family=Gloria+Hallelujah|Source+Sans+Pro:400i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Bangers" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="SideMenu.css">
    <link rel="stylesheet" type="text/css" href="bootstrap.css">
    <link rel="shortcut icon" type="image/png" href="img/network/favicon.png"/>
    <title>Umbra: Game Scenario Designer</title>



    <script type="text/javascript" src="vis.js"></script>
    <script src="jquery-3.2.0.min.js"></script>
    <script type="text/javascript" src="coreApp.js"></script>
    <script type="text/javascript" src="tabManagment.js"></script>
    <script type="text/javascript" src="metricsCalc.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link href="vis-network.min.css" rel="stylesheet" type="text/css" />
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">



</head>
<body onload="init('char');">
<?php
include("menu.php") ?>
<?php include("sideNavChar.php") ?>

<div id="charactermodel"></div>
<?php include("characterModelpopup.php")?>

<?php include("about.php") ?>
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
</body>
</html>
