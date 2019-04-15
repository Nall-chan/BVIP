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
 * BVIPCamImages Klasse implementiert eine Device für die Darstellung des Videobildes.
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
        $this->RegisterPropertyBoolean('UseAuth', false);
        $this->RegisterPropertyInteger('Line', 1);
        $this->RegisterPropertyBoolean('HTMLBoxObject', true);
        $this->RegisterPropertyInteger('Type', 0);
        $this->RegisterPropertyInteger('Stream', 1);
        $this->RegisterPropertyInteger('Encoding', 4);
        $this->RegisterPropertyBoolean('MediaObject', true);
        $this->RegisterPropertyInteger('MediaType', 1);
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
            $Options[] = ['caption' => (string) $Line, 'value' => $Line];
        }
        $data['elements'][0]['options'] = $Options;
        $Firmware = $this->GetFirmware();
        if ($Firmware >= 5) {
            $data['elements'][7]['options'][] = ['caption' => 'JPEG-Push', 'value' => 3];
        }
        if ($Firmware >= 6) {
            $data['elements'][7]['options'][] = ['caption' => 'HTTP / h.264x', 'value' => 4];
        }

        return json_encode($data);
    }

    public function RequestState()
    {
        $Host = '';
        $vid = $this->RegisterVariableString('IMAGE', 'IMAGE', '~HTMLBox');
        $line = $this->ReadPropertyInteger('Line');
        $UseMediaObject = $this->ReadPropertyBoolean('MediaObject');
        $MediaType = $this->ReadPropertyInteger('MediaType');
        $UseHTMLBoxObject = $this->ReadPropertyBoolean('HTMLBoxObject');
        $typ = $this->ReadPropertyInteger('Type');
        $stream = $this->ReadPropertyInteger('Stream');
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
            $MediaType = 9;
        }
        if ($this->ReadPropertyBoolean('UseAuth')) {
            if ($Pass != '') {
                $Host = $User . ':' . $Pass . '@' . $Host;
            }
        }
        if ($UseHTMLBoxObject) {
            $vid = $this->RegisterVariableString('IMAGE', 'IMAGE', '~HTMLBox');

            switch ($typ) {
                case 0: // VLC
                    if ($this->GetFirmware() > 4) {
                        $h264 = '&h26x=' . $this->ReadPropertyInteger('Encoding');
                    } else {
                        $h264 = '';
                    }
                    $htmlData = '<div align="center"><embed type="application/x-vlc-plugin" autoplay="yes" controls="no" branding="no" loop="no" width="'
                            . $width . '" height="' . $height . '" target="rtsp://' . $Host . '/video?line=' . $line . '&inst=' . $stream . $h264 . '" align="center" /></div>';
                    //todo ?enableaudio=1&audio_line=1&audio_mode=0
                    // ?meta=1&metaline=1
                    //?vcd=1
                    break;
                case 2: // JPEG-Pull
                    $htmlData = '<script language="JavaScript" type="text/javascript" src="http://' . $Host . '/pushimage.js"></script>'
                            . '<script type="text/javascript">var pimg' . $vid . ';var debugarea;function init' . $vid . '() {'
                            . 'pimg' . $vid . '=createPushImage("bvip_' . $vid . '", 0);pimg' . $vid . '.startPush();}'
                            . '</script>'
                            . '<div align="center"><img onLoad="init' . $vid . '()" id="bvip_' . $vid . '" name="bvip_' . $vid . '" src="http://'
                            . $Host . '/snap.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegBurst=1&JpegDomain=' . $vid . '&JpegQuality=' . $jpegquali . '" /></div>';
                    break;
                case 3: // JPEG-Push
                    // FW 5
                    $htmlData = '<div align="center"><img src="http://' . $Host . '/push.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegQuality=' . $jpegquali . '" /></div>';
                    // FW 6
                    //video.mp4?line=1&inst=1&rec=0&rnd=40590
                    break;
                case 4:
                    $htmlData = '<div align="center"><video id="video" autoplay="" autobuffer="" poster="http://' . $Host . '/logo.jpg" src="http://' . $Host . '/video.mp4?line=' . $line . '&inst=' . $stream . '&rec=0"></video></div>';
                    break;
                default:
                    $htmlData = '';
                    break;
            }
            $this->SetValueString('IMAGE', $htmlData);
        } else {
            $this->UnregisterVariable('IMAGE');
        }

        if ($UseMediaObject) {
            switch ($MediaType) {
                case 0:
                    $Url = 'http://' . $Host . '/push.jpg?JpegSize=' . $jpegsize . '&JpegCam=' . $line . '&JpegQuality=' . $jpegquali;
                    break;
                case 1:
                    $Url = 'http://' . $Host . '/video.mp4?line=' . $line . '&inst=' . $stream;
                    break;
                case 2:
                    $Url = 'rtsp://' . $Host . '?line=' . $line . '&inst=' . $stream;
                    break;
                default:
                    $Url = '';
                    break;
            }
            $mid = @$this->GetIDForIdent('STREAM');
            if ($mid == false) {
                $mid = IPS_CreateMedia(MEDIATYPE_STREAM);
                IPS_SetParent($mid, $this->InstanceID);
                IPS_SetName($mid, 'STREAM');
                IPS_SetIdent($mid, 'STREAM');
            }
            IPS_SetMediaFile($mid, $Url, false);
        } else {
            $mid = @$this->GetIDForIdent('STREAM');
            if ($mid > 0) {
                IPS_DeleteMedia($mid, false);
            }
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

    protected function DecodeRCPEvent(RCPData $RCPData)
    {
        
    }

}

/* @} */
