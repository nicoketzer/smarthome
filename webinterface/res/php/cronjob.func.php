<?php 
//Ab hier Funktionen
class cronjob{
    private $servers = array();
    private $comms_for_all = array();
    public function __construct(){
        return true;
    }
    private function get_all_servers(){
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
    private function get_commands_for_all(){
        $all_server_comms = $this->get_commands_for_server("*");
        return $all_server_comms;
    }
    private function get_commands_for_server($server){
        $sql = "SELECT * FROM `cronjob` WHERE `server`='" . bin2hex($server) . "' AND `imp`='" . bin2hex("no") . "'";
        $mysqli = new_mysqli();
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Umwandeln in Array
        $comms = array();
        foreach($res as $push){
            $comm = hex2bin($push["code"]);
            if(!in_array($comm,$comms)){
                array_push($comms,$comm);
            }
        }
        return $comms;
    }
    public function get_data(){
        $this->servers = $this->get_all_servers();
        $this->comms_for_all = $this->get_commands_for_all();
    }
    private function function get_ident_for_server($server){
        $sql = "SELECT * FROM `cc_server` WHERE `server`='" . bin2hex($server) . "' AND `type`='" . bin2hex("cronjob") . "'";
        $mysqli = new_mysqli();
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Umwandeln in Array
        $ident = $res[0]["token"];
        return $ident;
    }
    private function get_token_type($token){
        $mysqli = new_mysqli();
        $sql = "SELECT * FROM `token_action` WHERE `token`='" . $token . "'";
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        if(isset($res[0])){
            return $res[0]['action_type'];
        }else{
            //Es könnte eine weitere Rutine sein
            $mysqli = new_mysqli();
            $sql = "SELECT * FROM `rutine_token` WHERE `rut_token`='" . $token . "'";
            $res = sql_result_to_array(start_sql($mysqli,$sql));
            close_mysqli($mysqli);
            if(isset($res[0])){
                //Es ist eine weitere Rutine
                return "rutine";
            }else{
                return "unknown";
            }
        }
    }
    private function get_rut_comms($rut_token){
        //Für Rutine Komandos bekommen
        $mysqli = new_mysqli();
        $sql = "SELECT * FROM `rutine_token` WHERE `rut_token`='" . $rut_token . "'";
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Extrahieren des Aktions-Tokens und der ID
        $rut_tokens = array();
        foreach($res as $findout){
            $token = $findout["action_token"];
            $order_nr = $findout["order_nr"];
            $type = $this->get_token_type($token);
            //In array hinzufügen (sortiert)
            $rut_tokens[intval($order_nr)] = array("command"=>$token,"type"=>$type);
        }
        //Rückgabe
        return $rut_tokens;
    }
    private function send_command($server,$comm,$ident,$type){
        if($type != "rutine"){
            $curl_sess = curl_init();
            //Es wird auch der Typ übergeben dass dann lokal enschieden werden kann
            curl_setopt($curl_sess,CURLOPT_URL,$server . "/cronjob.php?ident=".$ident."&comm=".$comm."&type=".$type);
            $res = curl_exec($curl_sess);
            curl_close($curl_sess);
            return $res;
        }else{
            //Es handelt sich bei dem Befehl um eine Rutine sprich es müssen alle Befehle der
            //Rutine gesammelt und nacheinander ausgeführt werden
            $rut_comms = $this->get_rut_comms($comm);
            foreach($rut_comms as $send_comm){
                $comm_s = $send_comm["command"];
                $type_s = $send_comm["type"];
                $this->send_command($server,$comm_s,$ident,$type_s);
            }
        }
    }
    public function work_it_all_off(){
        //Schicken der allgemeinen Commands
        foreach($this->servers as $server){
            //Ident-Token für Server für CJ holen
            $ident = $this->get_ident_for_server($server);
            //An den Spezielen Server nun jetzt alle Commands schicken
            foreach($this->comms_for_all as $comm_send){
                $this->send_command($server,$comm_send,$ident);
            }
        }
        //Nun für jeden Server spezifische Commands holen
        foreach($this->servers as $server){
            //Spezifische Commands bekommen
            $comms_server = get_commands_for_server($server);
            if(isset($comms_server[0])){
                //Es gibt spezifische Commands für diesen Server
                $ident = get_ident_for_server($server);
                foreach($comms_server as $comm_send){
                    $this->send_command($server,$comm_send,$ident);
                }
            }else{
                //Diesen Server überspringen
                continue;
            }
        }
        return true;
    }
}//Ab hier Funktionen
class cronjob{
    private $servers = array();
    private $comms_for_all = array();
    public function __construct(){
        return true;
    }
    private function get_all_servers(){
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
    private function get_commands_for_all(){
        $all_server_comms = $this->get_commands_for_server("*");
        return $all_server_comms;
    }
    private function get_commands_for_server($server){
        $sql = "SELECT * FROM `cronjob` WHERE `server`='" . bin2hex($server) . "' AND `imp`='" . bin2hex("no") . "'";
        $mysqli = new_mysqli();
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Umwandeln in Array
        $comms = array();
        foreach($res as $push){
            $comm = hex2bin($push["code"]);
            if(!in_array($comm,$comms)){
                array_push($comms,$comm);
            }
        }
        return $comms;
    }
    public function get_data(){
        $this->servers = $this->get_all_servers();
        $this->comms_for_all = $this->get_commands_for_all();
    }
    private function function get_ident_for_server($server){
        $sql = "SELECT * FROM `cc_server` WHERE `server`='" . bin2hex($server) . "' AND `type`='" . bin2hex("cronjob") . "'";
        $mysqli = new_mysqli();
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Umwandeln in Array
        $ident = $res[0]["token"];
        return $ident;
    }
    private function get_token_type($token){
        $mysqli = new_mysqli();
        $sql = "SELECT * FROM `token_action` WHERE `token`='" . $token . "'";
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        if(isset($res[0])){
            return $res[0]['action_type'];
        }else{
            //Es könnte eine weitere Rutine sein
            $mysqli = new_mysqli();
            $sql = "SELECT * FROM `rutine_token` WHERE `rut_token`='" . $token . "'";
            $res = sql_result_to_array(start_sql($mysqli,$sql));
            close_mysqli($mysqli);
            if(isset($res[0])){
                //Es ist eine weitere Rutine
                return "rutine";
            }else{
                return "unknown";
            }
        }
    }
    private function get_rut_comms($rut_token){
        //Für Rutine Komandos bekommen
        $mysqli = new_mysqli();
        $sql = "SELECT * FROM `rutine_token` WHERE `rut_token`='" . $rut_token . "'";
        $res = sql_result_to_array(start_sql($mysqli,$sql));
        close_mysqli($mysqli);
        //Extrahieren des Aktions-Tokens und der ID
        $rut_tokens = array();
        foreach($res as $findout){
            $token = $findout["action_token"];
            $order_nr = $findout["order_nr"];
            $type = $this->get_token_type($token);
            //In array hinzufügen (sortiert)
            $rut_tokens[intval($order_nr)] = array("command"=>$token,"type"=>$type);
        }
        //Rückgabe
        return $rut_tokens;
    }
    private function send_command($server,$comm,$ident,$type){
        if($type != "rutine"){
            $curl_sess = curl_init();
            //Es wird auch der Typ übergeben dass dann lokal enschieden werden kann
            curl_setopt($curl_sess,CURLOPT_URL,$server . "/cronjob.php?ident=".$ident."&comm=".$comm."&type=".$type);
            $res = curl_exec($curl_sess);
            curl_close($curl_sess);
            return $res;
        }else{
            //Es handelt sich bei dem Befehl um eine Rutine sprich es müssen alle Befehle der
            //Rutine gesammelt und nacheinander ausgeführt werden
            $rut_comms = $this->get_rut_comms($comm);
            foreach($rut_comms as $send_comm){
                $comm_s = $send_comm["command"];
                $type_s = $send_comm["type"];
                $this->send_command($server,$comm_s,$ident,$type_s);
            }
        }
    }
    public function work_it_all_off(){
        //Schicken der allgemeinen Commands
        foreach($this->servers as $server){
            //Ident-Token für Server für CJ holen
            $ident = $this->get_ident_for_server($server);
            //An den Spezielen Server nun jetzt alle Commands schicken
            foreach($this->comms_for_all as $comm_send){
                $this->send_command($server,$comm_send,$ident);
            }
        }
        //Nun für jeden Server spezifische Commands holen
        foreach($this->servers as $server){
            //Spezifische Commands bekommen
            $comms_server = get_commands_for_server($server);
            if(isset($comms_server[0])){
                //Es gibt spezifische Commands für diesen Server
                $ident = get_ident_for_server($server);
                foreach($comms_server as $comm_send){
                    $this->send_command($server,$comm_send,$ident);
                }
            }else{
                //Diesen Server überspringen
                continue;
            }
        }
        return true;
    }
}
?>