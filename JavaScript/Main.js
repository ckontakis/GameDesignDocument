function confirmDelete(){
    let con = confirm('Are you sure you want to delete this game design document?');

    if(con === true){
        return true;
    }else{
        return false;
    }
}