<?php
    //F�r Stage 4
    if(isset($_GET["c"])){
        if($_GET['c'] == "test"){
            echo "ok";
            exit;
        }
    }else if(isset($_GET["skip_stage"])){
        if(read_file("stage") == "4"){
            set_stage("5");
            echo "Selbst-Test wurde &uuml;bersprungen";
        }
    }
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
    //Herunterladen des Repo�s
    do_pre_install();
    //Zur Verf�gung stellen der CSS und JS Dateien
    if(isset($_GET["include_file"]) && isset($_GET["file_name"])){
        if($_GET['include_file'] == "css"){
            //Setzen des Content-Type Headers
            header("Content-Type: text/css");
            if($_GET['file_name'] == "style.css"){
                $url = "https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/style.css";
                $process = curl_init($url);
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
                echo $response_body;
                exit;    
            }else{
                die("Unknown File-Name");
            }
        }else if($_GET['include_file'] == "js"){
            //Setzen des Content-Type Headers
            header("Content-Type: text/javascript");
            if($_GET['file_name'] == "main.js"){
                $url = "https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/main.js";
                $process = curl_init($url);
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
                echo $response_body;
                exit;
            }else{
                die("Unknown File-Name");
            }
        }else{
            //Kein Bekannter Include-Datei-Typ
            die("Unexpected include_file");
        }
    }
    
    //Normale Funktionen
    if(isset($_POST["start_install"]) && read_file("stage") == ""){
        do_install();
    }else{
        $s = read_file("stage");
        if($s == "1"){
            //Benutzerangben m�ssen gemacht werden
            if(isset($_POST["data_input"])){
                //Daten kommen
            }else{
                //Ignorieren keine Daten kommen
            }
        }else if($s == "2"){
            //Test der Mysqli Verbindung
        }else if($s == "2.1"){
            //Einrichten der Mysql-Umgebung
            
        }else if($s == "3"){
            //Bekommen eines IP-Update-Tokens
        }else if($s == "3.1"){
            //Erstes IP-Update
        }else if($s == "3.2"){
            //Anfragen des "Self-Test"-URL�s f�r Stage 4
        }else if($s == "4"){
            //Erreichbarkeit pr�fen(IP+Port+/install.php?c=test)
            $self_test_url = read_file("self_test_url");
            self_test($self_test_url);
        }else if($s == "5"){
            //Installation abschlie�en
            finish_install();    
        }
    }
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
        Datenschutzerkl&auml;rung von Github durch. Dies wird gemacht das Installationen immer auf den neusten Datein basieren 
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