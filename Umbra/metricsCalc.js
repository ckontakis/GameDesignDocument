//GLOBAL VARIABLES

var result=[];
var forks=[];
var edges=[];
var nodes=[];
var wForks=[];
var visited=[];
var noC=0; //Global varialbe to be used in later metrics
var noE=0;
var noA=0;
var noG=0;
function numberOfMetrics(nodes) {
    noC=0; //Global varialbe to be used in later metrics
    noE=0;
    noA=0;
    noG=0;
    var i;
    var temp;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        if(temp.type=="Choice"){
            noC++;
        }else if(temp.type=="Good Ending" || temp.type=="Bad Ending"){
            noE++;
        }else if(temp.type=="Narrative"){
            noA++;
        }else if(temp.type=="Goal"){
            noG++;
        }
    }
    return {noC:noC,noE:noE,noA:noA,noG:noG};
}

function aphe(nodes,edges) {
    var apHE=0;
    var total=0;
    var temp;
    var i;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        if(temp.type=="Good Ending"){
            total++;
            apHE+=getDestinationEdges(edges,temp.id);
        }
    }
    apHE=apHE/total;
    //alert(apHE);
    return {apHE:Math.round(apHE*100)/100};
}

function getDestinationEdges(edges,id) {
    var i;
    var r=0;
    for(i=0;i<edges.length;i++){
        if(edges[i].to==id){
            r++;
        }
    }
    return r;
}


function apc(nodes,edges) {
    var apC=0;
    var total=0;
    var temp;
    var i;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        if(temp.type=="Choice"){
            total++;
            apC+=getStartPointEdges(edges,temp.id);
        }
    }
    apC=apC/total;
    //alert(apC);
    return {apC:Math.round(apC*100)/100};
}

function getStartPointEdges(edges,id) {
    var i;
    var r=0;
    for(i=0;i<edges.length;i++){
        if(edges[i].from==id){
            r++;
        }
    }
    return r;
}

function choiceMetrics(edges) {
    var w5=0;
    var apIC=0;
    var aci=0;
    for(var i=0;i<wForks.length;i++){
        aci+=wForks[i]["w"];
        if(wForks[i]["w"]==5){
            w5++;
           apIC+=getStartPointEdges(edges,wForks[i]["id"]);
        }
    }
    var temp1=aci/wForks.length;
    var temp2=apIC/w5;
    return{acI:Math.round(temp1*100)/100,apIC:Math.round(temp2*100)/100,niC:w5}
}


function findWeights(theNodes,theEdges){
    edges=theEdges;
    nodes=theNodes;
    var i;
    var start;
    for(i=0;i<nodes.length;i++){
        if(nodes[i].type=="Start"){
            start=nodes[i].id;
            break;
        }
    }
    for(i=0;i<nodes.length;i++){
        if(getNodeType(getDestinations(start))!="Choice"){
            start=getDestinations(start);
            continue;
        }else{
            start=getDestinations(start);
            break;
        }
    }
    for(i=0;i<nodes.length;i++){
        if(nodes[i].type=="Choice"){
            forks.push(nodes[i].id);
        }
    }
   // alert(forks[0]);
    //compute(forks[0]);
    compute(start);
    sessionStorage.setItem("forks",JSON.stringify(wForks));
   // alert(JSON.stringify(wForks));
    for(i=0;i<wForks.length;i++){
        console.log(wForks[i]);
    }
   // alert(wForks.length);
    return wForks;
}
var bug=false;
/**
 * function for printing debug messages in console
 * @param msg
 */
function debug(msg) {
    if(bug) {
       // console.log(msg);
    }

}
/**
 *
 * @param fork, a choice node
 */
var checked=[];
bug=true;
function compute(fork,previousNode){
    debug("Started Node="+toLabel(fork));
    if(exists(fork)==false) {
        var t = getNodeType(fork);
        //console.log(t);
        if (t == "Good Ending" || t == "Bad Ending") {
            // alert("end");
            var visit=[];
            visit.push(fork);
            debug("Visited Ending="+toLabel(fork));
            debug("#######\n\n");
            checked.push(fork);
            return {w: 5, type: "ending",path:visit};
        } else if (t == "Narrative" || t == "Goal") {
            // alert("narrative or goal");
            //put a try-catch if u find a narrative alone without destination
            var r=getDestinations(fork);
           /* for(var l=0;l<checked.length;l++){
                if (checked[l]==r){

                }
            }*/
            var temp=compute(r,fork); //narrative or goal have only 1 destination
            console.log(getDestinations(fork).length);
            temp["path"].push(fork);
            //console.log(temp);
            //console.log(toLabel(fork)+" is visited with path length "+temp["path"].length);
            debug("Visited Narrative || Goal="+toLabel(fork))
            debug("Length to nearest ending="+temp["path"].length);
            debug("#######\n\n");
            checked.push(fork);
            return temp;

        } else if (t == "Choice") {
            for(var i=0;i<checked.length;i++){
                if(checked[i]==fork){
                    var temp=[];
                    temp.push(fork);
                    //console.log("Found loop at "+toLabel(fork));
                    debug("Found loop while acessing the node="+toLabel(fork));
                    debug("#######\n\n");
                    return {w:-1,type:"Choice",path:temp};
                }
            }
            checked.push(fork);
            var r = getDestinations(fork);
            var i;
            var topResult = [];
            var netWeight = -1;
            var flagEnding = false;
            for (i = 0; i < r.length; i++) {
                // alert("compute!!");
                //if(exists(r[i])==false) {
                    topResult.push(compute(r[i],null));
                //}
            }



           // alert(topResult.length);
            var visit=[];
            var flag=false;
            var flagFound=false;
            var returned=[];
            debug("In fork no="+toLabel(fork));
            debug("Node has edges== "+topResult.length);
            debug("=============FORKS=============");
            for (i = 0; i < topResult.length; i++) {
                debug("fork weight=="+topResult[i]["w"]+' type=='+topResult[i]["type"]);
                for(var s=0;s<topResult[i]["path"].length;s++){
                    visit.push(topResult[i]["path"][s]);
                }
                //visit.concat(topResult[i]["path"]);
                if(topResult[i]["returned"]!=null){
                    flag=true;
                    for(var e=0;e<topResult[i]["returned"].length;e++){
                        returned.push(topResult[i]["returned"][e]);
                        debug("found a return node no."+toLabel(topResult[i]["returned"][e])+" ,length=="+returned.length);
                    }





                  //  console.log("RETURNEDD FROM "+toLabel(returned));
                }
                if (topResult[i]["w"] == 5 && topResult[i]["type"]=="ending") { //fork leads directly to an end.
                    flagEnding = true;
                    netWeight=5;
                }
            }
            debug("=============END=============\n\n");
            //visit.push(previousNode);
            visit.push(fork);
            //FINDING DUPLICATES
            debug("=============FINDING NO 1=============");
            if (flag){
                var count=0;
                for(var g=0;g<returned.length;g++){
                    debug(toLabel(fork)+" looking for=="+toLabel(returned[g]));
                    for(var m=0;m<visit.length;m++){
                        // console.log(toLabel(visit[m]));
                        if(visit[m].toString()==returned[g].toString()){
                            count++;
                            console.log("count");
                        }
                    }
                    console.log("have="+count+"need="+topResult.length);
                    if(count>=topResult.length){ //count>1 //==
                        flag=false;
                        console.log("FOUND"+toLabel(returned[g]));
                        returned.splice(g,1);
                        if(flagEnding){
                            netWeight=3;
                        }else {
                            netWeight = 1;
                        }
                    }else {
                        flagFound=true;
                    }
                }

            }else{
                flag=true;
            }
            debug("=============END NO 1=============");
            //EVALUATION PROCESS
            if(flag) {
                for (i = 0; i < topResult.length; i++) {
                    if (topResult[i]["w"] == 5 && topResult[i]["type"] == "Choice") {
                        if (flagEnding) {
                            netWeight = 4;
                        } else {
                            netWeight = 2;
                        }
                    } else if (topResult[i]["w"] == 4 /*&& topResult[i]["type"] == "fork"*/) {
                        if (flagEnding) {
                            netWeight = 3;
                        } else {
                            netWeight = 2;
                        }
                    } else if (topResult[i]["w"] == 3 /*&& topResult[i]["type"] == "fork"*/) {
                        console.log("bika");
                        if (flagEnding) {
                            netWeight = 3;
                        } else {
                            netWeight = 2;
                        }
                    } else if (topResult[i]["w"] == 2 /*&& topResult[i]["type"] == "fork"*/) { //TBD
                        if (flagEnding) {
                            netWeight = 3;
                        } else {
                            netWeight = 2;
                        }
                    }
                    else if (topResult[i]["w"] == 1 /*&& topResult[i]["type"] == "fork"*/) {
                        if (flagEnding) {
                            netWeight = 3;
                        } else {
                            netWeight = 2;
                        }
                    }else if(topResult[i]["w"] == -1 /*&& topResult[i]["type"] == "fork"*/){
                        if (flagEnding) {
                            netWeight = 3;
                        } else {
                           // alert("oki@"+toLabel(fork));
                            netWeight = 1;
                           // break;
                        }
                    }
                }
            }
            if(netWeight==-1){ //will be deleted once the changes are implemented

                //alert('FAULT');
               // netWeight=1;
            }



            if (exists(fork) == false) {
                wForks.push({id: fork, w: netWeight,path:visit});

            }
            for(i=0;i<nodes.length;i++){
                if(nodes[i].id==fork){
                    //  console.log(nodes[i].label+" =="+netWeight);
                     // console.log("**********");
                      for(var k=0;k<topResult.length;k++){
                       //   console.log(topResult[k]["w"]+" "+topResult[k]["type"]);
                      }
                     // console.log("---------------");
                    break;
                }
            }
            for(var i=0;i<checked.length;i++){
                if(checked[i]==fork){
                    checked.splice(i,1);
                }
            }
            console.log(toLabel(fork)+" is visited with path length "+visit.length);
            if(returned.length>0){
                console.log("Didn't found two paths to-"+toLabel(returned)+" sending onwards...")
                return {w: netWeight, type: "Choice",path:visit,returned:returned};
            }
            return {w: netWeight, type:"Choice",path:visit};

        }
    }else{
        console.log("the node has been visited..we have a no.1 fork among us "+ toLabel(fork));
        for(i=0;i<wForks.length;i++){
            if(wForks[i]["id"]==fork.toString()){
                //returned is the node that has been visited again
                var temp=[];
                temp.push(fork);
                return {w:wForks[i]["w"] , type: getNodeType(fork),path:wForks[i]["path"],returned:temp};
            }
        }
    }


}


function getDestinations(id) {
    var i;
    var r=[];
    for(i=0;i<edges.length;i++){
        if(edges[i].from==id){
            r.push(edges[i].to);
        }
    }
    return r;
}

function getNodeType(id) {
    for(i=0;i<nodes.length;i++){
        if(nodes[i].id==id) {
            return nodes[i].type;
        }
    }
}

function exists(id) {
    var i;
    for(i=0;i<wForks.length;i++){
        if(wForks[i]["id"]==id.toString()){
            return true;
        }
    }
    return false;
}

function toLabel(fork) {
    for(i=0;i<nodes.length;i++){
        if(nodes[i].id==fork) {
            return nodes[i].label;
        }
    }
}



function ccf(nodes,edges) {
    if(nodes.length<2){
        return 0;
    }
    return Math.round(edges.length/(factorial(nodes.length)/(2*factorial(nodes.length-2)))*100)/100;

}

function factorial(n) {
    if(n==1 || n==0){
        return 1;
    }else{
        return n*(factorial(n-1));
    }

}

var costs=[]; //from,to,cost
var original=[]; //fork,cCost
var choices=[]; //for order issues
var path=[]; //from,to
var tempCosts=[];
function findCost(nodes,edges) {
    var i;
    var start;
    for(i=0;i<nodes.length;i++){
        if(nodes[i].type=="Start"){
            start=nodes[i].id;
            break;
        }
    }
    for(i=0;i<nodes.length;i++){
        if(getNodeType(getDestinations(start))!="Choice"){
            start=getDestinations(start);
            continue;
        }else{
            start=getDestinations(start);
            break;
        }
    }
   // alert(toLabel(start));
    console.log("start");
    travel(start);
    costs=temporaryClearDuplicates();
    for(var n=0;n<costs.length;n++){
        console.log("from"+toLabel(costs[n]["from"])+" to "+toLabel(costs[n]["to"])+"//cost "+costs[n]["cost"]);
    }
    console.log("finish");
    return costs;
}



function travel(fork) {
  //  console.log("@"+toLabel(fork));
    var t=getNodeType(fork);
    if(t == "Good Ending" || t == "Bad Ending"){
        return 0;
    }else if(t == "Narrative" || t == "Goal"){
        var r=getDestinations(fork);
        return travel(r[0]);
    } else if (t == "Choice") {
        var flag=false;
        for(var i=0;i<choices.length;i++){
            if(choices[i]==fork){
                flag=true;
            }
        }
        if(!flag){ //if the  choice is unchecked
            choices.push(fork);
            original[original.length]={fork:fork,cCost:1};
        }else{ //if the choice has been checked
            //TODO; des edw ti paizei
            for(var m=0;m<original.length;m++){
                if(original[m]["fork"]==fork){ //if we reached the choice again while following his own path.(made a loop)
                   // console.log("return from"+toLabel(fork));
                    return 0;
                }
            }
            return fork; //fork has been checked but with no loop see ret below.**

        }
        for(var j=0;j<original.length;j++){
            //try {
                if (original[j]["fork"] != fork) {
                    var minCostAlreadyInside = minCostAlreadyInsideArray(original[j]["fork"], fork);
                    if (minCostAlreadyInside["m"] == Number.MAX_SAFE_INTEGER) {
                        costs.push({from: original[j]["fork"], to: fork, cost: original[j]["cCost"]});
                        original[j]["cCost"]++;
                        temporaryPath(original[j]["fork"],fork);
                    } else if (original[j]["cCost"] < minCostAlreadyInside["m"]) {
                        costs.splice(minCostAlreadyInside["p"], 1);
                        //console.log("spliced afrodite");
                        costs.push({from: original[j]["fork"], to: fork, cost: original[j]["cCost"]});
                        original[j]["cCost"]++;
                        temporaryPath(original[j]["fork"],fork);
                    }


                }
           /* }catch(e){
                console.log(e);
               // console.log(original);
                throw "exit...";
            }*/
        }

        var r = getDestinations(fork);
        var ret;
        for (var i = 0; i < r.length; i++) {
            //console.log(i);
            ret = travel(r[i]);

            if (ret != 0) { // **   from =original, to=cost[n][ret]["to"]
              //  console.log("!!!!!!!"+toLabel(fork)+" ret"+toLabel(ret));
                /*for (var n = 0; n < costs.length; n++) {
                    if (costs[n]["from"] == ret) {
                        alert("befpre=="+toLabel(costs[n]["from"]));
                        for (var m = 0; m < original.length; m++) {

                            var minCostAlreadyInside = minCostAlreadyInsideArray(original[m]["fork"], costs[n]["to"]);

                           if (minCostAlreadyInside["m"] == Number.MAX_SAFE_INTEGER) {
                              // alert("after=="+toLabel(costs[n]["from"]));
                                console.log("from"+toLabel(costs[n]["from"])+"to"+toLabel(costs[n]["to"])+" currentCost:"+costs[n]["cost"]);
                                costs.push({
                                    from: original[m]["fork"],
                                    to: costs[n]["to"],
                                    cost: costs[n]["cost"] + original[m]["cCost"]
                                });
                            } else if (original[m]["cCost"] < minCostAlreadyInside["m"]) {
                               alert("after=="+toLabel(costs[n]["from"]));
                               console.log("from"+toLabel(costs[n]["from"])+"to"+toLabel(costs[n]["to"])+" currentCost:"+costs[n]["cost"]);
                                costs.splice(minCostAlreadyInside["p"], 1);
                                //console.log("spliced afrodite");

                                costs.push({
                                    from: original[m]["fork"],
                                    to: costs[n]["to"],
                                    cost: costs[n]["cost"] + original[m]["cCost"] //no need for +1 because original[m][ccost] has been increased
                                });
                            }

                        }

                        //  costs.push({from: original[j]["fork"], to: fork, cost: original[j]["cCost"]});
                    }
                }*/
                costs.push({
                    from: fork,
                    to: ret,
                    cost: 1
                });
                for (var m = 0; m < original.length; m++) {
                    for (var n = 0; n < costs.length; n++) {
                        if (costs[n]["from"] == ret) {
                            var minCostAlreadyInside = minCostAlreadyInsideArray(original[m]["fork"], costs[n]["to"]);

                            if (minCostAlreadyInside["m"] == Number.MAX_SAFE_INTEGER) {
                                // alert("after=="+toLabel(costs[n]["from"]));
                               // console.log("from"+toLabel(original[m]["fork"])+"to"+toLabel(costs[n]["to"])+"through"+toLabel(ret)+" currentCost:"+costs[n]["cost"]+"+"+original[m]["cCost"]);
                                costs.push({
                                    from: original[m]["fork"],
                                    to: costs[n]["to"],
                                    cost: costs[n]["cost"] + original[m]["cCost"]
                                });
                                temporaryPath(original[m]["fork"],costs[n]["to"]);
                            } else if ((original[m]["cCost"]+costs[n]["cost"]) < minCostAlreadyInside["m"]) {
                               // alert("after=="+toLabel(costs[n]["from"]));
                               // console.log("from"+toLabel(original[m]["fork"])+"to"+toLabel(costs[n]["to"])+"through"+toLabel(ret)+" currentCost:"+costs[n]["cost"]+"+"+original[m]["cCost"]);
                                costs.splice(minCostAlreadyInside["p"], 1);
                                //console.log("spliced afrodite");

                                costs.push({
                                    from: original[m]["fork"],
                                    to: costs[n]["to"],
                                    cost: costs[n]["cost"] + original[m]["cCost"] //no need for +1 because original[m][ccost] has been increased
                                });
                                temporaryPath(original[m]["fork"],costs[n]["to"]);
                            }
                        }
                    }


                }
            }
        }
        for(var k=0;k<original.length;k++){
            if(original[k]["fork"]==fork){
                original.splice(k,1);
                //costs.push({from: fork, to: "finished", cost: 0});
                break;
            }else{ //!!!!!!!
                original[k]["cCost"]--;
            }
        }
        return 0;
    }
   // console.log("=====");
}


function minCostAlreadyInsideArray(from,to) {
    var min=Number.MAX_SAFE_INTEGER;
    var pos=0;
    for(var i=0;i<costs.length;i++){
        if(costs[i]["from"]==from && costs[i]["to"]==to){
            if(costs[i]["cost"]<min){
                min=costs[i]["cost"];
                pos=i;
            }
        }
    }
    //console.log(min+"///"+pos)
    //return {m:min,p:pos};
    return {m:Number.MAX_SAFE_INTEGER,p:0};
}


/**
 * TODO! In later version implement minCostAlreadyInsideArray to delete duplicates as the function runs.
 */
function temporaryPath(from,to) {
    for(var i=0;i<path.length;i++){
        if(path[i]["from"]==from && path[i]["to"]==to){
            return;
        }
    }
    path.push({from:from,to:to});
    return;

}


function temporaryClearDuplicates() {
    var finalCost=[];
    for(var i=0;i<path.length;i++){
        var min=Number.MAX_SAFE_INTEGER;
        for(var n=0;n<costs.length;n++){
            if(costs[n]["from"]==path[i]["from"] && costs[n]["to"]==path[i]["to"]){
                if(costs[n]["cost"]<min){
                    min=costs[n]["cost"];
                }
            }
        }
        if(min!=Number.MAX_SAFE_INTEGER){
            finalCost.push({from:path[i]["from"],to:path[i]["to"],cost:min});
        }else{
            alert("ERROR");
            //TODO
        }
    }
    return finalCost;
}


var sum=[];
var num=[];
function ADbC(nodes,edges) {
    var costs=findCost(nodes,edges);

    for(var i=0;i<choices.length;i++){
        sum[i]=0;
        num[i]=0;
    }
    console.log(costs);
    for(var k=0;k<costs.length;k++){
        sum[choices.indexOf(costs[k]["from"])]+=costs[k]["cost"];
        num[choices.indexOf(costs[k]["from"])]+=1;
        //sum+=costs[k]["cost"];
      //  sum[costs[i].from]

    }
    console.log(sum);
    console.log(num);
    var finalSum=0;
    for(k=0;k<sum.length;k++){
        if(sum[k]==0 || num[k]==0){
            continue;
        }
        finalSum+=sum[k]/num[k];
    }
    //alert(Math.round((sum/noC)*100)/100);
    return Math.round((finalSum/choices.length)*100)/100;
}


/**
 * Level of Narrative metric consists of all the previous aforementioned metrics in each of the 4 narrative types.
 */

var noC2;
function numberOfLevelOfNarrative(nodes) {
    noC=[0,0,0,0,0];
    noE=[0,0,0,0,0];
    noA=[0,0,0,0,0];
    noG=[0,0,0,0,0];
    var i;
    var temp;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        //alert(temp.type);
        if(temp.type=="Choice"){
            noC[getNodeNarrative(temp)]++;
        }else if(temp.type=="Good Ending" || temp.type=="Bad Ending"){
            noE[getNodeNarrative(temp)]++;
        }else if(temp.type=="Narrative"){
            noA[getNodeNarrative(temp)]++;
        }else if(temp.type=="Goal"){
            noG[getNodeNarrative(temp)]++;
        }
    }
    noC2=noC;
    return {noC:noC,noE:noE,noA:noA,noG:noG};
}


function getNodeNarrative(node) {
    try {
        if (node.narrative == "Exposition") {
            return 0;
        } else if (node.narrative == "Rising Action") {
            return 1;
        } else if (node.narrative == "Climax") {
            return 2;
        } else if (node.narrative == "Falling Action") {
            return 3;
        }
        else if (node.narrative == "Conclusion") {
            return 4;
        }
    }catch (err){
        alert("FATAL ERROR ") //TODO
    }

}

function apheLoN(nodes,edges) {
    var apHE=[0,0,0,0,0];
    var total=[0,0,0,0,0];
    var temp;
    var i;
    var nr;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        if(temp.type=="Good Ending"){
            nr=getNodeNarrative(temp);
            apHE[nr]+=getDestinationEdges(edges,temp.id);
            total[nr]++;
        }
    }
    apHE[0]=apHE[0]/total[0];
    apHE[1]=apHE[1]/total[1];
    apHE[2]=apHE[2]/total[2];
    apHE[3]=apHE[3]/total[3];
    apHE[4]=apHE[4]/total[4];
    apHE[0]= Math.round(apHE[0]*100)/100
    apHE[1]= Math.round(apHE[1]*100)/100
    apHE[2]= Math.round(apHE[2]*100)/100
    apHE[3]= Math.round(apHE[3]*100)/100
    apHE[4]= Math.round(apHE[4]*100)/100
    //alert(apHE);
    return apHE;
}


function apcLoN(nodes,edges) {
    var apC=[0,0,0,0,0];
    var total=[0,0,0,0,0];
    var temp;
    var i;
    for(i=0;i<nodes.length;i++){
        temp=nodes[i];
        if(temp.type=="Choice"){
            nr=getNodeNarrative(temp);
            apC[nr]+=getStartPointEdges(edges,temp.id);
            total[nr]++;
        }
    }
    apC[0]=apC[0]/total[0];
    apC[1]=apC[1]/total[1];
    apC[2]=apC[2]/total[2];
    apC[3]=apC[3]/total[3];
    apC[4]=apC[4]/total[4];
    apC[0]= Math.round(apC[0]*100)/100;
    apC[1]= Math.round(apC[1]*100)/100;
    apC[2]= Math.round(apC[2]*100)/100;
    apC[3]= Math.round(apC[3]*100)/100;
    apC[4]= Math.round(apC[4]*100)/100;
    //alert(apC);
    return apC;
}


function choiceMetricsLoN(edges) {
    var w5=[0,0,0,0,0];
    var apIC=[0,0,0,0,0];
    var aci=[0,0,0,0,0];
    for(var i=0;i<wForks.length;i++){
        aci[getNodeNarrative(wForks[i]["id"])]+=wForks[i]["w"];
        if(wForks[i]["w"]==5){
            w5[getNodeNarrative(wForks[i]["id"])]++;
            apIC[getNodeNarrative(wForks[i]["id"])]+=getStartPointEdges(edges,wForks[i]["id"]);
        }
    }
    var temp1=[0,0,0,0,0];
    temp1[0]=aci[0]/wForks.length;
    temp1[1]=aci[1]/wForks.length;
    temp1[2]=aci[2]/wForks.length;
    temp1[3]=aci[3]/wForks.length;
    temp1[4]=aci[4]/wForks.length;
    var temp2=[0,0,0,0,0]
    temp2[0]=apIC[0]/w5;
    temp2[1]=apIC[1]/w5;
    temp2[2]=apIC[2]/w5;
    temp2[3]=apIC[3]/w5;
    temp2[4]=apIC[4]/w5;


    temp1[0]=Math.round(temp1[0]*100)/100;
    temp1[1]=Math.round(temp1[1]*100)/100;
    temp1[2]=Math.round(temp1[2]*100)/100;
    temp1[3]=Math.round(temp1[3]*100)/100;
    temp1[4]=Math.round(temp1[4]*100)/100;

    temp2[0]=Math.round(temp2[0]*100)/100;
    temp2[1]=Math.round(temp2[1]*100)/100;
    temp2[2]=Math.round(temp2[2]*100)/100;
    temp2[3]=Math.round(temp2[3]*100)/100;
    temp2[4]=Math.round(temp2[4]*100)/100;

    return{acI:temp1,apIC:temp2,niC:w5}
}

function ADbCLoN(nodes,edges) {
    var costs=findCost(nodes,edges);
    var ret=[0,0,0,0,0];
    for(var i=0;i<choices.length;i++){
        sum[i]=0;
        num[i]=0;
        ret[getNodeNarrative(choices[i])]++;
    }
    console.log(noC2[0]);
    for(i=0;i<nodes.length;i++){
        ret[getNodeNarrative(nodes[i])]++;
    }
    for(var k=0;k<costs.length;k++){
        sum[choices.indexOf(costs[k]["from"])]+=costs[k]["cost"];
        num[choices.indexOf(costs[k]["from"])]+=1;
        //sum+=costs[k]["cost"];
        //  sum[costs[i].from]

    }
    var finalSum=[0,0,0,0,0];
   // alert(sum.length+" "+num.length+" "+nodes.length);
    for(k=0;k<sum.length;k++){
        if(sum[k]==0 || num[k]==0){
            continue;
        }
        for(var j=0;j<nodes.length;j++){

            if(nodes[j].id==choices[k]){
                //alert(nodes[j].id+" "+costs[k]["from"]);
                console.log(getNodeNarrative(nodes[j]));
                finalSum[getNodeNarrative(nodes[j])]+=sum[k]/num[k];
            }
        }

    }
    finalSum[0]=finalSum[0]/noC2[0];
    finalSum[1]=finalSum[1]/noC2[1];
    finalSum[2]=finalSum[2]/noC2[2];
    finalSum[3]=finalSum[3]/noC2[3];
    finalSum[4]=finalSum[4]/noC2[4];

    finalSum[0]=Math.round(finalSum[0]*100)/100;
    finalSum[1]=Math.round(finalSum[1]*100)/100;
    finalSum[2]=Math.round(finalSum[2]*100)/100;
    finalSum[3]=Math.round(finalSum[3]*100)/100;
    //finalSum[4]=Math.round(finalSum[4]*100)/100;
    finalSum[4]=0;

    return finalSum;
}