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
 * @copyright     2019 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.0
 *
 */

/**
 * BVIPCamEvents Klasse implementiert eine Device für Videoloss und Motion.
 * Erweitert BVIPBase.
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2019 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       3.0
 *
 * @example <b>Ohne</b>
 */
class BVIPCamEvents extends BVIPBase
{
    protected static $RCPTags = [RCPTag::TAG_VIDEO_ALARM_STATE, RCPTag::TAG_MOTION_ALARM_STATE];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('Rename', true);
        $this->RegisterPropertyInteger('Line', 1);
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
            } else {
                $this->SetStatus(IS_EBASE + 2);
                trigger_error($this->InstanceID . ':' . $this->Translate('Cameraline not valid.'), E_USER_NOTICE);
            }
        }
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Lines = $this->ReadNbrOfVideoIn();
        $Options = [];
        for ($Line = 1; $Line <= $Lines; $Line++) {
            $Options[] = ['caption' => (string) $Line, 'value' => $Line];
        }
        $data['elements'][0]['options'] = $Options;
        return json_encode($data);
    }

    public function RequestState()
    {
        $Result = true;
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_VIDEO_ALARM_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $this->ReadPropertyInteger('Line');
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $this->DecodeRCPEvent($RCPReplyData);
        } else {
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error($this->InstanceID . ':' . 'VIDEOLOSS - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_MOTION_ALARM_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $this->ReadPropertyInteger('Line');
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $this->DecodeRCPEvent($RCPReplyData);
        } else {
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error($this->InstanceID . ':' . 'MOTION_SUMMARY - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }

        return $Result;
    }

    public function RequestName()
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Cameraline not valid.'), E_USER_NOTICE);

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
            trigger_error($this->InstanceID . ':' . $this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->InstanceID . ':' . $this->Translate('Cameraline not valid.'), E_USER_NOTICE);

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

    protected function GetOrCreateVariable(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableBoolean($Ident, $this->Translate($Ident), '~Alert');
        }

        return $vid;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        if ($RCPData->Num != $this->ReadPropertyInteger('Line')) {
            return;
        }

        if ($RCPData->Tag == RCPTag::TAG_VIDEO_ALARM_STATE) {
            $this->GetOrCreateVariable('VIDEOLOSS');
            $this->SetValueBoolean('VIDEOLOSS', $RCPData->Payload);
        }
        if ($RCPData->Tag == RCPTag::TAG_MOTION_ALARM_STATE) {
            $this->GetOrCreateVariable('MOTION_SUMMARY');
            $this->SetValueBoolean('MOTION_SUMMARY', $RCPData->Payload);
        }
    }
}

/* @} */
