<?php

/**
 * Trait welcher Array in eine String Attribute schreiben und lesen kann.
 * @filesource 
 */
trait AttributeArrayHelper
{
    /**
     * Wert einer Eigenschaft aus den InstanceBuffer lesen.
     * 
     * @access public
     * @param string $name Propertyname
     * @return mixed Value of Name
     */
    protected function RegisterAttributeArray($name, $Value, $Size = 0)
    {
        $Data = json_encode($Value);
        if (strpos($name, 'Multi_') === 0) {
            $Lines = str_split($Data, 8000);
            $Size = (count($Lines) < $Size) ? $Size : count($Lines);
            for ($i = 0; $i < $Size; $i++) {
                $Line = (array_key_exists($i, $Lines)) ? $Lines[$i] : '';
                $this->RegisterAttributeString('Part_' . $name . $i, $Line);
            }
            $this->RegisterAttributeInteger("MultiListe_" . $name, $Size);
        } else {
            $this->RegisterAttributeString($name, $Data);
        }
    }

    protected function ReadAttributeArray($name)
    {
        if (strpos($name, 'Multi_') === 0) {
            $Lines = "";
            $Size = $this->ReadAttributeInteger("MultiListe_" . $name);
            for ($i = 0; $i < $Size; $i++) {
                $Lines .= $this->ReadAttributeString('Part_' . $name . $i);
            }
            return json_decode($Lines, true);
        }
        return json_decode($this->ReadAttributeString($name), true);
    }

    protected function WriteAttributeArray($name, $value)
    {
        $Data = json_encode($value);
        if (strpos($name, 'Multi_') === 0) {
            $Size = $this->ReadAttributeInteger("MultiListe_" . $name);
            $Lines = str_split($Data, 8000);
            if (count($Lines) > $Size) {
                trigger_error($this->InstanceID.':'.'Data for AttributeArray is too big.', E_USER_NOTICE);
                return false;
            }
            for ($i = 0; $i < $Size; $i++) {
                $Line = (array_key_exists($i, $Lines)) ? $Lines[$i] : '';
                $this->WriteAttributeString('Part_' . $name . $i, $Line);
            }
        } else {
            $this->WriteAttributeString($name, $Data);
        }
    }

}
