/*Wenn mit Energie versorgt meldet er dem definierten C&C Server das Power da ist
 * 
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266mDNS.h>
#include <WiFiUdp.h>
#include <ArduinoOTA.h>
//Wifi-Server aufsetzen damit der Status abgefragt werden kann
WiFiServer server(8081);

const char* ssid = "[WLAN_NAME]";
const char* password = "[WLAN_PASSWORT]";
int LED_PIN = 2;

void setup() {
  initHardware();
  initOTA();
  server.begin();  
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
  while ( WiFi.status() != WL_CONNECTED ) {
    // Blink the LED.
    digitalWrite( LED_PIN, ledStatus ); // Write LED high/low.
    ledStatus = ( ledStatus == HIGH ) ? LOW : HIGH;
    int wait = random(1,1000);
    delay( wait );
  }
  return true;
}
void initOTA(){
  Serial.println("Booting");
  connectWiFi();

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
    ESP.restart();
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
}
void GetClient( WiFiClient client ) {
  // Read the first line of the request.
  String req = client.readStringUntil( '\r' );
  Serial.println( req );
  client.flush();
 
  String s = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n<!DOCTYPE HTML>\r\n";
 
  if ( req.indexOf( "OPTIONS" ) != -1 ) {
    s += "ok";
 
  } else if ( req.indexOf( "GET" ) != -1 ) {
    s += "ok";
  } else 
    s += "ok";
          
  client.flush();
 
  // Send the response to the client.
  client.print( s );
  delay( 1 );
  Serial.println( "Client response sent." );
}
void initHardware() {
  Serial.begin( 9600 );
  pinMode(LED_PIN,OUTPUT);
}
