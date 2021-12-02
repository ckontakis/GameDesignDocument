/**
 * Untitles Thesis App JavaScript for the Core App
 *
 * Copyright Nick Baf (Bafatakis) 2017. Untitled Thesis App.
 *
 * nickbaf@icloud.com
 */


//Global Variables
var cNodes = null; //global variables
var cEdges = null;
var charNetwork = null;

var nodes = null; //global variables
var edges = null;
var network = null;

var count;
var data;
var seed = 2;

var modelName;

var charModels=[];
var storyModels=[];
var metrics=[];
//FILE IO
var reader = new FileReader();
var forkWeights=[];


/**
 * Function that handles uploaded file
 * @param e the file
 */
reader.onload = function(e) {
    /**
     * FILE STRUCTURE
     * 0:       Number of char models (n)
     * 1:       Character Model 1 Name
     * 2:       Character Model 1 Data
     *              ''-''
     * 3:       Number of story models (s)
     * 4:       Story Model 1 Name
     * 5:       Story Model 1 Data
     *              ''-''
     * 6:       Project Name
     *
     */
    try {
        sessionStorage.clear();
        var text = reader.result;
        var storage = JSON.parse(text);
        //sessionStorage.setItem("storyData",storage[0]);
        // alert(storage[0]);
        var c = storage[0];
        var charModels = [];
        var storyModels = [];
        var metrics=[];
        var step = 1;
        for (i = 0; i < c; i++) {
           // alert(storage[step]);
            charModels.push(storage[step]);
            if (storage[step + 1] != null) {
                sessionStorage.setItem("charData" + storage[step], storage[step + 1]);
            }
            step += 2;
        }
        var s = storage[step];
        //alert("story "+s);
        step++;
        for (i = 0; i < s; i++) {
          //  alert(storage[step]);
            storyModels.push(storage[step]);
            metrics.push(storage[step]);
            if (storage[step + 1] != null) {
                sessionStorage.setItem("storyData" + storage[step], storage[step + 1]);
            }
            step += 2;
        }
        sessionStorage.setItem("charModels", JSON.stringify(charModels));
        sessionStorage.setItem("storyModels", JSON.stringify(storyModels));
        sessionStorage.setItem("metrics", JSON.stringify(metrics));
        sessionStorage.setItem("ProjectTitle", storage[step]);
        mainTab();
        alert("Load Successful");
        window.location.reload();
    }catch(err){
        alert(err+"\nin file handling function");
        sessionStorage.clear();
        window.location.reload();
    }
}
//*************

function check_cookie_name(name)
{
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) {
        return JSON.parse(decodeURIComponent(match[2]));
    }
    else{
        console.log('--something went wrong---');
    }
}

function loadUmbraFile() {
    /**
     * FILE STRUCTURE
     * 0:       Number of char models (n)
     * 1:       Character Model 1 Name
     * 2:       Character Model 1 Data
     *              ''-''
     * 3:       Number of story models (s)
     * 4:       Story Model 1 Name
     * 5:       Story Model 1 Data
     *              ''-''
     * 6:       Project Name
     *
     */
    console.log('Umbra file loaded!');
    try {
        sessionStorage.clear();
        let gddName = check_cookie_name("current_gdd");
        let umbraFile = check_cookie_name(gddName["name"] + "_gdd_umbra");
        console.log(gddName["name"] + "_gdd_umbra");
        console.log(gddName["name"]);
        // console.log(umbraFile);
        // var storage = JSON.parse(umbraFile);
        var storage = umbraFile;
        var c = storage[0];
        var charModels = [];
        var storyModels = [];
        var metrics=[];
        var step = 1;
        for (i = 0; i < c; i++) {
            // alert(storage[step]);
            charModels.push(storage[step]);
            if (storage[step + 1] != null) {
                sessionStorage.setItem("charData" + storage[step], storage[step + 1]);
            }
            step += 2;
        }
        var s = storage[step];
        //alert("story "+s);
        step++;
        for (i = 0; i < s; i++) {
            //  alert(storage[step]);
            storyModels.push(storage[step]);
            metrics.push(storage[step]);
            if (storage[step + 1] != null) {
                sessionStorage.setItem("storyData" + storage[step], storage[step + 1]);
            }
            step += 2;
        }
        sessionStorage.setItem("charModels", JSON.stringify(charModels));
        sessionStorage.setItem("storyModels", JSON.stringify(storyModels));
        sessionStorage.setItem("metrics", JSON.stringify(metrics));
        sessionStorage.setItem("ProjectTitle", storage[step]);
        mainTab();
        alert("Load Successful");
        window.location.reload();
    }catch(err){
        console.log(err);
        // sessionStorage.clear();
        window.location.reload();
    }
}

/**
 * Left menu bar functions.
 */
$(function () {
    $('.navbar-toggle').click(function () {
        $('.navbar-nav').toggleClass('slide-in');
        $('.side-body').toggleClass('body-slide-in');
        $('#search').removeClass('in').addClass('collapse').slideUp(200);

        /// uncomment code for absolute positioning tweek see top comment in css
        //$('.absolute-wrapper').toggleClass('slide-in');

    });

    // Remove menu for searching
    $('#search-trigger').click(function () {
        $('.navbar-nav').removeClass('slide-in');
        $('.side-body').removeClass('body-slide-in');

        /// uncomment code for absolute positioning tweek see top comment in css
        //$('.absolute-wrapper').removeClass('slide-in');

    });
});




//Character Model script
/**
 * Function for creating the network for a Character model;
 */
function charDraw() {
    destroy();
    cNodes = [];
    cEdges = [];

    // create a network
    var container = document.getElementById('charactermodel');
    var options = {
        physics:{
            barnesHut: {
                gravitationalConstant: -5000,
                centralGravity: 0.3,
                springLength: 350,
                springConstant: 0.14,
                damping: 0.09,
                avoidOverlap: 0.5,
            }

        },
        edges : {
            arrows : {
                to : {
                    enabled : false,
                    scaleFactor : 1,
                    type : 'arrow'
                },
                middle : {
                    enabled : false,
                    scaleFactor : 1,
                    type : 'arrow'
                },
                from : {
                    enabled : false,
                    scaleFactor : 1,
                    type : 'arrow'
                }
            },
        },
        locale : 'en',//,
        manipulation : {
            addNode : function(data, callback) {
                // filling in the popup DOM elements
                openNav();
                document.getElementById('nodeOperation').innerHTML = "Add Node";
                document.getElementById('node-id').value = data.id;
                document.getElementById('node-id').disabled=true;
                document.getElementById('nodeSaveButton').onclick = saveCharNodeData.bind(this, data, callback);
                document.getElementById('nodeCancelButton').onclick = clearPopUp.bind(this);
                document.getElementById('node-popUp').style.display = 'block';
                //document.getElementById('node-popUp').style.top = screen.height;
                //document.getElementById('node-popUp').style.left = screen.width;
            },
            editNode : function(data, callback) {
                // filling in the popup DOM elements
                openNav();
                document.getElementById('nodeOperation').innerHTML = "Edit Node";
                document.getElementById('node-id').value = data.id;
                document.getElementById('node-name').value = data.charName;
                document.getElementById('node-thoughts').value = data.thoughts;
                document.getElementById('nodeSaveButton').onclick = saveCharNodeData.bind(this, data, callback);
                document.getElementById('nodeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('node-popUp').style.display = 'block';
            },
            addEdge : function(data, callback) {
                openNav();
                document.getElementById('edgeOperation').innerHTML = "Add Edge";
                document.getElementById('edge-id').value = data.id;
                document.getElementById('edge-label').value = data.label;
                document.getElementById('edgeSaveButton').onclick = saveCharEdgeData.bind(this, data, callback);
                document.getElementById('edgeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('edge-popUp').style.display = 'block';

            },
            editEdge : function (data,callback) {
                // filling in the popup DOM elements
                openNav();
                document.getElementById('edgeOperation').innerHTML = "Edit Edge";
                document.getElementById('edge-id').value = data.id;
                document.getElementById('edge-label').value = data.label;
                document.getElementById('edgeSaveButton').onclick = saveCharEdgeData.bind(this, data, callback);
                document.getElementById('edgeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('edge-popUp').style.display = 'block';
            },
            deleteNode: function(data,callback){
                console.log(data.nodes);
                callback(data);
                /**
                 * Searches withing cNodes to find the node that we want to delete
                 * and makes a splice to that position
                 */
                for(i=0;i<cNodes.length;i++){
                    if(cNodes[i].id==data.nodes[0]){
                        cNodes.splice(i,1);
                        console.log(cNodes.length);
                    }
                }
                /**
                 * if a node gets deleted,its edges must be deleted too
                 */
                for(i=0;i<cEdges.length;i++){
                    if(cEdges[i].from==data.nodes[0]){
                        cEdges.splice(i,1);
                    }else if(cEdges[i].to==data.nodes[0]){
                        cEdges.splice(i,1);
                    }
                }
                saveCharStorage();
                priceTable(1);
                console.log(JSON.stringify(cNodes));
            },
            deleteEdge: function (data,callback) {
                console.log(data.edges);
                callback(data);
                for(i=0;i<cEdges.length;i++){
                    if(cEdges[i].id==data.edges[0]){
                        cEdges.splice(i,1);
                    }
                }
                saveCharStorage();
                priceTable(1);
                console.log(JSON.stringify(cEdges));
            }

        }
    };
    /**
     * If our model is inside the session storage, recall the model.
     */
    if(sessionStorage.getItem("charData"+modelName)!=null){
        console.log("old session");
        var temp=JSON.parse(sessionStorage.getItem("charData"+modelName));
        charNetwork = new vis.Network(container, temp , options);
        cNodes=temp.nodes;
        cEdges=temp.edges;
    }/** If not create a new one */
    else {
        console.log("new session");
        charNetwork = new vis.Network(container, data, options);

    }
    /**
     * Function for drawing the characters goals inside the bubble.
     * After formating its thoughts(goals) into separate lines we draw its line with
     * a space between them.
     */
    charNetwork.on("afterDrawing", function (ctx) {
        var d=[0,20,20,5] //the coordinates
        ctx.font="17px Helvetica";
        for(i=0;i<cNodes.length;i++){
            var nodeId=cNodes[i].id;
            var nodePosition = charNetwork.getPositions(cNodes[i].id);
            var thought=formatThoughts(cNodes[i].thoughts).split('\n');
            /**
             * printing its line in the appropriate coordinates
             */
            for(k=0;k<4;k++){
                ctx.fillText(thought[k], nodePosition[nodeId].x + cNodes[i].size *0.23 -d[k], nodePosition[nodeId].y - cNodes[i].size * 0.72 +k*20);
            }

            ctx.fill();
        }


    });
    openNav();
    priceTable(1);

}
/**
 * Function for formating the lines.
 * @param text
 * @returns {string}
 */
function formatThoughts(text) {
    var tokens=text.split(" ");//split into separate words.
    var letters=[21,26,28,30]; //the number of letters its line holds.
    var final_text="";
    var k=0;
    var i=0;
    for(i=0;i<4;i++){
        while(letters[i]>0){
            if(k<tokens.length && (letters[i]-tokens[k].length >0)){
                letters[i]=letters[i]-tokens[k].length;
                final_text=final_text.concat(tokens[k]);
                k++; //check mhn xefigei
            }else{
                break;
            }
            final_text=final_text.concat(" ");
            letters[i]--;
        }
        final_text=final_text.concat('\n');
    }
    //alert(final_text);
    return final_text;
}
//window.onbeforeunload=function(){return "Leaving so soon??"}

/**
 * Function for saving a characters' model node
 * @param data api node
 * @param callback the api callback for saving into network
 */
function saveCharNodeData(data, callback) {
    //Attributes
    data.id = document.getElementById('node-id').value;
    data.charName = document.getElementById('node-name').value;
    data.thoughts=  document.getElementById('node-thoughts').value
    data.label=document.getElementById('node-name').value;
    data.size=150;
    data.font={
        size:20
    };
    data.shadow=false;
    data.shape="image";
    data.image="img/network/charAlpha.png";
    data.type="char";
    //data.labelHighlightBold="true"; ?????
    clearPopUp();
    callback(data);
    for(i=0;i<cNodes.length;i++){
        if(cNodes[i].id==data.id){
            cNodes.splice(i,1);
        }
    }
    /*for(var j=0;j<150;j++) { create multiple char nodes
        data.id=j;
        cNodes.push(JSON.parse(JSON.stringify(data)));
    }*/
    cNodes.push(data);
    saveCharStorage();
    priceTable(1);
    console.log(JSON.stringify(cNodes));
    console.log('@@@@@@@@@@');
    console.log('########');
}
/**
 * Function for saving a characters' model edge
 * @param data api edge
 * @param callback the api callback for saving into network
 */
function saveCharEdgeData(data, callback) {
    data.label=document.getElementById("edge-label").value;
    clearPopUp();
     data.length=500;
    if (data.from == data.to) {
        var r = confirm("Do you want to connect the node to itself?");
        if (r == true) {
            callback(data);
            for(i=0;i<cEdges.length;i++){
                if(cEdges[i].id==data.id){
                    cEdges.splice(i,1);
                }
            }
            cEdges.push(data);
        }
    } else {
        callback(data);
        for(i=0;i<cEdges.length;i++){
            if(cEdges[i].id==data.id){
                cEdges.splice(i,1);
            }
        }
        cEdges.push(data);
    }
    saveCharStorage();
    priceTable(1);
    console.log(JSON.stringify(cEdges));
    console.log('@@@@@@@@@@');
    console.log('########');

}


//SHARED FUNCTIONS between Character Model and Flow Chart


/**
 * Function for initializing the network page
 * @param type -char,the character model tabs.-story,the story tabs
 */
function init(type) {
    document.title=sessionStorage.getItem("ProjectTitle")+" Project-Umbra App"; //change the tab title
    /**
     * if a character model tab is selected, open the tab and the write the rest of the character model tabs
     * as active so if the user choses one the page wont be refreshed.Then write as normal the rest of the story tabs
     */
    //closeNav();

    if(type=="char"){
        writeActiveTabs("char");
        writeTabs("story");
        writeTabs("metrics");
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle");
        modelName=sessionStorage.getItem("modelName");
        document.getElementById("char"+modelName).className="active";
        document.getElementById("tab1").className="active";
        document.getElementById("characterModel").className="sub-menu collapse in";
        document.getElementById("characterModel").setAttribute("aria-expanded",true);
        charDraw();
    }/**
     * if a stoty model tab is selected, open the tab and the write the rest of the character model tabs
     * as active so if the user choses one the page wont be refreshed.Then write as normal the rest of the story tabs
     */
    else if(type=="story"){
        writeActiveTabs("story");
        writeTabs("char");
        writeTabs("metrics");
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle");
        modelName=sessionStorage.getItem("storyName");
        document.getElementById("story"+modelName).className="active";
        document.getElementById("tab2").className="active";
        document.getElementById("storyflow").className="sub-menu collapse in";
        document.getElementById("storyflow").setAttribute("aria-expanded",true);
        draw();
    }else if(type=="metric"){
        document.getElementsByClassName("wrapper")[0].style.maxWidth="400px";
        writeActiveTabs("metrics");
        writeTabs("story");
        writeTabs("char");
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle");
        modelName=sessionStorage.getItem("metricName");
        document.getElementById("metric"+modelName).className="active";
        document.getElementById("tab3").className="active";
        document.getElementById("metrics").className="sub-menu collapse in";
        document.getElementById("metrics").setAttribute("aria-expanded",true);
        if(sessionStorage.getItem("storyData"+modelName)!=null) {
            var temp = JSON.parse(sessionStorage.getItem("storyData" + modelName));
            nodes = temp.nodes;
            edges = temp.edges;
            count = nodes.length;
            computeMetrics();
        }
    }else if(type=="compare"){
        writeTabs("story");
        writeTabs("char");
        writeTabs("metrics");
        document.getElementById("compare").className="active";
        document.getElementById("tab3").className="active";
        document.getElementById("metrics").className="sub-menu collapse in";
        document.getElementById("metrics").setAttribute("aria-expanded",true);
        compareMetrics();
    }

}

/**
 * TODO delete later.
 */
function temp(){
    var nodes=JSON.parse(sessionStorage.getItem("storyDataStory")).nodes;
    var edg=JSON.parse(sessionStorage.getItem("storyDataStory")).edges;
    for(var i=0;i<nodes.length;i++){
        nodes[i].narrative="Rising Action";
    }
    sessionStorage.setItem("storyDataStory",JSON.stringify({nodes:nodes,edges:edg}));

}

/**
 * Function for saving the current story model into the sessionStorage
 */
function saveStorage(){
    sessionStorage.removeItem("storyData"+modelName);
    var nds=[];
    var edg=[];
    for(i=0;i<nodes.length;i++){
        nds.push(nodes[i]);
    }
    for(i=0;i<edges.length;i++){
        edg.push(edges[i]);
    }
    sessionStorage.setItem("storyData"+modelName,JSON.stringify({nodes:nds,edges:edg}));
}
/**
 * Function for saving the current story model into the sessionStorage
 */
function saveCharStorage() {
    sessionStorage.removeItem("charData");
    var nds=[];
    var edg=[];
    for(i=0;i<cNodes.length;i++){
        nds.push(cNodes[i]);
    }
    for(i=0;i<cEdges.length;i++){
        edg.push(cEdges[i]);
    }
    sessionStorage.setItem("charData"+modelName,JSON.stringify({nodes:nds,edges:edg}));

}

/**
 * Function that clears the manipulation pop up.
 */
function clearPopUp() {
    document.getElementById('nodeSaveButton').onclick = null;
    document.getElementById('nodeCancelButton').onclick = null;
    document.getElementById('edgeSaveButton').onclick = null;
    document.getElementById('edgeCancelButton').onclick = null;
    try {
        document.getElementById('node-name').value = null;
        document.getElementById('node-thoughts').value = null;
    }catch(err){
        console.log("Must add a different clearPopUp for story flow char sometime");
    }
    try{
        document.getElementById("node-info").value=" ";
    }catch (err){
        console.log("Must add a different clearPopUp for story flow char sometime");
    }
    document.getElementById('node-popUp').style.display = 'none';
    document.getElementById('edge-popUp').style.display = 'none';

}

/**
 * Function that cancels the edit .
 * @param callback
 */
function cancelEdit(callback) {
    clearPopUp();
    callback(null);
}

/**
 * Function that destoys the network.
 */
function destroy() {
    if (network !== null) {
        network.destroy();
        network = null;
    }
    if (charNetwork !== null) {
        charNetwork.destroy();
        charNetwork = null;
    }
}

//END OF SHARED FUNCTIONS

//Flow Chart Script
/**
 * Function for creating the network for a Story Flow Chart;
 */
function draw() {
    destroy();
    nodes = [];
    edges = [];
    // create a network
    var container = document.getElementById('storychart');
    var options = {
        physics:{
            enabled:true,
            /*barnesHut: {
                gravitationalConstant: -2000,
                centralGravity: 0.3,
                springLength: 550,
                springConstant: 1.14,
                //damping: 0.09,
                avoidOverlap: 1,
            }*/
            forceAtlas2Based: {
                gravitationalConstant: -50,
                centralGravity: 0.01,
                springConstant: 0.08,
                springLength: 100,
                damping: 0.4,
                avoidOverlap: 1
            },

        },
        layout: {
            randomSeed: 3,
            improvedLayout:false,
            hierarchical: {
                enabled:false,
                levelSeparation: 150,
                nodeSpacing: 100,
                treeSpacing: 200,
                blockShifting: true,
                edgeMinimization: true,
                parentCentralization: true,
                direction: 'LR',        // UD, DU, LR, RL
                sortMethod: 'directed'   // hubsize, directed
            }
        },
        edges : {
            arrows : {
                to : {
                    enabled : true,
                    scaleFactor : 1,
                    type : 'arrow'
                },
                middle : {
                    enabled : false,
                    scaleFactor : 1,
                    type : 'arrow'
                },
                from : {
                    enabled : false,
                    scaleFactor : 1,
                    type : 'arrow'
                }
            },
        },
        locale : 'en',//,
        manipulation : {
            addNode : function(data, callback) {
                openNav();
                // filling in the popup DOM elements
                document.getElementById('nodeOperation').innerHTML = "Add Node";
                document.getElementById('node-id').value = data.id ;//++count;
                document.getElementById('node-id').disabled=true;
                document.getElementById('node-label').value = ++count;
                //document.getElementById('node-type').value ="Start";
                document.getElementById('nodeSaveButton').onclick =saveNodeData.bind(this, data, callback);
                document.getElementById('nodeCancelButton').onclick = clearPopUp.bind(this, callback);
                document.getElementById('node-popUp').style.display = 'block';
                document.getElementById('edge-popUp').style.display = 'none';

               // alert(document.getElementById('nodeSaveButton').onclick);
            },
            editNode : function(data, callback) {
                // filling in the popup DOM elements
                openNav();
                document.getElementById('nodeOperation').innerHTML = "Edit Node";
                document.getElementById('node-id').value = data.id;
                document.getElementById('node-label').value = data.label;
                document.getElementById('node-info').value = data.info;
                document.getElementById('node-type').value = data.type;
                document.getElementById('narrative-phase').value = data.narrative;
                document.getElementById('nodeSaveButton').onclick = saveNodeData.bind(this, data, callback);
                document.getElementById('nodeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('node-popUp').style.display = 'block';
                document.getElementById('edge-popUp').style.display = 'none';

            },
            addEdge : function(data, callback) {
               /* document.getElementById('edgeOperation').innerHTML = "Add Edge";
                document.getElementById('edge-id').value =data.from+" "+data.to;
                document.getElementById('edge-id').disabled=true;
                document.getElementById('edge-label').value = "";
                document.getElementById('edgeSaveButton').onclick = saveEdgeData.bind(this, data, callback);
                document.getElementById('edgeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('edge-popUp').style.display = 'block';*/
                saveEdgeData(data, callback);

            },
            editEdge : function (data,callback) {
                // filling in the popup DOM elements
                openNav();
                document.getElementById('edgeOperation').innerHTML = "Edit Edge";
                document.getElementById('edge-id').value = data.id;
                document.getElementById('edge-label').value = data.label;
                document.getElementById('edgeSaveButton').onclick = saveEdgeData.bind(this, data, callback);
                document.getElementById('edgeCancelButton').onclick = cancelEdit.bind(this, callback);
                document.getElementById('edge-popUp').style.display = 'block';
                document.getElementById('node-popUp').style.display = 'none'; //close this
            },
            deleteNode: function(data,callback){ //the data variable contains only the id in data->nodes (array)
                console.log(data.nodes);
                callback(data);
                for(i=0;i<nodes.length;i++){
                    if(nodes[i].id==data.nodes[0]){
                        nodes.splice(i,1);
                    }
                }
                /**
                 * if a node gets deleted,its edges must be deleted too
                 */
                for(i=0;i<edges.length;i++){
                    if(edges[i].from==data.nodes[0]){
                        edges.splice(i,1);
                    }else if(edges[i].to==data.nodes[0]){
                        edges.splice(i,1);
                    }
                }
                saveStorage();
                console.log(JSON.stringify(nodes));
                console.log("After="+network.body.data.edges.length);
                priceTable(2);
            },
            deleteEdge: function (data,callback) { //the data vairable contains the id data.edges [0]
                console.log("Previous"+edges.length);
                callback(data);
                //edges.remove(data.edges);
                for(i=0;i<edges.length;i++){
                    if(edges[i].id==data.edges[0]){
                        edges.splice(i,1);
                    }
                }
                saveStorage();

            }

        }
    };
    if(sessionStorage.getItem("storyData"+modelName)!=null){
        console.log("old session");
        var temp=JSON.parse(sessionStorage.getItem("storyData"+modelName));
        network = new vis.Network(container, temp , options);
        nodes=temp.nodes;
        edges=temp.edges;
        count=nodes.length;

    }else {
        console.log("Story new session");
        count=0;
        network = new vis.Network(container, data, options);

    }
    /**
     * Function that handles the double click inside the canvas.If
     * the event returns a node display his attributes in the right side Customizer.
     */
    network.on('doubleClick',function (evnt) {
        openNav();
        var nodeid = evnt.nodes[0];
        if (nodeid != null) {
        var node = network.body.data.nodes.get(nodeid);
        // alert(node.id);
        document.getElementById("properties").style.display = "block";
        //document.getElementById("fileClose").style.display = "block"; //x button
        document.getElementById("propertyID").innerHTML = toLabel(node.id);
        document.getElementById("propertyType").innerHTML = node.type;
        document.getElementById("propertyPhase").innerHTML = node.narrative;
        document.getElementById("propertyInfo").innerHTML = node.info;
        //display the fork's weight in later version
       // openNav();
    }
    });
    network.on("deselectNode" , function (params) {
        document.getElementById("properties").style.display = "none";
        document.getElementById("fileClose").style.display = "none";
    });
    if(forkWeights.length>0) {
        network.on("afterDrawing", function (ctx) {
            ctx.font = "17px Helvetica";
            for (i = 0; i < forkWeights.length; i++) {
                for(var k=0;k<nodes.length;k++) {
                    if (nodes[k].id == forkWeights[i]["id"]) {

                    var nodeId = nodes[k].id;
                    var nodePosition = network.getPositions(nodes[k].id);
                    /**
                     * printing its line in the appropriate coordinates
                     *
                     */
                    ctx.fillText(forkWeights[i]["w"], nodePosition[nodeId].x, nodePosition[nodeId].y);


                    //ctx.fill();
                }
                }
                //splice here for performance improvement
            }
            ctx.fill();


        });
    }
    openNav();
    priceTable(2);
}
/**
 * Function for saving a story's flow chart node
 * @param data api node
 * @param callback the api callback for saving into network
 */
function saveNodeData(data, callback) {
    data.label = document.getElementById('node-label').value;
    var shape=document.getElementById('node-type').value;
    var checkedValue = $('#nodeFix:checked').val();
    if(checkedValue){
        data.physics=false;
        data.fixed={
            x:true,
            y:true
        };
    }else{
        data.physics=true;
        data.fixed={
            x:false,
            y:false
        };
    }
    data.shadow=false;


    if(shape=="Choice"){
        data.type="Choice";
        data.shape="diamond";
        data.color= {
            border: 'blue',
            background: 'white',
            highlight: {
                border: 'blue',
                background: 'white'
            }
        };
    }else if(shape=="Start"){
        data.type="Start";
        data.shape="ellipse";
        data.borderWidth=1;
        data.border="black";
        data.color= {
            border: 'gray',
            background: 'white',
            highlight: {
                border: 'black',
                background: 'white'
            }
        };
    }else if(shape=="Good Ending"){
        data.type="Good Ending";
        data.shape="ellipse";
        data.color= {
            border: 'gray',
            background: 'green',
            highlight: {
                border: 'green',
                background: 'green'
            }
        };
    }
    else if(shape=="Bad Ending"){
        data.type="Bad Ending";
        data.shape="ellipse";
        data.color= {
            border: 'gray',
            background: 'orange',
            highlight: {
                border: 'orange',
                background: 'orange'
            }
        };
    }else if(shape=="Goal"){
        data.type="Goal";
        data.shape="box";
        data.widthConstraint={
            widthConstraint: true,
            minimum:32
        };
        data.shapeProperties={
            borderRadius:1
        }
        data.width=300;
        data.color= {
            border: 'gray',
            background: 'yellow',
            highlight: {
                border: 'orange',
                background: 'yellow'
            }
        };
    }
    else if(shape=="Narrative"){
        data.type="Narrative";
        data.shape="box";
        data.widthConstraint={
            widthConstraint: true,
            minimum:32
        };
        data.shapeProperties={
            borderRadius:1
        }
        data.color= {
            border: 'blue',
            background: 'white',
            highlight: {
                border: 'blue',
                background: 'white'
            }
        };
    }
    data.info=document.getElementById("node-info").value;
    data.narrative=document.getElementById("narrative-phase").value;
    clearPopUp();
    callback(data);
    for(i=0;i<nodes.length;i++){
        if(nodes[i].id==data.id){
            nodes.splice(i,1);
        }
    }
    nodes.push(data);
    priceTable(2);
    saveStorage();
    console.log(JSON.stringify(nodes));
    console.log('@@@@@@@@@@');
    console.log('########');

}
/**
 * Function for saving a story's flow chart edge
 * @param data api edge
 * @param callback the api callback for saving into network
 */
function saveEdgeData(data,callback) {
    data.label=document.getElementById("edge-label").value;
    clearPopUp();
    data.length=150+2*data.label.length;
    data.smooth={
        enabled:false,

    };
    if (data.from == data.to) {
        var r = confirm("Do you want to connect the node to itself?");
        if (r == true) {
            callback(data);
            for(i=0;i<edges.length;i++){
                if(edges[i].id==data.id){
                    edges.splice(i,1);
                }
            }
            edges.push(data);
        }
    } else {
        callback(data);
        for(i=0;i<edges.length;i++){
            if(edges[i].id==data.id){
                edges.splice(i,1);
            }
        }
        edges.push(data);
    }
    saveStorage();
    console.log(JSON.stringify(edges));
    console.log('@@@@@@@@@@');
    console.log('########');
}

//METRICS COMPUTE AND ANALYSIS

var flagAgain=false;
function computeMetrics() {
    //TODO
    /**
     * Metrics that require cardinality
     */
    //try {
        var rep = numberOfMetrics(nodes);
    setItemtoMetricsNormal("nocHead","noc",rep["noC"]);
    setItemtoMetricsNormal("noeHead","noe",rep["noE"]);
    setItemtoMetricsNormal("noaHead","noA",rep["noA"]);
    setItemtoMetricsNormal("noc2Head","noc2",rep["noC"]);
    setItemtoMetricsNormal("nogHead","nog",rep["noG"]);
    setItemtoMetricsNormal("noe2Head","noe2",rep["noE"]);
    setItemtoMetricsNormal("noc3Head","noc3",rep["noC"]);
    setItemtoMetricsNormal("noa2Head","noA2",rep["noA"]);
       /* document.getElementById("noc").innerHTML = rep["noC"];
        document.getElementById("noe").innerHTML = rep["noE"];

        document.getElementById("noa").innerHTML = rep["noA"];
        document.getElementById("noc2").innerHTML = rep["noC"];
        document.getElementById("nog").innerHTML = rep["noG"];

        document.getElementById("noe2").innerHTML = rep["noE"];
        document.getElementById("noc3").innerHTML = rep["noC"];
        document.getElementById("noa2").innerHTML = rep["noA"];*/
        rep = aphe(nodes,edges);
    setItemtoMetricsNormal("apheHead","aphe",rep["apHE"]);
        //document.getElementById("aphe").innerHTML = rep["apHE"];

        rep = apc(nodes, edges);
    setItemtoMetricsNormal("apcHead","apc",rep["apC"]);
      //  document.getElementById("apc").innerHTML = rep["apC"];

        findWeights(nodes, edges);

        rep=choiceMetrics(edges);
       /* document.getElementById("nic").innerHTML = rep["niC"];
        document.getElementById("aci").innerHTML = rep["acI"];
        document.getElementById("apic").innerHTML = rep["apIC"];*/
    setItemtoMetricsNormal("nicHead","nic",rep["niC"]);
    setItemtoMetricsNormal("aciHead","aci",rep["acI"]);
    setItemtoMetricsNormal("apicHead","apic",rep["apIC"]);
        document.getElementById("projectName").innerHTML+="  ---   Metrics for "+sessionStorage.getItem("metricName");
       // document.getElementById("metricsTitle").innerHTML="Metrics for "+sessionStorage.getItem("metricName");


        rep=ADbC(nodes,edges);
    setItemtoMetricsNormal("adbcHead","adbc",rep);
    flagAgain=true;
      //  document.getElementById("adbc").innerHTML = rep;
   /* }catch (err){
        console.log("Invalid graph");
        alert(err);
    }*/




   /* LEVEL OF NARRATIVE*/
    var rep=numberOfLevelOfNarrative(nodes);
    var repAPHE=apheLoN(nodes,edges);
    var repapC=apcLoN(nodes,edges);
    var repChoiceMetrics=choiceMetricsLoN(edges);
    var repADbC=ADbCLoN(nodes,edges);
    var ctx = document.getElementById('myChart2').getContext('2d');
    Chart.defaults.global.animation.duration=3000;
    Chart.defaults.global.animation.easing='easeInOutCubic';
    var myChart = new Chart(ctx, {
        type: 'bar',
        options:{
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Level of Narrative'
            }
        },
        data: {
            labels: ["Exposition","Rising Action","Climax","Falling Action","Conclusion"],
            datasets: [{
                label: 'NoA',
                data: rep["noA"],
                backgroundColor: "rgba(44,37,133,0.8)"
            }, {
                label: 'NoC',
                data: rep["noC"],
                backgroundColor: "rgba(30,162,190,0.8)"
            },
                {
                    label: 'NoE',
                    data: rep["noE"],
                    backgroundColor: "rgba(21,162,67,0.8)"
                },

                {
                    label: 'NoG',
                    data: rep["noG"],
                    backgroundColor: "rgba(251,233,66,0.8)"
                },
                {
                    label: 'NIC',
                    data: repChoiceMetrics["niC"],
                    backgroundColor: "rgba(250,186,35,0.6)"
                },
                {
                    label: 'ACI',
                    data: repChoiceMetrics["acI"],
                    backgroundColor: "rgba(241,127,39,0.8)"
                },
                {
                    label: 'APIC',
                    data: repChoiceMetrics["apIC"],
                    backgroundColor: "rgba(233,73,38,0.8)"
                },
                {
                    label: 'APHE',
                    data: repAPHE,
                    backgroundColor: "rgba(220,0,35,0.8)"
                },
                {
                    label: 'APC',
                    data: repapC,
                    backgroundColor: "rgba(165,0,117,0.8)"
                },{
                    label: 'ADbC',
                    data: repADbC,
                    backgroundColor: "rgba(122,24,126,0.8)"
                }]
        }
    });


}

function sideWeight() {
    forkWeights=findWeights(nodes,edges);
    destroy();
    draw();
}
var counter=0;
function compareMetrics() {
    document.getElementsByClassName("wrapper")[0].style.position="absolute";
    document.getElementsByClassName("wrapper")[0].style.zIndex="-1";
    //document.getElementsByClassName("wrapper")[0].style.width="100%";
    var doc=document.getElementsByClassName("header");
    var models=JSON.parse(sessionStorage.getItem("storyModels"));
    for(var l=0;l<doc.length;l++){
        for(var k=0;k<models.length;k++){
            var one=document.createElement("div");
            one.setAttribute("class","cell");
            one.innerHTML=models[k];
             doc[l].appendChild(one);
        }
    }

    counter=0;

    for(var m=0;m<models.length;m++){
        console.log("Reading metrics for "+models[m]);
        var temp = JSON.parse(sessionStorage.getItem("storyData" + models[m]));
        var tNodes = temp.nodes; //Scope within loop
        var tEdges = temp.edges; //Scope within loop
        var rep = numberOfMetrics(tNodes); //nodes=local variable
        /**
         * Cardinality Metrics
         */
        console.log("cardinality metrics");
        setItemtoMetricsCompare("nocHead","noc",rep["noC"]);
        setItemtoMetricsCompare("noeHead","noe",rep["noE"]);
        setItemtoMetricsCompare("noaHead","noA",rep["noA"]);
        setItemtoMetricsCompare("noc2Head","noc2",rep["noC"]);
        setItemtoMetricsCompare("nogHead","nog",rep["noG"]);
        setItemtoMetricsCompare("noe2Head","noe2",rep["noE"]);
        setItemtoMetricsCompare("noc3Head","noc3",rep["noC"]);
        setItemtoMetricsCompare("noa2Head","noA2",rep["noA"]);

        //aphe
        console.log("aphe metrics");
        rep = aphe(tNodes, tEdges);
        setItemtoMetricsCompare("apheHead","aphe",rep["apHE"]);

        //apc
        console.log("apc metrics");
        rep = apc(tNodes, tEdges);
        setItemtoMetricsCompare("apcHead","apc",rep["apC"]);

        /**
         * Choice Metrics
         */
        console.log("choice metrics");
        findWeights(tNodes, tEdges);

        rep=choiceMetrics(tEdges);
        setItemtoMetricsCompare("nicHead","nic",rep["niC"]);
        setItemtoMetricsCompare("aciHead","aci",rep["acI"]);
        setItemtoMetricsCompare("apicHead","apic",rep["apIC"]);


        rep=ADbC(tNodes,tEdges);
        setItemtoMetricsCompare("adbcHead","adbc",rep);


        counter++;
    }


}


function setItemtoMetricsCompare(parent,child,value) {
    console.log(parent);
    var temp2=document.getElementById(parent);
    var one=document.createElement("div");
    one.setAttribute("id",child+counter);
    one.setAttribute("class","cell");
    one.innerHTML=value;
    temp2.appendChild(one);
}

function setItemtoMetricsNormal(parent,child,value) {
    //alert(flagAgain);
    if(flagAgain){
        document.getElementById(child).innerHTML=value;

        return;
    }
    console.log(parent);
    var temp2=document.getElementById(parent);
    var one=document.createElement("div");
    one.setAttribute("id",child);
    one.setAttribute("class","cell");
    one.innerHTML=value;
    temp2.appendChild(one);
}





//HTML UI-UX Script
/**
 * Functions for the User Interface and User EXperience.
 */
/**
 * Function that handles the main dashboard
 */
function mainTab() {
    document.getElementById("dashboard").className="active";
    console.log('something');
    console.log(sessionStorage.getItem("ProjectTitle"));
    if(sessionStorage.getItem("ProjectTitle")!=null){ //if a project exists
        //if the user chooses new project over an old one warn him!!
        //also clear the variables and reset the entire workspace
        document.getElementById("projectName").innerHTML="Current Project: "+sessionStorage.getItem("ProjectTitle");
        writeTabs("char");
        writeTabs("story");
        writeTabs("metrics");

    }else {
        document.getElementById("saveData").style.display = 'none';
        document.getElementById("projectName").style.display = 'none';
        document.getElementById("tab1").style.display = 'none';
        document.getElementById("tab2").style.display = 'none';
        document.getElementById("tab3").style.display = 'none';
        charModels=[];
        storyModels=[];
        metrics=[];
    }
    document.getElementById('upload').addEventListener('change',function () {
        reader.readAsText(document.getElementById("upload").files[0]);
    });

}
/**
 * Function for creating a new project.
 * @param type, load: load the popup and blur the backround, save:save and create the new project
 * cancel:cancel all and dispose the popup.
 */
function insertName(type) {
    if(type=="load") {
        document.getElementById('blackout').style.display = 'block';
        document.getElementById("newProjectPane").style.display = 'block';
    }else if(type=="save"){
        var flag=false;
        if(sessionStorage.getItem("ProjectTitle")!=null){
            var r = confirm("There is already an open project.\nHit Cancel and save the changes or OK to proceed.");
            if (r == false) {
                document.getElementById('blackout').style.display = 'none';
                document.getElementById("name").value = "New Project";
                document.getElementById("newProjectPane").style.display = 'none';
                return;
            }else{
                flag=true;
            }
        }
        document.getElementById("projectName").innerHTML=document.getElementById("name").value;
        sessionStorage.clear();
        sessionStorage.setItem("ProjectTitle",document.getElementById("name").value);
        document.getElementById("projectName").style.display='block';
        document.getElementById("tab1").style.display = 'block';
        document.getElementById("tab2").style.display = 'block';
        document.getElementById("tab3").style.display = 'block';
        document.getElementById("newProjectPane").style.display = 'none';
        document.getElementById("name").value = "New Project";
        document.getElementById('blackout').style.display = 'none';
        if(flag){
            location.reload();
        }
    }else if(type=="cancel"){
        document.getElementById("newProjectPane").style.display = 'none';
        document.getElementById("name").value = "New Project";
        document.getElementById('blackout').style.display = 'none';

    }

}

/**
 * Function for creating a new character model tab.
 * @param type, load: load the popup and blur the backround, save:save and create the model tab
 * cancel:cancel all and dispose the popup.
 */
function insertCharName(type) { //YOU HAVE TO DO THIS S*** WITH PHP SOMETIME
    if (type == "load") {
        document.getElementById('blackout').style.display = 'block';
        document.getElementById("newCharPane").style.display = 'block';
    } else if (type == "save") {
        var tempName = document.getElementById("cname").value;
        var names=JSON.parse(sessionStorage.getItem("charModels"));
        if(names!=null){
            for(var i=0;i<names.length;i++){
                console.log(names[i]);
                if(names[i]==tempName){
                    alert("Character Model with name "+tempName+" already exists..");
                    return;
                }
            }}


        charModels.push(tempName);
        sessionStorage.setItem("charModels",JSON.stringify(charModels));
        var el=document.createElement("a"); //create an "a" element
        el.appendChild(document.createTextNode(tempName)); //append the node name
        var oncl="openCharTab('"; //create the function
        oncl=oncl.concat(tempName.toString());
        oncl=oncl.concat("');");
        console.log(oncl);
        el.setAttribute("onclick",oncl); //set the onlick function
        var temp=document.getElementById("characterModel");
        var par=document.getElementById("cOriginal"); //get the parent
        var child=document.createElement("li"); //create the child
        child.appendChild(el); //append the a to the child
        child.setAttribute("id","char"+tempName); //set the id attribute
        temp.insertBefore(child,par); //insert the child before the parent.

        document.getElementById("newCharPane").style.display = 'none';
        document.getElementById("cname").value = "New Project";
        document.getElementById('blackout').style.display = 'none';
        openCharTab(tempName);

    }else if(type=="cancel"){
        document.getElementById("newCharPane").style.display = 'none';
        document.getElementById("cname").value = "New Project";
        document.getElementById('blackout').style.display = 'none';

    }

}
/**
 * Function for creating a new story flow chart tab.
 * @param type, load: load the popup and blur the backround, save:save and create the story flow chart tab
 * cancel:cancel all and dispose the popup.
 */
function insertFlowName(type) { //YOU HAVE TO DO THIS S*** WITH PHP SOMETIME
    if (type == "load") {
        document.getElementById('blackout').style.display = 'block';
        document.getElementById("newStoryPane").style.display = 'block';
    } else if (type == "save") {
        var tempName = document.getElementById("sfname").value;
        var names=JSON.parse(sessionStorage.getItem("storyModels"));
        if(names!=null){
        for(var i=0;i<names.length;i++){
            console.log(names[i]);
            if(names[i]==tempName){
                alert("Flow Chart with name "+tempName+" already exists..");
                return;
            }
        }}


        //WATCH OUT!!
        storyModels.push(tempName);
        sessionStorage.setItem("storyModels", JSON.stringify(storyModels));

        //Flow Chart
        var el = document.createElement("a");
        el.appendChild(document.createTextNode(tempName));
        var oncl = "openStoryTab('";
        oncl = oncl.concat(tempName.toString());
        oncl = oncl.concat("');");
        console.log(oncl);
        el.setAttribute("onclick", oncl);
        var temp = document.getElementById("storyflow");
        var par = document.getElementById("sOriginal");
        var child = document.createElement("li");
        child.appendChild(el);
        child.setAttribute("id", "story" + tempName);
        temp.insertBefore(child, par);

        //Metrics
        metrics.push(tempName);
        sessionStorage.setItem("metrics", JSON.stringify(storyModels));
        var el = document.createElement("a");
        el.appendChild(document.createTextNode(tempName));
        var oncl = "openMetricsTab('";
        oncl = oncl.concat(tempName.toString());
        oncl = oncl.concat("');");
        console.log(oncl);
        el.setAttribute("onclick", oncl);
        var temp = document.getElementById("metrics");
        var child = document.createElement("li");
        child.appendChild(el);
        child.setAttribute("id", "metric" + tempName);
        temp.appendChild(child);


        document.getElementById("newStoryPane").style.display = 'none';
        document.getElementById("sfname").value = "New Project";
        document.getElementById('blackout').style.display = 'none';
        openStoryTab(tempName);
    } else if (type == "cancel") {
        document.getElementById("newStoryPane").style.display = 'none';
        document.getElementById("sfname").value = "New Project";
        document.getElementById('blackout').style.display = 'none';

    }


}

/**
 * Function for saving the hole project to file
 * the file structure is as described in the reader.onload
 */
function sevaga() {
    var storage=[];
    var c=JSON.parse(sessionStorage.getItem("charModels")); //charModels is a stringified array
    var s=JSON.parse(sessionStorage.getItem("storyModels"));
    if(c!=null) {


        storage.push(c.length);
        for (i = 0; i < c.length; i++) {
            storage.push(c[i]);
            storage.push(sessionStorage.getItem("charData" + c[i]));
        }
    }else{
        storage.push(0);
    }
    if(s!=null) {


        storage.push(s.length);
        for (i = 0; i < s.length; i++) {
            storage.push(s[i]);
            storage.push(sessionStorage.getItem("storyData" + s[i]));
        }
    }else{
        storage.push(0);
    }
    if(sessionStorage.getItem("ProjectTitle")==null){
        console.log("no project selected");
        return;
    }
    storage.push(sessionStorage.getItem("ProjectTitle"));
    uriContent = encodeURIComponent(JSON.stringify(storage));
    var link = document.createElement('a'); //create the a element
    link.download = sessionStorage.getItem("ProjectTitle")+".umbra"; //append the dowload file
    link.href = 'data:,' + uriContent;
    link.click(); //click it and download the file.
}

/**
 * Function that has the hard work of clicking the upload element for the file handling to start. :P
 */
function loadaga() {
    document.getElementById("upload").click();
}

/**
 * Functions that hadles the right side menu.
 */
function openNav() {
    document.getElementById("tooltipSideNav").style.display="none";
    /* From Modernizr */
   // document.getElementById("spanNav").style.display="none";
    if(window.location.href.search("charactermodel")>0){ //CharacterModel
        document.getElementById("tabDel").innerHTML="Delete "+sessionStorage.getItem("modelName");
      //  document.getElementById("metricsButton").innerHTML="Metrics for "+sessionStorage.getItem("modelName");
        function whichTransitionEvent(){
            var t;
            var el = document.createElement('fakeelement');
            var transitions = {
                'transition':'transitionend',
                'OTransition':'oTransitionEnd',
                'MozTransition':'transitionend',
                'WebkitTransition':'webkitTransitionEnd'
            }

            for(t in transitions){
                if( el.style[t] !== undefined ){
                    return transitions[t];
                }
            }
        }



        var transitionEvent = whichTransitionEvent();
        transitionEvent && document.getElementById("mySidenav").addEventListener(transitionEvent,event1);
        function event1() {
            try {
                var rep = ccf(cNodes,cEdges);
                document.getElementById("pricing-table").style.display = "block";
                var temp=document.getElementById("h3actions");
                document.getElementById("h3actions").innerText="Characters Coupling Factor-CCF";
                var el = document.createElement("span");
                var el2= document.createElement("div");
                el2.setAttribute("id","actions");
                el.appendChild(el2);
                temp.appendChild(el);
                document.getElementById("actions").innerHTML=rep;
                var temp=document.getElementById("h3choices");
                document.getElementById("h3choices").innerHTML = "Number of Characters <br></br>";
                var el = document.createElement("span");
                var el2= document.createElement("div");
                el2.setAttribute("id","choices");
                el.appendChild(el2);
                temp.appendChild(el);
                document.getElementById("choices").innerHTML=cNodes.length;
            }catch (e){alert(e)
                 };
            document.getElementById("mySidenav").removeEventListener(transitionEvent,event);
        }

    }else {


        document.getElementById("tabDel").innerHTML = "Delete " + sessionStorage.getItem("storyName");
        document.getElementById("metricsButton").innerHTML="Metrics for "+sessionStorage.getItem("storyName");
        function whichTransitionEvent() {
            var t;
            var el = document.createElement('fakeelement');
            var transitions = {
                'transition': 'transitionend',
                'OTransition': 'oTransitionEnd',
                'MozTransition': 'transitionend',
                'WebkitTransition': 'webkitTransitionEnd'
            }

            for (t in transitions) {
                if (el.style[t] !== undefined) {
                    return transitions[t];
                }
            }
        }

        var transitionEvent = whichTransitionEvent();
        transitionEvent && document.getElementById("mySidenav").addEventListener(transitionEvent, event2);
        function event2() {
            try {
                var rep = numberOfMetrics(nodes);

                document.getElementById("pricing-table").style.display = "block";
                document.getElementById("ends").innerHTML = rep["noE"];
                document.getElementById("choices").innerHTML = rep["noC"];
                document.getElementById("actions").innerHTML = rep["noA"];
            } catch (e) {
            }

            document.getElementById("mySidenav").removeEventListener(transitionEvent, event);
        };
    }

    //document.getElementById("mySidenav").style.width = "450px";
    document.getElementById("pricing-table").style.display="block";
    document.getElementById("mySidenav").style.width = "10%";

}


function priceTable(mode) {
    if(mode==1){ //character model
        try {
            var rep = ccf(cNodes,cEdges);
            document.getElementById("pricing-table").style.display = "block";
            var temp=document.getElementById("h3actions");
            document.getElementById("h3actions").innerText="Characters Coupling Factor-CCF";
            var el = document.createElement("span");
            var el2= document.createElement("div");
            el2.setAttribute("id","actions");
            el.appendChild(el2);
            temp.appendChild(el);
            document.getElementById("actions").innerHTML=rep;
            var temp=document.getElementById("h3choices");
            document.getElementById("h3choices").innerHTML = "Number of Characters <br></br>";
            var el = document.createElement("span");
            var el2= document.createElement("div");
            el2.setAttribute("id","choices");
            el.appendChild(el2);
            temp.appendChild(el);
            document.getElementById("choices").innerHTML=cNodes.length;
        }catch (e){alert(e)
        };
    }else if(mode==2){ //story flow chart
        try {
            var rep = numberOfMetrics(nodes);

            //document.getElementById("pricing-table").style.display = "block";
            document.getElementById("ends").innerHTML = rep["noE"];
            document.getElementById("choices").innerHTML = rep["noC"];
            document.getElementById("actions").innerHTML = rep["noA"];
        } catch (e) {
            alert(mode)
        }
    }

}

/**
 * Function that closes the nodes' info
 */
function closeInfo(){
    document.getElementById("properties").style.display = "none";
    document.getElementById("fileClose").style.display = "none";
}
function closeNav() {
    document.getElementById("tooltipSideNav").style.display="block";
    document.getElementById("spanNav").style.display="block";
    document.getElementById("pricing-table").style.display="none";
    try {
       // document.getElementById("properties").style.display = "none";
    }catch (err){
        console.log("closeNav closes properties...need to fix this sometime :P")
    }
    document.getElementById("mySidenav").style.width = "0";
}


//Tab management UX
/**
 * Function that is called from a tab to draw an other model without refreshing the page.
 * @param type, char:character model, story:story flow chart.
 * @param name the name of the model that is gonna be loaded.
 */
function openActiveTab(type,name) {

    //it is a char model that we want to open
    if(type=="char"){
        closeNav();
        document.getElementById("char"+sessionStorage.getItem("modelName")).className="";
        sessionStorage.setItem("modelName",name);
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle"); //put char model here
        modelName=sessionStorage.getItem("modelName");
        document.getElementById("char"+modelName).className="active";
        document.getElementById("characterModel").className="sub-menu collapse in";
        document.getElementById("characterModel").setAttribute("aria-expanded",true);
        charDraw();
    }//it is a story flowchart that we want to open
    else if(type=="story"){
        closeNav();
        document.getElementById("story"+sessionStorage.getItem("storyName")).className="";
        sessionStorage.setItem("storyName",name);
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle"); //put char model here
        modelName=name;
        document.getElementById("story"+modelName).className="active";
        document.getElementById("storyflow").className="sub-menu collapse in";
        document.getElementById("storyflow").setAttribute("aria-expanded",true);
        draw();
    }else if(type=="metric"){
        document.getElementById("metric"+sessionStorage.getItem("metricName")).className="";
        sessionStorage.setItem("metricName",name);
        document.getElementById("projectName").innerHTML=sessionStorage.getItem("ProjectTitle"); //put char model here
        modelName=name;
        document.getElementById("metric"+modelName).className="active";
        document.getElementById("metrics").className="sub-menu collapse in";
        document.getElementById("metrics").setAttribute("aria-expanded",true);
        if(sessionStorage.getItem("storyData"+modelName)!=null) {
            var temp = JSON.parse(sessionStorage.getItem("storyData" + modelName));
            nodes = temp.nodes;
            edges = temp.edges;
            count = nodes.length;
            computeMetrics();
        }
    }


}

function clickMetrics()
{



    sessionStorage.setItem("metricName", sessionStorage.getItem("storyName")); //sets the variable in order to open the correct model
    window.location = "metrics.php"; //redirects user
}
function aboutBaf(type) {
    if(type=="open") {
        document.getElementById("aboutBBF").style.display = "block";
        document.getElementById('blackout').style.display = 'block';
    }else{
        document.getElementById("aboutBBF").style.display = "none";
        document.getElementById('blackout').style.display = 'none';
    }
}






