<?php

class RCPTag
{
    //----------------------------------------------------------------------------//
//------------------------------RCP-TAGs--------------------------------------//
//----------------------------------------------------------------------------//
//-------------------------------CONNECTION-----------------------------------//
    const TAG_CLIENT_REGISTRATION = 0xff00; // read/write (P_OCTET)                                   **
    // payload
    //0x01,0x00,0x00,0x00
    //0x00,0x00,NUM_MESSAGE(2B)
    // TAG1(2B)....TAG n (2B)
    //
    // TAG= 0xffff = Alle Tags
    const TAG_CLIENT_UNREGISTER = 0xff01; // only write (P_OCTET)                                   **
    const TAG_CLIENT_TIMEOUT_WARNING = 0xff03; //only incoming 1min befor Server kick Client             **
    const TAG_REG_MD5_RANDOM = 0xff05;
    const TAG_TRANSFER_TRANSPARENT_DATA = 0xffdd;                //                                  **
    /* {CONNECT_PRIMITIVE
      DISCONNECT_PRIMITIVE
      CONNECT_TO
      ACTIVE_CONNECTION_LIST
      MEDIA_SOCKETS_COMPLETE
      RCP_CONNECTIONS_ALIVE     } */
    const TAG_CAPABILITY_LIST = 0xff10;  //only read p_octet
    /* {RCP_CODER_LIST
      PUBLISH_MEDIA_KEYS
      CRYPT_KEYAUDIO_ENC
      CRYPT_KEYVIDEO_ENC
      CRYPT_KEYAUDIO_DEC
      CRYPT_KEYVIDEO_DEC
      CRYPT_KEY_GENERATE_ALL
      } */
    //-----------------------------IDENTIFICATION---------------------------------//
    const TAG_UNIT_NAME = 0x0024; // R/W (P_UNICODE)                                      **
    const TAG_UNIT_ID = 0x0025; // R/W (P_UNICODE)
    const TAG_CAMNAME = 0x0019; // R/W (P_UNICODE) NUM = VideoLine                      **
    const TAG_CAMNAME2 = 0x0a7e; // R/W (P_UNICODE) NUM = VideoLine                       **
    const TAG_HARDWARE_VERSION = 0x002e; // only Read (P_STRING)
    const TAG_SOFTWARE_VERSION = 0x002f; // only Read (P_STRING)                                 **
    const TAG_BOOTLOADER_VERSION = 0x09ef; // only Read (T_DWORD)
    const TAG_SERIAL_NUMBER = 0x0ae7; // only Read (P_OCTET)
    const TAG_CTN = 0x0aea; // only Read (P_STRING)
    //DEVICE_TYPE_IDS wozu ?
// SUPPORTED_UPLOAD_TARGETS
//----------------------------------TIME--------------------------------------//
    /* {	BROWSER_DATETIME_FORMAT_VAL
      DATE_WDAY
      DATE_DAY
      DATE_MONTH
      DATE_YEAR
      TIME_HRS
      TIME_MIN
      TIME_SEC
      TIMEZONE
      UTC_ZONEOFFSET
      NTP_SERVER_IP_STR
      DAY_LIGHT_SAVE_TIME_TABLE
      DAY_LIGHT_SAVE_TIME
      FORCE_TIME_SET
      } */
    //-------------------------------CONNECTION-----------------------------------//
    /* {PASSWORD_SETTINGS
      REMOTE_PASSWORD
      MASTERPWD_CHALLENGE
      } */
    //--------------------------------STATUS--------------------------------------//

    const TAG_NBR_OF_TEMP_SENS = 0x09c4; // only read                                            **
    const TAG_TEMP_SENS = 0x09c5; // only read [or pushed] (t_dword) num:temp sens        **
    /*
      REDUNDANT_POWER */
    //const TAG_POWER_SUPPLY = 0x09dd;
    const TAG_NBR_OF_FANS = 0x09d5; //                                                      **
    const TAG_FAN_SPEED = 0x09d6; //                                                      **
    const TAG_MINIMUM_FAN_SPEED = 0x09de; //                                                      **
    /* {	BOOT_STATE
      UPLOAD_PROGRESS	HEATER_MODE
      } */
    //--------------------------------------VIDPROC-------------------------------//
    const TAG_VIPROC_ALARM = 0x0807; // only read [or pushed] (P_OCTET) num:video line       **
    //PAYLOAD:
    //ALAM FLAGs:
    //1.Byte
    /* {
      VIDPROC_ALARM_MOTION               =      0x80;
      VIDPROC_ALARM_GLOBAL_CHANGE        =      0x40;
      VIDPROC_ALARM_TOO_BRIGHT           =      0x20;
      VIDPROC_ALARM_TOO_DARK             =      0x10;
      VIDPROC_ALARM_TOO_NOISY            =      0x08;
      VIDPROC_P_ALARM_TOO_BLURRY         =      0x04;
      VIDPROC_ALARM_SIGNAL_LOSS          =      0x02;
      VIDPROC_ALARM_REFERENCE_IMAGE      =      0x01;
      //2.Byte
      VIDPROC_INVALID_CONF               =      0x80;
      //Detector (2Byte) ??
      //ConfigID (1Byte) Number of VCA_Profile
      } */
    const TAG_VIPROC_ID = 0x0803;
    /* {VIPROC_ONOFF
      VIPROC_CUSTOM_PARAMETERS
      VIPROC_DLL_NAME
      VIPROC_DLL_NAME_LIST
      VIPROC_DLL_RELOAD
      VIPROC_SAVE_REFERENCE_IMAGE
      VIPROC_REFERENCE_IMAGE_FILENAME
      START_VIPROC_CONFIG_EDITING
      STOP_VIPROC_CONFIG_EDITING
      CONT_VIPROC_CONFIG_EDITING
      VIPROC_SCENE
      NUMBER_OF_VIPROC_CONFIGS
      ACTIVE_VIPROC_CONFIG
      LOADED_VIPROC_CONFIG
      VIPROC_CONFIG_NAME
      LIST_OF_VIPROC_SCENES
      VIPROC_TAGGED_CONFIG
      VIPROC_TAGGED_CONFIG_INTERNAL
      VIPROC_ALARM
      AUPROC_ALARM
      AUPROC_CONFIG
      AUPROC_MELPEGEL
      AUPROC_NAME
      VIPROC_ALARM_MASK
      VIPROC_CONFIG_CHANGE_IN_RECORDING
      VIPROC_MODE
      VIPROC_WEEKLY_SCHEDULE
      VIPROC_HOLIDAYS_SCHEDULE
      PTZ_CONTROLLER_AVAILABLE
      ENABLE_VCA
      VIPROC_ALARM_AGGREGATION_TIME
      VIPROC_MOTION_DEBOUNCE_TIME
      VCA_FRAME_RESOLUTION
      VIPROC_VIDEO_FORMAT
      VCA_TASK_RUNNING_STATE
      VIPROC_VERSION
      SELECT_VCA_SCALER
      TRANSPARENT_DATA_OVER_IP
      AUTO_TRACKER_TRACK_OBJECT
      MODE_AUTO_TRACKER
      SENSITIVITY_OBJECT_BASED_VCA
      REFERENCE_IMAGE_CHECK_INFO_MESSAGE
      IVA_COUNTER_VALUES} */
    //----------------------------------DEBUG-------------------------------------//
    //SYSUPTIME
    //-----------------------------------AUX--------------------------------------//

    /* 	{
      OEM_DEVICE_NAME
      OEM_EXT_ID
      OEM_DEVICE_DOMAIN
      DEFAULTS
      FACTORY_DEFAULTS
      BOARD_RESET
      BOOT_DEFAULT_APP
      VIDEO_TX_BITRATE_REQUEST
      VIDEO_ENC_PRIO
      LED_BLINKING
      JPEG
      CLUSTER_GROUP_SETTING
      } */

    const TAG_CLUSTER_ID = 0x09cb;  // only Read (T_DWORD)
    /* {
      APP_OPTION
      APP_OPTION_UNIT_ID
      APP_OPTION_SET
      } */
    const TAG_CPU_LOAD_IDLE = 0x0a06; // only Read (T_DWORD) num: host=1; coproc=2
    const TAG_CPU_LOAD_CODER = 0x0a07; // only Read (T_DWORD) num: host=1; coproc=2
    const TAG_CPU_LOAD_CODER_INST = 0x0a7d; // only Read (p_octet) num: host=1, coproc=2
    const TAG_CPU_LOAD_VCA = 0x0a08; // only Read (T_DWORD) num: host=1, coproc=2
    const TAG_CPU_LOAD = 0x0a0a; // only Read (p_octet) num: host=1, coproc=2 payload:cpu load vca in percent (byte 0=idle; byte 1 = coder, byte 2=vca)
    const TAG_CPU_COUNT = 0x0a09; // only Read (T_DWORD)
    /* 	{
      SANITY_CHECK
      NIGHT_MODE_STATE
      POE_GRANTED_POWER
      INSTALLER_SEQUENCE ??
      UPLOAD_HISTORY
      } */
    //-------------------------------VIDEO--INPUT---------------------------------//
    /* {
      LOW_LIGHT_MIN_FPS
      VID_IN_CONTRAST
      VID_IN_SATURATION
      VID_IN_BRIGHTNESS
      VID_IN_SAMPLING_MODE
      VID_IN_WHITE_BALANCE_MODE
      VID_IN_RGB_GAIN
      VID_IN_MIRROR
      VIDEO_TERMINATION_RESISTOR_ON
      VIDEO_TERMINATION_RESISTOR_OFF
      VIDEO_INPUT_FORMAT
      VIDEO_INPUT_FORMAT_EX
      INPUT_SOURCE_VAL
      } */
    const TAG_NBR_OF_VIDEO_IN = 0x01d6; // only Read (T_DWORD)                                    **
    /* {
      PRIV_MSK
      VIN_BASE_FRAMERATE
      ILLUMINATION_MODE
      MAINS_FREQUENCY
      } */
    //-------------------------------VIDEO--OUTPUT---------------------------------//
    /* {
      OUTPUT
      VIDEO_OUT_STANDARD
      MONITOR_NAME
      VID_DECODER_ON
      LOGO
      DEC_SHOW_FREEZE
      DECODER_MODE
      DECODER_LAYOUT_LIST
      DECODER_LAYOUT
      VIDEO_OUT_STANDARD_SPEC
      VIDEO_OUT_CURRENT_SPEC
      VIDEO_OUT_MONITOR_SPEC
      VIDEO_OUT_OVERSCAN
      DECODED_FRAMES
      NBR_OF_VIDEO_OUT} */

    //-------------------------------------OSD------------------------------------//
    /* {
      ENABLE_OSD
      OSD_ACCESS
      OSD_POS
      } */

    //-------------------------------------AUDIO----------------------------------//
    /* {
      AUDIO_INPUT_LEVEL
      AUDIO_OUTPUT_LEVEL
      AUDIO_ON_OFF
      AUDIO_STARTUP_SOUND
      AUDIO_INPUT
      AUDIO_OUTPUT
      AUDIO_INPUT_MAX
      AUDIO_OUTPUT_MAX
      AUDIO_MIC_LEVEL
      AUDIO_MIC_MAX
      AUDIO_LOUDSPEAKER_ON_OFF
      AUDIO_OPTIONS
      AUDIO_INPUT_PEEK
      AUDIO_OUTPUT_PEEK
      NBR_OF_AUDIO_OUT
      NBR_OF_AUDIO_IN
      } */

    //--------------------------------ALARM-SOURCE

    const TAG_ALARM_INPUT_LH_VAL = 0x008d; // R/W Input active low/high num:Alarm Input
    const TAG_INPUT_PIN_NAME = 0x0108; // R/W Input Name (P_UNICODE) num:Alarm Input             **
    /* {
      VCD_OPERATOR_PARAMS
     *
     */
    const TAG_VIPROC_RE_TASK_NAMES = 0x0b2b; // R/W VCA Task Name & Typ

    /*
      CAMERA_MOTION_ALARM_VAL

      ALARM_CONNECT_TO_IP_STR
      ALARM_CONNECT_TO_IP
      NBR_OF_ALTERNATIVE_ALARM_IPS
      AUTO_DISCONNECT_TIME
      DEFAULT_CONNECTION_MODE
      STD_MEDIA_CONNECTION_DIRECTION
      STD_MEDIA_ENCAPSULATION_PROTOKOL
      DEFAULT_CAM
      ALARM_CONNECTION_DESTINATION_PORT
      ALARM_CONNECTION_USE_SSL
      CONNECT_URL
      } */
    //------------------------------DIGITAL-IO
    const TAG_VIDEO_ALARM_STATE = 0x01c2; // only read (FLAG) 0=off; 1=Alarm num: Video Line
    const TAG_INPUT_PIN_STATE = 0x01c0; // only Read (FLAG) 0=off, 1=Alarm num:Alarm Input  **
    const TAG_MOTION_ALARM_STATE = 0x01c3; // only Read (FLAG) 0=off, 1=Alarm (alles außer Video loss) num:Video Line
    const TAG_RELAY_OUTPUT_STATE = 0x01c1; // R/W (FLAG) num:Relay                             **
    const TAG_NBR_OF_ALARM_IN = 0x01db; // only Read (T_DWORD)                              **
    const TAG_NBR_OF_ALARM_OUT = 0x01dc; // only Read (T_DWORD)                              **
    const TAG_NBR_OF_MOTION_DETECTORS = 0x09af; // only Read (T_DWORD)
// HD_MGR_SIGNAL_ALARM
    const TAG_VIRTUAL_ALARM_STATE = 0x0a8b; // R/W(FLAG) 0=off, 1=Alarm num:Virtual Alarm Input **
    const TAG_SET_VIRTUAL_ALARM_ID = 0x0b41; // R/W (P_UNICODE) 32char   num:Virtual Alarm Input
    const TAG_NBR_OF_VIRTUAL_ALARMS = 0x0aed; // only Read (T_DWORD)
    const TAG_MANIPULATION_ALARM_STATE = 0x0af0; //only Read (FLAG) 0=off, 1=Alarm num: Manipul Alarm Input
    const TAG_NBR_OF_MANIPULATION_ALARMS = 0x0af1; // only Read (T_DWORD)
    const TAG_RELAIS_NAME = 0x0109; // R/W Input Name (P_UNICODE) num:Relay             **
//RELAIS_SWITCH
//------------------------------SERIAL
    const TAG_SERIAL_PORT_APP_VAL = 0x01f1;
    const TAG_SERIAL_PORT_RATE = 0x027e;
    const TAG_SERIAL_PORT_BITS = 0x027f;
    const TAG_SERIAL_PORT_STBITS = 0x0280;
    const TAG_SERIAL_PORT_PAR = 0x0281;
    const TAG_SERIAL_PORT_MODE_VAL = 0x0208;
    const TAG_SERIAL_PORT_HD_MODE_VAL = 0x020b;
    /* {SERIAL_PORT_HANDSHAKE                                 //
      SERIAL_ACTS_ACCESS_RIGHTS                             //
      BICOM_COMMAND
      BICOM_SRV_CONNECTED
      BICOM_UPLOAD_PACKET
      BICOM_SUBCOMPONENTS_LIST
      KBD_CONFIG_CAMERA
      KBD_CONFIG_MONITOR
      KBD_CONNECT_PARAMS
      KBD_PASSWORD_CAMERA
      KBD_PASSWORD
      KBD_CONFIG_SALVO
      KBD_KEY_LABEL
      KBD_SET_ALARM
      } */
    //------------------------------NETWORK
    /* {
      MAC_ADDRESS
      IP
      IP_STR
      SUBNET
      GATEWAY_IP_V6_STRING
      IP_V6_PREFIX_LEN
      IP_V6_STR
      GATEWAY_IP_STR
      DNS_SERVER_IP
      DNS_SERVER_IP_STRING
      ETH_LINK
      } */
    const TAG_ETH_LINK_STATUS = 0x092d;
    /* {
      ETH_LINK_TROUGHPUT
      ETH_TX_PKT_BURST} */
    const TAG_NBR_OF_EXT_ETH_PORTS = 0x0a28;
    /* {NBR_OF_EXT_ETH_COPPER_PORTS
      NBR_OF_EXT_ETH_FIBER_PORTS
      ENABLE_TRAFFIC_LED
      SND_MSS
      SND_MSS_ISCSI
      EAP_IDENTITY
      EAP_ENABLE
      EAP_PASSWORD
      SWITCH_TRUNKING
      SWITCH_LACP_ENABLE
      SWITCH_LACP_KEY
      SWITCH_RSTP_PRIO
      SWITCH_RSTP_HELLO_TIME
      SWITCH_RSTP_MAX_AGE
      SWITCH_RSTP_FWD_DELAY
      SWITCH_RSTP_VERSION
      SWITCH_RSTP_PORT_ENABLE
      SWITCH_RSTP_PORT_EDGE
      SWITCH_RSTP_PORT_PATHCOST
      BACKPLANE_TYPE
      BACKPLANE_FW_VERSION
      SWITCH_IGMP_IP
      SWITCH_IGMP_SNOOPING_ENABLE
      SWITCH_MAC_IS_SET
      REBOOT_SWITCH
      WLAN_SSID
      WLAN_WPA_PSK
      PORT_FC_MODE
      WLAN_SCAN
      WLAN_LINK_TEST
      WLAN_OPERATING_MODE
      SWITCH_POST_UPDATE_ACTION
      WLAN_REGION_CODE
      WLAN_LINK_QUALITY
      FTP_SERVER_IP
      FTP_SERVER_IP_STR
      FTP_SERVER_LOGIN
      FTP_SERVER_PASSWORD
      FTP_SERVER_PATH
      FTP_PWD
      FTP_LIST
      FTP_CWD
      FTP_CDUP
      FTP_START_CLIENT
      FTP_STOP_CLIENT
      FTP_FILE_NAME
      FTP_LOGIN_TEST
      DIFF_SERV_VAL
      DIFF_SERV_POST_ALARM_TIME
      IPV4_FILTER
      } */
    //DDNS
    /* {
      DYNDNS_SERVER
      DYNDNS_TIMEOUT
      DYNDNS_SERVER_REPLY
      } */
    //DYNDNS
    /* {
      DYNDNS_HOST_NAME
      DYNDNS_USER_NAME
      DYNDNS_PASSWORD
      DYNDNS_ENABLE
      DYNDNS_STATE
      DYNDNS_LAST_REGISTER
      DYNDNS_FORCE_REGISTER_NOW
      } */
    //DISCOVERY
    /* {
      AUTODETECT_REPLY_GROUP
      UNSOLICITED_AUTODETECT_REPLY_TIME
      DISCOVER_PORT
      } */
    //HTTP
    /* {
      LOCAL_HTTP_PORT
      LOCAL_HTTPS_PORT
      RCP_SERVER_PORT
      TELNET_PORT
      RTSP_PORT
      ENABLE_UPNP
      GET_RTSP_SESSION_ID
      TCP_FWD
      ADD_DEVICE
      } */
    //DHCP
    /* {
      DHCP_VAL
      DHCP_ON
      DHCP_OFF
      DHCP_STABLE
      DHCP_COMPLIANCY
      } */
    //ISCSI
    /* {
      ISCSI_IP
      ISCSI_PORT
      ISCSI_LUN
      ISCSI_TARGET_IDX
      ISCSI_TARGET
      ISCSI_TCP_CONNECTIONS
      ISCSI_DISCOVERY
      ISCSI_TARGET_PWD
      ISCSI_LOCK_OVERRIDE
      ISCSI_LOCK_RELEASE_ON_LEAVE
      ISCSI_INITIATOR_NAME
      ISCSI_INITIATOR_NAME_EXTENTION
      ISCSI_SERVER_STATE
      ISCSI_MNI
      ISCSI_AUTH
      ISCSI_SEG_SIZE
      ISCSI_DATARATE
      ISCSI_LOWERDATARATE
      ISCSI_READDATARATE
      } */
    //---------------------------------RECORD
    /* {
      HD_MAX_NUMBER_OF_PARTITIONS                         //
      HD_MAX_SLICES_PER_TRACK                             //
      HD_MAX_ALARM_TRACKS_PER_PARTITION                   //
      HD_PARTITIONS_RECORDING
      HD_PARTITION_RECORDING
      HD_PARTITION_RECORDING_SECONDARY
      HD_PARTITION_FILE_INFO                              //
      HD_PARTITION_PROP
      HD_PARTITION_PROP_SECONDARY
      HD_SIZE_MB                                          //
      HD_PARTITION_GEO                                    //
      HD_RECORD_SCHEDULE                                  //
      HD_RECORD_SCHEDULE_SECONDARY
      HD_RECORD_HOLIDAYS
      HD_RECORD_PROFILES
      HD_RECORD_PROFILES_SECONDARY
      HD_RECORD_PROFILES_V2
      HD_RECORD_PROFILES_V2_SECONDARY
      HD_ALARM_MOTION                                     //
      HD_ALARM_INPUT                                      //
      HD_MGR_START                                        //
      HD_MGR_START_SECONDARY                              //
      HD_MGR_STOP                                         //
      HD_MGR_STOP_SECONDARY                               //
      HD_MPEG4_ACTIVE
      HD_RECORDING_REPORT                                 //
      HD_RECORDING_REPORT_SECONDARY                       //
      RECORDING_STATUS                                    //
      RECORDING_RETENTION_TIME
      RECORDING_RETENTION_TIME_SECONDARY
      REMOTE_REC_DEVICE                                   //
      REC_MGNT
      } */

    const TAG_HD_MGR_REC_STATUS = 0x0aae; // read only p_octet
    const TAG_HD_MGR_REC_STATUS_SECONDARY = 0x0aaf;
    const TAG_HD_RECORDING_ACTIVE = 0x0908;

    /* {
      HD_FILE_INFO
      HD_FILE_INFO_SECONDARY
      HD_RELOAD_PARTITION_FILE_INFO
      } */
    /* {
      START_RECORD
      SET_REC_BUFFER_SIZE
      AUDIO_REC_FORMAT
      MANAGING_VRM
      REC_STORAGE_REQ_CFG
      } */
    //Replay
    //HD_REPLAY_START
    const TAG_HD_REPLAY_STOP = 0x0903;

    /* {
      HD_REPLAY_STOP_TIME
      HD_REPLAY_SEEK_TIME
      HD_REPLAY_SEEK_IFRAME
      HD_REPLAY_EVENT_INFO
      HD_REPLAY_PARTITION_EVENT_INFO
      HD_REPLAY_MOTION_SAMPLES
      HD_REPLAY_FAST_INTRA_DELAY
      HD_REPLAY_FAST_INTRA_FPS
      HD_REPLAY_LIVE
      HD_REPLAY_SIZE_INFO
      HD_REPLAY_VCD_LAYER
      HD_REPLAY_VCD_CONFIG_ID
      HD_REPLAY_FORENSIC_SEARCH_SETUP
      HD_REPLAY_FORENSIC_SEARCH_RESULT
      BACKUP_STATUS
      } */

    public static function ToString($RCPTag)
    {
        switch ($RCPTag) {
            case self::TAG_CLIENT_REGISTRATION:
                return 'CLIENT_REGISTRATION';
            case self::TAG_CLIENT_UNREGISTER:
                return 'CLIENT_UNREGISTER';
            case self::TAG_CLIENT_TIMEOUT_WARNING:
                return 'CLIENT_TIMEOUT_WARNING';
            case self::TAG_REG_MD5_RANDOM:
                return 'TAG_REG_MD5_RANDOM';
            case self::TAG_TRANSFER_TRANSPARENT_DATA:
                return 'TRANSFER_TRANSPARENT_DATA';
            case self::TAG_CAPABILITY_LIST:
                return 'CAPABILITY_LIST';
            case self::TAG_UNIT_NAME:
                return 'UNIT_NAME';
            case self::TAG_UNIT_ID:
                return 'UNIT_ID';
            case self::TAG_CAMNAME:
                return 'CAMNAME';
            case self::TAG_CAMNAME2:
                return 'CAMNAME2';
            case self::TAG_HARDWARE_VERSION:
                return 'HARDWARE_VERSION';
            case self::TAG_SOFTWARE_VERSION:
                return 'SOFTWARE_VERSION';
            case self::TAG_BOOTLOADER_VERSION:
                return 'BOOTLOADER_VERSION';
            case self::TAG_SERIAL_NUMBER:
                return '';
            case self::TAG_CLUSTER_ID:
                return 'CLUSTER_ID';
            case self::TAG_CTN:
                return 'CTN';
            case self::TAG_TEMP_SENS:
                return 'TEMP_SENS';
            case self::TAG_NBR_OF_TEMP_SENS:
                return 'NBR Temp Sens';
            case self::TAG_NBR_OF_FANS:
                return 'NBR Fans';
            case self::TAG_FAN_SPEED:
                return 'Fan Speed';
            case self::TAG_MINIMUM_FAN_SPEED:
                return 'Fan Speed Minimum';
            case self::TAG_VIPROC_ALARM:
                return 'VIPProc Alarm';
            case self::TAG_VIPROC_ID:
                return 'VIPProc ID';
            case self::TAG_CPU_LOAD_IDLE:
                return 'CPU_IDLE';
            case self::TAG_CPU_LOAD_CODER:
                return 'CPU_CODER';
            case self::TAG_CPU_LOAD_CODER_INST:
                return 'CPU_CODER_INST';
            case self::TAG_CPU_LOAD_VCA:
                return 'CPU_VCA';
            case self::TAG_CPU_LOAD:
                return 'CPU_LOAD';
            case self::TAG_CPU_COUNT:
                return 'NBR CPU';
            case self::TAG_NBR_OF_VIDEO_IN:
                return 'NBR Video In';
            case self::TAG_ALARM_INPUT_LH_VAL:
                return 'Input Low/high';
            case self::TAG_INPUT_PIN_NAME:
                return 'Input Name';
            case self::TAG_VIDEO_ALARM_STATE:
                return 'Video Alarm State';
            case self::TAG_INPUT_PIN_STATE:
                return 'Input State';
            case self::TAG_MOTION_ALARM_STATE:
                return 'Motion Alarm State';
            case self::TAG_RELAY_OUTPUT_STATE:
                return 'Relay State';
            case self::TAG_NBR_OF_ALARM_IN:
                return 'NBR Alarm in';
            case self::TAG_NBR_OF_ALARM_OUT:
                return 'NBR Alarm out';
            case self::TAG_NBR_OF_MOTION_DETECTORS:
                return 'NBR Motion detect';
            case self::TAG_VIRTUAL_ALARM_STATE:
                return 'VIRTUAL_ALARM_STATE';
            case self::TAG_NBR_OF_VIRTUAL_ALARMS:
                return 'NBR Virtual Alarm';
            case self::TAG_SET_VIRTUAL_ALARM_ID:
                return 'Virtual Alarm Data Send';
            case self::TAG_MANIPULATION_ALARM_STATE:
                return 'Manip Alarm State';
            case self::TAG_NBR_OF_MANIPULATION_ALARMS:
                return 'NBR Manip Alarm';
            case self::TAG_RELAIS_NAME:
                return 'Relay Name';
            case self::TAG_SERIAL_PORT_APP_VAL:
                return 'SERIAL_PORT_APP_VAL';
            case self::TAG_SERIAL_PORT_RATE:
                return 'SERIAL_PORT_RATE';
            case self::TAG_SERIAL_PORT_BITS:
                return 'SERIAL_PORT_BITS';
            case self::TAG_SERIAL_PORT_STBITS:
                return 'SERIAL_PORT_STBITS';
            case self::TAG_SERIAL_PORT_PAR:
                return 'SERIAL_PORT_PAR';
            case self::TAG_SERIAL_PORT_MODE_VAL:
                return 'SERIAL_PORT_MODE_VAL';
            case self::TAG_SERIAL_PORT_HD_MODE_VAL:
                return 'SERIAL_PORT_HD_MODE_VAL';
            case self::TAG_HD_MGR_REC_STATUS:
                return '';
            case self::TAG_HD_MGR_REC_STATUS_SECONDARY:
                return '';
            case self::TAG_HD_RECORDING_ACTIVE:
                return '';
            case self::TAG_HD_REPLAY_STOP:
                return '';
            case self::TAG_ETH_LINK_STATUS:
                return 'ETH_LINK_STATUS';
            case self::TAG_NBR_OF_EXT_ETH_PORTS:
                return 'NBR_OF_EXT_ETH_PORTS';
            default:
                return '';
        }
    }
}

class RCPDataType
{
    const RCP_F_FLAG = 0x00; //payload: (1 Byte)
    const RCP_T_OCTET = 0x01; //payload: (1 Byte)
    const RCP_T_WORD = 0x02; //payload: (2 Byte)
    const RCP_T_INT = 0x04; //payload: (4 Byte)
    const RCP_T_DWORD = 0x08; //payload: (4 Byte)
    const RCP_P_OCTET = 0x0C; //payload: (N Byte)
    const RCP_P_STRING = 0x10; //payload: (N Byte)
    const RCP_P_UNICODE = 0x14; //payload: (N Byte)

    public static function ToString($DataType)
    {
        switch ($DataType) {

            case self::RCP_F_FLAG:
                return 'F_FLAG';
            case self::RCP_T_OCTET:
                return 'T_OCTET';
            case self::RCP_T_WORD:
                return 'T_WORD';
            case self::RCP_T_INT:
                return 'T_INT';
            case self::RCP_T_DWORD:
                return 'T_DWORD';
            case self::RCP_P_OCTET:
                return 'P_OCTET';
            case self::RCP_P_STRING:
                return 'P_STRING';
            case self::RCP_P_UNICODE:
                return 'P_UNICODE';
            default:
                return '';
        }
    }
}

class RCPReadWrite
{
    const RCP_DO_READ = 0x30;
    const RCP_DO_WRITE = 0x31;

    public static function ToString($ReadWrite)
    {
        switch ($ReadWrite) {
            case self::RCP_DO_READ:
                return 'READ';
            case self::RCP_DO_WRITE:
                return 'WRITE';
            default:
                return '';
        }
    }
}

class RCPAction
{
    const RCP_Request = 0x00;
    const RCP_Reply = 0x01;
    const RCP_Message = 0x02;
    const RCP_Error = 0x03;

    public static function ToString($Action)
    {
        switch ($Action) {
            case self::RCP_Request:
                return'Request';
            case self::RCP_Reply:
                return 'Reply';
            case self::RCP_Message:
                return 'Message';
            case self::RCP_Error:
                return 'Error';
            default:
                return '';
        }
    }
}

class RCPError
{
    const RCP_ERROR_NO_ERROR = 0x00;
    const RCP_ERROR_CANNOT_SEND = 0xFE;
    const RCP_ERROR_REPLY_TIMEOUT = 0xFD;
    const RCP_ERROR_SEND_ERROR = 0xFC;
    const RCP_ERROR_UNKNOWN = 0xFF;
    const RCP_ERROR_INVALID_VERSION = 0x10;
    const RCP_ERROR_NOT_REGISTERED = 0x20;
    const RCP_ERROR_INVALID_CLIENT_ID2 = 0x15;
    const RCP_ERROR_INVALID_CLIENT_ID = 0x21;
    const RCP_ERROR_INVALID_METHOD = 0x30;
    const RCP_ERROR_INVALID_CMD = 0x40;
    const RCP_ERROR_INVALID_ACCESS_TYPE = 0x50;
    const RCP_ERROR_INVALID_DATA_TYPE = 0x60;
    const RCP_ERROR_WRITE_ERROR = 0x70;
    const RCP_ERROR_PACKET_SIZE = 0x80;
    const RCP_ERROR_READ_NOT_SUPPORTED = 0x90;
    const RCP_ERROR_INVALID_AUTH_LEVEL = 0xa0;
    const RCP_ERROR_INVAILD_SESSION_ID = 0xb0;
    const RCP_ERROR_TRY_LATER = 0xc0;
    const RCP_ERROR_TIMEOUT = 0xd0;
    const RCP_ERROR_COMMAND_SPECIFIC = 0xf0;
    const RCP_ERROR_ADDRESS_FORMAT = 0xf1;

    public static function ToString($Error)
    {
        switch ($Error) {
            case self::RCP_ERROR_NO_ERROR:
                return '';
            case self::RCP_ERROR_CANNOT_SEND:
                return 'No active parent';
            case self::RCP_ERROR_REPLY_TIMEOUT:
                return 'Timeout';
            case self::RCP_ERROR_SEND_ERROR:
                return 'Send error';
            case self::RCP_ERROR_UNKNOWN:
                return 'Unkown Error';
            case self::RCP_ERROR_INVALID_VERSION:
                return 'invalid Version';
            case self::RCP_ERROR_NOT_REGISTERED:
                return 'not registered';
            case self::RCP_ERROR_INVALID_CLIENT_ID2:
                return 'invalid ClientId2';
            case self::RCP_ERROR_INVALID_CLIENT_ID:
                return 'invalid ClientId';
            case self::RCP_ERROR_INVALID_METHOD:
                return 'invalid Method';
            case self::RCP_ERROR_INVALID_CMD:
                return 'invalid Command';
            case self::RCP_ERROR_INVALID_ACCESS_TYPE:
                return 'invalid access type';
            case self::RCP_ERROR_INVALID_DATA_TYPE:
                return 'invalid data type';
            case self::RCP_ERROR_WRITE_ERROR:
                return 'write error';
            case self::RCP_ERROR_PACKET_SIZE:
                return 'invalid packet size';
            case self::RCP_ERROR_READ_NOT_SUPPORTED:
                return 'read not supported';
            case self::RCP_ERROR_INVALID_AUTH_LEVEL:
                return 'invalid access level';
            case self::RCP_ERROR_INVAILD_SESSION_ID:
                return 'invalid session id';
            case self::RCP_ERROR_TRY_LATER:
                return 'try later';
            case self::RCP_ERROR_TIMEOUT:
                return 'Timeout';
            case self::RCP_ERROR_COMMAND_SPECIFIC:
                return 'Specific Error';
            case self::RCP_ERROR_ADDRESS_FORMAT:
                return 'Adress format Error';
            default:
                return '';
        }
    }
}

class RCPData
{
    /**
     * toSplitter.
     */
    const IIPSSendBVIPData = '{0E1D027B-E420-4F8E-91E7-59FFA485F8FD}';

    /**
     * toDevices.
     */
    const IIPSReceiveBVIP = '{B974BF3E-45B8-4648-BC7F-952AC79929B7}';

    public $DataID;

    /**
     * @var RCPTag
     */
    public $Tag;

    /**
     * @var RCPDataType
     */
    public $DataType;

    /**
     * @var RCPReadWrite
     */
    public $RW;

    /**
     * @var int
     */
    public $Num = 0;

    /**
     * @var string|bool|int
     */
    public $Payload = '';

    /**
     * @var RCP
     */
    public $Error = RCPError::RCP_ERROR_NO_ERROR;

    public function FromJSONString($JSONString)
    {
        $Data = json_decode($JSONString);
        /* @var $Data RCPData */
        $this->DataType = $Data->DataType;
        $this->Num = $Data->Num;
        $this->Tag = $Data->Tag;
        $this->RW = $Data->RW;
        $this->Payload = $Data->Payload;
        unset($this->DataID);
    }

    public function FromRCPFrame(RCPFrame $RCPFrame)
    {
        $this->Tag = $RCPFrame->Tag;
        $this->DataType = $RCPFrame->DataType;
        $this->RW = $RCPFrame->RW;
        $this->Num = $RCPFrame->Num;
        //$this->Payload = $RCPFrame->Payload;
        if ($RCPFrame->Action == RCPAction::RCP_Error) {
            $this->Error = ord($RCPFrame->Payload[0]);
        } else {
            $this->Error = RCPError::RCP_ERROR_NO_ERROR;
            switch ($this->DataType) {
                case RCPDataType::RCP_F_FLAG:
                    if (strlen($RCPFrame->Payload) != 1) {
                        $this->Error = RCPError::RCP_ERROR_PACKET_SIZE;
                    } else {
                        $this->Payload = (bool) (ord($RCPFrame->Payload[0]));
                    }
                    break;

                case RCPDataType::RCP_T_OCTET:
                    if (strlen($RCPFrame->Payload) != 1) {
                        $this->Error = RCPError::RCP_ERROR_PACKET_SIZE;
                    } else {
                        $this->Payload = ord($RCPFrame->Payload[0]);
                    }
                    break;
                case RCPDataType::RCP_T_WORD:
                    if (strlen($RCPFrame->Payload) != 2) {
                        $this->Error = RCPError::RCP_ERROR_PACKET_SIZE;
                    } else {
                        $this->Payload = unpack('n', $RCPFrame->Payload)[1];
                    }
                    break;
                case RCPDataType::RCP_T_INT:
                case RCPDataType::RCP_T_DWORD:
                    if (strlen($RCPFrame->Payload) != 4) {
                        $this->Error = RCPError::RCP_ERROR_PACKET_SIZE;
                    } else {
                        $this->Payload = unpack('N', $RCPFrame->Payload)[1];
                    }
                    break;
                case RCPDataType::RCP_P_STRING:
                    $end = strpos($RCPFrame->Payload, chr(0));
                    $this->Payload = substr($RCPFrame->Payload, 0, $end);
                    break;
                case RCPDataType::RCP_P_UNICODE:
                    $this->Payload = mb_convert_encoding($RCPFrame->Payload, 'UTF-8', 'UTF-16');
                    $end = strpos($this->Payload, chr(0));
                    $this->Payload = substr($this->Payload, 0, $end);
                    break;
                case RCPDataType::RCP_P_OCTET:
                    $this->Payload = $RCPFrame->Payload;
                    break;
            }
        }
        $this->DataID = self::IIPSReceiveBVIP;
    }
}

class RCPFrame
{
    /**
     * @var RCPTag
     */
    public $Tag;

    /**
     * @var RCPDataType
     */
    public $DataType;

    /**
     * @var RCPReadWrite
     */
    public $RW;

    /**
     * @var int
     */
    public $Num = 0;

    /**
     * @var string
     */
    public $Payload = '';
    public $Continuation = false;

    /**
     * @var RCPAction
     */
    public $Action;

    /**
     * @var int
     */
    public $Reserved;

    /**
     * @var int
     */
    public $ClientID = "\x00\x00";

    /**
     * @var int
     */
    public $SessionID = "\x00\x00\x00\x00";

    public function __construct($Data)
    {
        if (is_a($Data, 'RCPData')) {
            $this->Tag = $Data->Tag;
            $this->DataType = $Data->DataType;
            $this->RW = $Data->RW;
            $this->Num = $Data->Num;
            $this->Action = RCPAction::RCP_Request;
            switch ($Data->DataType) {
                case RCPDataType::RCP_F_FLAG:
                    if ($Data->Payload === true) {
                        $this->Payload = chr(0x01);
                    } else {
                        $this->Payload = chr(0x00);
                    }
                    break;

                case RCPDataType::RCP_T_OCTET:
                    $this->Payload = chr($Data->Payload);
                    break;
                case RCPDataType::RCP_T_WORD:
                    $this->Payload = pack('n', $Data->Payload);
                    break;
                case RCPDataType::RCP_T_INT:
                case RCPDataType::RCP_T_DWORD:
                    $this->Payload = pack('N', $Data->Payload);
                    break;
                case RCPDataType::RCP_P_STRING:
                    $this->Payload = $Data->Payload.chr(0);
                    break;
                case RCPDataType::RCP_P_UNICODE:
                    $this->Payload = mb_convert_encoding($Data->Payload, 'UTF-16', 'UTF-8').chr(0).chr(0);
                    break;

                case RCPDataType::RCP_P_OCTET:
                    $this->Payload = $Data->Payload;
                    break;
            }
        } elseif (is_string($Data)) {
            // Datapaket zerlegen in RCPFrame
            $this->Tag = unpack('n', substr($Data, 0, 2))[1];
            $this->DataType = ord($Data[2]);
            $this->RW = ord($Data[3]);
            $this->Action = ord($Data[4]) & 0xEF;
            $this->Continuation = (bool) (ord($Data[4]) >> 7);
            $this->Reserved = ord($Data[5]);
            $this->ClientID = substr($Data, 6, 2);
            $this->SessionID = substr($Data, 8, 4);
            $this->Num = unpack('n', substr($Data, 12, 2))[1];
            $len = unpack('n', substr($Data, 14, 2))[1];
            $this->Payload = substr($Data, 16, $len); //RAW Payload
        }
    }

    public function ToJSONStringForIO()
    {
        $SendData = new stdClass();
        $SendData->DataID = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        $SendData->Buffer = utf8_encode($this->EncodeFrame());

        return json_encode($SendData);
    }

    public function EncodeFrame()
    {
        //RCPFrame in Stream bauen.
        $RCP_Frame = pack('n', $this->Tag);
        $RCP_Frame .= chr($this->DataType);
        $RCP_Frame .= chr($this->RW);
        $RCP_Frame .= chr($this->Action);
        $RCP_Frame .= chr($this->Reserved);
        $RCP_Frame .= $this->ClientID;
        $RCP_Frame .= $this->SessionID;
        $RCP_Frame .= pack('n', $this->Num);
        $RCP_Frame .= pack('n', strlen($this->Payload));
        $RCP_Frame .= $this->Payload;

        $TCP_Frame = chr(0x03);
        $TCP_Frame .= chr(0x00);
        $TCP_Frame .= pack('n', 4 + strlen($RCP_Frame));
        $TCP_Frame .= $RCP_Frame;

        return $TCP_Frame;
    }
}
