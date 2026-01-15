<?php
/*------------------------------------------------------------------------
# mod_zhgooglemap - Zh GoogleMap Module
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use ZhukDL\Component\ZhGoogleMap\Site\Helper\MapDataHelper;

$document    = Factory::getDocument();

// ***** Init Section Begin ***********************************

        $MapXsuffix = "ZhGMMOD";

        $markercluster = 0;
        $markermanager = 0;
        $places = 0;
        $weather = 0;
        $loadVisualisation = 0;
        $loadVisualisationKML = 0;
        $main_lang = "";
        $infobubble = 0;
        $featureMarkerWithLabel = 0;
        $use_object_manager = 0;
        $map_region = "";
        $load_by_script = "";

        $current_custom_js_path = URI::root() .'components/com_zhgooglemap/assets/js/';

        $useObjectStructure = 0;
        
        
// ***** Init Section End *************************************

$id = $params->get('mapid', '');

$MapXdoLoad = 0;
$useObjectStructure = 2;
$MapXArticleId = $module->id;


$map = MapDataHelper::getMap((int)$id);

// Change translation language and load translation
$currentLanguage = Factory::getLanguage();
$currentLangTag = $currentLanguage->getTag();
if (isset($map->lang) && $map->lang != "")
{

    $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap', $map->lang, true);    

    $currentLanguage->load('mod_zhgooglemap', JPATH_SITE, $map->lang, true);    

}
else
{

    $currentLanguage->load('com_zhgooglemap', JPATH_SITE . '/components/com_zhgooglemap', $currentLangTag, true);    

    $currentLanguage->load('mod_zhgooglemap', JPATH_SITE, $currentLangTag, true);    
    
}

if (isset($map) && (int)$map->id != 0)
{

// ***** Settings Begin *************************************
    
$centerplacemarkid = $params->get('centerplacemarkid', '');
$centerplacemarkaction = $params->get('centerplacemarkaction', '');
$centerplacemarkactionid = $params->get('centerplacemarkid', '');

$externalmarkerlink = (int)$params->get('externalmarkerlink', '');

$placemarklistid = $params->get('placemarklistid', '');
$explacemarklistid = $params->get('explacemarklistid', '');
$grouplistid = $params->get('grouplistid', '');
$categorylistid = $params->get('categorylistid', '');
$taglistid = $params->get('taglistid', '');

// Pass it but not use there (only in query)
$routelistid = $params->get('routelistid', '');
$exroutelistid = $params->get('exroutelistid', '');
$routegrouplistid = "";
$routecategorylistid = $params->get('routecategorylistid', '');

// Pass, used in query
$pathlistid = $params->get('pathlistid', '');
$expathlistid = $params->get('expathlistid', '');
$pathgrouplistid = $params->get('pathgrouplistid', '');
$pathcategorylistid = $params->get('pathcategorylistid', '');
$pathtaglistid = $params->get('pathtaglistid', '');
//

$usermarkersfilter = "";

// addition parameters
if ($usermarkersfilter == "")
{
    $usermarkersfilter = (int)$map->usermarkersfilter;
}
else
{
    $usermarkersfilter = (int)$usermarkersfilter;
}

if ($map->useajaxobject == 0)
{
    $markers = MapDataHelper::getMarkers($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                              $map->usermarkers, $usermarkersfilter, $map->usercontact, $map->markerorder);

    $mappaths = MapDataHelper::getPaths($map->id, $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);
}
else
{
    unset($markers);
    unset($mappaths);
}
$routers = MapDataHelper::getRouters($map->id, $routelistid, $exroutelistid, $routegrouplistid, $routecategorylistid);
$maptypes = MapDataHelper::getMapTypes();

$markergroups = MapDataHelper::getMarkerGroups($map->id, $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                    $map->markergrouporder);
$mgrgrouplist = MapDataHelper::getMarkerGroupsManage($map->id, 
                                                            $placemarklistid, $explacemarklistid, $grouplistid, $categorylistid, $taglistid,
                                                            $map->markergrouporder, $map->markergroupctlmarker, $map->markergroupctlpath, 
                                                            $pathlistid, $expathlistid, $pathgrouplistid, $pathcategorylistid, $pathtaglistid);

$mapzoom = "";

$map_region = $map->region;
$map_country = $map->country;

$mapMapWidth = "";
$mapMapHeight = "";



// -- -- extending ------------------------------------------
// class suffix, for example for module use

$cssClassSuffix = "";

// -- -- -- component options - begin -----------------------

$compatiblemode = MapDataHelper::getCompatibleMode();

$licenseinfo = MapDataHelper::getMapLicenseInfo();

$apikey4map = MapDataHelper::getMapAPIKey();
$apikey4map_nz = MapDataHelper::getNZMapAPIKey();
$loadtype = MapDataHelper::getLoadType();
$apiversion = MapDataHelper::getMapAPIVersion();

$apitype = MapDataHelper::getMapAPIType();

$loadjquery = MapDataHelper::getLoadJQuery();

$httpsprotocol = MapDataHelper::getHttpsProtocol();

$urlProtocol = 'http';
if ($httpsprotocol != "")
{
    if ((int)$httpsprotocol == 0)
    {
        $urlProtocol = 'https';
    }
}

$placemarkTitleTag = MapDataHelper::getPlacemarkTitleTag();

$enable_map_gpdr = MapDataHelper::getMapGPDR();

$map_gpdr_buttonlabel =  MapDataHelper::getMapGPDR_Button();
$map_gpdr_header =  MapDataHelper::getMapGPDR_Header();
$map_gpdr_footer =  MapDataHelper::getMapGPDR_Footer();
$map_gpdr_buttonc =  MapDataHelper::getMapGPDR_Cookie();
$map_gpdr_buttonclabel =  MapDataHelper::getMapGPDR_Cookie_Button();
$map_gpdr_buttoncexp =  MapDataHelper::getMapGPDR_Cookie_Days();

// -- -- -- component options - end -------------------------

// ***** Settings End ***************************************




require (JPATH_SITE . '/components/com_zhgooglemap/tmpl/map/display_map_data.php');

require (JPATH_SITE . '/components/com_zhgooglemap/tmpl/map/display_script.php');

}
else
{
  echo Text::_( 'MOD_ZHGOOGLEMAP_MAP_NOTFIND_ID' ).' '. $id;
}
