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

require_once(__DIR__ . '/helpers/install.php');

class plgContentPlg_ZhGoogleMapInstallerScript {
    
    function doInit()
    {
        plgZhGoogleMapInstallHelper::setExtensionName('plg_zhgooglemap');
        plgZhGoogleMapInstallHelper::setExtensionType('plugin');
        plgZhGoogleMapInstallHelper::setExtensionFolder('content');

        //plgZhGoogleMapInstallHelper::setDebugMode(false);    
        //plgZhGoogleMapInstallHelper::setDebugInfo(1);
    }
    
    /**
     * method to install the plugin
     *
     * @return void
     */
    function install($parent) 
    {
    }
 
    /**
     * method to uninstall the plugin
     *
     * @return void
     */
    function uninstall($parent) 
    {
    }
 
    /**
     * method to update the plugin
     *
     * @return void
     */
    function update($parent) 
    {
        $manifest = $parent->getParent()->getManifest();
    }
 
    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent) 
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        plgContentPlg_ZhGoogleMapInstallerScript::doInit();
    }
 
    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        plgZhGoogleMapInstallHelper::addLog('<h4>Check plugin status</h4>', 4, 0, 0);
        
        $extID = plgZhGoogleMapInstallHelper::getExtensionID();
        
        if (isset($extID) && $extID != "")
        {
            //JFactory::getApplication()->enqueueMessage('Plugin is installed!', 'notice');
            $extStatus = plgZhGoogleMapInstallHelper::getExtensionEnabledStatus();
            if (isset($extStatus) && $extStatus != "")
            {
                if ((int)$extStatus == 1)
                {
                    //JFactory::getApplication()->enqueueMessage('Plugin is enabled!', 'message');
                    plgZhGoogleMapInstallHelper::addLog('<h5>plugin is enabled<h5>', 3, 0, 0);
                }
                else
                {
                    JFactory::getApplication()->enqueueMessage('You should enable plugin!', 'warning');
                }
            }
            else
            {
                JFactory::getApplication()->enqueueMessage('Unable to get plugin status!', 'error');
            }
        }
        else
        {
            JFactory::getApplication()->enqueueMessage('Plugin is not installed!', 'error');
        }
        
        plgZhGoogleMapInstallHelper::addLog('<h4>Plugin status checked</h4>', 4, 0, 0);
        
        /*
        
        $extID = plgZhGoogleMapInstallHelper::getExtensionIDPrams('package', 'pkg_zhgooglemap', '');
        
        plgZhGoogleMapInstallHelper::addLog('<h4>Check package installation</h4>', 4, 0, 0);
        if (isset($extID) && $extID != "")
        {
        }
        else
        {
            JFactory::getApplication()->enqueueMessage('You have to install package to get new extensions!', 'warning');
        }
        plgZhGoogleMapInstallHelper::addLog('<h4>Package installation checked</h4>', 4, 0, 0);
        
        */

    
    }    
    
    

}