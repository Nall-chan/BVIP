<?php

trait UTF8Coder
{
    /**
     * Führt eine UTF8-Dekodierung für einen String oder ein Objekt durch (rekursiv).
     *
     * @param string|object|array &$item Zu dekodierene Daten.
     */
    private function DecodeUTF8(&$item)
    {
        if (is_string($item)) {
            $item = utf8_decode($item);
        } elseif (is_array($item)) {
            foreach ($item as &$value) {
                $this->DecodeUTF8($value);
            }
        } elseif (is_object($item)) {
            foreach ($item as &$value) {
                $this->DecodeUTF8($value);
            }
        }
    }

    /**
     * Führt eine UTF8-Enkodierung für einen String, ein Array oder ein Objekt durch (rekursiv).
     *
     * @param string|object|array &$item Zu Enkodierene Daten.
     */
    private function EncodeUTF8(&$item)
    {
        if (is_string($item)) {
            $item = utf8_encode($item);
        } elseif (is_array($item)) {
            foreach ($item as &$value) {
                $this->EncodeUTF8($value);
            }
        } elseif (is_object($item)) {
            foreach ($item as &$value) {
                $this->EncodeUTF8($value);
            }
        }
    }

}
