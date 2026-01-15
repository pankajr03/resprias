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
namespace ZhukDL\Component\ZhGoogleMap\Site\View\Map;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;


/**
 * Main Admin View
 */
class HtmlView extends BaseHtmlView {
    
    // Overwriting JView display method
    function display($tpl = null) 
    {
        
        // Assign data to the view
        $this->item = $this->get('Item');


        // Map API Key
        $this->mapapikey4map = $this->get('MapAPIKey');
        
        $this->mapapiversion = $this->get('MapAPIVersion');
               
        $this->mapapikey4map_nz = $this->get('NZMapAPIKey');
        
        $this->mapapitype = $this->get('MapAPIType');
		
		$this->loadjquery = $this->get('LoadJQuery');       
        
        $this->placemarktitletag = $this->get('PlacemarkTitleTag');
        
        // Map markers
        $this->markers = $this->get('Markers');
        
        // Map markergroups
        $this->markergroups = $this->get('MarkerGroups');
        // Group list manager
        $this->mgrgrouplist = $this->get('MgrGroupsList');
        
        $this->licenseinfo = $this->get('LicenseInfo');
        
        // Map routers
        $this->routers = $this->get('Routers');
        
        // Map paths
        $this->mappaths = $this->get('Paths');
                
        $this->mapcompatiblemode = $this->get('CompatibleMode');
        
        // Map types
        $this->maptypes = $this->get('MapTypes');
        
        // Protocol
        $this->httpsprotocol = $this->get('HttpsProtocol');
        
        // LoadType
        $this->loadtype = $this->get('LoadType');
        
        $this->centerplacemarkid = $this->get('CenterPlacemarkId');
        
        $this->centerplacemarkaction = $this->get('CenterPlacemarkAction');
        
        $this->mapzoom = $this->get('MapZoom');
        
        $this->mapwidth = $this->get('MapWidth');
        $this->mapheight = $this->get('MapHeight');
        
        $this->externalmarkerlink = $this->get('ExternalMarkerLink');
        
        $this->usermarkersfilter = $this->get('UserMarkersFilter');
        
        $this->placemarklistid = $this->get('PlacemarkListID');
        
        $this->explacemarklistid = $this->get('ExPlacemarkListID');
        
        $this->grouplistid = $this->get('GroupListID');
        
        $this->categorylistid = $this->get('CategoryListID');
        
        $this->mapid = $this->get('MapID');
        
        $this->routelistid = $this->get('RouteListID');
        
        $this->exroutelistid = $this->get('ExRouteListID');
        
        $this->routecategorylistid = $this->get('RouteCategoryListID');
        
        $this->pathlistid = $this->get('PathListID');
        
        $this->expathlistid = $this->get('ExPathListID');
        
        $this->pathgrouplistid = $this->get('PathGroupListID');
        
        $this->pathcategorylistid = $this->get('PathCategoryListID');
        
        $this->taglistid = $this->get('TagListID');
        
        $this->pathtaglistid = $this->get('PathTagListID');
		
		$this->enable_map_gpdr = $this->get('MapGPDR');
		$this->map_gpdr_buttonlabel = $this->get('MapGPDR_Button');
		$this->map_gpdr_header = $this->get('MapGPDR_Header');
		$this->map_gpdr_footer = $this->get('MapGPDR_Footer');
        
		$this->map_gpdr_buttonc = $this->get('MapGPDR_Cookie');
		$this->map_gpdr_buttonclabel = $this->get('MapGPDR_Cookie_Button');
		$this->map_gpdr_buttoncexp = $this->get('MapGPDR_Cookie_Days');
		
        // Display the template
        parent::display($tpl);
    }

}
