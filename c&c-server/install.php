<?php
    //Für Stage 4
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
    include("https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/include_install.php");
    if(isset($_POST["start_install"]) && read_file("stage") == ""){
        do_install();
    }else{
        $s = read_file("stage");
        if($s == "1"){
            //Benutzerangben müssen gemacht werden
            if(isset($_POST["data_input"])){
                //Daten kommen
            }else{
                //Ignorieren keine Daten kommen
            }
        }else if($s == "2"){
            //Test der Mysqli Verbindung
        }else if($s == "3"){
            //Bekommen eines IP-Update-Tokens
        }else if($s == "3.1"){
            //Erstes IP-Update
        }else if($s == "3.2"){
            //Anfragen des "Self-Test"-URL´s für Stage 4
        }else if($s == "4"){
            //Erreichbarkeit prüfen(IP+Port+/install.php?c=test)
            $self_test_url = read_file("self_test_url");
            self_test($self_test_url);
        }else if($s == "5"){
            //Installation abschließen
            finish_install();    
        }
    }
?>
<html>
    <head>
        <title>Installer</title>
        <script src="https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/main.js"></script>
        <link href="https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/style.css" type="text/css" rel="stylesheet" />
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
    </body>
</html>