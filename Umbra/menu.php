<div id="blackout" style='display:none;'></div>
<ul  class="dark-primary-color" id="topNav">
    <li id="topMenu"><img src="img/network/logoAlpha.png" alt="" align="left"  style="width:25px;height:25px;">Umbra</li>
    <li id="projectName">Untitled Project</li>
    <li id="tooltipSideNav" style="display:none">Customizer -></li>
</ul>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-129449430-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-129449430-1');
</script>


<!-- MENU BAR -->

<!--ul id="nav">
    <li><a id="home" class="active" href="main.html"><img src="img/network/home.png" alt="" align="left" style="width:25px;height:25px;">Home</a></li>
    <li><a id="charModel" href="charactermodel.html"><img src="img/network/charIco.png" alt="" align="left" style="width:35px;height:39px;">Character Model</a></li>
    <li><a id="storyModel" href="flowchart.html"><img src="img/network/diamond.png" alt="" align="left" style="width:25px;height:22px;">Story Flow Chart</a></li>
</ul-->
<div class="tw-bs">
    <div class="nav-side-menu text-primary-color">
        <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>

        <div class="menu-list">

            <ul id="menu-content" class="menu-content collapse out">
                <li id="dashboard">
                    <a   href="index.php">
                        <img src="img/network/home.png" alt="" align="bottom" style="width:30px;height:30px; padding-left: 3px;"> Dashboard
                    </a>
                </li>

                <li  id="tab1" data-toggle="collapse" data-target="#characterModel" class="collapsed">
                    <a href="#"><img src="img/network/charIco.png" alt="" align="bottom" style="width:38px;height:39px; padding-left: 3px; padding-right: 3px;">Character Model <span class="arrow"></span></a>
                </li>

                <ul class="sub-menu collapse" id="characterModel">

                    <li id="cOriginal">
                    <span>
                        <a  class="fa fa-plus"></a>
                        <a href="#"  onclick='insertCharName("load");'> Character Model</a>
                        </span>
                    </li>
                </ul>


                <li id="tab2" data-toggle="collapse" data-target="#storyflow" class="collapsed">
                    <a href="#"><img src="img/network/diamond.png" alt="" align="bottom" style="width:35px;height:28px; padding-left: 3px; padding-right: 3px;"> Story Flow Chart <span class="arrow"></span></a>
                </li>


                <ul class="sub-menu collapse" id="storyflow">
                        <li id="sOriginal">
                            <span>
                                <a class="fa fa-plus"></a>
                            <a href="#"   onclick='insertFlowName("load");'> Story Flow Chart Model</a>
                                </span>
                        </li>

                </ul>


                <li id="tab3" data-toggle="collapse" data-target="#metrics" class="collapsed">
                    <a href="#"><img src="img/network/mush.png" alt="" align="bottom" style="width:35px;height:28px; padding-left: 3px; padding-right: 3px;">  Metrics <span class="arrow"></span></a>
                </li>
                <ul class="sub-menu collapse" id="metrics">
                    <li id="compare">
                        <span>
                            <a class="fa fa-check-square-o" ></a>
                        <a href="metricsCompared.php"  > Compare Projects</a>
                            </span>
                    </li>
                </ul>

            </ul>
        </div>
    </div>
</div>
<!-- END OF MENU BAR -->