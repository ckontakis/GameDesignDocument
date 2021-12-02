/**
 * Untitles Thesis App JavaScript for tab management
 *
 * Copyright Nick Baf (Bafatakis) 2017. Untitled Thesis App.
 *
 * nickbaf@icloud.com
 */


//GLOBAL VARIABLES
/**
 * Session Storage Overview-Explanation:
 *
 * ProjectTitle:    The project title
 * charModels:      array w/ the names of all character Models
 * storyModels:     array w/ the names of all story Models
 * charData+Name:   character model data for "Name" model
 * storyData+Name:  story model data for "Name" model
 * modelName:       the current open character model
 * storyName:      the current open story model
 */

/**
 * Function for opening a new character tab when the user is in the main menu.
 * @param name the model's name
 */
function openCharTab(name) {
    sessionStorage.setItem("modelName", name); //sets the variable in order to open the correct model
    window.location = "charactermodel.php"; //redirects user

}
/**
 * Function for opening a new flow chart tab when the user is in the main menu.
 * @param name the model's name
 */
function openStoryTab(name) {
    sessionStorage.setItem("storyName", name); //sets the variable in order to open the correct model
    window.location = "flowchart.php"; //redirects user

}

function openMetricsTab(name) {
    sessionStorage.setItem("metricName", name); //sets the variable in order to open the correct model
    window.location = "metrics.php"; //redirects user
}


/**
 * This function creates the tabs in the left side menu
 * @param type -char, write the character model tabs.-story, write the story tabs
 */
function writeTabs(type) {
    //if charModels is empty dont bother make tabs
    if (type == "char" && (sessionStorage.getItem("charModels") != null)) {
        charModels = JSON.parse(sessionStorage.getItem("charModels"));
        for (i = 0; i < charModels.length; i++) { //for all char models
            var tempName = charModels[i];
            var el = document.createElement("a");
            el.appendChild(document.createTextNode(tempName));
            var oncl = "openCharTab('";
            oncl = oncl.concat(tempName.toString());
            oncl = oncl.concat("');");
            console.log(oncl);
            el.setAttribute("onclick", oncl);
            var temp = document.getElementById("characterModel");
            var par = document.getElementById("cOriginal");
            var child = document.createElement("li");
            child.appendChild(el);
            child.setAttribute("id", "char" + tempName);
            temp.insertBefore(child, par);
        }
    } //if storyModels is empty dont bother make tabs
    else if (type == "story" && (sessionStorage.getItem("storyModels") != null)) {
        storyModels = JSON.parse(sessionStorage.getItem("storyModels"));
        for (i = 0; i < storyModels.length; i++) {
            var tempName = storyModels[i];
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
        }
    } else if (type == "metrics" && (sessionStorage.getItem("metrics") != null)) {
        metrics = JSON.parse(sessionStorage.getItem("metrics"));
        for (i = 0; i < metrics.length; i++) {
            var tempName = metrics[i];
            var el = document.createElement("a");
            el.appendChild(document.createTextNode(tempName));
            var oncl = "openMetricsTab('";
            oncl = oncl.concat(tempName.toString());
            oncl = oncl.concat("');");
            console.log(oncl);
            el.setAttribute("onclick", oncl);
            var temp = document.getElementById("metrics");
            var par = document.getElementById("compare");
            var child = document.createElement("li");
            child.appendChild(el);
            child.setAttribute("id", "metric" + tempName);
            temp.insertBefore(child, par);
        }
    }
}

/**
 * This function does the same job as writeTabs but in a different way.
 * It writes the tabs but with different onclick function so when te user presses the tab
 * the page wont be reloaded.This function is used for the tabs that are in the same category as the user.
 * E.x if the user is in a story tab the function is used only for the story tabs so when the user clicks a story tab
 * the network is displayed asynchronously without reloading the page .
 * @param type type -char, write the character model tabs.-story, write the story tabs
 */
function writeActiveTabs(type) { //na ginei gia story also
    if (type == "char") {
        charModels = JSON.parse(sessionStorage.getItem("charModels"));
        for (i = 0; i < charModels.length; i++) {
            var tempName = charModels[i];
            var el = document.createElement("a");
            el.appendChild(document.createTextNode(tempName));
            var oncl = "openActiveTab('char','"; //this function refers to coreApp.js
            oncl = oncl.concat(tempName.toString());
            oncl = oncl.concat("');");
            console.log(oncl);
            el.setAttribute("onclick", oncl);
            var temp = document.getElementById("characterModel");
            var par = document.getElementById("cOriginal");
            var child = document.createElement("li");
            child.appendChild(el);
            child.setAttribute("id", "char" + tempName);
            temp.insertBefore(child, par);
        }
    } else if (type == "story") {
        console.log("writing story active tabs");
        storyModels = JSON.parse(sessionStorage.getItem("storyModels"));
        for (i = 0; i < storyModels.length; i++) {
            var tempName = storyModels[i];
            var el = document.createElement("a");
            el.appendChild(document.createTextNode(tempName));
            var oncl = "openActiveTab('story','"; //this function refers to coreApp.js
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
        }

    } else if (type == "metrics") {
        console.log("writing metrics active tabs");
        metrics = JSON.parse(sessionStorage.getItem("metrics"));
        for (i = 0; i < metrics.length; i++) {
            var tempName = metrics[i];
            var el = document.createElement("a");
            el.appendChild(document.createTextNode(tempName));
            var oncl = "openActiveTab('metric','"; //this function refers to coreApp.js
            oncl = oncl.concat(tempName.toString());
            oncl = oncl.concat("');");
            console.log(oncl);
            el.setAttribute("onclick", oncl);
            var temp = document.getElementById("metrics");
            var par = document.getElementById("compare");
            var child = document.createElement("li");
            child.appendChild(el);
            child.setAttribute("id", "metric" + tempName);
            temp.insertBefore(child, par);
        }
    }
}

function deleteTab() {
    /**
     * if the user makes the request to delete a tab in character model.
     */
    if (window.location.href.search("charactermodel") > 0) {
        var name = sessionStorage.getItem("modelName");
        var r = confirm("Are you sure you want to Delete Tab " + name + " ?\nAll unsaved changes will be lost forever...");

        if (r == true) {

            sessionStorage.removeItem("charData" + name);
            sessionStorage.removeItem("modelName");
            var names = JSON.parse(sessionStorage.getItem("charModels"));
            for (var i = 0; i < names.length; i++) {
                console.log(names[i]);
                if (names[i] == name) {
                    names.splice(i, 1);
                }
            }
            sessionStorage.removeItem("charModels");
            sessionStorage.setItem("charModels", JSON.stringify(names));
            alert("Tab " + name + " deleted...");
            window.location = "index.php"; //redirects user
        }
        return;
    } else {

        /**
         * if the user makes the request from a flowchart tab.
         */
        var name = sessionStorage.getItem("storyName");
        var r = confirm("Are you sure you want to Delete the Tab " + name + " ?\nAll unsaved changes will be lost forever.");

        if (r == true) {

            sessionStorage.removeItem("storyData" + name);
            var names = JSON.parse(sessionStorage.getItem("storyModels"));
            for (var i = 0; i < names.length; i++) {
                console.log(names[i]);
                if (names[i] == name) {
                    names.splice(i, 1);
                }
            }
            sessionStorage.removeItem("storyModels");
            sessionStorage.setItem("storyModels", JSON.stringify(names));

            var names = JSON.parse(sessionStorage.getItem("metrics"));
            for (var i = 0; i < names.length; i++) {
                console.log(names[i]);
                if (names[i] == name) {
                    names.splice(i, 1);
                }
            }
            sessionStorage.removeItem("metrics");
            sessionStorage.setItem("metrics", JSON.stringify(names));
            alert("Tab " + name + " deleted...");
            window.location = "index.php"; //redirects user
        }
    }
}