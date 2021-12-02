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
    <script type="text/javascript" src="metricsCalc.js"></script>
    <script type="text/javascript" src="Chart.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-Side menu icons-->
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="img/network/favicon.png"/>
    <title>Umbra: Game Scenario Designer</title>


</head>
<body onload="init('compare');" >
<?php include("menu.php") ?>
<!-- HOME SCREEN ASSETS -->
<!--script>
    var colors = new Array(
        [62,35,255],
        [60,255,60],
        [255,35,98],
        [45,175,230],
        [255,0,255],
        [255,128,0]);

    var step = 0;
    //color table indices for:
    // current color left
    // next color left
    // current color right
    // next color right
    var colorIndices = [0,1,2,3];

    //transition speed
    var gradientSpeed = 0.002;

    function updateGradient()
    {

        if ( $===undefined ) return;

        var c0_0 = colors[colorIndices[0]];
        var c0_1 = colors[colorIndices[1]];
        var c1_0 = colors[colorIndices[2]];
        var c1_1 = colors[colorIndices[3]];

        var istep = 1 - step;
        var r1 = Math.round(istep * c0_0[0] + step * c0_1[0]);
        var g1 = Math.round(istep * c0_0[1] + step * c0_1[1]);
        var b1 = Math.round(istep * c0_0[2] + step * c0_1[2]);
        var color1 = "rgb("+r1+","+g1+","+b1+")";

        var r2 = Math.round(istep * c1_0[0] + step * c1_1[0]);
        var g2 = Math.round(istep * c1_0[1] + step * c1_1[1]);
        var b2 = Math.round(istep * c1_0[2] + step * c1_1[2]);
        var color2 = "rgb("+r2+","+g2+","+b2+")";

        $('#gradient').css({
            background: "-webkit-gradient(linear, left top, right top, from("+color1+"), to("+color2+"))"}).css({
            background: "-moz-linear-gradient(left, "+color1+" 0%, "+color2+" 100%)"});

        step += gradientSpeed;
        if ( step >= 1 )
        {
            step %= 1;
            colorIndices[0] = colorIndices[1];
            colorIndices[2] = colorIndices[3];

            //pick two new target color indices
            //do not pick the same as the current one
            colorIndices[1] = ( colorIndices[1] + Math.floor( 1 + Math.random() * (colors.length - 1))) % colors.length;
            colorIndices[3] = ( colorIndices[3] + Math.floor( 1 + Math.random() * (colors.length - 1))) % colors.length;

        }
    }

    setInterval(updateGradient,100);
</script-->
<div id="gradient" ></div>
<?php include("theMetrics.php") ?>
<!--table id="theTable">
    <tr>
        <td>
<table class="metricTable" style="margin:auto;">
    <tr>
        <th class="bigone">Re-playability</th>
    </tr>
    <tr>
        <td >Number of Choices- NoC</td>
        <td id="noc">NaN</td>
    </tr>
    <tr>
        <td >Number of Ends- NoE</td>
        <td id="noe">NaN</td>
    </tr>
</table>
        </td>
        <td>
<table class="metricTable" style="margin:auto;">
    <tr>
        <th class="bigone">Interactivity</th>
    </tr>
    <tr>
        <td >Number of Importand Choices- NIC</td>
        <td id="nic">NaN</td>
    </tr>
    <tr>
        <td >Average Choice Importance- ACI</td>
        <td id="aci">NaN</td>
    </tr>
    <tr>
        <td >Average Paths after Importand Choices -APIC</td>
        <td id="apic">NaN</td>
    </tr>
</table>
        </td>
        <td>
            <table class="metricTable" style="margin:auto;">
                <tr>
                    <th class="bigone">Characters' Interaction</th>
                </tr>
                <tr>
                    <td >Characters Coupling Factor- CCF</td>
                    <td id="ccf">NaN</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table class="metricTable" style="margin:auto;">
                <tr>
                    <th class="bigone">Content</th>
                </tr>
                <tr>
                    <td >Number of Actions- NoA</td>
                    <td id="noa">NaN</td>
                </tr>
                <tr>
                    <td >Number of Choices- NoC</td>
                    <td id="noc2">NaN</td>
                </tr>
                <tr>
                    <td >Number of Goals- NoG</td>
                    <td id="nog">NaN</td>
                </tr>
            </table>
        </td>
        <td>
            <table class="metricTable" style="margin:auto;">
                <tr>
                    <th class="bigone">Achieved Curiosity</th>
                </tr>
                <tr>
                    <td >Number of Choices- NoC</td>
                    <td id="noc3">NaN</td>
                </tr>
                <tr>
                    <td >Number of Ends- NoE</td>
                    <td id="noe2">NaN</td>
                </tr>
                <tr>
                    <td >Average Paths to Happy Endings- APHE</td>
                    <td id="aphe">NaN</td>
                </tr>
            </table>
        </td>
        <td>
            <table class="metricTable" style="margin:auto;">
                <tr>
                    <th class="bigone">Desirability</th>
                </tr>
                <tr>
                    <td >Number of Actions- NoA</td>
                    <td id="noa2">NaN</td>
                </tr>
                <tr>
                    <td >Average Paths after Choices- APC</td>
                    <td id="apc">NaN</td>
                </tr>
                <tr>
                    <td >Average Distance Between Choices- ADbC</td>
                    <td id="adbc">NaN</td>
                </tr>
            </table>
        </td>


    </tr>
</table-->


<!--div id="chart1" class="camvas2d">
    <canvas id="myChart"></canvas>

</div>
<div id="chart2" class="camvas2d">
    <canvas id="myChart2"></canvas>

</div-->

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
    <p>Made by: <br> Nick Baf (Bafatakis)</p>
    <!--p>Made with <img src="img/network/heart.png" alt="love"  style="width:15px;height:15px;"> by:<br> Nick Baf(Bafatakis) in SKG.</p-->
</footer>
</body>
</html>
