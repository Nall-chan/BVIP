<?php

//declare(strict_types=1);
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
require_once __DIR__ . '/../libs/BVIPBase.php';

/**
 * BVIPConfigurator Klasse implementiert
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       1.0
 *
 * @example <b>Ohne</b>
 */
class BVIPConfigurator extends BVIPBase
{
    protected static $RCPTags = [];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent('{58E3A4FB-61F2-4C30-8563-859722F6522D}');
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    protected function KernelReady()
    {
        parent::KernelReady();
    }

    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
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
    }

    public function GetCapability()
    {
        return parent::GetCapability();
    }

    /**
     * Interne Funktion des SDK.
     */
    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Capas = $this->GetCapability();
        if ($Capas === false) {
            return json_encode($Form);
        }
        $NbrOfVideoIn = count($Capas['Video']['Encoder']);
        $HasInput = $Capas['IO']['Input'] > 0;
        $HasOutput = $Capas['IO']['Output'] > 0;
        $HasVirtual = $Capas['IO']['Virtual'] > 0;
        $Serials = $Capas['SerialPorts'];
        
        $Instances='';
        
        
        /*
          if (count($this->Devices) == 0) {
          $this->Discover();
          }
          $Devices = $this->Devices;

          $InstanceIDListDevices = IPS_GetInstanceListByModuleID('{58E3A4FB-61F2-4C30-8563-859722F6522D}');
          $InstancesDevices = [];
          foreach ($InstanceIDListDevices as $InstanceIDDevice) {
          $IO = IPS_GetInstance($InstanceIDDevice)['ConnectionID'];
          if ($IO > 0) {
          $InstancesDevices[$InstanceIDDevice] = IPS_GetProperty($IO, 'Host');
          }
          }
          $this->SendDebug('IPS', $InstancesDevices, 0);
          foreach ($Devices as &$Device) {
          $InstanceIDDevice = array_search($Device['unitIPAddress'], $InstancesDevices);
          if ($InstanceIDDevice === false) {
          $Device['instanceID'] = 0;
          $Device['name'] = '';
          } else {
          unset($InstancesDevices[$InstanceIDDevice]);
          $Device['name'] = IPS_GetLocation($InstanceIDDevice);
          $Device['instanceID'] = $InstanceIDDevice;
          $Device['id'] = $InstanceIDDevice;
          }
          $Device['create'] = [
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
          $MissingDevices = [];
          foreach ($InstancesDevices as $InstanceIDDevice => $unitIPAddress) {
          $MissingDevices[] = [
          'unitIPAddress' => $unitIPAddress,
          'friendlyName'  => '',
          'unitName'      => '',
          'deviceType'    => '',
          'instanceID'    => $InstanceIDDevice,
          'name'          => IPS_GetLocation($InstanceIDDevice)
          ];
          }


          $Values = array_merge($Devices, $MissingDevices); // $Sensors, $MissingSensors);

          $Form['actions'][0]['values'] = $Values;
          $this->SendDebug('FORM', json_encode($Form), 0);
          $this->SendDebug('FORM', json_last_error_msg(), 0);
         */
        return json_encode($Form);
    }

    public function RequestState()
    {
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
    }
}

/* @} */
