[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-2.01-blue.svg)]()
[![Version](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-4.3%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-4-3-%28Stable%29-Changelog)

# IPSBVIP
Ermöglich die Einbindung von RCP+ Geräten in IP-Symcon.  

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Software-Installation](#3-software-installation) 
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Anhang](#5-anhang)  
    1. [GUID der Module](#1-guid-der-module)
    2. [Changlog](#2-changlog)
    3. [Spenden](#3-spenden)
6. [Lizenz](#6-lizenz)

## 1. Funktionsumfang

### [BVIPConfigurator:](BVIPConfigurator/)  
### [BVIPSplitter:](BVIPSplitter/)  
### [BVIPCamEvents:](BVIPCamEvents/)  
### [BVIPCamImages:](BVIPCamImages/)  
### [BVIPHealth:](BVIPHealth/)  
### [BVIPInputs:](BVIPInputs/)  
### [BVIPOutputs:](BVIPOutputs/)  
### [BVIPSerialPort:](BVIPSerialPort/)  
### [BVIPVidProc:](BVIPVidProc/)  
### [BVIPVirtualInputs:](BVIPVirtualInputs/)  

 
## 2. Voraussetzungen

 - IPS 4.3 oder höher
 
## 3. Software-Installation

   Über das 'Modul Control' folgende URL hinzufügen:  
    `git://github.com/Nall-chan/IPSBVIP.git`  

## 4. Einrichten der Instanzen in IP-Symcon

Ist direkt in der Dokumentation der jeweiligen Module beschrieben.  
Es wird empfohlen die Einrichtung mit dem BVIP Konfigurator zu starten (BVIPConfigurator).  

## 5. Anhang

###  1. GUID der Module

 
| Modul             | Typ          |Prefix  | GUID                                   |
| :---------------: | :----------: | :----: | :------------------------------------: |
| BVIPSplitter      | Splitter     | BVIP   | {58E3A4FB-61F2-4C30-8563-859722F6522D} |
| BVIPCamEvents     | Device       | BVIP   | {5E82180C-E0AD-489B-BFFB-BA2F9653CD4A} |
| BVIPCamImages     | Device       | BVIP   | {9CD9975F-D6DF-4287-956D-53C65B8675F3} |
| BVIPHealth        | Device       | BVIP   | {C42D8967-BF82-4D37-8309-2581F484B3BD} |
| BVIPInputs        | Device       | BVIP   | {1DC90109-FBDD-4F5B-8E29-5E95B8029F20} |
| BVIPOutputs       | Device       | BVIP   | {C900EEDF-60C3-4BDC-BC7D-39109CA05042} |
| BVIPSerialPort    | Splitter     | BVIP   | {CBEA6475-2EE1-4EC7-85F0-0B042FED87BB} |
| BVIPVidProc       | Device       | BVIP   | {6A046B86-C098-4A96-9038-800AE0BBFA10} |
| BVIPVirtualInputs | Device       | BVIP   | {3B02A316-33AE-4DCF-8AAF-A40453904DFF} |
| BVIPConfigurator  | Configurator | BVIP   |  |

### 2. Changlog

Version 2.01:  
 - Fixes für IPS 5.0

Version 2.0:  
 - Komplett überarbeitete Version für IPS 4.3 und höher  

Version 1.0:  
 - Erstes offizielles Release  für IPS 3.4

### 3. Spenden  
  
  Die Library ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  