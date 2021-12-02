<span id="spanNav" style="cursor:pointer" onclick="openNav()">
        <!--img src="img/network/gear.png" alt="" align="left"  style="width:35px;height:35px;"></span-->

<div id="mySidenav" class="sidenav">
    <!--p class="sidenavTitle" id="title">Customizer</p>
    <p class="sidenavTitle" id="sidetitle">Customize and view your Network prefrences</p>
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a-->
    <!--div class="line-separator"></div-->
    <span><a href="#" onclick="aboutBaf('open');" style=" cursor: help;">About</a>
    <a id="metricsButton" href="#">Network Physics</a>
    <a onclick="deleteTab();" id="tabDel" style="color:#ff0003; cursor:pointer;">Delete</a>
        </span>
    <br>

    <input type="button" value="Fork Weight" onclick="sideWeight();" />
    <!--input type="button" value="Delete Tab" onclick="deleteTab();" id="tabDel" /-->
    <!--?php include("characterModelpopup.php")?-->
    <div id="pricing-table" class="clear">
        <div class="plan" id="most-popular">
            <!--TA H3 diaxeirizontai apo js-->
            <h3 id="h3choices">Number of Choices-NoC <span ><div id="choices">NaN</div></span></h3>
        </div>
        <div class="plan" id="most-popular">
            <h3 id="h3actions">Number of Actions-NoA <span ><div id="actions">NaN</div></span></h3>
        </div>
        <div class="plan" id="most-popular">
            <h3 id="h3ends">Number of Ends-NoE   <br></br><span ><div id="ends">NaN</div></span></h3>
        </div>
    </div>
</div>


<!--script>
    var colors = new Array(
        [22,175,202],
        [20,255,60],[20,255,60],[240,240,250],[22,175,202]);

    var step = 0;
    //color table indices for:
    // current color left
    // next color left
    // current color right
    // next color right
    var colorIndices = [0,1,2,3];

    //transition speed
    var gradientSpeed = 0.005;

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
        var color1 = "rgba("+r1+","+g1+","+b1+","+0+")";

        var r2 = Math.round(istep * c1_0[0] + step * c1_1[0]);
        var g2 = Math.round(istep * c1_0[1] + step * c1_1[1]);
        var b2 = Math.round(istep * c1_0[2] + step * c1_1[2]);
        var color2 = "rgb("+r2+","+g2+","+b2+")";

        $('#gradient').css({
            background: "-webkit-gradient(linear, left top, left bottom, from("+color1+"), to("+color2+"))"}).css({
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