/*
 * Test-Script für Eingang auf WiFi-Relay-Platine
 * hier wird der Opto-Eingang des Wifi-Relay-Boards 
 * getestet
 */
void test_pin( int i){
  pinMode(i, INPUT_PULLUP);
  Serial.println(digitalRead(i));
}
void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  delay(3000);
  Serial.println("abc");
}

void loop() {
  Serial.println("NEW Start");
  test_pin(5);
  delay(1000);                         
}
