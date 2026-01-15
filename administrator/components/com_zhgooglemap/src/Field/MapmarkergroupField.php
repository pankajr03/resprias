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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;


class MapmarkergroupField extends ListField
{
    /**
     * The field type.
     *
     * @var        string
     */
    protected $type = 'Mapmarkergroup';

    /**
     * Method to get a list of options for a list input.
     *
     * @return    array        An array of JHtml options.
     */
    protected function getOptions() 
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('h.*,c.title as category');
        $query->from('#__zhgooglemaps_markergroups as h');
        $query->leftJoin('#__categories as c on h.catid=c.id');
        $query->order('h.title');
        
        $db->setQuery((string)$query);
        $list_values = $db->loadObjectList();
        $options = array();
        if ($list_values)
        {
            foreach($list_values as $list_value) 
            {
                $options[] = HTMLHelper::_('select.option', $list_value->id, $list_value->title . ($list_value->catid ? ' (' . $list_value->category . ')' : ''));
            }
        }
        
        // Do not add a null option, because it depends on filter or form case
        //array_unshift($options, HTMLHelper::_('select.option', '', Text::_( 'COM_ZHGOOGLEMAP_MAPMARKERGROUP_FILTER_GROUP')));
        
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
