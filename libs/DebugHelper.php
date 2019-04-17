<?php

declare(strict_types=1);

namespace bvip;

/**
 * DebugHelper ergänzt SendDebug um die Möglichkeit Array und Objekte auszugeben.
 */
trait DebugHelper
{
    /**
     * Ergänzt SendDebug um Möglichkeit Objekte und Array auszugeben.
     *
     * @param string                                           $Message Nachricht für Data.
     * @param LMSResponse|LMSData|array|object|bool|string|int $Data    Daten für die Ausgabe.
     *
     * @return int $Format Ausgabeformat für Strings.
     */
    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_a($Data, 'RCPData')) {
            /* @var $Data RCPData */
            $this->SendDebug($Message, '~~RCP DATA~~', 0);
            $this->SendDebug($Message . ':Tag', \RCPTag::ToString($Data->Tag), 0);
            $this->SendDebug($Message . ':DataType', \RCPDataType::ToString($Data->DataType), 0);
            $this->SendDebug($Message . ':RW', \RCPReadWrite::ToString($Data->RW), 0);
            $this->SendDebug($Message . ':Number', $Data->Num, 0);
            $this->SendDebug($Message . ':Error', $this->Translate(\RCPError::ToString($Data->Error)), 0);
            switch ($Data->DataType) {
                case \RCPDataType::RCP_F_FLAG:
                    $this->SendDebug($Message . ':Payload', $Data->Payload, 0);
                    break;
                case \RCPDataType::RCP_P_OCTET:
                    $this->SendDebug($Message . ':Payload', $Data->Payload, 1);
                    break;
                case \RCPDataType::RCP_T_OCTET:
                case \RCPDataType::RCP_T_WORD:
                case \RCPDataType::RCP_T_INT:
                case \RCPDataType::RCP_T_DWORD:
                case \RCPDataType::RCP_P_STRING:
                case \RCPDataType::RCP_P_UNICODE:
                    $this->SendDebug($Message . ':Payload', $Data->Payload, 0);
                    break;
            }
        } elseif (is_a($Data, 'RCPFrame')) {
            /* @var $Data RCPFrame */
            $this->SendDebug($Message, '~~RCP-FRAME~~', 0);
            $this->SendDebug($Message . ':Action', \RCPAction::ToString($Data->Action), 0);
            $this->SendDebug($Message . ':Tag', \RCPTag::ToString($Data->Tag), 0);
            $this->SendDebug($Message . ':Continuation', $Data->Continuation, 0);
            $this->SendDebug($Message . ':SessionId', $Data->SessionID, 1);
            $this->SendDebug($Message . ':ClientId', $Data->ClientID, 1);
            $this->SendDebug($Message . ':Reserved', $Data->Reserved, 0);
            $this->SendDebug($Message . ':DataType', \RCPDataType::ToString($Data->DataType), 0);
            $this->SendDebug($Message . ':RW', \RCPReadWrite::ToString($Data->RW), 0);
            $this->SendDebug($Message . ':Number', $Data->Num, 0);
            $this->SendDebug($Message . ':Payload', $Data->Payload, 1);
        } elseif (is_array($Data)) {
            if (count($Data) > 25) {
                $this->SendDebug($Message, array_slice($Data, 0, 20), 0);
                $this->SendDebug($Message . ':CUT', '-------------CUT-----------------', 0);
                $this->SendDebug($Message, array_slice($Data, -5, null, true), 0);
            } else {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . ':' . $Key, $DebugData, 0);
                }
            }
        } elseif (is_object($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . '->' . $Key, $DebugData, 0);
            }
        } elseif (is_bool($Data)) {
            parent::SendDebug($Message, ($Data ? 'TRUE' : 'FALSE'), 0);
        } else {
            parent::SendDebug($Message, (string) $Data, $Format);
        }
    }

}

/* @} */
