#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266mDNS.h>
#include <WiFiUdp.h>
#include <ArduinoOTA.h>

#define STASSID "[WLAN_NAME]"
#define STAPSK  "[WLAN_PASSWORT]"
// Esp8266 pinouts
#define ESP8266_GPIO2    2  // Blue (onboard) LED.
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
const char* dev_name = "Garagentor-Oefner v 1.4.stabel";

void setup() {
  //Normal Prozess
  initHardware();
  initOTA();
  server.begin();  
}
void do_smth(String a){    
  if(a == "switch"){
    digitalWrite( ESP8266_GPIO4, 1);
    delay(200);
    digitalWrite( ESP8266_GPIO4, 0);
  }else{
    digitalWrite( ESP8266_GPIO4, 0); //Sicherheitshalber das Relay ausschalten
    Serial.println(" Nothing found " );
    Serial.println( a );
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
    ESP.restart();
  }
  if(!digitalRead(ESP8266_GPIO5)){
    //Test-Kontakte wurden geschlossen
    do_smth("switch");
    //Delay damit nicht mehrmals hintereinander ausgelesen wird
    digitalWrite( ESP8266_GPIO2, 0); //LED Anschalten
    delay(5000);
    digitalWrite( ESP8266_GPIO2, 1); //LED Ausschalten    
  }
  delay(1);
  digitalWrite( ESP8266_GPIO2, 0); //LED Anschalten
  delay(500);
  digitalWrite( ESP8266_GPIO2, 1); //LED Ausschalten
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
  // WiFI.begin([ssid], [passkey]) initiates a WiFI connection.
  // to the stated [ssid], using the [passkey] as a WPA, WPA2, or WEP passphrase.
  WiFi.begin(ssid, password);
  int runs = 0;
  while ( WiFi.status() != WL_CONNECTED) {
    //Run´s mitzählen
    runs++;
    // Blink the LED.
    digitalWrite( LED_PIN, ledStatus ); // Write LED high/low.
    ledStatus = ( ledStatus == HIGH ) ? LOW : HIGH;
    int wait = random(50,1000);
    delay( wait );
    if(runs >= 50){
      Serial.println("Conntecting to Wifi Failed... Start reboot");
      ESP.restart();
    }
  }
  //Zurücksetzen der LED AUF "NICHT-LEUCHTEN"
  digitalWrite( LED_PIN, LOW );
  delay(1500);
  digitalWrite( LED_PIN, HIGH ); 
  return true;
}
void initOTA(){
  Serial.println("Booting");
  connectWiFi();

  // Port defaults to 8266
  ArduinoOTA.setPort(8080);

  // Hostname defaults to esp8266-[ChipID]
  ArduinoOTA.setHostname( dev_name );

  // No authentication by default
  // ArduinoOTA.setPassword("admin");

  // Password can be set with it's md5 value as well
  // MD5(admin) = 21232f297a57a5a743894a0e4a801fc3
  // ArduinoOTA.setPasswordHash("21232f297a57a5a743894a0e4a801fc3");

  ArduinoOTA.onStart([]() {
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
    if ( req.indexOf( "switch/ehueheiidsssxxejoff" ) != -1 ) {
      do_smth("switch");
      s += "ok";
    }else if ( req.indexOf( "MAC/ehueheiidsssxxejoff" ) != -1 ) {
      s += "MAC address: " + String(WiFi.macAddress());
 
    } else {
      s += "Invalid Request.<br> Try: switch/[password] or MAC/[password]";
    }
  } else {
    s = "HTTP/1.1 501 Not Implemented\r\nContent-Type: text/html\r\n\r\n<!DOCTYPE HTML>\r\n<html>\r\n";
  }
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
  digitalWrite( ESP8266_GPIO4, 0 );// Set relay control pin low.
  Serial.println("Off");
}
