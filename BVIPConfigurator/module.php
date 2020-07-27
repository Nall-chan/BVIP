<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/BVIPBase.php';

/*
 * @addtogroup bvip
 * @{
 *
 * @package       BVIP
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.1
 *
 */

/**
 * BVIPConfigurator Klasse implementiert.
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       3.1
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
        parent::MessageSink($TimeStamp, $SenderID, $Message, $Data);
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
        $this->SendDebug('Parent', $this->ParentID, 0);

        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Capabilities = $this->GetCapability();
        if ($Capabilities === false) {
            $Form['actions'][] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                        'type'    => 'Label',
                        'caption' => 'Error on read capabilities.'
                    ]]
                ]
            ];
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);

            return json_encode($Form);
        }
        if (!$this->HasActiveParent()) {
            $Form['actions'][] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                        'type'    => 'Label',
                        'caption' => 'Instance has no active parent.'
                    ]]
                ]
            ];
        }

        $NbrOfVideoIn = count($Capabilities['Video']['Encoder']);
        $HasInput = $Capabilities['IO']['Input'] > 0;
        $HasOutput = $Capabilities['IO']['Output'] > 0;
        $HasVirtual = $Capabilities['IO']['Virtual'] > 0;
        $NbrOfSerialPorts = $Capabilities['SerialPorts'];

        $this->SendDebug('NbrOfVideoIn', $NbrOfVideoIn, 0);
        $this->SendDebug('HasInput', $HasInput, 0);
        $this->SendDebug('HasOutput', $HasOutput, 0);
        $this->SendDebug('HasVirtual', $HasVirtual, 0);
        $this->SendDebug('NbrOfSerialPorts', $NbrOfSerialPorts, 0);
        //CamEventsInstance
        $CamEventsInstance = $this->GetConfiguratorArray('{5E82180C-E0AD-489B-BFFB-BA2F9653CD4A}', 'Line', $NbrOfVideoIn);
        $CamImagesInstance = $this->GetConfiguratorArray('{9CD9975F-D6DF-4287-956D-53C65B8675F3}', 'Line', $NbrOfVideoIn);
        $CamVidProcInstance = $this->GetConfiguratorArray('{6A046B86-C098-4A96-9038-800AE0BBFA10}', 'Line', $NbrOfVideoIn);
        $SerialPortInstance = $this->GetConfiguratorArray('{CBEA6475-2EE1-4EC7-85F0-0B042FED87BB}', 'Number', $NbrOfSerialPorts);
        $InputInstance = [];
        if ($HasInput) {
            $InputInstance = $this->GetConfiguratorArray('{1DC90109-FBDD-4F5B-8E29-5E95B8029F20}', '', 1);
        }
        $OutputInstance = [];
        if ($HasOutput) {
            $OutputInstance = $this->GetConfiguratorArray('{C900EEDF-60C3-4BDC-BC7D-39109CA05042}', '', 1);
        }
        $VirtualInstance = [];
        if ($HasVirtual) {
            $VirtualInstance = $this->GetConfiguratorArray('{3B02A316-33AE-4DCF-8AAF-A40453904DFF}', '', 1);
        }

        $HealthInstance = $this->GetConfiguratorArray('{C42D8967-BF82-4D37-8309-2581F484B3BD}', '', 1);

        $Values = array_merge($CamEventsInstance, $CamImagesInstance, $CamVidProcInstance, $SerialPortInstance, $InputInstance, $OutputInstance, $VirtualInstance, $HealthInstance);

        $Form['actions'][0]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);

        return json_encode($Form);
    }

    public function RequestState()
    {
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
    }

    private function GetInstanceList(string $GUID, string $ConfigParam)
    {
        $InstanceIDList = array_flip(array_values(array_filter(IPS_GetInstanceListByModuleID($GUID), [$this, 'FilterInstances'])));
        if ($ConfigParam != '') {
            array_walk($InstanceIDList, [$this, 'GetConfigParam'], $ConfigParam);
        }
        return $InstanceIDList;
    }

    private function FilterInstances(int $InstanceID)
    {
        return IPS_GetInstance($InstanceID)['ConnectionID'] == $this->ParentID;
    }

    private function GetConfigParam(&$item1, $InstanceID, $ConfigParam)
    {
        $item1 = IPS_GetProperty($InstanceID, $ConfigParam);
    }

    private function GetConfiguratorArray(string $GUID, string $ConfigParamName, int $MaxValueConfig)
    {
        $Splitter = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        $IO = IPS_GetInstance($Splitter)['ConnectionID'];

        $ParentCreate = [
            [
                'moduleID'      => '{58E3A4FB-61F2-4C30-8563-859722F6522D}',
                'configuration' => [
                    'User'     => IPS_GetProperty($Splitter, 'User'),
                    'Password' => IPS_GetProperty($Splitter, 'Password')
                ]
            ],
            [
                'moduleID'      => '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}',
                'configuration' => [
                    'Host' => IPS_GetProperty($IO, 'Host'),
                    'Port' => (int) IPS_GetProperty($IO, 'Port')
                ]
            ]
        ];
        $ModuleName = substr(IPS_GetModule($GUID)['Aliases'][0], 5);
        $NoLocation = (IPS_GetModule($GUID)['ModuleType'] != 3);
        $InstancesDevices = $this->GetInstanceList($GUID, $ConfigParamName);
        $this->SendDebug($ModuleName, $InstancesDevices, 0);
        $Devices = [];
        for ($index = 1; $index <= $MaxValueConfig; $index++) {
            $Device = [];
            $Device['type'] = $ModuleName;
            if ($ConfigParamName != '') {
                $InstanceID = array_search($index, $InstancesDevices);
                $Device['line'] = $index;
            } else {
                $InstanceID = array_search(0, $InstancesDevices);
                $Device['line'] = '';
            }
            if ($InstanceID === false) {
                $Device['instanceID'] = 0;
                $Device['name'] = $ModuleName;
                $Device['location'] = '';
            } else {
                unset($InstancesDevices[$InstanceID]);
                $Device['instanceID'] = $InstanceID;
                $Device['name'] = IPS_GetName($InstanceID);
                $Device['location'] = stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true);
            }
            $Create = [
                'moduleID'      => $GUID,
                'configuration' => new stdClass()
            ];
            if ($ConfigParamName != '') {
                $Create['configuration'] = [
                    $ConfigParamName => $index
                ];
            }
            if (!$NoLocation) {
                $Create['location'] = [$this->Translate('BVIP Devices'), IPS_GetName($this->InstanceID)];
            }

            $Device['create'] = array_merge([$Create], $ParentCreate);
            $Devices[] = $Device;
        }
        if ($ConfigParamName !== '') {
            foreach ($InstancesDevices as $InstanceID => $Line) {
                $Devices[] = [
                    'instanceID' => $InstanceID,
                    'type'       => $ModuleName,
                    'line'       => $Line,
                    'name'       => IPS_GetName($InstanceID),
                    'location'   => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
                ];
            }
        } else {
            foreach ($InstancesDevices as $InstanceID => $Line) {
                $Devices[] = [
                    'instanceID' => $InstanceID,
                    'type'       => $ModuleName,
                    'name'       => IPS_GetName($InstanceID),
                    'location'   => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
                ];
            }
        }

        return $Devices;
    }
}

/* @} */
