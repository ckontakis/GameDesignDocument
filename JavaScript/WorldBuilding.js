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
    if(x.style.display === 'none'){
        x.style.display = 'block';
        y.className = 'fa fa-minus';
    }else{
        x.style.display = 'none';
        y.className = 'fa fa-plus';
    }
}

/*
Function to show and hide the password.
 */
function showPassword() {
    let x = document.getElementById("password");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}