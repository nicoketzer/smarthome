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
        return true;
    }else{
        //Die Datei existiert es kann aber sein das sie mit dem Standart-Bind Token "0000" gefüllt ist
        //um das rauszufinden muss sie geöffnet und ausgelesen werden
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
//Move
// Function to remove folders and files
function rrmdir($dir){
    if(is_dir($dir)){
        $files = scandir($dir);
        foreach ($files as $file){
            if($file != "." && $file != ".."){
                rrmdir("$dir/$file");
            }
        }
        rmdir($dir);
    }else if(file_exists($dir)){
        unlink($dir);
    }
}
function generate_dep_dirs($source_dir,$at){
    $tmp = explode("/",$source_dir);
    if($at <= (count($tmp)-1)){
        $dir_for_lookup = "";
        for($i = 0; $i <= $at; $i++){
            if($tmp[$i] != ""){
                $dir_for_lookup .= $tmp[$i] . "/";
            }
        }
        if(!is_dir($dir_for_lookup) && $dir_for_lookup != ""){
            mkdir($dir_for_lookup);
        }
        $at_new = $at+1;
        generate_dep_dirs($source_dir,$at_new);
    }else{
        return true;
    }
}
function test_all_dep_dirs($find_dirs){
    $tmp = explode("/",$find_dirs);
    //$i=1 weil es sich bei dem ersten Element in dem array um das source dir an sich handelt
    //count($tmp)-1 da count bei 1 und nicht bei 0 das zählen anfängt
    $search_dirs = "";
    for($i=1; $i<count($tmp)-1;$i++){
        $search_dirs .= $tmp[$i] . "/";
    }
    generate_dep_dirs($search_dirs,0);
    $data_array = array($search_dirs,$tmp[count($tmp)-1]);
    return $data_array;
}
function copy_file_to_dest($source_file, $ziel_dir){
    //Daten bekommen und gebrauchte Verzeichnisse erstellen lassen
    $data = test_all_dep_dirs($source_file);
    //Daten auslesen
    $dep_dir = $data[0];
    $name = $data[1];
    $dest_file = $ziel_dir . "/" . $dep_dir . "/" . $name;
    //Kopieren
    copy($source_file,$dest_file);
}
function can_del_dir($source_file){
    //Herausbekommen des Verzeichnisses
    $tmp = explode("/",$source_file);
    $source_dir = "";
    for($i = 0; $i<count($tmp)-1; $i++){
        $source_dir .= $tmp[$i] . "/";
    }
    $parent_dir = "";
    for($i = 0; $i<count($tmp)-2; $i++){
        $parent_dir .= $tmp[$i] . "/";
    }
    //Scannen des Verzeichnisses
    $scan = scandir($source_dir);
    if(count($scan) <= 2){
        //Es gibt nur noch die Elemente . und .. somit kann das Verzeichniss gelöscht werden
        rmdir($source_dir);
        //Schauen ob dann Parent-Dir auch löschbar ist
        if(is_dir($parent_dir) && $parent_dir != ""){
            can_del_dir($parent_dir);
        }
    }
}
// Function to Copy folders and files
function copy_all_files($source_dir, $ziel_dir){
    //Neue Funktion
    if(is_dir($source_dir)){
        $scan = scandir($source_dir);
        foreach($scan as $scan_erg){
            if($scan_erg != "." && $scan_erg != ".."){
                //Es handelt sich um normale Datei
                #Abfrage ob es sich um das source-dir handelt da sich das im normalfall
                #im Ziel-Dir befindet
                if($ziel_dir . "/" . $scan_erg != $source_dir){
                    copy_all_files($source_dir . "/" . $scan_erg, $ziel_dir);
                }
            }
        }
    }else{
        //Es handelt sich um eine Datei
        copy_file_to_dest($source_dir,$ziel_dir);
        //Anschließend in Sourcedir löschen
        unlink($source_dir);
        //Überprüfen ob Verzeichniss auch gelöscht werden kann
        can_del_dir($source_dir);
    }
}


function set_stage($stage){
    write_file("stage",$stage,"w");
}
function do_pre_install(){
    //Herunterladen der .zip - Datei des Repo´s
    $url_repo_zip = "https://codeload.github.com/nicoketzer/smarthome_cc/zip/main";
    $zip_file = "main.zip";
    //Herunterladen
    $process = curl_init($url_repo_zip);
    curl_setopt($process, CURLOPT_HTTPHEADER, array ('content-type: application/zip',"Cache-Control: no-cache"));
    curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    //Damit immer die neuste Version von GitHub gezogen wird
    curl_setopt($process, CURLOPT_FRESH_CONNECT, TRUE);
    $response_body = curl_exec($process);
    $http_code = curl_getinfo($process, CURLINFO_HTTP_CODE);
    if($http_code >= 300) {
      die("Unexpected Response Code: ${http_code}: ${response_body}");
    }
    curl_close($process);
    //Speichern des heruntergeladenen
    $handle = fopen($zip_file,"w");
    fwrite($handle,$response_body);
    fclose($handle);
    //Entpacken der .zip
    $zip = new ZipArchive;
    $res = $zip->open($zip_file);
    if ($res === TRUE) {
      $zip->extractTo('./');
      $zip->close();
    } else {
      die ("Unziping Failed!");
    }
    //Entpackete Dateien Verschieben
    #Liegen momentan in "smarthome_cc-main" - Ordner
    #Um später kompatibel zu sein mit anderen Branch´s wird das letzte Teil
    #des URL´s als Zusatz "-[ENDE_URL]" Verwendet da sich der URL immer mit der Zip
    #Datei selbst ändert
    $tmp_arr = explode("/",$url_repo_zip);
    $branch = $tmp_arr[count($tmp_arr)-1];
    $respo_name = $tmp_arr[count($tmp_arr)-3];
    $folder_name = $respo_name . "-" . $branch;
    if(!is_dir($folder_name)){
        //Nicht geglückt das ZIP-Verzeichnis zu finden
        die("Entzipte Ordnerstruktur wurde nicht gefunden. Name: ". $folder_name . " Error: Not Found");
    }
    copy_all_files($folder_name, ".");
    //Löschen der .zip
    unlink($zip_file);
}
//Main-Funktion
function do_install(){
    //Als erstes alles Downloaden und entpacken lassen
    if(new_install()){
        if(download()){
            //Jetzt sind alle Datein soweit Verfügbar
            #Anschließend neuen Bind-Token erstellen
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
    //Alle Installationsdatein löschen
    unlink("install.php");
    unlink("self_test_url");

}
?>