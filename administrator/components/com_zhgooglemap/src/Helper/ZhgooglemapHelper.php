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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Helper;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * Helper.
 *
 * @since  1.6
 */
class ZhgooglemapHelper extends ContentHelper
{
    
    
    public static function getAPIKey() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapapikey = $params->get( 'map_map_key', '' );
    }

    public static function getAPIVersion() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapapiversion = $params->get( 'map_api_version', '' );
    }
    
    public static function getDefLat() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapDefLat = $params->get( 'map_lat', '' );
    }

    public static function getDefLng() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapDefLng = $params->get( 'map_lng', '' );
    }

    public static function getMapTypeGoogle() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapMapTypeGoogle = $params->get( 'map_type_google', '' );
    }
    public static function getMapTypeOSM() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapMapTypeOSM = $params->get( 'map_type_osm', '' );
    }
    public static function getMapTypeCustom() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $mapMapTypeCustom = $params->get( 'map_type_custom', '' );
    }
    
    public static function getHttpsProtocol() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $httpsprotocol = $params->get( 'httpsprotocol', '' );
    }
        
        public static function getMapHeight() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $map_height = $params->get( 'map_height', '' );
    }


    public static function getLoadJQuery() 
    {
        // Get global params
        $params = ComponentHelper::getParams( 'com_zhgooglemap' );

        return $load_jquery = $params->get( 'load_jquery', '' );
    }
	
	public static function getMapTypeList()
	{

        $db   = Factory::getDbo();
        $query = $db->getQuery(true)
                ->select('h.*, c.title as category ')
                ->from('#__zhgooglemaps_maptypes as h')
                ->leftJoin('#__categories as c ON h.catid=c.id')
                ->where('h.published=1')
                ->order('h.title');
                    
        $db->setQuery($query);
        
        try
        {
            $mapTypeList = $db->loadObjectList();           
        }
        catch (RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
                      
        return $mapTypeList;

	}
    
        
}
