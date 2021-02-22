<?php
class get_Wifi_state{
    public $client;
    public $fritzbox_ip;
    public $tr64_port;
    public $hosts;
    public $options;
    public $context;
    public $NumberOfHosts;
    public $hosts_state;
    public $get_all;
    public $host_sort;
    public $host_tmp;
    public function __construct(){
        $this->client = null;
        $this->fritzbox_ip = "192.168.178.1";
        $this->tr64_port = "49000";
        $this->hosts = array();
        $this->options = array();
        $this->context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $this->NumberOfHosts = null;
        $this->client = null;
        $this->hosts_state = array();
        $this->get_all = false;
        $this->host_sort = array();
        $this->host_tmp = array();   
    }
    
    private function get_fb_data(){
    // SOAP Abfrage:
        $this->client = new SoapClient(null,array('location'	=> "http://" . $this->fritzbox_ip . ":" . $this->tr64_port . "/upnp/control/hosts",
        									'uri'		=> "urn:dslforum-org:service:Hosts:1",
        									'soapaction'=> "urn:dslforum-org:service:Hosts:1#GetSpecificHostEntry",
        									'noroot'	=> False
        ));    
        $this->NumberOfHosts = $this->client->GetHostNumberOfEntries();
        if (!is_soap_fault($this->NumberOfHosts)){
            for($i=0; $i<$this->NumberOfHosts; $i++){
                $Host = $this->client->GetGenericHostEntry(new SoapParam($i,'NewIndex'));
                $hostname = $Host['NewHostName'];
                $host_state = $Host['NewActive'];
                $push_back = array('hostname'=>$hostname,'state'=>$host_state);
                if(!in_array($push_back,$this->hosts_state)){
                    array_push($this->hosts_state,$push_back);
                }
            }
        }
    }
    public function def_all_hosts($array_over){
        $this->hosts = $array_over;
    }
    public function get_all($state){
        $this->get_all = $state;    
    }
    public function exist_all_hosts(){
        //Es wird $hosts mit $hosts_state verglichen
        //Es wird get_fb_data() ausgeführt
        $this->get_fb_data();
        $all_exists = true;
        if(isset($this->hosts[0])){
            //Vergleich starten
            foreach($this->hosts_state as $test_host){
                $hostname = $test_host['hostname'];
                if(!in_array($hostname,$this->host_tmp)){
                    array_push($this->host_tmp,$hostname);
                }        
            }
            foreach($this->hosts as $test){
                if(!in_array($test,$this->host_tmp)){
                    $all_exists = false;
                }
            }
            //Rückgabe
            return $all_exists;
        }else{
            //Es wurden noch keine Hosts definiert sprich es existieren 
            //alle definierten Hosts
            return $all_exists;
        }    
    }
    public function get_for_all_hosts(){
        //Abfrage ob überhaupt ein host vorher festgelegt wurde
        if(isset($this->hosts[0])){
            //Es wurde mindestens ein Host vorher festgelegt
            //Alle Geräte abfragen und Status speichern
            $this->get_fb_data();
            //Jetzt sind alle Geräte mit Status in $hosts_state gespeichert
            foreach($this->hosts_state as $test_host){
                $host_name = $test_host["hostname"];
                if(in_array($host_name,$this->hosts)){
                    array_push($this->host_sort,$test_host);    
                }    
            }
            return $this->host_sort;    
        }else{
            //Es wurde kein Host vorher festgelegt
            #Abfrage ob alle Clients zurückgeschickt werden sollen oder ein 
            #fehler String
            if($this->get_all){
                $this->get_fb_data();
                //Alle Hosts sollen zurückgeschickt werden
                return $this->hosts_state;
            }else{
                return "No Hosts were given";
            }
        }
    }
}
class proc_data{
    private $hosts;
    private $error;
    private $dublicate;
    public function __construct($given_hosts){
        $this->hosts = $given_hosts;
        $this->error = false;
        $this->dublicate = array();        
    }
    public function proc_now(){
        if(isset($this->hosts[0])){
            $this->test_for_double_hostnames();
            foreach($this->hosts as $fill_in){
                
            }    
        }
    }
    private function into_error_db($set){
        //Erst testen ob schon Datensatz vorhanden ist
            
    }
    private function test_for_double_hostnames(){
        $tmp_array = array();
        foreach($this->hosts as $test){
            if(!in_array($test,$tmp_array)){
                array_push($tmp_array,$test);        
            }else{
                //Dublicat erkannt
                $this->into_error_db($test);
            }
        }
        $tmp_array = null;    
    }
    private function into_normal_db($set){
        
    }
}
//Test Starten
//Neuse Obj
$obj = new get_Wifi_state();
//Funktion um Status eines gewissen Hostnames zu bekommen
/*
$obj->def_all_hosts(array("handy-nico"));
//Funktion ob alle Hostnamen existieren
$can_get_all = $obj->exist_all_hosts();
//Alle definierten Hosts bekommen
$state_for_hosts = $obj->get_for_all_hosts();
*/
//Keine Hosts definieren
$obj->def_all_hosts(null);
//Es sollen alle Hosts zurückgesendet werden
$obj->get_all(true);
//Alle Hosts bekommen
$all_hosts = $obj->get_for_all_hosts();
print_r($all_hosts);
//Zurücksetzen
$obj = null;
//Alle Hosts verarbeiten und TBL updaten
#Neuse Objekt
//$proc_obj = new proc_data($all_hosts);
//$proc_obj->proc_now();
?> 















