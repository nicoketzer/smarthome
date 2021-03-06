<?php
//Alle Funktionen einbinden
include("./res/all.php");

//Diese Cronjob.php liegt auf dem Webserver
//Sie muss jede Minute aufgerufen werden.
//Sie ist Zuständig für die Verteilung der Cronjobs an die einzelnen C&C-Server
//!ACHTUNG! Cronjobs mit einer Verzögerung von >=500ms (z.B. Alamierung Feuerwehr)
//müssen gesondert und direkt und für jeden C&C-Server einzeln eingerichtet werden


//Dieses Skript wird jede Minute ausgeführt
$cj = new cronjob();
$cj->get_data();
$cj->work_it_all_off();
exit;
?>