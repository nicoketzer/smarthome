<?php 
//Alle Datein aus dem php-Verzeichnis einbinden die auf ".php" enden
//Scannen
$scan = scandir("./php");
$incl = array();
//Herausfiltern
foreach($scan as $test){
    $tmp = explode(".php",$test);
    if(isset($tmp[1])){
        //Es handelt sich um eine .php
        if(!in_array($test,$incl)){
            array_push($incl,$test);
        }
    }
}
//Einbinden 
foreach($incl as $do){
    include("./php/".$do);
}
?>