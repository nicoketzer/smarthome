<?php
//Diese Cronjob.php liegt auf dem Webserver
//Sie muss jede Minute aufgerufen werden.
//Sie ist Zuständig für die Verteilung der Cronjobs an die einzelnen C&C-Server
//!ACHTUNG! Cronjobs mit einer Verzögerung von >=500ms (z.B. Alamierung Feuerwehr)
//müssen gesondert und direkt und für jeden C&C-Server einzeln eingerichtet werden

//Ab hier Funktionen
function get_all_servers(){
    
}
function get_ident_for_server($server){
    
}
function send_command($server,$comm,$ident){
    
}
function get_commands_for_all(){
    
}
function work_it_all_off(){
    //Alle C&C - Server bekommen
    $servers = get_all_servers();
    //Befehle Bekommen die an alle C&C-Server geschickt werden sollen
    $comm_all = get_commands_for_all();    
    //Schicken der allgemeinen Commands
    foreach($servers as $server){
        //Ident-Token für Server für CJ holen
        $ident = get_ident_for_server($server);
        //An den Spezielen Server nun jetzt alle Commands schicken
        foreach($comm_all as $comm_send){
            send_command($server,$comm_send,$ident);
        }
    }
    //Nun für jeden Server spezifische Commands holen
    foreach($servers as $server){
        //Spezifische Commands bekommen
        $comms_server = get_commands_for_server($server);
        if(isset($comms_server[0])){
            //Es gibt spezifische Commands für diesen Server
            $ident = get_ident_for_server($server);
            foreach($comms_server as $comm_send){
                send_command($server,$comm_send,$ident);
            }
        }else{
            //Diesen Server überspringen
            continue;
        }
    }
    return true;
}

//Alle Cronjobs starten
work_it_all_off();
exit;
?>