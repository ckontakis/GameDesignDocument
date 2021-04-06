function confirmDelete(){
    let con = confirm('Are you sure you want to delete this game design document?');

    if(con){
        return true;
    }else{
        return false;
    }
}

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