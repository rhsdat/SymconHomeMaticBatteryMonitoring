## SymconHomeMaticBatteryMonitoring

[![Version](https://img.shields.io/badge/Symcon_Version-5.0>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Modul_Version-1.00-blue.svg)
![Version](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/135727452/shield?branch=master)](https://github.styleci.io/repos/135727452)

Dieses Modul überwacht den Batteriestatus von batteriebetriebenen [HomeMatic](http://www.eq-3.de/produkte/homematic.html) oder [HomeMaticIP](http://www.eq-3.de/produkte/homematic-ip.html) Geräten (nachfolgend Geräte genannt), welche in [IP-Symcon](https://www.symcon.de) angelegt, bzw vorhanden sind. 

Die Prüfung des Batteriestatus wird automatisch einmal täglich durchgeführt oder über den Instanzeditor kann eine manuelle Überprüfung durchgeführt werden.

Für die Nutzung dieses Moduls wird mindestens die Version 5.0 von IP-Symcon vorausgesetzt.

Die Entwicklung dieses Moduls findet in der Freizeit als Hobby statt.
Somit besteht auch kein Anspruch auf Fehlerfreiheit, Weiterentwicklung oder sonstige Unterstützung / Support.
Ziel ist es, den Funktionsumfang von IP-Symcon zu erweitern.

Bevor das Modul installiert wird, sollte ein Backup von IP-Symcon durchgeführt werden.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanz in IP-Symcon](#4-einrichten-der-instanz-in-ip-symcon)
5. [Variablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [GUIDs](#8-guids)
9. [Changelog](#9-changelog)
10. [Lizenz](#10-lizenz)
11. [Author](#11-author)


### 1. Funktionsumfang

Automatische Funktionen:

- Statusüberprüfung zu einer frei definierbaren Uhrzeit.
- Push Benachrichtigung über das Webfront oder Pushover, entsprechende Instanz muss vorhanden sein.
- Benachrichtigung per E-Mail, entsprechende SMTP Instanz muss vorhanden sein.

Manuelle Funktionen über den Instanzeditor:

- Anzeigen des aktuellen Batteriestatus.
- Überprüfen des aktuellen Batteriestatus mit Benachrichtigung.
- Anlegen einer Batterieliste der vorhandenen Geräte.
- Zuweisen des Batterieprofils für die vorhandenen Geräte.
 
### 2. Voraussetzungen

- IP-Symcon ab Version 5.0

### 3. Software-Installation

Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Autor.

Bei privater Nutzung:

Nachfolgend wird die Installation dieses Moduls anhand der neuen Web-Console der Version 5.0 beschrieben.
Folgende Instanzen stehen dann in IP-Symcon zur Verfügung:

- HomeMatic BatteryMonitoring bzw. HomeMatic Batterieüberwachung

Im Objektbaum von IP-Symcon die Kern-Instanzen aufrufen. Danach die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) aufrufen. Sie sehen nun die bereits installierten Module.
Fügen Sie über das `+` Symbol (unten rechts) ein neues Modul hinzu.
Wählen Sie als URL:

`https://github.com/ubittner/SymconHomeMaticBatteryMonitoring.git`  

Anschließend klicken Sie auf `OK`, um die HomeMatic Batterieüberwachung zu installieren.

### 4. Einrichten der Instanz in IP-Symcon

Klicken Sie in der Objektbaumansicht unten links auf das `+` Symbol. Wählen Sie anschließen `Instanz` aus. Geben Sie im Schnellfiler das Wort "HomeMatic Batterieüberwachung" ein oder wählen den Hersteller "Ulrich Bittner" aus. Wählen Sie aus der Ihnen angezeigten Liste "HomeMatic Batterieüberwachung" oder "HomeMatic BatteryMonitoring" aus und klicken Sie anschließend auf `OK`, um die Instanz zu installieren.

Hier zunächst die Übersicht der Konfigurationsfelder.

__Konfigurationsseite__:

Name | Beschreibung
----------------------------------- | ---------------------------------------------
(1) Allgemeine Einstellungen        | Allgemeine Einstellungen.
Kategorie                           | Kategorie in der die HomeMatic Batterieüberwachung abgelegt werden soll.
Instanzbezeichnung                  | Bezeichnung für die Instanz.
Standortbezeichnung                 | Bezeichnung für den Standort, z.B. Straße oder einen Namen.
(2) Statusüberprüfung               | Statusüberprüfung.
Tägliche Überprüfung                | Schaltet die tägliche Überprüfung ein, bzw. aus.
Uhrzeit                             | Uhrzeit, zu der die Überprüfung durchgeführt werden soll.
(3) Push Benachrichtigungen         | Pushbenachrichtigungen.
Titelbezeichnung                    | Bezeichnung für den Nachrichtentitel. 
Liste                               | Liste der Benachrichtigungsinstanzen.
Position                            | Position, darf nur einmal vorhanden sein.
WebFront / Pushover                 | WebFront oder Pushover Instanz, die verwendet werden soll.
Verwendung                          | Verwendung des Nachrichtendienstes kann de-, bzw. aktiviert werden.
Status OK                           | Benachrichtigung bei Status OK kann de-, bzw. aktiviert werden.
Batterie schwach                    | Benachrichtigung bei Batterie schwach kann de-, bzw. aktiviert werden. 
(4) E-Mail Benachrichtigungen       | E-Mail Benachrichtigungen.
E-Mail Betreff                      | Bezeichnung für den E-Mail Betreff. 
Liste                               | Liste der SMTP E-Mail Instanzen.
Position                            | Position, darf nur einmal vorhanden sein.
E-Mail (SMTP) Instanz               | E-Mail (SMTP) Instanz, die verwendet werden soll.
E-Mail Adresse                      | E-Mail Adresse des Empfängers.
Verwendung                          | Verwendung der E-Mail Benachrichtigung kann de-, bzw. aktiviert werden.
Status OK                           | Benachrichtigung bei Status OK kann de, bzw. aktiviert werden.
Batterie schwach                    | Benachrichtigung bei Batterie schwach kann de-, bzw. aktiviert werden. 


Über das Konfigurationsfeld `Kategorie` können Sie festlegen, in welcher Kategorie die Instanz abgelegt werden sollen. Es kann auch die Hauptkategorie genutzt werden.

Geben Sie eine Bezeichung für die Instanz an, z.B. Batterieüberwachung. 

Optional können Sie noch eine Standortbezeichnung hinzufügen.

Aktivieren Sie die Statusüberprüfung `Tägliche Überprüfung`.

Geben Sie die erforderlichen Daten für Push / E-Mail Benachrichtigung an, um die entsprechende Mitteilung zu erhalten. 

Wenn Sie die Daten eingetragen haben, erscheint unten im Instanzeditor eine Meldung `Die Instanz hat noch ungespeicherte Änderungen`. Klicken Sie auf den Button `Änderungen übernehmen`, um die Konfigurationsdaten zu übernehmen und zu speichern.

Sie können den Vorgang für weitere HomeMatic Batterieüberwachung Instanzen wiederholen.

##### Hinweis:

Beim Anlegen der Instanz werden unterhalb der Instanz alle in IP-Symcon vorhanden Geräte ein Link des Batteriestatus angelegt.

### 5. Variablen und Profile

##### Variablen:

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Es werden keine Variablen angelegt.

##### Profile:

Nachfolgende Profile werden zusätzlichen hinzugefügt:

Es werden keine Profile angelegt.

### 6. WebFront

Über das WebFront können Sie den Batteriestatus der vorhandenen Geräte erkennen.

### 7. PHP-Befehlsreferenz

Präfix des Moduls `UBHMBM` (HomeMaticBatteryMonitoring)

`UBHMBM_ShowBatteryState(integer $InstanzID)`

Zeigt den Batteriestatus aller in IP-Symcon vorhandenen Geräte an.

`UBHMBM_CheckBatteryState(integer $InstanzID)`

Prüft den Batteriestatus aller in IP-Symcon vorhandenen  Geräte und führt die Benachrichtigungen aus.

`UBHMBM_CreateBatteryLinks(integer $InstanzID)`

Ein Link über den Batteriestatus aller in IP-Symcon vorhandenen Geräte wird unterhalb der Instanz angelegt.

`UBHMBM_AssignBatteryProfile(integer $InstanzID)`

Weist den vorhandenen Geräten das Batterieprofil zu.

### 8. GUIDs

__Modul GUIDs__:

| Name           | GUID                                   | Bezeichnung  |
| ---------------| -------------------------------------- | -------------|
| Bibliothek     | {2FF2A23B-D6BD-4474-ACBE-382773341175} | Library GUID |
| Modul          | {AF3D2026-7739-4011-A0A4-B0A53F6556F8} | Module GUID  |

### 9. Changelog

Version     | Datum      | Beschreibung
----------- | -----------| -------------------
1.00        | 24.05.2018 | Modulerstellung

### 10. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

### 11. Author

Ulrich Bittner
