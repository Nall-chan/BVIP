<?php

declare(strict_types=1);

require_once __DIR__ . '/BVIPTraits.php';  // diverse Klassen
/**
 */

abstract class BVIPBase extends IPSModule
{

    use bvip\VariableProfile,
        bvip\VariableHelper,
        bvip\DebugHelper,
        bvip\BufferHelper,
        bvip\InstanceStatus,
        bvip\Semaphore,
        bvip\UTF8Coder {
        bvip\InstanceStatus::MessageSink as IOMessageSink; // MessageSink gibt es sowohl hier in der Klasse, als auch im Trait InstanceStatus. Hier wird für die Methode im Trait ein Alias benannt.
        bvip\InstanceStatus::RegisterParent as IORegisterParent;
        bvip\InstanceStatus::RequestAction as IORequestAction;
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

        $this->RegisterMessage(0, IPS_KERNELSTARTED);
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

        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
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
        $Config = json_decode(IPS_GetConfiguration($this->InstanceID), true);
        $Line = (array_key_exists('Line', $Config)) ? '@' . $Config['Line'] : '';
        $SplitterId = $this->ParentID;
        if ($SplitterId > 0) {
            $IOId = @IPS_GetInstance($SplitterId)['ConnectionID'];
            if ($IOId > 0) {
                $this->SetSummary(IPS_GetProperty($IOId, 'Host') . $Line);
                return;
            }
        }
        $this->SetSummary(('none' . $Line));
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return true;
        }
        return false;
    }

    abstract protected function RequestState();
    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->RegisterParent();
        /* if ($this->HasActiveParent()) {
          $this->IOChangeState(IS_ACTIVE);
          } */
    }

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
            $this->EncodeUTF8($RCPData);
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
            trigger_error($this->InstanceID . ':' . $this->Translate($exc->getMessage()), E_USER_NOTICE);
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
        $this->DecodeUTF8($RCPData);
        $this->SendDebug('Event', $RCPData, 0);
        $this->DecodeRCPEvent($RCPData);
    }

    abstract protected function DecodeRCPEvent(RCPData $RCPData);
    protected function GetFirmware()
    {
        if ($this->HasActiveParent()) {
            $Data = json_encode(['DataID' => RCPData::IIPSSendBVIPData, 'Method' => 'ReadFirmware']);
            $anwser = $this->SendDataToParent($Data);

            if ($anwser === false) {
                $this->SendDebug('ReadFirmware', 'No valid answer', 0);

                throw new Exception($this->Translate('No valid answer.'), E_USER_NOTICE);
            }
            return (float) substr(unserialize($anwser), 0, 5);
        }


        return 0;
    }

    protected function GetCapability()
    {
        if ($this->HasActiveParent()) {
            $Data = json_encode(['DataID' => RCPData::IIPSSendBVIPData, 'Method' => 'ReadCapability']);
            $anwser = $this->SendDataToParent($Data);

            if ($anwser === false) {
                $this->SendDebug('GetCapability', 'No valid answer', 0);

                throw new Exception($this->Translate('No valid answer.'), E_USER_NOTICE);
            }
            return unserialize($anwser);
        }
        return ['Video' => ['Encoder' => [], 'Decoder' => [], 'Transcoder' => []], 'SerialPorts' => 0, 'IO' => ['Input' => 0, 'Output' => 0, 'Virtual' => 0]];
    }

    protected function ReadNbrOfVideoIn()
    {
        $VideoIn = count($this->GetCapability()['Video']['Encoder']);
        if ($VideoIn == 0) {
            return 16;
        }
        return $VideoIn;
    }

    protected function GetNbrOfSerialPorts()
    {
        return $this->GetCapability()['SerialPorts'];
    }

    protected function GetNbrOfInputs()
    {
        return $this->GetCapability()['IO']['Input'];
    }

    protected function GetNbrOfOutputs()
    {
        return $this->GetCapability()['IO']['Output'];
    }

    protected function GetNbrOfVirtualAlarms()
    {
        return $this->GetCapability()['IO']['Virtual'];
    }

}
