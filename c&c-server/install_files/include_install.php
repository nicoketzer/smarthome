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
    return $bind_token;
}
}
//Move
// Function to remove folders and files
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
    if(read_file("stage") == ""){
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
        //Starten des Installationsprozesses
        set_stage("1");
    }
}
if(!function_exists("constrain")){
function constrain($val, $min, $max){
    if($min < $max){
        //Normaler Ablauf
        if(floatval($val) >= floatval($max)){
            //Value ist größer als das MAX-Value also wird das Max-Value zurückgegeben
            return $max;
        }else{
            if(floatval($val) <= floatval($min)){
                //Value ist kleiner als das MIN-Value also wird das Min-Value zurückgegebn
                return $min;
            }else{
                //Wenn nichts zutrifft heißt das das das Value zw. MIN und MAX liegt also wird das eig. Value zurückgegeben
                return $val;
            }
        }
    }else if($min == $max){
        //Wenn min und max gleich sind kann egal was kommt nur min bzw. max zurückgegeben werden
        return $min;    
    }else{
        //Wenn max kleiner wie Min ist werden die Werte vertauscht und das Ergebnis davon zurückgegeben
        return constrain($val, $max, $min);
    }
}
}
if(!function_exists("valid_json")){
function valid_json($json) {
    json_decode($json);
    return (json_last_error() == JSON_ERROR_NONE);
}
}
if(!function_exists("fetch_data")){
function fetch_data($url){
    $process = curl_init($url);
    curl_setopt($process, CURLOPT_HTTPHEADER, array ('content-type: text/plain',"Cache-Control: no-cache"));
    curl_setopt($process, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    //Damit immer die neuste Version von GitHub gezogen wird
    curl_setopt($process, CURLOPT_FRESH_CONNECT, TRUE);
    $response_body = curl_exec($process);
    $http_code = curl_getinfo($process, CURLINFO_HTTP_CODE);
    $error = "";
    if($http_code >= 300) {
      $error = "FETCH_ERROR_".$http_code;
    }
    curl_close($process);
    echo $response_body;
    $ret_arr = array("main"=>$response_body,"title"=>"","ref"=>$url,"error"=>$error,"resp_code"=>$http_code);
    return $ret_arr;
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
if(!function_exists("all_filled")){
function all_filled($search_arr, $para){
    if(all_set($search_arr, $para){
        if(is_array($para)){
            $all_filled = true;
            foreach($para as $one_para){
                if($all_filled){
                    $all_filled = all_filled($search_arr,$one_para);
                }else{
                    break;
                }
            }
            return $all_filled;    
        }else{
            if(!empty($search_arr[$para]) && $search_arr[$para] != null && $search_arr != ""){
                return true;
            }else{
                return false;
            }
        }
    }else{
        //Einige Parameter sind nicht mal gesetzt also können sie auch nicht gefüllt sein
        return false;
    }
}
}
if(!function_exists("all_set"){
function all_set($search_arr, $para){
    if(is_array($para)){
        $all_set = true;
        foreach($para as $one_para){
            if($all_set){
                $all_set = all_set($search_arr,$one_para);
            }else{
                break;
            }    
        }
        return $all_set;    
    }else{
        if(isset($search_arr[$para])){
            return true;
        }else{
            return false;
        }    
    }
}
}

//Implementierung der einzelnen Installations-Step´s über die install - Klasse
class install{
    public function __constructor(){
        return true;    
    }
    private function new_downloaded_var_file(){
        $local_var_file = read_file("res/php/var.php");
        $remote_var_file = fetch_data("https://raw.githubusercontent.com/nicoketzer/smarthome_cc/main/res/php/var.php");
        if(trim(strtolower($local_var_file)) == trim(strtolower($remote_var_file))){
            //Es handelt sich noch um eine "frische" Installation
            return true;
        }else{
            //Es ist schon das x-te Mal das die Installation durchgeführt wird.
            return false;
        }
    }
    private function fill_into_var($where,$what){
        $var_file = read_file("res/php/var.php");
        $tmp = explode($where,$var_file);
        $new_var_file = $tmp[0];
        for($i = 1; $i<count($tmp); $i++){
            $new_var_file .= $what . $tmp[$i];
        }
        write_file("/res/php/var.php",$new_var_file,"w");
        return true;
    }
    public function stage_1($stage_data){
        if($this->new_downloaded_var_file()){
            #Zugangsdaten Remote Mysql-Server
            $mysqli_server = $stage_data["mysqli_server"];
            $mysqli_bn = $stage_data["mysqli_bn"];
            $mysqli_pw = $stage_data["mysqli_pw"];
            $mysqli_db = $stage_data["mysqli_db"];
            #Zugangsdaten Lokaler Mysql-Server
            $mysqli_offline_server = ($stage_data["mysqli_offline_server"] !== null ? $stage_data['mysqli_offline_server'] : "localhost");
            $mysqli_offline_bn = $stage_data["mysqli_offline_bn"];
            $mysqli_offline_pw = $stage_data["mysqli_offline_pw"];
            $mysqli_offline_db = $stage_data["mysqli_offline_db"];
            //Zusätzliche Daten generieren
            $cc_bind_token = get_bind_token();
            $cc_cronjob_ident = generate_token();
            $cc_work_ident = generate_token();
            $cc_ip_update_token = generate_token();
            //Adressdaten einfügen
            $cc_port_extern = $stage_data["cc_port"];
            $cc_server_addr = ($stage_data["cc_addr"] !== null ? $stage_data['cc_addr'] : "localhost");
            $cc_server_hostname = $stage_data["cc_host"];
            $cc_server_name = ($stage_data["cc_name"] !== null ? $stage_data['cc_name'] : "Comand and Controll Server - Smarthome(Default)");
            
            //Einfügen in die var.php
            $this->fill_into_var("__MYSQLI_SERVER__",$mysqli_server);
            $this->fill_into_var("__MYSQLI_BN__",$mysqli_bn);
            $this->fill_into_var("__MYSQLI_PW__",$mysqli_pw);
            $this->fill_into_var("__MYSQLI_DB__",$mysqli_db);
            $this->fill_into_var("__MYSQLI_OFFLINE_SERVER__",$mysqli_offline_server);
            $this->fill_into_var("__MYSQLI_OFFLINE_BN__",$mysqli_offline_bn);
            $this->fill_into_var("__MYSQLI_OFFLINE_PW__",$mysqli_offline_pw);
            $this->fill_into_var("__MYSQLI_OFFLINE_DB__",$mysqli_offline_db);
            $this->fill_into_var("__CC_BIND_TOKEN__",$cc_bind_token);
            $this->fill_into_var("__CC_CRONJOB_IDENT__",$cc_cronjob_ident);
            $this->fill_into_var("__CC_WORK_IDENT__",$cc_work_ident);
            $this->fill_into_var("__CC_IP_UPDATE_TOKEN__",$cc_ip_update_token);
            $this->fill_into_var("__CC_PORT_EXTERN__",$cc_port_extern);
            $this->fill_into_var("__CC_SERVER_ADDR__",$cc_server_addr);
            $this->fill_into_var("__CC_SERVER_HOSTNAME__",$cc_server_hostname);
            $this->fill_into_var("__CC_SERVER_NAME__",$cc_server_name);
            return "all_ok";
        }else{
            //Ersetzen
            if(read_file("stage") == "1"){
                write_file("res/php/var.php",fetch_data("https://raw.githubusercontent.com/nicoketzer/smarthome_cc/main/res/php/var.php"),"w");
                /*
                 * !ACHTUNG!
                 * Beim ersetzen der var.php entsteht grundsätzlich ein Risiko da es dadurch zur "feindlichen" übernahme kommen kann.
                 * Es wird jedoch hier doch durchgeführt da stage_1 nur ausgeführt wird wenn im File "stage" die Zahl 1 steht sprich
                 * für eine "feindliche" Übernahme müsste der Hacker/Unbefugte Schreibzugriff auf das "stage" File haben und dann kann er 
                 * so und so schon manipulieren da er ja dann auch auf andere Datein Schreibzugriff hat.
                 * Es wird vorher auch nochmal abgefragt ob man wirklch in Stage 1 ist 
                 */
                 //Den Schritt neu starten
                 return $this->stage_1($stage_data);
             }else{
                 //Sollte dies Klammer hier ausgeführt werden ist Grundsätzlich davon auszugehen das es sich um einen Hacking-Versuch handelt
                 //Der löscht in diesem Falle die install.php und überschreibt vorher die alle anderen .php datein so das kein weiterer schaden
                 //verursacht werden kann
                 write_file("work.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("sync_db.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("stage","'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'","w");
                 write_file("install.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("index.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("cronjob.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("cronjob.imp.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("all_func.tmp.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/all.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/email.func.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/fb_device_list.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/http_req.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/rw.func.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/sql.func.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 write_file("res/php/var.php","<? echo 'ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde'; exit; ?>","w");
                 //Beenden mit Kompletter Fehlermeldung
                 die("ich habe mich selbst deaktiviert da ein hackingversuch festgestellt wurde");
                 //Sicherheitshalber noch ein exit nicht das die die() Funktion vorher deaktiviert wurde
                 exit;
                 //Sicherheitshalbe noch das Skript solange laufen lassen das es mit einem Timeout beendet wird wenn auch die exit-Funktion deaktiviert wurde
                 //Umsetzung doppelt ausgeführt
                 for($i = 2; $i = 1; $i = 2){
                     true;
                 }
                 while(true){
                     true;
                 }
             }
        }
    }
    public function stage_2(){

    }
    public function stage_2_1(){

    }
    public function stage_3(){

    }
    public function stage_3_1(){

    }
    public function stage_3_2(){

    }
    public function stage_4(){

    }
    public function stage_5(){

    }
    public function finish_install(){
        
    }
}
?>