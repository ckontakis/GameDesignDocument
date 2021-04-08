/*
Function to show and hide an element.
 */
function showFunction(el){
    let x = document.getElementById(el);
    if (x.style.visibility === "visible"){
        x.style.visibility = "hidden";
    }else{
        x.style.visibility = "visible";
    }
}

/*
Function to show and hide elements. The first parameter is the element that we want to show and hide and the second parameter
is the font of the button that we change.
 */
function showAndHide(el, btnFont){
    let x = document.getElementById(el);
    let y = document.getElementById(btnFont);

    let disp = window.getComputedStyle(x).display;

    if(disp === 'none'){
        x.style.display = 'block';
        y.className = 'fa fa-minus';
    }else{
        x.style.display = 'none';
        y.className = 'fa fa-plus';
    }
}