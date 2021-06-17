# Own Smart-Home System
## Version 1.1
Da sich wärend der Entwicklung des Systems Webserver->CC-Server->Gerät probleme mit Verzögerung etc. ergeben haben wird nun am Ursprünglichen Design festgehalten.

Jeder CC-Server ist eigenständig und hat auch eine eigenständige Benutzerverwaltung etc.
Zu jedem CC-Server können Geräte hinzugefügt, gelöscht und geändert werden.

Für den Betrieb des Smarthome-Systems wird keine Internetverbindung mehr benötigt! 
Das Smarthome-System kann somit in einem eigenen Netz betrieben werden.

Ein CC-Server kann (muss aber nicht) mit einem Webinterface verbunden werden. Ein Webinterface kann auch mehrere CC-Server bedienen.

Die Haupt-Programmierarbeit findet deswegen nun im "smarthome_cc"-Repo statt. 

Veraltet:
## Smarthome Allgemeine Info´s

Momentan noch nicht Fertig!

-->Ich versuche Gerade mein aktuell Verwendetes Smarthome System (selbst entwickelt) für die öffnetlichkeit zur verfügung zu stellen.

-->Commits kommen nach und nach das Projekt ist so gesehen schon "vorprogrammiert"

-->Neu Programmiert werden u. a. die Installer das z.B. der C&C-Server durch einfaches ausführen einer Datei installiert werden kann

-->Bevor Commits in dieses Public Repo rein kommen wird der Code nochmal überarbeitet und dann erst hochgeladen.

-->Ein größerer Umbau findet dahingehend statt das jetzt in der var.php sich Werte Benutzerspezifisch ändern können und somit das fixe aus 
   dem Code entfernt werden muss damit es zu keinen Fehern kommt

## Was kann das Smarthome - System
Es wird hier ein mehr oder weniger Komplett-System zur Verfügung gestellt.

Es Basiert auf einem Klassischen Hierarchi-System.

Ganz oben ist der Webserver mit dem Webinterface.

Darunter sind die einzelnen C&C-Server

Darunter sind alle für den jeweiligen C&C-Server verknüpften Geräte


Das Webinterface besitzt ein Klassisches Rechte-System:

Es drei verschiedene Stufen:

-Admin<br />
-Verwalter<br />
-Benutzer<br />


Der Benutzer bekommt vom Verwalter/Admin Geräte zugeteilt die er steuern kann.

Der Verwalter bekommt vom Admin Geräte(-Gruppen) zugeteilt die er Verwalten kann.

Der Admin wird bei der Installation erstellt. Er hat alle Berechtigungen und kann z.B. neue Geräte anlernen oder neue C&C-Server verbinden.


Das gesamte System ist modular aufgebaut sprich mit einem Webinterface können mehrere C&C-Server verbudnen werden.
Gleiches gilt auch anders rum, ein C&C-Server kann auch mit mehreren Webiterface´s gekoppelt werden.

Ein weiterer C&C-Server kommt zum einsatz wenn z.B. die insg. Gerätemenge zu groß wird und sich dadurch zu lange Delay´s ergeben.
Im normalfall kommt ein weiterer C&C-Server zum Einsatz wenn man z.B. Haus1 und Haus2 von einem Webinterface aus Steuern möchte.

## Gebraucht wird:
Trotzdem das noch nichts fertig ist kann ich schonmal sagen was ca. an Hardware gebraucht wird:

### C&C-Server:
Ein C&C Server benötigt folgende Hardware:

-Raspberry PI (ich verwende einen 3b+ und einen 4, mit 1 und 2 nicht getestet!)<br />
-Netzwerkanschluss (am besten via LAN)<br />
-Netzteil und andere Zubehörteile zum Raspi z.B. ein HDMI-zu-VGA Converter<br />
-SD-Karte (ich denke 8GB+ ist am beseten)<br />
-Optional wenn Konfiguration nicht über SSH erfolgen soll auch noch eine USB Maus+Tastatur<br />

### Webinterface
Für das Webinterface wird folgendes gebraucht:

Möglichkeit 1:<br />
Nichts --> Ich z.B. nutze einen Webserver von einen Hostinganbieter

Möglichketi 2:<br />
Der Webserver wird selber gehostet--> Am besten die gleiche Hardware wie beim C&C-Server nur das mehr Leistung (wenn es viele Cronjobs gibt) benötigt werden kann
Zudem ist es immer ein Problem den Webserver von außen zu erreichen falls keine Feste externe IP oder eine DDNS-Adresse besteht

### Wifi-Taster
--Momentan kann ich noch keine genaue Angabe geben da meine WiFi-Taster Marke Eigenbau sind--

### Wifi-Relais
--Momentan kann ich noch keine genaue Angabe geben da meine Wifi-Relais Marke Eigenbau sind--


Das Repo wird für Download-Zwecke (bzw. für die Installer der einzelnen Instanzen) nach und nach aufgeteilt, z.B. wird der C&C server in dem 
Repo "smarthome_cc" - Entwickelt und programmiert, der Installer für den C&C - Server findet sich jedoch in diesem (Haupt-Repo)

## Das wars mehr hab ich bis jetzt noch nicht zu sagen
