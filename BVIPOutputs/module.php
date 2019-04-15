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
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 *
 */

/**
 * BVIPOutputs Klasse implementiert eine Device für Ausgänge.
 * Erweitert BVIPBase.
 *
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 *
 * @version       1.0
 *
 * @example <b>Ohne</b>
 */
class BVIPOutputs extends BVIPBase
{
    protected static $RCPTags = [RCPTag::TAG_RELAY_OUTPUT_STATE];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('Rename', true);
        $this->RegisterPropertyInteger('Number', 0);
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
            $this->RequestState();
        }
    }

    public function Scan()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_NBR_OF_ALARM_OUT;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        /* @var $RCPReplyData RCPData */
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if ($this->ReadPropertyInteger('Number') != $RCPReplyData->Payload) {
                IPS_SetProperty($this->InstanceID, 'Number', $RCPReplyData->Payload);
            }
            @IPS_ApplyChanges($this->InstanceID);

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    public function WriteBoolean(string $Ident, bool $Value)
    {
        $VarId = @$this->GetIDForIdent($Ident);
        if ($VarId == 0) {
            trigger_error($this->Translate('IDENT invalid.'), E_USER_NOTICE);

            return false;
        }
        if (!is_bool($Value)) {
            trigger_error(sprintf($this->Translate('%s must be bool.'), 'Value'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_RELAY_OUTPUT_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $result = [];
        preg_match('/\d+/', $Ident, $result);
        $RCPData->Num = (int) $result[0];
        $RCPData->Payload = $Value;

        /* @var $RCPReplyData RCPData */
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if ($RCPReplyData->Payload != $Value) {
                trigger_error($this->Translate('Error write RelayState.'), E_USER_NOTICE);

                return false;
            }
            $this->SetValueBoolean($Ident, $Value);

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    public function RequestAction($Ident, $Value)
    {
        if (parent::RequestAction($Ident, $Value)) {
            return;
        }

        return $this->WriteBoolean($Ident, (bool) $Value);
    }

    public function RequestState()
    {
        $Result = true;
        $Nbr = $this->ReadPropertyInteger('Number');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_RELAY_OUTPUT_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $Nbr; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $this->DecodeRCPEvent($RCPReplyData);
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error('RELAY_' . $index . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            }
        }

        return $Result;
    }

    public function RequestName()
    {
        $Result = true;

        $Nbr = $this->ReadPropertyInteger('Number');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_RELAIS_NAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $Nbr; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $vid = $this->GetOrCreateVariable('RELAY_' . $index);
                if (IPS_GetName($vid) != $RCPReplyData->Payload) {
                    IPS_SetName($vid, $RCPReplyData->Payload);
                }
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error('Read Name RELAY_' . $index . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            }
        }

        return $Result;
    }

    public function SetName(string $Name, string $Ident)
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_RELAIS_NAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $result = [];
        preg_match('/\d+/', $Ident, $result);
        $RCPData->Num = (int) $result[0];
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $vid = $this->GetOrCreateVariable($Ident);
            if (IPS_GetName($vid) != $Name) {
                IPS_SetName($vid, $RCPReplyData->Payload);
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error('Write Name ' . $Ident . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    protected function GetOrCreateVariable(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableBoolean($Ident, $Ident, '~Switch');
            $this->EnableAction($Ident);
        }

        return $vid;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        $this->GetOrCreateVariable('RELAY_' . $RCPData->Num);
        $this->SetValueBoolean('RELAY_' . $RCPData->Num, $RCPData->Payload);

        if ($this->ReadPropertyInteger('Number') < $RCPData->Num) {
            IPS_SetProperty($this->InstanceID, 'Number', $RCPData->Num);
            IPS_ApplyChanges($this->InstanceID);
        }
    }

}

/* @} */
