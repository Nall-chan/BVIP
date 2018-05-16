<?php

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
 * BVIPCamImages Klasse implementiert eine Device für die Darstelleung des Videobildes.
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
class BVIPCamImages extends BVIPBase
{
    protected static $RCPTags = [];

    /**
     * Interne Funktion des SDK.
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('Rename', true);
        $this->RegisterPropertyInteger('Line', 1);
        $this->RegisterPropertyInteger('Type', 0);
        $this->RegisterPropertyInteger('Stream', 1);
        $this->RegisterPropertyInteger('Encoding', 4);
        $this->RegisterPropertyBoolean('MediaObject', false);
        $this->RegisterPropertyInteger('Width', 640);
        $this->RegisterPropertyInteger('Height', 480);
        $this->RegisterPropertyString('JPEGSize', 'XL');
        $this->RegisterPropertyInteger('JPEGQuality', 5);
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

    protected function KernelReady()
    {
        parent::KernelReady();
    }

    protected function IOChangeState($State)
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            if ($this->ReadPropertyBoolean('Rename') === true) {
                $this->RequestName();
            }
            if ($this->ReadNbrOfVideoIn() >= $this->ReadPropertyInteger('Line')) {
                $this->SetStatus(IS_ACTIVE);
                $this->RequestState();
            } else {
                $this->SetStatus(IS_EBASE + 2);
                trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);
            }
        }
    }

    public function GetConfigurationForm()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $Lines = $this->ReadNbrOfVideoIn();
        $Options = [];
        for ($Line = 1; $Line <= $Lines; $Line++) {
            $Options[] = ['label' => (string) $Line, 'value' => $Line];
        }
        $data['elements'][0]['options'] = $Options;

        if ($this->GetFirmware() < 5) {
            array_splice($data['elements'], 2, 1);
        } else {
            $data['elements'][3]['options'][] = ['label' => 'JPEG-Push', 'value' => 3];
        }

        return json_encode($data);
    }

    public function RequestState()
    {
        $Host = '';
        $vid = $this->GetOrCreateVariable('IMAGE');
        $line = $this->ReadPropertyInteger('Line');
        $typ = $this->ReadPropertyInteger('Type');
        $stream = $this->ReadPropertyInteger('Stream');
        $encoding = $this->ReadPropertyInteger('Encoding');
        $width = $this->ReadPropertyInteger('Width');
        $height = $this->ReadPropertyInteger('Height');       //width="640" height="480"
        $jpegsize = $this->ReadPropertyString('JPEGSize');
        $jpegquali = $this->ReadPropertyInteger('JPEGQuality');

        $ParentId = $this->ParentID;
        if ($ParentId > 0) {
            $IOId = @IPS_GetInstance($ParentId)['ConnectionID'];
            $User = IPS_GetProperty($ParentId, 'User');
            $Pass = IPS_GetProperty($ParentId, 'Password');
            if ($IOId > 0) {
                $Host = IPS_GetProperty($IOId, 'Host');
            }
        }
        if ($Host == '') {
            $typ = 9;
        }

        if ($this->GetFirmware() > 4) {
            $h264 = '&h26x=' . $encoding;
        } else {
            $h264 = '';
        }
        if ($Pass != '') {
            $Host = $User . ':' . $Pass . '@' . $Host;
        }
        switch ($typ) {
            case 0: // VLC

                $htmlData = '<div align="center"><embed type="application/x-vlc-plugin" autoplay="yes" controls="no" branding="no" loop="no" width="'
                        . $width . '" height="' . $height . '" target="rtsp://' . $Host . '/video?line=' . $line . '&inst=' . $stream . $h264 . '" align="center" /></div>';
                //todo ?enableaudio=1&audio_line=1&audio_mode=0
                // ?meta=1&metaline=1
                //?vcd=1
                break;
            case 2: // JS-Pull
                $htmlData = '<script language="JavaScript" type="text/javascript" src="http://' . $Host . '/pushimage.js"></script>'
                        . '<script type="text/javascript">var pimg' . $vid . ';var debugarea;function init' . $vid . '() {'
                        . 'pimg' . $vid . '=createPushImage("bvip_' . $vid . '", 0);pimg' . $vid . '.startPush();}'
                        . '</script>'
                        . '<div align="center"><img onLoad="init' . $vid . '()" id="bvip_' . $vid . '" name="bvip_' . $vid . '" src="http://'
                        . $Host . '/snap.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegBurst=1&JpegDomain=' . $vid . '&JpegQuality=' . $jpegquali . '" /></div>';
                break;
            case 3: // JPEG-Push
                // FW 5
                if ($this->GetFirmware() < 6) {
                    $htmlData = '<div align="center"><img src="http://' . $Host . '/push.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegQuality=' . $jpegquali . '" /></div>';
                } else {
                    $htmlData = '<div align="center"><video id="video" autoplay="" autobuffer="" poster="http://' . $Host . '/logo.jpg"  src="http://' . $Host . '/video.mp4?line=' . $line . '&inst=1&rec=0"></video></div>';
                }
                // FW 6
                //video.mp4?line=1&inst=1&rec=0&rnd=40590
                break;
            default:
                $htmlData = '';
                break;
        }
        $this->SetValueString('IMAGE', $htmlData);
        if (!$this->ReadPropertyBoolean('MediaObject') or ($this->GetFirmware() < 5)) {
            // todo unregister Media
        } else {
            if ($this->GetFirmware() < 6) {
                $Url = 'http://' . $Host . '/push.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegQuality=' . $jpegquali;
            } else {
                $Url = 'http://' . $Host . '/video.mp4?line=' . $line . '&inst=1&rec=0';
            }
            $mid = @$this->GetIDForIdent('STREAM');
            if ($mid == false) {
                $mid = IPS_CreateMedia(3);
                IPS_SetParent($mid, $this->InstanceID);
                IPS_SetName($mid, 'STREAM');
                IPS_SetIdent($mid, 'STREAM');
            }
            IPS_SetMediaFile($mid, $Url, false);
        }
    }

    public function RequestName()
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAMNAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_READ;
        $RCPData->Num = $Line;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if (IPS_GetName($this->InstanceID) != $RCPReplyData->Payload) {
                IPS_SetName($this->InstanceID, $RCPReplyData->Payload);
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error('Write Name Line' . $Line . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    public function SetName(string $Name, string $Ident)
    {
        $Line = $this->ReadPropertyInteger('Line');
        if ($Line == 0) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        if ($this->ReadNbrOfVideoIn() < $Line) {
            trigger_error($this->Translate('Cameraline not valid.'), E_USER_NOTICE);

            return false;
        }
        $RCPData = new RCPData();
        $RCPData->Tag = RCPTag::TAG_CAMNAME;
        $RCPData->DataType = RCPDataType::RCP_P_UNICODE;
        $RCPData->RW = RCPReadWrite::RCP_DO_WRITE;
        $RCPData->Num = $Line;
        $RCPReplyData = $this->Send($RCPData);
        /* @var $RCPReplyData RCPData */
        if ($RCPReplyData->Error == RCPError::RCP_ERROR_NO_ERROR) {
            if (IPS_GetName($this->InstanceID) != $Name) {
                IPS_SetName($this->InstanceID, $RCPReplyData->Payload);
            }

            return true;
        }
        if ($RCPReplyData->Error != RCPError::RCP_ERROR_SEND_ERROR) {
            trigger_error('Write Name Line' . $Line . ' - ' . RCPError::ToString($RCPReplyData->Error), E_USER_NOTICE);
        }

        return false;
    }

    protected function GetOrCreateVariable(string $Ident)
    {
        $vid = @$this->GetIDForIdent($Ident);
        if ($vid == false) {
            $vid = $this->RegisterVariableString($Ident, $Ident, '~HTMLBox');
        }

        return $vid;
    }

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
    }
}

/* @} */
