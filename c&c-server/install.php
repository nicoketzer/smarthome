<?php
    //Infos
    #Javascript und CSS werden von Github bezogen sodass nur eine Datei gebraucht wird
    #Internetverbindung wird gebraucht
    include("https://raw.githubusercontent.com/nicoketzer/smarthome/master/c%26c-server/install_files/include_install.php");
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
        <button id="button" onclick="start_install()">OK, Installation auf diesen Server starten!</button>
    </body>
</html>