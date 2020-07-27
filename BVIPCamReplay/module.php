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
 * BVIPCamReplay Klasse implementiert eine Device für die Einbindung eines Wiedergabestream.
 * Erweitert BVIPBase.
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       3.1
 *
 * @example <b>Ohne</b>
 */
class BVIPCamReplay extends BVIPBase
{
    protected static $RCPTags = [];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('Rename', true);
        $this->RegisterPropertyBoolean('UseAuth', false);
        $this->RegisterPropertyInteger('Line', 1);
        $this->RegisterPropertyInteger('Recording', 1);
        $this->RegisterPropertyBoolean('Autoplay', true);
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterProfileIntegerEx('BVIP.ReplayControl', '', '', '', [
            [-16, '16x', '', 0],
            [-8, '8x', '', 0],
            [-4, '4x', '', 0],
            [-2, '<<', '', 0],
            [-1, '<', '', 0],
            [0, 'II', '', 0],
            [1, '>', '', 0],
            [2, '>>', '', 0],
            [4, '4x', '', 0],
            [8, '8x', '', 0],
            [16, '16x', '', 0]
        ]);
        $this->RegisterVariableInteger('CONTROL', $this->Translate('Control'), 'BVIP.ReplayControl');
        $this->EnableAction('CONTROL');
        $this->SetValue('CONTROL', ($this->ReadPropertyBoolean('Autoplay') ? 1 : 0));

        if ($this->HasActiveParent()) {
            $this->IOChangeState(IS_ACTIVE);
        }
    }

    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            if ($this->ReadPropertyBoolean('Rename') === true) {
                $this->RequestName();
            }
            if ($this->ReadNbrOfVideoIn() >= $this->ReadPropertyInteger('Line')) {
                $this->SetStatus(IS_ACTIVE);
                $this->RequestState();
            } else {
                $this->SetStatus(IS_EBASE + 2);
                trigger_error($this->InstanceID . ':' . $this->Translate('Videoline not valid.'), E_USER_NOTICE);
            }
        }
    }

    public function GetConfigurationForm()
    {
        $Firmware = $this->GetFirmware();
        if ($Firmware < 5.7) {
            $Form['actions'][0] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                    'type'    => 'Label',
                    'caption' => 'Firmware is not supported.'
                        ]]
                ]
            ];
            return json_encode($Form);
        }

        $data = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Lines = $this->ReadNbrOfVideoIn();
        $Options = [];
        for ($Line = 1; $Line <= $Lines; $Line++) {
            $Options[] = ['caption' => (string) $Line, 'value' => $Line];
        }
        $data['elements'][0]['options'] = $Options;

        return json_encode($data);
    }

    public function RequestAction($Ident, $Value)
    {
        if (parent::RequestAction($Ident, $Value)) {
            return;
        }
        if ($Ident != 'CONTROL') {
            echo 'Invalid ident.';
            return;
        }
        $this->SetValue('CONTROL', $Value);
        return $this->RequestState();
    }

    public function RequestState()
    {
        $Firmware = $this->GetFirmware();
        if ($Firmware < 5.7) {
            return;
        }
        $Host = '';
        $User = '';
        $Pass = '';
        $line = $this->ReadPropertyInteger('Line');
        $stream = $this->ReadPropertyInteger('Recording');
        $ParentId = $this->ParentID;
        if ($ParentId > 0) {
            $IOId = @IPS_GetInstance($ParentId)['ConnectionID'];
            $User = IPS_GetProperty($ParentId, 'User');
            $Pass = IPS_GetProperty($ParentId, 'Password');
            if ($IOId > 0) {
                $Host = IPS_GetProperty($IOId, 'Host');
            }
        }
        if ($Host == '') {
            $Url = '';
        } else {
            if ($this->ReadPropertyBoolean('UseAuth')) {
                if ($Pass != '') {
                    $Host = $User . ':' . $Pass . '@' . $Host;
                }
            }
            $Speed = $this->GetValue('CONTROL');
            $Url = 'rtsp://' . $Host . '?rec=1&rnd=' . $this->InstanceID . '&line=' . $line . '&inst=' . $stream . '&seek=0xFFFFFFFF&speed=' . $Speed;
        }
        $mid = @$this->GetIDForIdent('STREAM');
        if ($mid == false) {
            $mid = IPS_CreateMedia(MEDIATYPE_STREAM);
            IPS_SetParent($mid, $this->InstanceID);
            IPS_SetName($mid, 'STREAM');
            IPS_SetIdent($mid, 'STREAM');
        }
        IPS_SetMediaFile($mid, $Url, false);
        $this->SendDebug('URL', $Url, 0);
    }

    public function RequestName()
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Videoline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Videoline not valid.'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAMNAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $Line;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if (IPS_GetName($this->InstanceID) != $RCPReplyData->Payload) {
                IPS_SetName($this->InstanceID, $RCPReplyData->Payload);
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID . ':' . 'Write Name Line' . $Line . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return false;
    }

    public function SetName(string $Name, string $Ident)
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Videoline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Videoline not valid.'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAMNAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $RCPData->Num = $Line;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if (IPS_GetName($this->InstanceID) != $Name) {
                IPS_SetName($this->InstanceID, $RCPReplyData->Payload);
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID . ':' . 'Write Name Line' . $Line . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return false;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
    }
}

/* @} */
