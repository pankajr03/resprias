<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class com_ZhGoogleMapInstallerScript {
    

    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) 
    {
        
    }
 
    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) 
    {
        
    }
 
    /**
     * method to update the component
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
    }    
}