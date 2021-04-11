/*
Function to confirm if a user wants to delete a document or not.
 */
function confirmDelete(){
    let con = confirm('Are you sure you want to delete this game design document?');

    if(con){
        return true;
    }else{
        return false;
    }
}

/*
Function that shows and hides categories from write page.
 */
function showCategories(el, fontArrow){
    let x = document.getElementById(el);
    let y = document.getElementById(fontArrow);

    if(x.style.display === 'block'){
        x.style.display = 'none';
        y.className = 'fa fa-chevron-down'
    }else{
        x.style.display = 'block';
        y.className = 'fa fa-chevron-up'
    }
}

/*
Function to show and hide the password.
 */
function showPassword(el) {
    let x = document.getElementById(el);
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

/*
Function to show elements.
 */
function showElement(el){
    let x = document.getElementById(el);
    x.style.display = 'block';
}

/*
Function to hide elements.
 */
function hideElement(el){
    let x = document.getElementById(el);
    x.style.display = 'none';
}