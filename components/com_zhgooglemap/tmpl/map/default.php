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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$document    = Factory::getDocument();

// ***** Init Section Begin ***********************************
        $this->MapXsuffix = "ZhGMCOM";

        $this->markercluster = 0;
        $this->markermanager = 0;
        $this->places = 0;
        $this->weather = 0;
        $this->loadVisualisation = 0;
        $this->loadVisualisationKML = 0;
        $this->main_lang = "";
        $this->infobubble = 0;
        $this->featureMarkerWithLabel = 0;
        $this->use_object_manager = 0;
        $this->map_region = "";

        $this->load_by_script = "";
                
        $this->current_custom_js_path = URI::root() .'components/com_zhgooglemap/assets/js/';    
        $current_custom_js_path = $this->current_custom_js_path;

        
        $this->useObjectStructure = 1;
        $useObjectStructure = $this->useObjectStructure;
        
        
// ***** Init Section End *************************************


// ***** Settings Begin *************************************

$map = $this->item;
$markers = $this->markers;
$mappaths = $this->mappaths;
$routers = $this->routers;
$maptypes = $this->maptypes;

$mgrgrouplist = $this->mgrgrouplist;
$markergroups = $this->markergroups;


$centerplacemarkid = $this->centerplacemarkid;
$centerplacemarkactionid = $this->centerplacemarkid;
$centerplacemarkaction = $this->centerplacemarkaction;
$externalmarkerlink = (int)$this->externalmarkerlink;

$placemarklistid = $this->placemarklistid;
$explacemarklistid = $this->explacemarklistid;
$grouplistid = $this->grouplistid;
$categorylistid = $this->categorylistid;
//
// Pass it but not use there (only in query)
$routelistid = $this->routelistid;
$exroutelistid = $this->exroutelistid;
$routegrouplistid = "";
$routecategorylistid = $this->routecategorylistid;

// Pass, used in query
$pathlistid = $this->pathlistid;
$expathlistid = $this->expathlistid;
$pathgrouplistid = $this->pathgrouplistid;
$pathcategorylistid = $this->pathcategorylistid;
//
$taglistid = $this->taglistid;
$pathtaglistid = $this->pathtaglistid;


$mapzoom = $this->mapzoom;

// addition parameters
if ($this->usermarkersfilter == "")
{
    $usermarkersfilter = (int)$map->usermarkersfilter;
}
else
{
    $usermarkersfilter = (int)$this->usermarkersfilter;
}

$mapMapWidth = $this->mapwidth;
$mapMapHeight = $this->mapheight;


// -- -- extending ------------------------------------------
// class suffix, for example for module use
$cssClassSuffix = "";


// -- -- -- component options - begin -----------------------
$compatiblemode = $this->mapcompatiblemode;

$loadjquery = $this->loadjquery;

$licenseinfo = $this->licenseinfo;

$apikey4map = $this->mapapikey4map;
$apikey4map_nz = $this->mapapikey4map_nz;
$loadtype = $this->loadtype;
$load_by_script = $this->load_by_script;

$apiversion = $this->mapapiversion;

$apitype = $this->mapapitype;


$main_lang = $this->main_lang;

$this->urlProtocol = "http";
if ($this->httpsprotocol != "")
{
    if ((int)$this->httpsprotocol == 0)
    {
        $this->urlProtocol = 'https';
    }
}    

$urlProtocol = $this->urlProtocol;

$placemarkTitleTag = $this->placemarktitletag;

$enable_map_gpdr = $this->enable_map_gpdr;
$map_gpdr_buttonlabel = $this->map_gpdr_buttonlabel;
$map_gpdr_header = $this->map_gpdr_header;
$map_gpdr_footer = $this->map_gpdr_footer;
$map_gpdr_buttonc = $this->map_gpdr_buttonc;
$map_gpdr_buttonclabel = $this->map_gpdr_buttonclabel;
$map_gpdr_buttoncexp = $this->map_gpdr_buttoncexp;

// Fix Global Scope Variable names
$this->apitype = $apitype;
$this->apikey4map = $apikey4map;
$this->apikey4map_nz = $apikey4map_nz;
$this->apiversion = $apiversion;

$map_region = $this->map_region;


// -- -- -- component options - end -------------------------

// ***** Settings End ***************************************



require_once (JPATH_SITE . '/components/com_zhgooglemap/tmpl/map/display_map_data.php');


// add local variables for common script
//   because module doesn't use object model

$main_lang = $this->main_lang;
$featureMarkerWithLabel = $this->featureMarkerWithLabel;
$places = $this->places;
$weather = $this->weather;
$markercluster = $this->markercluster;
$markermanager = $this->markermanager;
$map_region = $this->map_region;

$loadVisualisation = $this->loadVisualisation;
$loadVisualisationKML = $this->loadVisualisationKML;

$infobubble = $this->infobubble;
$use_object_manager = $this->use_object_manager;

$useObjectStructure = $this->useObjectStructure;

$current_custom_js_path = $this->current_custom_js_path;

$load_by_script = $this->load_by_script;


require_once (JPATH_SITE . '/components/com_zhgooglemap/tmpl/map/display_script.php');

