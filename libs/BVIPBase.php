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

    protected function GetCapability()
    {
        if (!$this->HasActiveParent()) {
            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAPABILITY_LIST;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        /* @var $RCPReplyData RCPData */
        $RCPReplyData = @$this->Send($RCPData);
        $Capas = [];
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $i = 0;
            $pointer = 6;
            $NbrOfSections = unpack('n', substr($RCPReplyData->Payload, 4, 2))[1];
            $Capas = ['Video' => ['Encoder' => [], 'Decoder' => [], 'Transcoder' => []], 'SerialPorts' => 0, 'IO' => ['Input' => 0, 'Output' => 0, 'Virtual' => 0]];
            for ($Section = 1; $Section <= $NbrOfSections; $Section++) {
                $len = unpack('n', substr($RCPReplyData->Payload, $pointer + 2, 2))[1];
                $SectionTyp = unpack('n', substr($RCPReplyData->Payload, $pointer, 2))[1];
                $NbrOfSectionElements = unpack('n', substr($RCPReplyData->Payload, $pointer + 4, 2))[1];
                $FullSectionData = substr($RCPReplyData->Payload, $pointer + 6, $len - 6);
                switch ($SectionTyp) {
                    case 0x0001: // Video - 10
                        $ElementsData = str_split($FullSectionData, 10);
                        $Video = [];
                        for ($i = 1; $i <= $NbrOfSectionElements; $i++) {
                            /*
                              Type        2 Bytes
                              Identifier  2 Bytes
                              Compression 2 Bytes
                              InputNo     2 Bytes
                              Resolution  2 Bytes
                             */
                            $ElementData = str_split($ElementsData[$i - 1], 2);
                            $Video[unpack('n', $ElementData[3])[1]][] = [
                                'Compression' => unpack('n', $ElementData[2])[1],
                                'Resolution'  => unpack('n', $ElementData[4])[1]
                            ];
                            $Typ = unpack('n', $ElementData[0])[1];
                            switch ($Typ) {
                                case 0x0001:
                                    $Capas['Video']['Encoder'] = $Video;
                                    break;
                                case 0x0002:
                                    $Capas['Video']['Decoder'] = $Video;
                                    break;
                                case 0x0003:
                                    $Capas['Video']['Transcoder'] = $Video;
                                    break;
                            }
                        }

                        break;
                    case 0x0003: // Serial - 4
                        $Capas['SerialPorts'] = $NbrOfSectionElements;
                        break;
                    case 0x0004: // IO - 4
                        $ElementsData = str_split($FullSectionData, 4);
                        $IOs = [1 => 0, 2 => 0, 3 => 0];
                        for ($i = 1; $i <= $NbrOfSectionElements; $i++) {
                            /*
                              Type        2 Bytes
                              Identifier  2 Bytes
                             */
                            $ElementData = str_split($ElementsData[$i - 1], 2);
                            $IOs[unpack('n', $ElementData[0])[1]] ++;
                        }
                        $Capas['IO'] = ['Input' => $IOs[1], 'Output' => $IOs[2], 'Virtual' => $IOs[3]];
                        break;
                }
                $pointer = $pointer + $len;
            }

            return $Capas;
        }
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

    protected function GetNbrOfSerialPorts()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAPABILITY_LIST;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        /* @var $RCPReplyData RCPData */
        $RCPReplyData = @$this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            $i = 0;
            $pointer = 6;
            $NbrSection = unpack('n', substr($RCPReplyData->Payload, 4, 2))[1];
            for ($Section = 1; $Section <= $NbrSection; $Section++) {
                $len = unpack('n', substr($RCPReplyData->Payload, $pointer + 2, 2))[1];
                if (ord($RCPReplyData->Payload[$pointer + 1]) == 0x03) {
                    return unpack('n', substr($RCPReplyData->Payload, $pointer + 4, 2))[1];
                }
                $pointer = $pointer + $len;
            }

            return 0;
        }

        return 0;
    }

    protected function GetNbrOfVirtualAlarms()
    {
        if ($this->GetFirmware() > 4) {
            $RCPData = new RCPData();
            $RCPData->Tag = RCPTag::TAG_NBR_OF_VIRTUAL_ALARMS;
            $RCPData->DataType = RCPDataType::RCP_T_DWORD;
            $RCPData->RW = RCPReadWrite::RCP_DO_READ;
            /* @var $RCPReplyData RCPData */
            $RCPReplyData = $this->Send($RCPData);
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                return $RCPReplyData->Payload;
            }
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            }
            return false;
        } else {
            $RCPData = new RCPData();
            $RCPData->Tag = RCPTag::TAG_CAPABILITY_LIST;
            $RCPData->DataType = RCPDataType::RCP_P_OCTET;
            $RCPData->RW = RCPReadWrite::RCP_DO_READ;
            /* @var $RCPReplyData RCPData */
            $RCPReplyData = $this->Send($RCPData);
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $i = 0;
                $pointer = 6;
                $NbrSection = unpack('n', substr($RCPReplyData->Payload, 4, 2))[1];
                for ($Section = 1; $Section <= $NbrSection; $Section++) {
                    $len = unpack('n', substr($RCPReplyData->Payload, $pointer + 2, 2))[1];
                    if (ord($RCPReplyData->Payload[$pointer + 1]) == 0x04) {
                        $NbrElement = unpack('n', substr($RCPReplyData->Payload, $pointer + 4, 2))[1];
                        for ($Element = 1; $Element <= $$NbrElement; $Element++) {
                            if (ord($RCPReplyData->Payload[$pointer + 3 + ($Element * 4)]) == 0x03) {
                                if (ord($RCPReplyData->Payload[$pointer + 5 + ($Element * 4)]) > $i) {
                                    $i = ord($ReplyData->Payload[$pointer + 5 + ($Element * 4)]);
                                }
                            }
                        }
                    }
                    $pointer = $pointer + $len;
                }
                return $i;
            }
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
            }

            return false;
        }
    }
}
