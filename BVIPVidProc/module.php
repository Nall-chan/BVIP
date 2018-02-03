<?php

require_once __DIR__.'/../libs/BVIPBase.php';

/*
 * @addtogroup bvip
 * @{
 *
 * @package       BVIP
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 *
 */

/**
 * BVIPViProc Klasse implementiert eine Device für VCA.
 * Erweitert BVIPBase.
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       1.0
 *
 * @example <b>Ohne</b>
 *
 * @todo VCA Alarme und Task Namen
 */
class BVIPVidProc extends BVIPBase
{
    protected static $RCPTags = [RCPTag::TAG_VIPROC_ALARM];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('Rename', false);
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

    protected function KernelReady()
    {
        parent::KernelReady();
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
                trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);
            }
        }
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__.'/form.json'), true);
        $Lines = $this->ReadNbrOfVideoIn();
        $Options = [];
        for ($Line = 1; $Line <= $Lines; $Line++) {
            $Options[] = ['label' => (string) $Line, 'value' => $Line];
        }
        $data['elements'][0]['options'] = $Options;

        return json_encode($data);
    }

    public function RequestState()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_VIPROC_ALARM;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $this->ReadPropertyInteger('Line');
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $this->DecodeRCPEvent($RCPReplyData);

            return true;
        } else {
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error('VIPROC_ALARM - '.RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            }
        }

        return false;
    }

    public function RequestVcaName()
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_VIPROC_RE_TASK_NAMES;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $Line;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            // TODO
            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error('Write Name Line'.$Line.' - '.RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    public function RequestName()
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

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
            trigger_error('Write Name Line'.$Line.' - '.RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
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

        $this->GetOrCreateVariable('MOTION');
        $this->SetValueBoolean('MOTION', ord($RCPData->Payload[0]) & 0b10000000);
        $this->GetOrCreateVariable('GLOBAL_CHANGE');
        $this->SetValueBoolean('GLOBAL_CHANGE', ord($RCPData->Payload[0]) & 0b01000000);

        $this->GetOrCreateVariable('SIGNAL_TOO_BRIGHT');
        $this->SetValueBoolean('SIGNAL_TOO_BRIGHT', ord($RCPData->Payload[0]) & 0b00100000);
        $this->GetOrCreateVariable('SIGNAL_TOO_DARK');
        $this->SetValueBoolean('SIGNAL_TOO_DARK', ord($RCPData->Payload[0]) & 0b00010000);

        $this->GetOrCreateVariable('SIGNAL_TOO_NOISY');
        $this->SetValueBoolean('SIGNAL_TOO_NOISY', ord($RCPData->Payload[0]) & 0b00001000);
        $this->GetOrCreateVariable('SIGNAL_TOO_BLURRY');
        $this->SetValueBoolean('SIGNAL_TOO_BLURRY', ord($RCPData->Payload[0]) & 0b00000100);

        $this->GetOrCreateVariable('SIGNAL_LOSS');
        $this->SetValueBoolean('SIGNAL_LOSS', ord($RCPData->Payload[0]) & 0b00000010);
        $this->GetOrCreateVariable('REFERENCE_IMAGE_FAILED');
        $this->SetValueBoolean('REFERENCE_IMAGE_FAILED', ord($RCPData->Payload[0]) & 0b00000001);

        $this->GetOrCreateVariable('INVALID_CONFIGURATION');
        $this->SetValueBoolean('INVALID_CONFIGURATION', ord($RCPData->Payload[1]) & 0b10000000);
    }
}

/* @} */
