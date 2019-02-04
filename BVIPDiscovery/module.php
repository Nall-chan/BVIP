<?php

declare(strict_types = 1);

require_once __DIR__ . '/../libs/BVIPTraits.php';  // diverse Klassen

/*
 * @addtogroup bvip
 * @{
 *
 * @package       BVIP
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 *
 */

/**
 * BVIPDiscovery Klasse implementiert
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       1.0
 *
 * @example <b>Ohne</b>
 * @property array $Devices
 */
class BVIPDiscovery extends ipsmodule
{
    use DebugHelper,
        BufferHelper;
    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->Devices = [];
        $this->RegisterPropertyString('User', 'service');
        $this->RegisterPropertyString('Password', '');
        $this->RegisterTimer('Discovery', 0, 'BVIP_Discover($_IPS[\'TARGET\']);');
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->Devices = $this->DiscoverDevices();
        $this->SetTimerInterval('Discovery', 300000);
    }

    /**
     * Interne Funktion des SDK.
     * Verarbeitet alle Nachrichten auf die wir uns registriert haben.
     *
     * @param int       $TimeStamp
     * @param int       $SenderID
     * @param int       $Message
     * @param array|int $Data
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->Devices = $this->DiscoverDevices();
                break;
        }
    }

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm()
    {
        $Devices = $this->DiscoverDevices();
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $InstanceIDListConfigurators = IPS_GetInstanceListByModuleID('{F9C6AC71-533B-4F93-8C9C-B348FAA336D2}');
        $DevicesIPAddress = [];
        foreach ($InstanceIDListConfigurators as $InstanceIDConfigurator) {
            $Splitter = IPS_GetInstance($InstanceIDConfigurator)['ConnectionID'];
            if ($Splitter > 0) {
                $IO = IPS_GetInstance($Splitter)['ConnectionID'];
                if ($IO > 0) {
                    $DevicesIPAddress[$InstanceIDConfigurator] = IPS_GetProperty($IO, 'Host');
                }
            }
        }
        $this->SendDebug('IPS', $DevicesIPAddress, 0);
        foreach ($Devices as &$Device) {
            $InstanceIDConfigurator = array_search($Device['unitIPAddress'], $DevicesIPAddress);
            if ($InstanceIDConfigurator === false) {
                $Device['instanceID'] = 0;
                $Device['name'] = $Device['unitName'];
            } else {
                unset($DevicesIPAddress[$InstanceIDConfigurator]);
                $Device['name'] = IPS_GetLocation($InstanceIDConfigurator);
                $Device['instanceID'] = $InstanceIDConfigurator;
                $Device['id'] = $InstanceIDConfigurator;
            }
            $Device['create'] = [
                [
                    'moduleID'      => '{F9C6AC71-533B-4F93-8C9C-B348FAA336D2}',
                    'configuration' => new stdClass()
                ],
                [
                    'moduleID'      => '{58E3A4FB-61F2-4C30-8563-859722F6522D}',
                    'configuration' => [
                        'User'     => $this->ReadPropertyString('User'),
                        'Password' => $this->ReadPropertyString('Password')
                    ]
                ],
                [
                    'moduleID'      => '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}',
                    'configuration' => [
                        'Host' => $Device['unitIPAddress'],
                        'Port' => (int) $Device['RCPPort'],
                        'Open' => true
                    ]
                ]
            ];
        }
        $MissingConfigurators = [];
        foreach ($DevicesIPAddress as $InstanceIDConfigurator => $unitIPAddress) {
            $MissingConfigurators[] = [
                'unitIPAddress' => $unitIPAddress,
                'friendlyName'  => '',
                'unitName'      => '',
                'deviceType'    => '',
                'instanceID'    => $InstanceIDConfigurator,
                'name'          => IPS_GetLocation($InstanceIDConfigurator)
            ];
        }


        $Values = array_merge($Devices, $MissingConfigurators); // $Sensors, $MissingSensors);
        /* if (count($Values) > 0) {
          foreach ($Values as $key => $row) {
          $SortDevice[$key] = $row['device'];
          $SortType[$key] = $row['type'];
          }
          array_multisort($SortDevice, SORT_ASC, $SortType, SORT_ASC, $Values);
          } */
        $Form['actions'][0]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    private function DiscoverDevices(): array
    {
        $DeviceData = [];
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) {
            return $DeviceData;
        }
        socket_bind($socket, '0.0.0.0', 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 100000));
        $Host = "";
        $Port = 0;
        socket_getsockname($socket, $Host, $Port);
        $message = "\x99\x39\xA4\x27" . openssl_random_pseudo_bytes(4) . "\xFF\x00" . pack('n', $Port);
        if (@socket_sendto($socket, $message, strlen($message), 0, '255.255.255.255', 1757) === false) {
            return $DeviceData;
        }
        usleep(100000);
        $i = 50;
        $buf = "";
        $Name = "";
        while ($i) {
            $ret = @socket_recvfrom($socket, $buf, 2048, 0, $Name, $Port);
            if ($ret == 0) {
                $i--;
                continue;
            }
            if (strlen($buf) > 32) {
                $Data = json_decode(json_encode(simplexml_load_string($buf)->device), true);
                $this->SendDebug($Name, $Data, 0);
                if (!array_key_exists('RCPPort', $Data)) {
                    $Data['RCPPort'] = 1756;
                }
                $DeviceData[] = $Data;
            }
        }
        socket_close($socket);
        return $DeviceData;
    }

    public function Discover()
    {
        $this->LogMessage($this->Translate('Background Discovery of BVIP Devices'), KL_NOTIFY);

        $this->Devices = $this->DiscoverDevices();
        // Alt neu vergleich fehlt, sowie die Events an IPS senden wenn neues Gerät im Netz gefunden wurde.
    }
}

/* @} */
