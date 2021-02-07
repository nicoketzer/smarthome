#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266mDNS.h>
#include <WiFiUdp.h>
#include <ArduinoOTA.h>

#define STASSID "[WLAN_NAME]"
#define STAPSK  "[WLAN_PASSWORT]"
// Esp8266 pinouts
#define ESP8266_GPIO2    2  // Blue LED.
#define ESP8266_GPIO4    4  // Relay control. 
#define ESP8266_GPIO5    5  // Optocoupler input.
//Wenn LED_PIN auf HIGH ist ist die LED aus
#define LED_PIN          ESP8266_GPIO2
WiFiServer server(8081);
volatile int relayState = 0;      // Relay state. 
volatile bool is_chrismas = false; //Weihnachten
volatile bool last_is_chr = false; //Funktion
volatile int web_req_t = 0;

const char* ssid = STASSID;
const char* password = STAPSK;

void setup() {
  Serial.begin( 9600 );
  //Debug Prozess
  //OUTPUT
  for(int i=1; i<22; i++){
    pinMode(i,OUTPUT);
    Serial.print("Das ist Pin ");
    Serial.print(i);
    Serial.println(" Output");
    digitalWrite(i,HIGH);
    delay(1000);
    digitalWrite(i,LOW);
    delay(1000);
  }
  //Input
  for(int i=1; i<22; i++){
    pinMode(i,INPUT);
    Serial.print("Das ist Pin ");
    Serial.print(i);
    Serial.println(" Input");
    digitalRead(i);
    delay(1000);
    digitalRead(i);
    delay(1000);
  }
  //Normal Prozess
  initHardware();
  initOTA();
  server.begin();  
}
void do_smth(String a){    
  if(a == "on"){
    // relay on!
    relayState = 1;
    digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
  }else if(a == "off"){
    // relay off!
    relayState = 0;
    digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.  
  }else if(a == "switch"){
    if(relayState == 0){
      // relay on!
      relayState = 1;
      digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
    }else{
      // relay off!
      relayState = 0;
      digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.
    }
  }else{
    Serial.println(" Nothing found " );
    Serial.println( a );
  }
}
String get_response(){
  HTTPClient http;
  http.begin("http://192.168.178.45/esp/work.php?mac="+String(WiFi.macAddress()));
  int httpCode = http.GET();
  String a = "abc";
  if(httpCode > 0){
    Serial.println(httpCode);
    Serial.println(http.getString());
    a = http.getString();
    if(a.length() <= 7){
      do_smth(a);
    }
  }else{
    Serial.println("fail");
    a = "fail";
  }
  http.end();
  return a;
}
void wait_response(){
  if(web_req_t < 0){
    web_req_t++;  
  }else{
    web_req_t = 0;
    get_response();
  }
}
void loop() {
  //Nach Updates sehen
  ArduinoOTA.handle();
  //Normaler Server
  if(checkWifi()){
    // Check if a client has connected.
    WiFiClient client = server.available();
    if ( client ){ 
      GetClient( client );
    }
  }else{
    server.stop();
    Serial.println("Not Connected");
  }
  if(last_is_chr == is_chrismas){
    chrismas();
  }else{
    if(is_chrismas){
      last_is_chr = true;
      chrismas();  
    }else{
      last_is_chr = false; 
    }
  }
  //Call for http
  wait_response();
  //Immer eine Sekunde warten bevor der nächste loop kommt
  delay(1000);
}
bool checkWifi(){
   if(WiFi.status() != WL_CONNECTED){
    Serial.println("WiFi broken");
    //Ist nicht verbunden
    return connectWiFi(); 
   }else{
    return true;
   }
}
bool connectWiFi() {
  byte ledStatus = LOW;
  Serial.println();
  Serial.println( "Connecting to: " + String( ssid ) );
  // Set WiFi mode to station (as opposed to AP or AP_STA).
  WiFi.mode( WIFI_STA );

    String out = "";
    String macID = "Mac Addr: "+String(WiFi.macAddress());
    out += macID;
    Serial.println(out);
 
  // WiFI.begin([ssid], [passkey]) initiates a WiFI connection.
  // to the stated [ssid], using the [passkey] as a WPA, WPA2, or WEP passphrase.
  WiFi.begin(ssid, password);

 
  while ( WiFi.status() != WL_CONNECTED ) {
    // Blink the LED.
    digitalWrite( LED_PIN, ledStatus ); // Write LED high/low.
    ledStatus = ( ledStatus == HIGH ) ? LOW : HIGH;
    int wait = random(1,1000);
    delay( wait );
  }
  //Zurücksetzen der LED AUF "NICHT-LEUCHTEN"
  digitalWrite( LED_PIN, LOW );
  digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
  delay(1500);
  digitalWrite( LED_PIN, HIGH ); 
  digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.
  delay ( 1500 ) ;
  Serial.println( "WiFi connected" );  
  Serial.println( "IP address: " );
  Serial.println( WiFi.localIP() );
  return true;
}
void chrismas(){
  if(is_chrismas){
    if(relayState == 0){
      // relay on!
      relayState = 1;
      digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
    }else{
      // relay off!
      relayState = 0;
      digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.
    }
  }
}
void initOTA(){
  Serial.println("Booting");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  while (WiFi.waitForConnectResult() != WL_CONNECTED) {
    Serial.println("Connection Failed! Rebooting...");
    delay(5000);
    ESP.restart();
  }

  // Port defaults to 8266
  ArduinoOTA.setPort(8080);

  // Hostname defaults to esp8266-[ChipID]
  ArduinoOTA.setHostname("Test-Board Wifi-Switch");

  // No authentication by default
  // ArduinoOTA.setPassword("admin");

  // Password can be set with it's md5 value as well
  // MD5(admin) = 21232f297a57a5a743894a0e4a801fc3
  // ArduinoOTA.setPasswordHash("21232f297a57a5a743894a0e4a801fc3");

  ArduinoOTA.onStart([]() {
    //Ausschalten des Relais
    do_smth("off");
    //Normale abfolge
    String type;
    if (ArduinoOTA.getCommand() == U_FLASH) {
      type = "sketch";
    } else { // U_FS
      type = "filesystem";
    }

    // NOTE: if updating FS this would be the place to unmount FS using FS.end()
    Serial.println("Start updating " + type);
  });
  ArduinoOTA.onEnd([]() {
    Serial.println("\nEnd");
  });
  ArduinoOTA.onProgress([](unsigned int progress, unsigned int total) {
    Serial.printf("Progress: %u%%\r", (progress / (total / 100)));
  });
  ArduinoOTA.onError([](ota_error_t error) {
    Serial.printf("Error[%u]: ", error);
    if (error == OTA_AUTH_ERROR) {
      Serial.println("Auth Failed");
    } else if (error == OTA_BEGIN_ERROR) {
      Serial.println("Begin Failed");
    } else if (error == OTA_CONNECT_ERROR) {
      Serial.println("Connect Failed");
    } else if (error == OTA_RECEIVE_ERROR) {
      Serial.println("Receive Failed");
    } else if (error == OTA_END_ERROR) {
      Serial.println("End Failed");
    }
  });
  ArduinoOTA.begin();
  Serial.println("Ready");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  Serial.println("Mac Addr: "+String(WiFi.macAddress()));
  digitalWrite ( LED_PIN , HIGH );
}
void GetClient( WiFiClient client ) {
  // Read the first line of the request.
  String req = client.readStringUntil( '\r' );
  Serial.println( req );
  client.flush();
 
  String s = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n<!DOCTYPE HTML>\r\n<html>\r\n";
 
  if ( req.indexOf( "OPTIONS" ) != -1 ) {
    s += "Allows: GET, OPTIONS";
 
  } else if ( req.indexOf( "GET" ) != -1 ) {
    if ( req.indexOf( "open/ehueheiidsssxxejoff" ) != -1 ) {
      // relay on!
      s += "relay on!";
      relayState = 1;
      digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
     
    } else if ( req.indexOf( "close/ehueheiidsssxxejoff" ) != -1 ) {
      // relay off!
      s += "relay off!";
      relayState = 0;
      digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.
    } else if ( req.indexOf( "switch/ehueheiidsssxxejoff" ) != -1 ) {
      if(relayState == 0){
        // relay on!
        s += "relay on!";
        relayState = 1;
        digitalWrite( ESP8266_GPIO4, 1 ); // Relay control pin.
      }else{
        // relay off!
        s += "relay off!";
        relayState = 0;
        digitalWrite( ESP8266_GPIO4, 0 ); // Relay control pin.
      } 
    } else if ( req.indexOf( "chrismas/ehueheiidsssxxejoff" ) != -1 ) {
      if(is_chrismas){
        is_chrismas = false;
      }else{
        is_chrismas = true;
      }
    } else if ( req.indexOf( "abc/ehueheiidsssxxejoff" ) != -1 ) {
      Serial.println("Call Funktion\r\n");
      s += get_response();  
      Serial.println("\n\rEnd Funktion\n\r");
      s+"OPEN";
    }else if ( req.indexOf( "relay/ehueheiidsssxxejoff" ) != -1 ) {
      if ( relayState == 0 )
        // relay off!
        s += "off";
      else
        // relay on!
        s += "on";
 
    } else if ( req.indexOf( "io" ) != -1 ) {
      if ( digitalRead( ESP8266_GPIO5 ) == 0 )
        s += "input io is:0!";
      else
        s += "input io is:1!";
     
    } else if ( req.indexOf( "MAC/ehueheiidsssxxejoff" ) != -1 ) {
      s += "MAC address: " + String(WiFi.macAddress());
 
    } else
      s += "Invalid Request.<br> Try: open/[password] or close/[password] or relay or io or MAC/[password]";
 
  } else 
    s = "HTTP/1.1 501 Not Implemented\r\nContent-Type: text/html\r\n\r\n<!DOCTYPE HTML>\r\n<html>\r\n";
          
  client.flush();
  s += "</html>\n";
 
  // Send the response to the client.
  client.print( s );
  delay( 1 );
  Serial.println( "Client response sent." );
}
void initHardware() {
  Serial.begin( 9600 );
  pinMode( ESP8266_GPIO4, OUTPUT );       // Relay control pin.
  pinMode( ESP8266_GPIO5, INPUT_PULLUP ); // Input pin.
  pinMode( LED_PIN, OUTPUT );             // ESP8266 module blue LED.
  digitalWrite( ESP8266_GPIO4, 0 );       // Set relay control pin low.
}
