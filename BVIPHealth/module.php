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
 * BVIPHealth Klasse implementiert eine Device für die CPU-Last, Status der Lüfter, Temperatur und Netzwerkports.
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
 */
class BVIPHealth extends BVIPBase
{
    protected static $RCPTags = [];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();

        $this->RegisterAttributeInteger('Number_Fan', 0);
        $this->RegisterAttributeInteger('Number_CPU', 0);
        $this->RegisterAttributeInteger('Number_Temp', 0);
        $this->RegisterAttributeInteger('Number_ETH', 0);
        $this->RegisterPropertyInteger('Interval', 5);
        $this->RegisterTimer('RequestState', 0, 'BVIP_RequestState($_IPS[\'TARGET\']);');
    }

    /**
     * Interne Funktion des SDK.
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->RegisterProfileIntegerEx('BVIP.LinkState', '', '', '', [
//      0=No link, 1=10MbitHD, 2=10MbitFD, 3=100MbitHD, 4=100MbitFD, 5=1000FD, 7=Wlan;
            [0, 'No Link', '', 0],
            [1, '10MbitHD', '', 0],
            [2, '10MbitFD', '', 0],
            [3, '100MbitHD', '', 0],
            [4, '100MbitFD', '', 0],
            [5, '1Gbit', '', 0],
            [6, '1Gbit', '', 0],
            [7, 'WLAN', '', 0],
        ]);
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
            $this->Scan();
            $this->RequestState();
            $this->SetTimerInterval('RequestState', $this->ReadPropertyInteger('Interval') * 1000);
        } else {
            $this->SetTimerInterval('RequestState', 0);
        }
    }

    public function Scan()
    {
        $this->WriteAttributeInteger('Number_Temp', $this->ReadNBRofTempSens());
        $this->WriteAttributeInteger('Number_Fan', $this->ReadNBRofFans());
        $this->WriteAttributeInteger('Number_CPU', $this->ReadNBRofCPU());
        $this->WriteAttributeInteger('Number_ETH', $this->ReadNBRofETH());
    }

    protected function ReadNBRofTempSens()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_NBR_OF_TEMP_SENS;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        /* @var $RCPReplyData RCPData */

        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            return $RCPReplyData->Payload;
        }

        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID.':'.'READ NBR_OF_TEMP_SENS - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return 0;
    }

    protected function ReadNBRofFans()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_NBR_OF_FANS;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        /* @var $RCPReplyData RCPData */

        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            return $RCPReplyData->Payload;
        }

        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            if ($RCPReplyData->Error != RCPError::RCP_ERROR_READ_NOT_SUPPORTED) {
                trigger_error($this->InstanceID.':'.'READ NBR_OF_FANS - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
        }

        return 0;
    }

    protected function ReadNBRofCPU()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CPU_COUNT;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        /* @var $RCPReplyData RCPData */

        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            return $RCPReplyData->Payload;
        }

        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID.':'.'READ NBR_OF_CPU - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return 0;
    }

    protected function ReadNBRofETH()
    {
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_NBR_OF_EXT_ETH_PORTS;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = 0;
        /* @var $RCPReplyData RCPData */

        $RCPReplyData = $this->Send($RCPData);
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            return $RCPReplyData->Payload;
        }

        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error($this->InstanceID.':'.'READ NBR_OF_EXT_ETH_PORTS - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
        }

        return 0;
    }

    public function RequestState()
    {
        $Result = true;
        $NbrTemp = $this->ReadAttributeInteger('Number_Temp');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_TEMP_SENS;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $NbrTemp; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $this->GetOrCreateVariableTEMP('TEMP_' . $index);
                $this->SetValueFloat('TEMP_' . $index, $RCPReplyData->Payload / 10);
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                if ($RCPReplyData->Error == RCPError::RCP_ERROR_READ_NOT_SUPPORTED) {
                    $this->WriteAttributeInteger('Number_Temp', $index - 1);
                    break;
                } else {
                    trigger_error($this->InstanceID.':'.'TEMP_' . $index . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
                }
            }
        }

        $NbrFans = $this->ReadAttributeInteger('Number_Fan');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_FAN_SPEED;
        $RCPData->DataType = RCPDataType::RCP_T_DWORD;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $NbrFans; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $this->GetOrCreateVariableRPM('FAN_SPEED_' . $index);
                $this->SetValueInteger('FAN_SPEED_' . $index, $RCPReplyData->Payload);
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                if ($RCPReplyData->Error == RCPError::RCP_ERROR_READ_NOT_SUPPORTED) {
                    $this->WriteAttributeInteger('Number_Fan', $index - 1);
                    break;
                } else {
                    trigger_error($this->InstanceID.':'.'FAN_SPEED_' . $index . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
                }
            }
        }
        if ($NbrFans > 0) {
            $RCPData = new RCPData();
            $RCPData->Tag = RCPTag::TAG_MINIMUM_FAN_SPEED;
            $RCPData->DataType = RCPDataType::RCP_T_DWORD;
            $RCPData->RW = RCPReadWrite::RCP_DO_READ;
            $RCPData->Num = 1;

            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $this->GetOrCreateVariableRPM('FAN_MINSPEED');
                $this->SetValueInteger('FAN_MINSPEED', $RCPReplyData->Payload);
            } else {
                if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                    trigger_error($this->InstanceID.':'.'FAN_MINSPEED - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
                }
                $Result = false;
            }
        }
        $NbrCPUs = $this->ReadAttributeInteger('Number_CPU');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CPU_LOAD;
        $RCPData->DataType = RCPDataType::RCP_P_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 1; $index <= $NbrCPUs; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $cpu_idle = ord($RCPReplyData->Payload[0]);
                $cpu_coder = ord($RCPReplyData->Payload[1]);
                $cpu_vca = ord($RCPReplyData->Payload[2]);
                $cpu_last = 100 - $cpu_idle;
                $cpu_etc = $cpu_last - $cpu_coder - $cpu_vca;
//        cpu_idle, cpu_coder, cpu_vca, cpu_etc
                $this->GetOrCreateVariableCPU('CPU_' . $index . '_SUMMARY_LOAD');
                $this->SetValueInteger('CPU_' . $index . '_SUMMARY_LOAD', $cpu_last);
                $this->GetOrCreateVariableCPU('CPU_' . $index . '_IDLE');
                $this->SetValueInteger('CPU_' . $index . '_IDLE', $cpu_idle);
                $this->GetOrCreateVariableCPU('CPU_' . $index . '_VCA_LOAD');
                $this->SetValueInteger('CPU_' . $index . '_VCA_LOAD', $cpu_vca);
                $this->GetOrCreateVariableCPU('CPU_' . $index . '_CODER_LOAD');
                $this->SetValueInteger('CPU_' . $index . '_CODER_LOAD', $cpu_coder);
                $this->GetOrCreateVariableCPU('CPU_' . $index . '_OTHER_LOAD');
                $this->SetValueInteger('CPU_' . $index . '_OTHER_LOAD', $cpu_etc);
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error($this->InstanceID.':'.'CPU_LOAD ' . $index . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
        }

        $NbrETH = $this->ReadAttributeInteger('Number_ETH');
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_ETH_LINK_STATUS;
        $RCPData->DataType = RCPDataType::RCP_T_OCTET;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;

        for ($index = 0; $index <= $NbrETH; $index++) {
            $RCPData->Num = $index;
            $RCPReplyData = $this->Send($RCPData);
            /* @var $RCPReplyData RCPData */
            if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
                $this->GetOrCreateVariableETH('ETH_' . $index);
                $this->SetValueInteger('ETH_' . $index, $RCPReplyData->Payload);
                continue;
            } else {
                $Result = false;
            }

            if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
                trigger_error($this->InstanceID.':'.'ETH_' . $index . ' - ' . $this->Translate(RCPError::ToString($RCPReplyData->Error)), E_USER_NOTICE);
            }
        }

        return $Result;
    }

    protected function GetOrCreateVariableRPM(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableInteger($Ident, $Ident);
        }

        return $vid;
    }

    protected function GetOrCreateVariableTEMP(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableFloat($Ident, $Ident, '~Temperature');
        }

        return $vid;
    }

    protected function GetOrCreateVariableCPU(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableInteger($Ident, $Ident, '~Intensity.100');
        }

        return $vid;
    }

    protected function GetOrCreateVariableETH(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableInteger($Ident, $Ident, 'BVIP.LinkState');
        }

        return $vid;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        // empty
    }
}

/* @} */
