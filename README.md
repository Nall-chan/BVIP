[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.00-blue.svg)]()
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)  
[![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-green.svg)](https://www.symcon.de/forum/threads/30857-IP-Symcon-5-1-%28Stable%29-Changelog)
[![Check Style](https://github.com/Nall-chan/BVIP/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/BVIP/actions) [![Run Tests](https://github.com/Nall-chan/BVIP/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/BVIP/actions)  

# Symcon-Modul: BVIP <!-- omit in toc -->
Ermöglicht die Einbindung von RCP+ Geräten in IP-Symcon.  

## Dokumentation <!-- omit in toc -->

**Inhaltsverzeichnis**
- [1. Funktionsumfang](#1-funktionsumfang)
  - [BVIPConfigurator:](#bvipconfigurator)
  - [BVIPSplitter:](#bvipsplitter)
  - [BVIPCamEvents:](#bvipcamevents)
  - [BVIPCamImages:](#bvipcamimages)
  - [BVIPCamReplay:](#bvipcamreplay)
  - [BVIPHealth:](#bviphealth)
  - [BVIPInputs:](#bvipinputs)
  - [BVIPOutputs:](#bvipoutputs)
  - [BVIPSerialPort:](#bvipserialport)
  - [BVIPVidProc:](#bvipvidproc)
  - [BVIPVirtualInputs:](#bvipvirtualinputs)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Anhang](#5-anhang)
  - [1. GUID der Module](#1-guid-der-module)
  - [2. Changelog](#2-changelog)
  - [3. Spenden](#3-spenden)
- [6. Lizenz](#6-lizenz)

## 1. Funktionsumfang

### [BVIPConfigurator:](BVIPConfigurator/)  
### [BVIPSplitter:](BVIPSplitter/)  
### [BVIPCamEvents:](BVIPCamEvents/)  
### [BVIPCamImages:](BVIPCamImages/)  
### [BVIPCamReplay:](BVIPCamReplay/)  
### [BVIPHealth:](BVIPHealth/)  
### [BVIPInputs:](BVIPInputs/)  
### [BVIPOutputs:](BVIPOutputs/)  
### [BVIPSerialPort:](BVIPSerialPort/)  
### [BVIPVidProc:](BVIPVidProc/)  
### [BVIPVirtualInputs:](BVIPVirtualInputs/)  

 
## 2. Voraussetzungen

 - IPS 5.1 oder höher
 
## 3. Software-Installation

**IPS 5.1:**  
   Bei privater Nutzung:
     Über den 'Module-Store' in IPS.  
   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**  

## 4. Einrichten der Instanzen in IP-Symcon

Ist direkt in der Dokumentation der jeweiligen Module beschrieben.  
Es wird empfohlen die Einrichtung mit dem BVIP Konfigurator zu starten (BVIPConfigurator).  

## 5. Anhang

###  1. GUID der Module

 
| Modul             | Typ          |Prefix  | GUID                                   |
| :---------------: | :----------: | :----: | :------------------------------------: |
| BVIPSplitter      | Splitter     | BVIP   | {58E3A4FB-61F2-4C30-8563-859722F6522D} |
| BVIPCamEvents     | Device       | BVIP   | {5E82180C-E0AD-489B-BFFB-BA2F9653CD4A} |
| BVIPCamReplay     | Device       | BVIP   | {95A5679D-1FBE-4ABC-9039-4D98802D337C} |
| BVIPCamImages     | Device       | BVIP   | {9CD9975F-D6DF-4287-956D-53C65B8675F3} |
| BVIPHealth        | Device       | BVIP   | {C42D8967-BF82-4D37-8309-2581F484B3BD} |
| BVIPInputs        | Device       | BVIP   | {1DC90109-FBDD-4F5B-8E29-5E95B8029F20} |
| BVIPOutputs       | Device       | BVIP   | {C900EEDF-60C3-4BDC-BC7D-39109CA05042} |
| BVIPSerialPort    | Splitter     | BVIP   | {CBEA6475-2EE1-4EC7-85F0-0B042FED87BB} |
| BVIPVidProc       | Device       | BVIP   | {6A046B86-C098-4A96-9038-800AE0BBFA10} |
| BVIPVirtualInputs | Device       | BVIP   | {3B02A316-33AE-4DCF-8AAF-A40453904DFF} |
| BVIPConfigurator  | Configurator | BVIP   | {F9C6AC71-533B-4F93-8C9C-B348FAA336D2} |
| BVIPDiscovery     | Discovery    | BVIP   | {7013126D-4AAB-41C2-BAE0-FD7A5C59B89C} |

### 2. Changelog

Version 3.0:  
 - Release für IPS 5.1 und den Module-Store  
 - Replay-Instanz hinzugefügt (beta)  

Version 2.5:  
 - Konfigurationform überarbeitet  
 - Discovery-Instanz hinzugefügt  
 - RTSP-Stream ergänzt  
 - JPEG-Push und h.26x für Media-Objekt ergänzt  

Version 2.01:  
 - Fixes für IPS 5.0

Version 2.0:  
 - Komplett überarbeitete Version für IPS 4.3 und höher  

Version 1.0:  
 - Erstes offizielles Release  für IPS 3.4

### 3. Spenden  
  
  Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:  

<a href="https://www.paypal.com/donate?hosted_button_id=G2SLW2MEMQZH2" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

  IPS-Modul:  
  [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)  