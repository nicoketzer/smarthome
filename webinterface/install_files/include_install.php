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
            //Ausgelesene Datei zur�ckgeben
            return $back;    
        }else{
            //Dateigr��e = 0 sprich leer
            //Leeren String zur�ckgeben
            return "";
        }
    }else{
        //Datei existiert nicht also leeren String zur�ckgeben
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
//Token haben eine definierte l�nge von 32 Zeichen
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
//Hierbei muss �berpr�ft werden ob schon ein Bind-Token besteht
function get_bind_token(){
    //Erst Generieren lassen
    $bind_token = generate_token() . generate_token();
    if(!file_exists($bind_token_file)){
        //Es existiert kein Bind-Token
        write_file($bind_token_file,$bind_token,"w");
        return true;
    }else{
        //Die Datei existiert es kann aber sein das sie mit dem Standart-Bind Token "0000" gef�llt ist
        //um das rauszufinden muss sie ge�ffnet und ausgelesen werden
        if(read_file($bind_token_file) == "0000"){
            write_file($bind_token_file,$bind_token,"w");
            return true;    
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
    //HIER VON GITHUB DATEI �FFNEN DIE DATEISTAMM ENTH�LT (AM BESTEN JSON-DATEI)    
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
function new_install(){
    //Hier �berpr�fung einbauen ob schon eine Installation vorhanden ist
    if(is_file("./LICENSE") || is_dir("./res") || is_file("./index.php") || is_file("./work.php")){
        //Wenn eine dieser Datein/Ordner existiert ist es keine neue installation mehr
        return false;
    }else{
        //Nichts vorhanden --> Neue installation
        return true;
    }
}
function set_stage($stage){
    write_file("stage",$stage,"w");
}
//Main-Funktion
function do_install(){
    //Als erstes alles Downloaden und entpacken lassen
    if(new_install()){
        if(download()){
            //Jetzt sind alle Datein soweit Verf�gbar
            #Anschlie�end neuen Bind-Token erstellen
            if(get_bind_token()){
                //Bind Token Fertig
                set_stage(1);   
            }else{
                //Es konnte kein neuer Bind-Token erzeugt werden
                echo "Die installation kann keinen neuen Bind-Token erzeugen. Fehlschlag!";
            }            
        }else{
            //Download oder Entpacken fehlgeschlagen
            echo "Die installation konnte nicht gestartet werden da der Download fehlschlug";    
        } 
    }else{
        echo "install.php kann hier nicht mehr ausgef&uuml;hrt werden";
        unlink("install.php");
    }   
}
function self_test($url){
    $options = array();
    $context = stream_context_create($options);
    $back = file_get_contents($url,false,$context);
    if($back == "ok"){
        //Ist erreichbar
        set_stage("5");
    }else{
        echo "Die IP-Adresse und der Port f&uuml;hren nicht zu diesen Server. Bitte Versuche es erneut";
        echo "Mit dem anh&auml;ngen von ?skip_stage=true an den URL &uuml;berspringst du diesen Test";
    }    
}
function finish_install(){
    //Alle Installationsdatein l�schen
    unlink("install.php");
    unlink("self_test_url");
    
}
?>