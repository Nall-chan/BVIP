<?

require_once(__DIR__ . "/../libs/BVIPBase.php");

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
 * BVIPVirtualInputs Klasse implementiert eine Device für Virtuelle Eingänge.
 * Erweitert BVIPBase.
 * 
 * @package       BVIP
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */
class BVIPVirtualInputs extends BVIPBase
{

    static protected $RCPTags = array(RCPTag::TAG_VIRTUAL_ALARM_STATE);

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger("Number", 0);
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();

        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;

        if ($this->HasActiveParent())
            $this->IOChangeState(IS_ACTIVE);
    }

    protected function KernelReady()
    {
        parent::KernelReady();
    }

    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE)
            $this->RequestState();
    }

    public function Scan()
    {
        if ($this->GetFirmware() > 4)
        {
            $RCPData = new RCPData();
            $RCPData->Tag = RCPTag::TAG_NBR_OF_VIRTUAL_ALARMS;
            $RCPData->DataType = RCPDataType::RCP_T_DWORD;
            $RCPData->RW = RCPReadWrite::RCP_DO_READ;
            /* @var $RCPReplyData RCPData */
            $RCPReplyData = $this->Send($RCPData);
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
            {
                if ($this->ReadPropertyInteger('Number') <> $RCPReplyData->Payload)
                {
                    IPS_SetProperty($this->InstanceID, 'Number', $RCPReplyData->Payload);
                    IPS_ApplyChanges($this->InstanceID);
                }
                return true;
            }
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR)
                trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            return false;
        }
        else
        {
            $RCPData = new RCPData();
            $RCPData->Tag = RCPTag::TAG_CAPABILITY_LIST;
            $RCPData->DataType = RCPDataType::RCP_P_OCTET;
            $RCPData->RW = RCPReadWrite::RCP_DO_READ;
            /* @var $RCPReplyData RCPData */
            $RCPReplyData = $this->Send($RCPData);
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
            {
                $i = 0;
                $pointer = 6;
                $NbrSection = unpack('n', substr($RCPReplyData->Payload, 4, 2))[1];
                for ($Section = 1; $Section <= $NbrSection; $Section++)
                {
                    $len = unpack('n', substr($RCPReplyData->Payload, $pointer + 2, 2))[1];
                    if (ord($RCPReplyData->Payload[$pointer + 1]) == 0x04)
                    {
                        $NbrElement = unpack('n', substr($RCPReplyData->Payload, $pointer + 4, 2))[1];
                        for ($Element = 1; $Element <= $$NbrElement; $Element++)
                        {
                            if (ord($RCPReplyData->Payload[$pointer + 3 + ($Element * 4)]) == 0x03)
                            {
                                if (ord($RCPReplyData->Payload[$pointer + 5 + ($Element * 4)]) > $i)
                                    $i = ord($ReplyData->Payload[$pointer + 5 + ($Element * 4)]);
                            }
                        }
                    }
                    $pointer = $pointer + $len;
                }
                IPS_SetProperty($this->InstanceID, 'Number', $i);
                IPS_ApplyChanges($this->InstanceID);
                return true;
            }
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR)
                trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            return false;
        }
    }

    public function WriteBoolean(string $Ident, bool $Value)
    {
        $VarId = @$this->GetIDForIdent($Ident);
        if ($VarId == 0)
        {
            trigger_error($this->Translate('IDENT invalid.'), E_USER_NOTICE);
            return false;
        }
        if (!is_bool($Value))
        {
            trigger_error(sprintf($this->Translate('Value must be %s.'), 'bool'), E_USER_NOTICE);
            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_VIRTUAL_ALARM_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $result = array();
        preg_match('/\d+/', $Ident, $result);
        $RCPData->Num = (int) $result[0];
        $RCPData->Payload = $Value;

        /* @var $RCPReplyData RCPData */
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
        {
            if ($RCPReplyData->Payload != $Value)
            {
                trigger_error($this->Translate('Error write VirtualAlarmState.'), E_USER_NOTICE);
                return false;
            }
            $this->SetValueBoolean($Ident, $Value);
            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR)
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        return false;
    }

    public function WriteString(string $Ident, string $Value)
    {
        $VarId = @$this->GetIDForIdent($Ident);
        if ($VarId == 0)
        {
            trigger_error($this->Translate('IDENT invalid.'), E_USER_NOTICE);
            return false;
        }
        if (!is_string($Value))
        {
            trigger_error(sprintf($this->Translate('Value must be %s.'), 'string'), E_USER_NOTICE);
            return false;
        }
        if (strlen($Value) > 32)
        {
            trigger_error($this->Translate('VirtualAlarm-String max size is 32 char.'), E_USER_NOTICE);
            return false;
        }
        if ($this->GetFirmware() < 5)
        {
            trigger_error($this->Translate('Device-Firmware must be 5.0 or higher.'), E_USER_NOTICE);
            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_SET_VIRTUAL_ALARM_ID;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $result = array();
        preg_match('/\d+/', $Ident, $result);
        $RCPData->Num = (int) $result[0];
        $RCPData->Payload = $Value;

        /* @var $RCPReplyData RCPData */
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
            return true;
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR)
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        return false;
    }

    public function RequestAction($Ident, $Value)
    {
        return $this->WriteBoolean($Ident, (bool) $Value);
    }

    public function RequestState()
    {
        $Result = true;

        $Nbr = $this->ReadPropertyInteger('Number');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_VIRTUAL_ALARM_STATE;
        $RCPData->DataType = RCPDataType::RCP_F_FLAG;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $Nbr; $index++)
        {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
            {
                $this->DecodeRCPEvent($RCPReplyData);
                continue;
            }
            else
                $Result = false;

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR)
                trigger_error('VIRTUAL_' . $index . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }
        return $Result;
    }

    protected function GetOrCreateVariable(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false)
        {
            $vid = $this->RegisterVariableBoolean($Ident, $Ident, '~Alert');
            $this->EnableAction($Ident);
        }
        return $vid;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        $this->GetOrCreateVariable('VIRTUAL_' . $RCPData->Num);
        $this->SetValueBoolean('VIRTUAL_' . $RCPData->Num, $RCPData->Payload);

        if ($this->ReadPropertyInteger('Number') < $RCPData->Num)
        {
            IPS_SetProperty($this->InstanceID, 'Number', $RCPData->Num);
            IPS_ApplyChanges($this->InstanceID);
        }
    }

}

/** @} */