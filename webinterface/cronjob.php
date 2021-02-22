<?php
//Diese Cronjob.php liegt auf dem Webserver
//Sie muss jede Minute aufgerufen werden.
//Sie ist Zuständig für die Verteilung der Cronjobs an die einzelnen C&C-Server
//!ACHTUNG! Cronjobs mit einer Verzögerung von >=500ms (z.B. Alamierung Feuerwehr)
//müssen gesondert und direkt und für jeden C&C-Server einzeln eingerichtet werden


//Dieses Skript wird jede Minute ausgeführt


//Ab hier Funktionen
function get_all_servers(){
    $sql = "SELECT * FROM `cc_server`";
    $mysqli = new_mysqli();
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    close_mysqli($mysqli);
    //Umwandeln in Array
    $servers = array();
    foreach($res as $push){
        $server = $push["server"];
        if(!in_array($server,$servers)){
            array_push($servers,$server);
        }
    }
    return $servers;        
}
function get_ident_for_server($server){
    $sql = "SELECT * FROM `cc_server` WHERE `server`='" . $server . "' AND `type`='cronjob'";
    $mysqli = new_mysqli();
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    close_mysqli($mysqli);
    //Umwandeln in Array
    $ident = $res[0]["token"];
    return $ident;    
}
function send_command($server,$comm,$ident){
    $curl_sess = curl_init();
    curl_setopt($curl_sess,CURLOPT_URL,$server . "/cronjob.php?ident=".$ident."&comm=".$comm);
    $res = curl_exec($curl_sess);
    curl_close($curl_sess);
    return $res;       
}
function get_commands_for_all(){
    $all_server_comms = get_commands_for_server("*");
    return $all_server_comms;   
}
function get_commands_for_server($server){
    $sql = "SELECT * FROM `cronjob` WHERE `server`='" . $server . "'";
    $mysqli = new_mysqli();
    $res = sql_result_to_array(start_sql($mysqli,$sql));
    close_mysqli($mysqli);
    //Umwandeln in Array
    $comms = array();
    foreach($res as $push){
        $comm = $push["code"];
        if(!in_array($comm,$comms)){
            array_push($comms,$comm);
        }
    }
    return $comms;    
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