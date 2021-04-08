<?php
    //Infos
    #Javascript und CSS werden von Github bezogen sodass nur eine Datei gebraucht wird
    #Internetverbindung wird gebraucht
    //Download der Funktionen falls noch nicht geschehen
    $dep_file = "all_func.tmp.php";
    if(!file_exists($dep_file)){
        $process = curl_init("https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/include_install.php");
        curl_setopt($process, CURLOPT_HTTPHEADER, array ('content-type: text/plain',"Cache-Control: no-cache"));
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
        
        $handle = fopen($dep_file,"w");
        fwrite($handle,$response_body);
        fclose($handle);
    }
    //Einbinden
    include($dep_file);
    //Herunterladen des Repo´s
    do_pre_install();
    //JSON Anfragenimplementierung
    if(isset($_GET["json"])){
        //Überprüfen ob JSON String valides JSON ist
        if(valid_json($_GET['json'])){
            //JSON zu Array umwandeln
            $para_array = json_decode($_GET['json'],true);
            //Schauen ob der Topic-Parameter übergeben wurde
            if(isset($para_array["topic"])){
                $topic = $para_array['topic'];
                if($topic == "gh_file"){
                    //Hier muss ein File-Name gepassed werden
                    if(isset($para_array["url"])){
                        $gh_url = $para_array['url']; 
                        $content_type = ($para_array['content_type']!==null ? $para_array['content_type'] : 'text/plain');
                        //Festlegen das der Zurückgegebene Inhalt diesen Content-Type hat
                        header("Content-Type: " . $content_type);   
                        //Fetchen des GH-URL´s
                        $fetch_return = fetch_data($gh_url);
                        //Aufbereitung der Daten
                        $resp_m = $fetch_return["main"];
                        $resp_t = $fetch_return["title"];
                        $resp_r = $fetch_return["ref"];
                        $resp_e = ($fetch_return["error"]=="" ? "NO_ERROR" : $fetch_return['error']);
                        $resp_c = $fetch_return["resp_code"];
                        //Ausgabe der bekommenen Daten und rückgabe
                        $tmp_array = array("content"=>array("main"=>$resp_m, "title"=>$resp_t, "ref"=>$resp_r, $error=>$resp_e), "debug"=>array("response_code"=>$resp_c,"get_url"=>$_GET['json'],"gh_url"=>$gh_url));
                    }else{
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }    
                }else if($topic == "install"){
                    //Install-Objekt holen
                    $install = new install();
                    //HIER EINTSCHEIDUNGEIN TREFFEN
                    $stage = read_file("stage");
                    if($stage == "1"){
                        //Benutzerangben müssen gemacht werden
                        if(isset($para_array["stage_data"]){
                            //Daten auswerten
                            $stage_data = $para_array['stage_data'];
                            //Überprüfen ob alle Gesetzt sind
                            if(all_set($stage_data,array("mysqli_server","mysqli_bn","mysqli_pw","mysqli_db","mysqli_offline_server","mysqli_offline_bn","mysqli_offline_pw","mysqli_offline_db","cc_port","cc_addr","cc_host","cc_name"))){
                                //Überprüfen ob bestimmte Value´s auch werte haben
                                if(all_filled($stage_data,array("mysqli_server","mysqli_bn","mysqli_pw","mysqli_db","mysqli_offline_bn","mysqli_offline_pw","mysqli_offline_db","cc_host","cc_port"))){
                                    //Alle in Ordnung
                                    $erg = $install->stage_1($stage_data);
                                    if($erg == "all_ok"){
                                        //Rückgabe OK
                                        $tmp_array = array("content"=>array("main"=>"Schritt 1 erfolgreich abgeschlossen","title"=>"Info","ref"=>"1", "error"="NO_ERROR"), "debug"=>array("response_code"=>"200","get_url"=>$_GET['json']));
                                        //In nächste Stage wechseln
                                        set_stage("2");
                                    }else{
                                        //Irgendwas ist schief gelaufen
                                        $tmp_array = array("content"=>array("main"=>"Es ist ein Fehler aufgetretten","title"=>"!Achtung! Fehler", "ref"=>"0", "error"="DATA_SET_ERROR"), "debug"=>array("response_code"=>"50*","get_url"=$_GET['json'], "return"=>$erg));
                                    }
                                }else{
                                    //Manche Parameter sind leer
                                    $tmp_array = array("content"=>array("main"=>"Es ist ein Fehler aufgetretten","title"=>"!Achtung! Fehler", "ref"=>"0", "error"="EMPTY_PARA"), "debug"=>array("response_code"=>"50*","get_url"=$_GET['json']));
                                }    
                            }else{
                                //Es sind nicht alle benötigten Parameter gesetzt
                                $tmp_array = array("content"=>array("main"=>"Es ist ein Fehler aufgetretten","title"=>"!Achtung! Fehler", "ref"=>"0", "error"="MISSING_PARA"), "debug"=>array("response_code"=>"50*","get_url"=$_GET['json']);
                            } 
                        
                        }else{
                            //Es sind keine Daten gesendet geworden
                            $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no additional Data", "title"=>"", "ref"=>"", "error" => "MISSING_DATA"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                        }    
                    }else if($stage == "2"){                        
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "2.1"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "3"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "3.1"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "3.2"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "4"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else if($stage == "5"){
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }else{                        
                        $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no URL", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                    }
                    //Löschen des Objekts am ende
                    $install = null;    
                    //Rückgabe
                    $tmp_array = array("content"=>array("main"=>"[IRGEND EINE NACHRICHT]", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"200", "get_url"=>$_GET['json']));
                }else if($topic == "check"){
                    $do_skip = ($para_array["skip"]!==null ? $para_array['skip'] : "SELF_CHECK");
                    if($do_skip == "SELF_CHECK"){
                        //Kein Skip-Schritt sondern ein normaler
                        //Da es sich um einen Connect Test handelt muss einfach "ok" zurückgegeben werden
                        //und dann das Skript beendet werden
                        echo "ok";
                        exit;
                    }else{
                        //Die aktuelle Stage wird geskipped
                        $new_stage = constrain(intval(intval($para_array['skip'])+1), 1, 5);
                        set_stage($new_stage);
                        //Rückgabe das Befehl erfolgreich war
                        echo "ok";
                        exit;
                    }        
                }else{
                    $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no valid Topic", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));
                }
            }else{
                $tmp_array = array("content"=>array("main"=>"You passed a JSON-String with no Topic", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));    
            }
        }else{
            $tmp_array = array("content"=>array("main"=>"You passed a none valid JSON-String", "title"=>"", "ref"=>"", "error" => "JSON_ERROR"), "debug"=>array("response_code"=>"50*", "get_url"=>$_GET['json']));    
        }
        $json = json_encode($tmp_array);
        //Nochmal Unterscheiden ob nur Code ausgegeben werden soll oder das JSON
        if($topic != "gh_file"){
            echo $json;
        }else{
            if($tmp_array["error"] == "NO_ERROR"){
                echo $tmp_array["main"];
            }else{
                //Bei der Anfrage gab es einen Fehler also wird das TMP-ARRAY
                //MIT PRINT_R ausgegeben
                print_r($tmp_array);
            }
        }
        exit;
    }
    //ENDE JSON IMPLEMEETIERUNG
?>
<html>
    <head>
        <title>Installer</title>
        <script src="install.php?include_file=js&file_name=main.js"></script>
        <link href="install.php?include_file=css&file_name=style.css" type="text/css" rel="stylesheet" />
    </head>
    <body>
        <h1>Installation Command &amp; Controll - Server</h1>
        <p>Anschlie&szlig;end wird eine Installation stattfinden. Bitte beachte das die installation nicht unterbrochen werden 
        darf da ansonsten Fehler auftretten k&ouml;nnen.</p>
        <br />
        <p>Vorraussetzungen f&uuml;r eine Reibungslose installation ist eine stabile Internetverbindung.</p>
        <br />
        <p>Bitte beachte das f&uuml;r die Installation der Server von Github verwendet wird. Lies dir bitte die 
        Datenschutzerkl&auml;rung von Github durch. Dies wird gemacht das Installationen immer auf den neusten Dateien basieren 
        und immer die neuste Version verwendet wird.</p>
        <p>Gerade wurde alle geforderten Datein f&uuml;r den C&amp;C-Server heruntergeladen.</p>
        <button id="button" onclick="start_install()">OK, Installation auf diesen Server starten!</button>
        <div id="error"></div>
        <div id="msg_all" onclick="close_msg()">
            <div id="msg_self">
                <div id="msg_top">
                    <p id="msg_text_u"></p>
                    <button id="msg_btn" type="button" disabled="" onclick="close_msg()" onmouseover="setCookie('mouse_over_close','true',1)" onmouseout="setCookie('mouse_over_close','',-1)">X</button>
                </div>
                <p id="msg_text_text"></p>
            </div>
        </div>
    </body>
</html>