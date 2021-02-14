<?php
//Variablen
$bind_token_file = "[HIER_ABSOLUTEN_PFAD+VERZEICHNIS+DATEINAMEN einf&uuml;gen]";
//Funktionen 
if(!function_exists("read_file")){
function read_file($file){
    if(file_exists($file)){
        $fs = filesize($file);
        if($fs >= 1){
            //Dateiinhalt auslesen
            $handle = fopen($file,"r");
            $back = fread($handle,$fs);
            fclose($handle);
            //Ausgelesene Datei zurückgeben
            return $back;    
        }else{
            //Dateigröße = 0 sprich leer
            //Leeren String zurückgeben
            return "";
        }
    }else{
        //Datei existiert nicht also leeren String zurückgeben
        return "";
    }
}
}
if(!function_exists("write_file")){
function write_file($dateiname,$dateiinhalt,$modus){
    $handle = fopen($dateiname,$modus);
    fwrite($handle,$dateiinhalt);
    fclose($handle);
    return true;
}
}
if(!function_exists("generate_token")){
//Token haben eine definierte länge von 32 Zeichen
//Ein Token kann aus Kleinbuchstaben und Zahlen bestehen
function generate_token(){
    $char = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9","0");
    $token = "";
    for($i=0; $i<32; $i++){
        $token .= $char[array_rand($char)];    
    }
    return $token;
}
}
if(!function_exists("get_bind_token")){
//Ein Bind-Token besteht aus 2x 32-Zeichen langen normalen Token
//Hierbei muss überprüft werden ob schon ein Bind-Token besteht
function get_bind_token(){
    //Erst Generieren lassen
    $bind_token = generate_token() . generate_token();
    if(!file_exists($bind_token_file)){
        //Es existiert kein Bind-Token
        write_file($bind_token_file,$bind_token,"w");
    }else{
        //Die Datei existiert es kann aber sein das sie mit dem Standart-Bind Token "0000" gefüllt ist
        //um das rauszufinden muss sie geöffnet und ausgelesen werden
        if(read_file($bind_token_file) == "0000"){
            write_file($bind_token_file,$bind_token,"w");    
        }else{
            //Es existiert schon ein Bind-Token
            return false;
        }
    }
}
}



//First-Download funktionen
function remote_read($url){
    
}
function generate_non_existing_dirs($local_file){
    
}
function get_file_list(){
    //HIER VON GITHUB DATEI ÖFFNEN DIE DATEISTAMM ENTHÄLT (AM BESTEN JSON-DATEI)    
}
function download_now($datei){
    $inh = remote_read($datei["remote"]);
    generate_non_existing_dirs($datei["local"]);
    write_file($datei['local'],$inh,"w");    
}
function download(){
    $file_list = get_file_list();
    foreach($file_list as $file){
        download_now($file);
    }
}
?>