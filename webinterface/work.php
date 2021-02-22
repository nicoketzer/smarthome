<?php 
//Hier werden alle Tasks ausgeführt.
//Das Script wird am schluss beendet, also kein Output wenn keine Anfrage getroffen wurde.
//Sollte es Output geben wird dieser über Return-Werte zurückgegeben. 
//Rückgaben auf Anfragen finden per Ausgabe mit echo statt.
/*
 * Seiten-Part
 */
if(isset($_POST["site"])){
    //Hier wird z.B. das Laden von Seiten stattfinden    
}
/*
 * LOGIN - PART
 */
if(isset($_POST["login"])){
    $login = $_POST['login'];
    if($login == "true"){
        if(isset($_POST["token"])){
            $token = $_POST['token'];
            if(real_token($token)){
                $sql = "SELECT * FROM `benutzer` WHERE `token`='" . $token . "'";
                $mysqli = new_mysqli();
                $res = sql_result_to_array(start_sql($mysqli,$sql));
                close_mysqli($mysqli);
                if(isset($res[0]) && !isset($res[1])){
                    echo "ok";
                }else{
                    echo "false";
                }
            }
        }else if(isset($_POST["bn"]) && isset($_POST["pw"])){
            $bn = $_POST['bn'];
            $pw = $_POST['pw'];
            if(test_not_null($bn) && test_not_null($pw)){
                if(only_allowed_syms(array($bn,$pw),$array_allowed)){
                    $sql = "SELECT * FROM `benutzer` WHERE `benutzername`='" . $bn . "'";
                    $mysqli = new_mysqli();
                    $res = sql_result_to_array(start_sql($mysqli,$sql));
                    close_mysqli($mysqli);
                    if(isset($res[0]) && !isset($res[1])){
                        $res = $res[0];
                        $pw_db = $res["passwort"];
                        $pw_check = hash("sha512",bin2hex($pw) . $server_secret);
                        if($pw_check == $pw_db){
                            $token = $res["token"];
                            if(real_token($token)){
                                echo $token;
                            }else{
                                $new_token = generate_token();
                                $sql = "UPDATE `benutzer` SET `token`='" . $new_token . "',`token_time`='" . time() . "' WHERE `benutzername`='" . $bn . "'";
                                $mysqli = new_mysqli();
                                start_sql($mysqli,$sql);
                                close_mysqli($mysqli);
                                echo $new_token;
                            }
                        }else{
                            echo "false";
                        }
                    }else{
                        echo "false";
                    }
                }else{
                    echo "false";
                }
            }else{
                echo "false";
            }
        }else{
            echo "false";
        }
        exit;
    }
}
exit;
?>