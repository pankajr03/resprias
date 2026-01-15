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

$wa  = $document->getWebAssetManager();


    $wa->registerAndUseScript('zhgooglemaps.common', $current_custom_js_path.'common-min.js');
    if (isset($use_object_manager) && (int)$use_object_manager == 1)
    {
        $wa->registerAndUseScript('zhgooglemaps.objectmanager', $current_custom_js_path.'objectmanager-min.js');
    }

    if (isset($compatiblemode) && (int)$compatiblemode == 1)
    {
        $wa->registerAndUseScript('zhgooglemaps.compatibility', $current_custom_js_path.'compatibility-min.js');
    }
	
	// Google Maps JS API loading moved to main script due to GPDR

    if (isset($markercluster) && (int)$markercluster == 1)
    {
        //new version of MarkerClusterer
        $wa->registerAndUseScript('zhgooglemaps.markerclusterer', $current_custom_js_path.'markerclusterer/2.0.16/markerclusterer_packed.js');
    }

    if ($markermanager == 1)
    {
        $wa->registerAndUseScript('zhgooglemaps.markermanager', $current_custom_js_path.'markermanager/1.2/markermanager_packed.js');
    }

   
    
    if (isset($infobubble) && (int)$infobubble == 1)
    {
        if ($urlProtocol == "https")
        {
            $wa->registerAndUseScript('zhgooglemaps.infobubble', $current_custom_js_path.'infobubble/trunk/infobubble-compiled_https.js');
        }
        else
        {
            $wa->registerAndUseScript('zhgooglemaps.infobubble', $current_custom_js_path.'infobubble/trunk/infobubble-compiled.js');
        }
    }

    if (isset($featureMarkerWithLabel) && (int)$featureMarkerWithLabel == 1)
    {
        $wa->registerAndUseScript('zhgooglemaps.markerwithlabel', $current_custom_js_path.'markerwithlabel/1.1.9/markerwithlabel_packed.js');
    }

	if ((int)$enable_map_gpdr == 1)
	{
		$wa->registerAndUseScript('zhgooglemaps.cookie', $current_custom_js_path.'js-cookie/3.0.5/js.cookie.min.js');
	}