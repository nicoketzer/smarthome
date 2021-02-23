<?php 
if(!function_exists("exist_site")){
function exist_site($site){
    global $abs_path;
    $file = $abs_path . "/res/html/".$site;
    if(file_exists($file)){
        return true;
    }else{
        return false;
    }     
}
}
if(!function_exists("is_login_site")){
function is_login_site($site){

}
}
if(!function_exists("load_login_site")){
function load_login_site($site,$erg){

}
}
if(!function_exists("load_site")){
function load_site($site){
    
}
}
?>