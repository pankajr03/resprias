<?php
/*------------------------------------------------------------------------
# plg_zhgooglemap - Zh GoogleMap Plugin
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


abstract class plgZhGoogleMapInstallHelper
{

    private static $extension_name = '';
    private static $extension_type = '';
    private static $extension_folder = '';

    // ************** section for debug **************
    // section for debug
    
    // allow debug log messages
    private static $script_debug_mode = false;
    
    // message offset
    private static $script_debug_message_level = 0;
    
    // view message level
    // 0 - all messages
    // 1 - 
    // 2 - 
    private static $script_debug_info = 10;
    // **********************************************
    
    public static function setExtensionName($pi_value) 
    {
        self::$extension_name = $pi_value;
    }    


    public static function setExtensionType($pi_value) 
    {
        self::$extension_type = $pi_value;
    }    

    public static function setExtensionFolder($pi_value) 
    {
        self::$extension_folder = $pi_value;
    }    
    

    public static function setDebugMode($pi_value) 
    {
        self::$script_debug_mode = $pi_value;
    }        
    
    public static function setDebugInfo($pi_value) 
    {
        self::$script_debug_info = (int)$pi_value;
    }    
    
    public static function addLog($text, $message_level, $change_level_before, $change_level_after )    
    {

        if (self::$script_debug_mode 
        && isset($text) && $text != "" && isset($message_level) 
        && (int)$message_level >= (int)self::$script_debug_info )
        {
    
            if (isset($change_level_before)
            && ((int)$change_level_before == 1 || (int)$change_level_before == 0 || (int)$change_level_before == -1))
            {
                self::$script_debug_message_level = self::$script_debug_message_level + (int)$change_level_before;
                
                
                if ((int)self::$script_debug_message_level < 0)
                {
                    self::$script_debug_message_level = 0;
                }
            }    
            
            echo '<p>' . str_pad(' ', self::$script_debug_message_level*3, ".", STR_PAD_LEFT) . $text .'</p>';
            
            if (isset($change_level_after)
            && ((int)$change_level_after == 1 || (int)$change_level_after == 0 || (int)$change_level_after == -1))
            {
                self::$script_debug_message_level = self::$script_debug_message_level + (int)$change_level_after;
                
                
                if ((int)self::$script_debug_message_level < 0)
                {
                    self::$script_debug_message_level = 0;
                }
            }                
        }
    }

    public static function getExtensionObject()
    {
        return plgZhGoogleMapInstallHelper::getExtensionObjectParams(self::$extension_type, self::$extension_name, self::$extension_folder);
    }
    
    public static function getExtensionID()
    {
        return plgZhGoogleMapInstallHelper::getExtensionIDParams(self::$extension_type, self::$extension_name, self::$extension_folder);
    }

    public static function getExtensionEnabledStatus()
    {
        return plgZhGoogleMapInstallHelper::getExtensionEnabledStatusParams(self::$extension_type, self::$extension_name, self::$extension_folder);
    }
    
    public static function getExtensionObjectParams($type, $name, $folder)
    {
        plgZhGoogleMapInstallHelper::addLog('Executing: getExtensionObject', 1, 0, 1);
        
        plgZhGoogleMapInstallHelper::addLog('type:'. $type, 0, 0, 0);
        plgZhGoogleMapInstallHelper::addLog('name:'. $name, 0, 0, 0);
        plgZhGoogleMapInstallHelper::addLog('folder:'. $folder, 0, 0, 0);
        
        $db = JFactory::getDbo();
        
        $querytext = 'SELECT extension_id, enabled FROM #__extensions WHERE 1=1';
        if (isset($type) && $type != "")
        {
            $querytext .= " and type='" . $type ."'";
        }
        if (isset($name) && $name != "")
        {
            $querytext .= " and element='" . $name ."'";
        }    
        if (isset($folder) && $folder != "")
        {
            $querytext .= " and folder='" . $folder ."'";
        }            
        
        $db->setQuery($querytext);
        $extensionobject = $db->loadObject();
        
        plgZhGoogleMapInstallHelper::addLog('done getExtensionObject', 1, 0, -1);

        return $extensionobject;

    }
    
    public static function getExtensionIDParams($type, $name, $folder)
    {
        plgZhGoogleMapInstallHelper::addLog('Executing: getExtensionID', 1, 0, 1);
        $extObj = plgZhGoogleMapInstallHelper::getExtensionObject($type, $name, $folder);
        
        if (isset($extObj))
        {
            $extensionid = $extObj->extension_id;
        }
        else
        {
            $extensionid = '';
        }
        
        plgZhGoogleMapInstallHelper::addLog('done getExtensionID', 1, 0, -1);
        return $extensionid;

    }
    
    public static function getExtensionEnabledStatusParams($type, $name, $folder)
    {
        plgZhGoogleMapInstallHelper::addLog('Executing: getExtensionEnabledStatus', 1, 0, 1);
        $extObj = plgZhGoogleMapInstallHelper::getExtensionObject($type, $name, $folder);
        
        if (isset($extObj))
        {
            $extensionstatus = $extObj->enabled;
        }
        else
        {
            $extensionstatus = '';
        }

        plgZhGoogleMapInstallHelper::addLog('done getExtensionEnabledStatus', 1, 0, -1);
        return $extensionstatus;

    }    


    
}