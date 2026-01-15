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
namespace ZhukDL\Component\ZhGoogleMap\Site\Model;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Input\Input;

use Joomla\CMS\Uri\Uri;

use Joomla\Registry\Registry;

class PlacemarkModel extends ItemModel
{
    var $currentplacemark;
	
	var $loadjquery;
	var $mapapitype;
	var $httpsprotocol;
    var $loadtype;
	var $mapapikey4map;
    var $mapapiversion;

	var $enable_map_gpdr;
	var $map_gpdr_buttonlabel;
    var $map_gpdr_header;
    var $map_gpdr_footer;

	var $map_gpdr_buttonc;
	var $map_gpdr_buttonclabel;
	var $map_gpdr_buttoncexp;
	
	var $item;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     * @since    1.6
     */
    protected function populateState()
    {
		$app = Factory::getApplication();
		
        // menu item parameters
        $pk = $app->input->get('id', '', "INT");
        $this->setState('placemark.id', $pk);
        
        $load_bootstrap = $app->input->get('load_bootstrap', '', "INT");
        $this->setState('placemark.load_bootstrap', $load_bootstrap);            

        $thumbnail = $app->input->get('thumbnail', '', "INT");
        $this->setState('placemark.thumbnail', $thumbnail);            

        $imagegalery = $app->input->get('imagegalery', '', "INT");
        $this->setState('placemark.imagegalery', $imagegalery);        

        $hidedescriptionhtml = $app->input->get('hidedescriptionhtml', '', "INT");
        $this->setState('placemark.hidedescriptionhtml', $hidedescriptionhtml);            

        $showdescriptionfullhtml = $app->input->get('showdescriptionfullhtml', '', "INT");
        $this->setState('placemark.showdescriptionfullhtml', $showdescriptionfullhtml);        

        
        parent::populateState();
        
    }        
    
    
    public function getItem($pk = null)
    {
        if (!isset($this->item))
        {
            $id = $this->getState('placemark.id');
			
			$db = Factory::getDBO();
			$query = $db->getQuery(true);
			$user = Factory::getUser();
			
			$query->select('h.*, '.
				' c.title as category, g.icontype as groupicontype, g.overridemarkericon as overridemarkericon, g.published as publishedgroup, g.markermanagerminzoom as markermanagerminzoom, g.markermanagermaxzoom as markermanagermaxzoom, g.activeincluster as activeincluster, '.
				' bub.disableanimation, bub.shadowstyle, bub.padding, bub.borderradius, bub.borderwidth, bub.bordercolor, bub.backgroundcolor, bub.minwidth, bub.maxwidth, bub.minheight, bub.maxheight, bub.arrowsize, bub.arrowposition, bub.arrowstyle, bub.disableautopan, bub.hideclosebutton, bub.backgroundclassname, bub.published infobubblepublished, '.
				' mp.lang as maplang')
				->from('#__zhgooglemaps_markers as h')
				->leftJoin('#__zhgooglemaps_maps as mp ON h.mapid=mp.id')
				->leftJoin('#__categories as c ON h.catid=c.id')
				->leftJoin('#__zhgooglemaps_markergroups as g ON h.markergroup=g.id')
				->leftJoin('#__zhgooglemaps_infobubbles as bub ON h.tabid=bub.id')
				->where('h.id='.$id);
				
			$db->setQuery($query);

			if (!$this->item = $db->loadObject()) 
			{
				$this->setError($db->getError());
			}
        

        }
        
        $this->currentplacemark = $this->item;
        
        return $this->item;        
    }
    

    
    public function getMapGPDR() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $enable_map_gpdr = $params->get( 'enable_map_gpdr');

            return $enable_map_gpdr;
    }
	
    public function getMapGPDR_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonlabel = $params->get( 'buttonlabel');

            return $map_gpdr_buttonlabel;
    }
    public function getMapGPDR_Header() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_header = $params->get( 'headerhtml');

            return $map_gpdr_header;
    }
    public function getMapGPDR_Footer() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_footer = $params->get( 'footerhtml');

            return $map_gpdr_footer;
    }	

    public function getMapGPDR_Cookie() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonc = $params->get( 'cookies_button');

            return $map_gpdr_buttonc;
    }
    public function getMapGPDR_Cookie_Button() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttonclabel = $params->get( 'buttonlabelc');

            return $map_gpdr_buttonclabel;
    }
    public function getMapGPDR_Cookie_Days() 
    {
        // Get global params
            $app = Factory::getApplication();
            $params = ComponentHelper::getParams( 'com_zhgooglemap' );
        
            $map_gpdr_buttoncexp = $params->get( 'cookies_days');

            return $map_gpdr_buttoncexp;
    }

	
    public function getMapAPIKey() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapikey4map = $params->get( 'map_map_key', '' );
    }

    public function getMapAPIVersion() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapiversion = $params->get( 'map_api_version', '' );
    }

    public function getMapAPIType() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $mapapitype = $params->get( 'api_type', '' );
    }
	
    public function getHttpsProtocol() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $httpsprotocol = $params->get( 'httpsprotocol', '' );
    }

    public function getLoadType() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');


        return $loadtype = $params->get( 'loadtype', '' );
    }
	
	public function getLoadJQuery() 
    {
        // Get global params
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_zhgooglemap');

        return $loadjquery = $params->get( 'load_jquery', '' );
    }
	
}
