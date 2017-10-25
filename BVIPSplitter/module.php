<?

require_once(__DIR__ . "/../libs/BVIPTraits.php");  // diverse Klassen

/*
 * @addtogroup bvip
 * @{
 *
 * @package       BVIP
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */

/**
 * BVIPSplitter Klasse für die Kommunikation mit dem BVIP Device.
 * Erweitert IPSModule.
 *
 * @package       BVIP
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2017 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 * @example <b>Ohne</b>
 * @property array $ReplyRCPData Enthält die versendeten Befehle und buffert die Antworten.
 * @property string $Buffer Empfangsbuffer
 * @property string $Host Adresse des BVIP (aus IO-Parent ausgelesen)
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @property int $FrameID
 * @property string $ClientID
 * @property string $SessionID
 */
class BVIPSplitter extends IPSModule
{

    use VariableProfile,
        DebugHelper,
        BufferHelper,
        InstanceStatus,
        Semaphore,
        UTF8Coder
    {
        InstanceStatus::MessageSink as IOMessageSink; // MessageSink gibt es sowohl hier in der Klasse, als auch im Trait InstanceStatus. Hier wird für die Methode im Trait ein Alias benannt.
        InstanceStatus::RegisterParent as IORegisterParent;
    }

    const RCPTags = array(
        RCPTag::TAG_CLIENT_TIMEOUT_WARNING,
        RCPTag::TAG_CLIENT_UNREGISTER,
        RCPTag::TAG_CLIENT_REGISTRATION,
        RCPTag::TAG_VIPROC_ALARM, // OK
        RCPTag::TAG_VIDEO_ALARM_STATE, // OK
        RCPTag::TAG_INPUT_PIN_STATE, // OK
        RCPTag::TAG_MOTION_ALARM_STATE, // OK
        RCPTag::TAG_RELAY_OUTPUT_STATE, // OK
        RCPTag::TAG_VIRTUAL_ALARM_STATE, // OK
        RCPTag::TAG_MANIPULATION_ALARM_STATE,
        RCPTag::TAG_TRANSFER_TRANSPARENT_DATA, // OK
        RCPTag::TAG_ETH_LINK_STATUS
    );

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
        $this->RegisterPropertyString("User", 'service');
        $this->RegisterPropertyString("Password", '');
        $this->RegisterTimer('KeepAlive', 0, 'BVIP_KeepAlive($_IPS["TARGET"]);');
        $this->ReplyRCPData = array();
        $this->Buffer = "";
        $this->Host = "";
        $this->ParentID = 0;
        $this->FrameID = 0;
        $this->ClientID = random_bytes(2);
        $this->SessionID = random_bytes(4);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterMessage($this->InstanceID, DM_CONNECT);
        $this->RegisterMessage($this->InstanceID, DM_DISCONNECT);
        $this->ReplyRCPData = array();
        $this->Buffer = "";
        $this->Host = "";
        $this->ParentID = 0;

        parent::ApplyChanges();

        // Wenn Kernel nicht bereit, dann warten... KR_READY kommt ja gleich
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;

        // Config prüfen
        $this->RegisterParent();

        // Wenn Parent aktiv, dann Anmeldung an der Hardware bzw. Datenabgleich starten
        if ($this->ParentID > 0)
            IPS_ApplyChanges($this->ParentID);
        //$this->IOChangeState(IS_ACTIVE);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);

        switch ($Message)
        {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->RegisterParent();
        if ($this->HasActiveParent())
            $this->IOChangeState(IS_ACTIVE);
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        if ($this->ParentID > 0)
        {
            $this->Host = IPS_GetProperty($this->ParentID, 'Host');
            $this->SetSummary($this->Host);
        }
        else
        {
            $this->Host = "";
            $this->SetSummary('none');
        }

        if ($State == IS_ACTIVE)
        {
            $this->SetStatus(IS_INACTIVE);
            $this->FrameID = 0;
            $this->ClientID = random_bytes(2);
            $this->SessionID = random_bytes(4);
            if ($this->StartConnect() !== true)
            {
                $this->SetStatus(IS_EBASE + 2);
                echo $this->Translate('Could not login.');
                return;
            }
            $this->GetRCPCapabilities();
            $this->SetStatus(IS_ACTIVE);
        }
        else
        {
            $this->SetStatus(IS_INACTIVE); // Setzen wir uns auf inactive, weil wir vorher eventuell im Fehlerzustand waren.
        }
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function GetConfigurationForParent()
    {
        $Config['Port'] = 1756;
        return json_encode($Config);
    }

    public function GetRCPCapabilities()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CLUSTER_ID;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
        {
            if ($RCPReplyData->Payload > 0)
            {
                $vid = @$this->GetIDForIdent('Modul');
                if ($vid == false)
                    $vid = $this->RegisterVariableInteger('Modul', 'Modul');
                SetValueInteger($vid, $RCPReplyData->Payload);
            }
        }
        else
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);

        $RCPData->Tag = RCPTag::TAG_SOFTWARE_VERSION;
        $RCPData->DataType = RCPDataType::RCP_P_STRING;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR)
        {
            $vid = @$this->GetIDForIdent('Firmware');
            if ($vid == false)
                $vid = $this->RegisterVariableString('Firmware', 'Firmware');
            SetValueString($vid, substr($RCPReplyData->Payload, 4, 2) . '.' . substr($RCPReplyData->Payload, 6, 2) . '.' . substr($RCPReplyData->Payload, 0, 4));
        }
        else
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
    }

    public function KeepAlive()
    {
        $this->SetTimerInterval('KeepAlive', 0);
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CLIENT_REGISTRATION;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)
            trigger_error(RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
    }

################## DATAPOINTS DEVICE

    /**
     * Interne Funktion des SDK. Nimmt Daten von Childs entgegen und sendet Diese weiter.
     *
     * @access public
     * @param string $JSONString Ein RCPData-Objekt welches als JSONString kodiert ist.
     * @result RCPData|bool 
     */
    public function ForwardData($JSONString)
    {
        $RCPData = new RCPData();
        $RCPData->FromJSONString($JSONString);
        $ret = $this->Send($this->DecodeUTF8($RCPData));
        if (!is_null($ret))
            return serialize($ret);

        return false;
    }

    /**
     * Sendet RCPData an die Childs.
     *
     * @access private
     * @param RCPData $RCPData Ein RCPData-Objekt.
     */
    private function SendDataToDevice(RCPData $RCPData)
    {
        $Data = json_encode($this->EncodeUTF8($RCPData));
        //$this->SendDebug('IPS_SendDataToChildren', $Data, 0);
        $this->SendDataToChildren($Data);
    }

################## DATAPOINTS PARENT

    /**
     * Empfängt Daten vom Parent.
     *
     * @access public
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @result bool True wenn Daten verarbeitet wurden, sonst false.
     */
    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);

        // Datenstream zusammenfügen
        $Head = $this->Buffer;
        $Data = utf8_decode($data->Buffer);
        if (($Head == '' ) and ( $Data[0] != chr(0x03))) // Müll
            return;
        $Data = $Head . $Data;
        while (true)
        {
            if ($Data[0] != chr(0x03)) // Müll
            {
                $this->Buffer = '';
                return;
            }

            $len = (ord($Data[2]) << 8) | (ord($Data[3]));
            //$this->SendDebug('Receive len', $len, 0);
            if (strlen($Data) < $len)
            {
                $this->Buffer = $Data;
                return;
            }

            // Stream in einzelne Pakete schneiden
            $Packet = substr($Data, 4, $len - 4);
            // Rest vom Stream wieder in den Empfangsbuffer schieben
            $Data = substr($Data, $len);

            // Paket verarbeiten
            //$this->SendDebug('Receive', $Packet, 1);
            $RCPFrame = new RCPFrame($Packet);
            $this->SendDebug('Receive', $RCPFrame, 0);
            $RCPData = new RCPData();
            $RCPData->FromRCPFrame($RCPFrame);
            $this->SendDebug(RCPAction::ToString($RCPFrame->Action), $RCPData, 0);

            switch ($RCPFrame->Action)
            {
                case RCPAction::RCP_Message:
                    if ($RCPFrame->Tag == RCPTag::TAG_CLIENT_TIMEOUT_WARNING)
                    {
                        $this->SetTimerInterval('KeepAlive', 2);
                        break;
                    }
                    $this->SendDataToDevice($RCPData);
                    break;
                case RCPAction::RCP_Error:
                case RCPAction::RCP_Reply:
                    $this->SendQueueUpdate($RCPFrame->Reserved, $RCPData);
                    break;
                case RCPAction::RCP_Request:
                    // FEHLER ?!
                    break;
            }
            if (strlen($Data) == 0)
            {
                $this->Buffer = '';
                break;
            }
        }
    }

    /**
     * Versendet ein RCPData-Objekt und empfängt die Antwort.
     *
     * @access protected
     * @param RCPData $RCPData Das Objekt welches versendet werden soll.
     * @return RCPData Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    protected function Send(RCPData $RCPData)
    {
        if (!$this->HasActiveParent())
        {
            $ReplyRCPData = $RCPData;
            $ReplyRCPData->Error = RCPError::RCP_ERROR_CANNOT_SEND;
        }
        else
        {
            $this->SendDebug('Send', $RCPData, 0);
            $RCPFrame = new RCPFrame($RCPData);
            $RCPFrame->ClientID = $this->ClientID;
            $RCPFrame->SessionID = $this->SessionID;
            $this->SendQueuePush($RCPFrame);
            $this->SendDebug('Send', $RCPFrame, 0);
            //$this->SendDebug('Send', $RCPFrame->ToJSONStringForIO(), 0);
            $this->SendDataToParent($RCPFrame->ToJSONStringForIO());
            /* @var $ReplyRCPData RCPData */
            $ReplyRCPData = $this->WaitForResponse($RCPFrame->Reserved);

            if ($ReplyRCPData === false)
            {
                $ReplyRCPData = $RCPData;
                $ReplyRCPData->Error = RCPError::RCP_ERROR_REPLY_TIMEOUT;
            }
            $this->SendDebug('Response', $ReplyRCPData, 0);
        }
        if ($ReplyRCPData->Error != RCPError::RCP_ERROR_NO_ERROR)
        {
            $this->SendDebug('Error', RCPError::ToString($ReplyRCPData->Error), 0);
            //trigger_error(RCPError::ToString($ReplyRCPData->Error), E_USER_NOTICE);
        }
        return $ReplyRCPData;
    }

    private function StartConnect()
    {
        if ($this->Host === "")
            return false;
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_REG_MD5_RANDOM;
        $RCPData->DataType = RCPDataType::RCP_P_STRING;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $ReplyRCPData = $this->Send($RCPData);
        if ($ReplyRCPData->Error != RCPError::RCP_ERROR_NO_ERROR)
            return false;
        // Login & Registration        
        $rand = $ReplyRCPData->Payload;
        $User = $this->ReadPropertyString('User');
        $Pass = $this->ReadPropertyString('Password');
        //$Login = "+" . $User . ":" . $Pass . "+";
        // +random_string+++username:password+
        $Login = "+" . $rand . "+++" . $User . ":" . $Pass . "+";
        $this->SendDebug('Login1', $Login, 0);
        $md5 = md5($Login);
        $this->SendDebug('MD5', $md5, 0);
        // +Username:random_string:response_string+
        $Login = "+" . $User . ":" . $rand . ":" . $md5 . "+";
        $this->SendDebug('Login2', $Login, 0);
        $payload_header = chr(0x01) . chr(0x00) . chr(0x00) . chr(0x00) .
                chr(0x01) . chr(strlen($Login));
        $payload_tags = "";
        $payload_tag_num = pack("n", count(self::RCPTags));

        foreach (self::RCPTags as $tag)
        {
            $payload_tags .= pack("n", $tag);
        }
        $RCPData = new RCPData();
        $RCPData->Payload = $payload_header . $payload_tag_num . $payload_tags . $Login;
        $RCPData->Tag = RCPTag::TAG_CLIENT_REGISTRATION;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $ReplyRCPData = $this->Send($RCPData);
        if ($ReplyRCPData->Error != RCPError::RCP_ERROR_NO_ERROR)
            return false;

        if ($ReplyRCPData->Payload[0] != chr(0x01))
            return false;

        $this->ClientID = substr($ReplyRCPData->Payload, 2, 2);
        $this->SendDebug('NEW CLIENTID', $this->ClientID, 1);
        return true;
    }

    /**
     * Wartet auf eine Antwort einer Anfrage an den LMS.
     *
     * @access private
     * @param int $FrameID
     * @result RCPData
     */
    private function WaitForResponse(int $FrameID)
    {
        for ($i = 0; $i < 1000; $i++)
        {

            $Buffer = $this->ReplyRCPData;
            if (!array_key_exists($FrameID, $Buffer))
                return false;
            if (!is_null($Buffer[$FrameID]))
            {
                $this->SendQueueRemove($FrameID);
                return $Buffer[$FrameID];
            }
            IPS_Sleep(5);
        }
        $this->SendQueueRemove($FrameID);
        return false;
    }

################## SENDQUEUE

    /**
     * Fügt eine Anfrage in die SendQueue ein.
     *
     * @access private
     * @param RCPFrame $RCPFrame Das versendete RCPData Objekt.
     */
    private function SendQueuePush(RCPFrame &$RCPFrame)
    {
        if (!$this->lock('ReplyRCPData'))
            throw new Exception($this->Translate('ReplyRCPData is locked'), E_USER_NOTICE);
        $data = $this->ReplyRCPData;
        $FrameID = $this->FrameID;
        $FrameID++;
        if ($FrameID == 256)
            $FrameID = 0;
        $this->FrameID = $FrameID;
        $RCPFrame->Reserved = $FrameID;
        $data[$FrameID] = null;
        $this->ReplyRCPData = $data;
        $this->unlock('ReplyRCPData');
    }

    /**
     * Fügt eine Antwort in die SendQueue ein.
     *
     * @access private
     * @param RCPFrame $RCPFrame Das empfangene RCPFrame Objekt.
     * @return bool True wenn Anfrage zur Antwort gefunden wurde, sonst false.
     */
    private function SendQueueUpdate(int $Frame, RCPData $RCPData)
    {
        if (!$this->lock('ReplyRCPData'))
            throw new Exception($this->Translate('ReplyRCPData is locked'), E_USER_NOTICE);
        $data = $this->ReplyRCPData;
        if (array_key_exists($Frame, $data))
        {
            $data[$Frame] = $RCPData;
            $this->ReplyRCPData = $data;
            $this->unlock('ReplyRCPData');
            return true;
        }
        $this->unlock('ReplyRCPData');
        return false;
    }

    /**
     * Löscht einen Eintrag aus der SendQueue.
     *
     * @access private
     * @param int $Index Der Index des zu löschenden Eintrags.
     */
    private function SendQueueRemove(int $Index)
    {
        if (!$this->lock('ReplyRCPData'))
            throw new Exception($this->Translate('ReplyRCPData is locked'), E_USER_NOTICE);
        $data = $this->ReplyRCPData;
        unset($data[$Index]);
        $this->ReplyRCPData = $data;
        $this->unlock('ReplyRCPData');
    }

}

/** @} */