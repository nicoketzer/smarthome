<?php 
//Mysql Anfrage starten
if(!function_exists("start_sql")){
    function start_sql($mysqli,$sql){
        if($mysqli->prepare($sql)){
            $statement = $mysqli->prepare($sql);
            $statement->execute();
        }else{
            return mysqli_error($mysqli);
        }
        $result = $statement->get_result();
        return $result;
    }
}
//Sql - Resultat als Array zur&uuml;ckgeben
if(!function_exists("sql_result_to_array")){
    function sql_result_to_array($result){
        if($result){
            $array = array();
            while($row = $result->fetch_assoc()) {
                array_push($array,$row);
            }
            return $array;
        }else{
            return array(false);
        }
    }
}
//Neue Verbindung aufbauen mit Daten aus var.php
if(!function_exists("new_mysqli")){
function new_mysqli(){
    global $db_bn;
    global $db_pw;
    global $db_tbl;
    global $db_conn;
    $mysqli = new mysqli($db_conn,$db_bn,$db_pw,$db_tbl);
    //echo mysqli_get_host_info($mysqli);
    if ($mysqli->connect_errno) {
        die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
    }else{
        return $mysqli;
    }
}
}
//Verbindung schließen
if(!function_exists("close_mysqli")){
function close_mysqli($mysqli){
    return $mysqli->close();
}
}
?>