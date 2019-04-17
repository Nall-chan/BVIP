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
 * BVIPSerialPort Klasse implementiert einen Spliter für den seriellen Anschluß.
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
class BVIPSerialPort extends BVIPBase
{
    protected static $RCPTags = [RCPTag::TAG_TRANSFER_TRANSPARENT_DATA];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyInteger('Number', 1);
        $this->RegisterPropertyInteger('Speed', 9600);
        $this->RegisterPropertyInteger('Bits', 8);
        $this->RegisterPropertyInteger('StopBits', 1);
        $this->RegisterPropertyInteger('Par', 0);
        $this->RegisterPropertyInteger('Mode', 1);
        $this->RegisterPropertyInteger('HalfDuplex', 0);
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
            if ($this->GetNbrOfSerialPorts() >= $this->ReadPropertyInteger('Number')) {
                $this->SetStatus(IS_ACTIVE);
                $this->ConfigAndOpenPort();
            } else {
                $this->SetStatus(IS_EBASE + 2);
                trigger_error($this->InstanceID.':'.$this->Translate('Port not valid.'), E_USER_NOTICE);
            }
        }
    }

    public function GetConfigurationForm()
    {
        $Ports = @$this->GetNbrOfSerialPorts();
        if ($Ports == 0) {
            return '{"elements":[{"type":"Label","caption":"Device has no serial port!"}]}';
        } else {
            $data = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $Options = [];
            for ($Port = 1; $Port <= $Ports; $Port++) {
                $Options[] = ['caption' => 'COM' . $Port, 'value' => $Port];
            }
            $data['elements'][0]['options'] = $Options;

            return json_encode($data);
        }
    }

    protected function ConfigAndOpenPort()
    {
        $Result = true;
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_APP_VAL;
        $RCPData->DataType = RCPDataType::RCP_T_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $RCPData->Num = $this->ReadPropertyInteger('Number');
        $RCPData->Payload = 0xff;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port to transparent - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }

        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_RATE;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->Payload = $this->ReadPropertyInteger('Speed');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port Baudrate - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_BITS;
        $RCPData->DataType = RCPDataType::RCP_T_OCTET;
        $RCPData->Payload = $this->ReadPropertyInteger('Bits');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port Databits - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_STBITS;
        $RCPData->Payload = $this->ReadPropertyInteger('StopBits');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port Stopbits - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_PAR;
        $RCPData->Payload = $this->ReadPropertyInteger('Par');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port Parity - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_MODE_VAL;
        $RCPData->Payload = $this->ReadPropertyInteger('Mode');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port RS232/485 mode - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }
        $RCPData->Tag = RCPTag::TAG_SERIAL_PORT_HD_MODE_VAL;
        $RCPData->Payload = $this->ReadPropertyInteger('HalfDuplex');
        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR) {
            if (($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) and ( $RCPReplyData->Error != RCPError::RCP_ERROR_NO_ERROR)) {
                trigger_error($this->InstanceID.':'.'Error Set Serial Port Duplex mode - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
            $Result = false;
        }

        return $Result;
    }

    public function ForwardData($JSONString)
    {
        $ForwardData = json_decode($JSONString);
        $Payload = utf8_decode($ForwardData->Buffer);
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_TRANSFER_TRANSPARENT_DATA;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $RCPData->Num = $this->ReadPropertyInteger('Number');
        $RCPData->Payload = "\x00\x00\xff\xff" . $Payload;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if ($RCPReplyData->Payload[0] != chr(0x01)) {
                trigger_error($this->InstanceID.':'.'Access denied to SerialPort', E_USER_NOTICE);

                return false;
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID.':'.'Error Write Data to SerialPort - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return false;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        if ($RCPData->Num != $this->ReadPropertyInteger('Number')) {
            return;
        }

        $SendData = new stdClass();
        $SendData->DataID = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
        $SendData->Buffer = utf8_encode($RCPData->Payload);
        $this->SendDataToChildren(json_encode($SendData));
    }

    protected function RequestState()
    {
        
    }

}

/* @} */
