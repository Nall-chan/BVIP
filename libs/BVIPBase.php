<?php

require_once __DIR__ . '/BVIPTraits.php';  // diverse Klassen

abstract class BVIPBase extends IPSModule
{
    use VariableProfile,
        VariableHelper,
        DebugHelper,
        BufferHelper,
        InstanceStatus,
        Semaphore,
        UTF8Coder {
        InstanceStatus::MessageSink as IOMessageSink; // MessageSink gibt es sowohl hier in der Klasse, als auch im Trait InstanceStatus. Hier wird für die Methode im Trait ein Alias benannt.
        InstanceStatus::RegisterParent as IORegisterParent;
    }
    protected static $RCPTags;

    public function Create()
    {
        parent::Create();
        $this->ParentID = 0;
        //$this->ConnectParent('{58E3A4FB-61F2-4C30-8563-859722F6522D}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        if (count(static::$RCPTags) > 0) {
            foreach (static::$RCPTags as $RCPTag) {
                $Lines[] = '.*"Tag":' . $RCPTag . '.*';
            }
            $Line = implode('|', $Lines);
            $this->SetReceiveDataFilter('(' . $Line . ')');
            $this->SendDebug('FILTER', $Line, 0);
        } else {
            $this->SetReceiveDataFilter('.*"Tag":"NOTING".*');
            $this->SendDebug('FILTER', 'NOTHING', 0);
        }

//        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterParent();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);

//        switch ($Message)
//        {
//            case IPS_KERNELSTARTED:
//                $this->KernelReady();
//                break;
//        }
    }

    protected function RegisterParent()
    {
        $SplitterId = $this->IORegisterParent();
        if ($SplitterId > 0) {
            $IOId = @IPS_GetInstance($SplitterId)['ConnectionID'];
            if ($IOId > 0) {
                $this->SetSummary(IPS_GetProperty($IOId, 'Host'));

                return;
            }
        }
        $this->SetSummary(('none'));
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     */
    protected function IOChangeState($State)
    {
        $SplitterId = $this->ParentID;
        if ($SplitterId > 0) {
            $IOId = @IPS_GetInstance($SplitterId)['ConnectionID'];
            if ($IOId > 0) {
                $this->SetSummary(IPS_GetProperty($IOId, 'Host'));

                return;
            }
        }
        $this->SetSummary(('none'));
    }

    abstract protected function RequestState();

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
//    protected function KernelReady()
//    {
//        $this->RegisterParent();
//        if ($this->HasActiveParent())
//            $this->IOChangeState(IS_ACTIVE);
//    }

    /*    public function ReceiveData($JSONString)
      {
      //We dont need any Data...here
      } */
    /**
     * @param RCPData $RCPData
     */
    protected function Send(RCPData $RCPData)
    {
        try {
            if (!$this->HasActiveParent()) {
                throw new Exception($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            }
            $this->SendDebug('Send', '~~~', 0);
            $this->SendDebug('Send', $RCPData, 0);
            $RCPData = $this->EncodeUTF8($RCPData);
            $RCPData->DataID = RCPData::IIPSSendBVIPData;
            $anwser = $this->SendDataToParent(json_encode($RCPData));
            if ($anwser === false) {
                $this->SendDebug('Response', 'No valid answer', 0);

                throw new Exception($this->Translate('No valid answer.'), E_USER_NOTICE);
            }
            $RCPData = unserialize($anwser);
            $this->SendDebug('RAW', $anwser, 0);
            $this->SendDebug('Response', $RCPData, 0);
        } catch (Exception $exc) {
            trigger_error($exc->getMessage(), E_USER_NOTICE);
            $RCPData->Error = RCPError::RCP_ERROR_SEND_ERROR;
        }

        return $RCPData;
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('Event', '~~~~', 0);
        $this->SendDebug('Event', $JSONString, 0);
        $RCPData = new RCPData();
        $RCPData->FromJSONString($JSONString);
        $RCPData = $this->DecodeUTF8($RCPData);

        $this->SendDebug('Event', $RCPData, 0);
        $this->DecodeRCPEvent($RCPData);
    }

    abstract protected function DecodeRCPEvent(RCPData $RCPData);
    protected function GetFirmware()
    {
        if ($this->ParentID > 0) {
            $vid = @IPS_GetObjectIDByIdent('Firmware', $this->ParentID);
            if ($vid > 0) {
                return (float) substr(GetValueString($vid), 0, 5);
            }
        }

        return false;
    }

    protected function ReadNbrOfVideoIn()
    {
        if (!$this->HasActiveParent()) {
            return 16;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_NBR_OF_VIDEO_IN;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        $RCPReplyData = @$this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            return $RCPReplyData->Payload;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            return 16;
        }
    }
}
