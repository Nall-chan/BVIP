[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.00-blue.svg)]()
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-5-1-%28Stable%29-Changelog)
[![Check Style](https://github.com/Nall-chan/BVIP/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/BVIP/actions) [![Run Tests](https://github.com/Nall-chan/BVIP/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/BVIP/actions)  

# BVIP Discovery <!-- omit in toc -->
**todo**  

## Dokumentation <!-- omit in toc -->

**Inhaltsverzeichnis**

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Installation](#3-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen und Profile](#5-statusvariablen-und-profile)
- [6. WebFront](#6-webfront)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Anhang](#8-anhang)
- [9. Lizenz](#9-lizenz)

## 1. Funktionsumfang


## 2. Voraussetzungen

 - IPS 5.1 oder höher

## 3. Installation

**IPS 5.1:**  
   Bei privater Nutzung:
     Über den 'Module-Store' in IPS.  
   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Einrichten der Instanzen in IP-Symcon

Eine einfache Einrichtung ist über den Konfigurator [BVIP Konfigurator](../../BVIPConfigurator/readme.md) möglich.  
Bei der manuellen Einrichtung ist die Instanz im Dialog 'Instanz hinzufügen' unter dem Hersteller 'BOSCH' zu finden.  
![Instanz hinzufügen](imgs/add.png)  

**Konfigurationsseite:**  
![Instanz hinzufügen](imgs/conf.png)  

| Name  | Eigenschaft |  Typ  | Standardwert | Funktion |
| :---: | :---------: | :---: | :----------: | :------: |


## 5. Statusvariablen und Profile

Folgende Statusvariablen werden automatisch angelegt.  
**Statusvariablen allgemein:**  

| Name  |  Typ  | Ident | Beschreibung |
| :---: | :---: | :---: | :----------: |

**Profile**:

| Name  |  Typ  | verwendet von Statusvariablen |
| :---: | :---: | :---------------------------: |


## 6. WebFront

Die direkte Darstellung im WebFront ist möglich, es wird aber empfohlen mit Links zu arbeiten.  
![WebFront Beispiel](imgs/wf1.png)  


## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.  
Dies gilt auch wenn ein übergebender Wert für einen Parameter nicht gültig ist, oder außerhalb seines zulässigen Bereiches liegt.  

```php
string BVIP_RequestState(int $InstanzID, string $Ident)
```

---


## 8. Anhang

**Changelog:**  

Version 3.0:  
 - Release für IPS 5.1 und den Module-Store  
 
Version 2.0:  
 - Komplett überarbeitete Version für IPS 4.3 und höher  

Version 1.0:  
 - Erstes offizielles Release  

## 9. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
