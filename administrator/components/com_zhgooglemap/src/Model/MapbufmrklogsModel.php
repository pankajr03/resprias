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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Model;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseIterator;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Factory;

/**
 * ZhGOOGLEBufmrks Model
 */
class MapbufmrklogsModel extends ListModel
{

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'h.id',
                                'kind', 'h.kind',                
                                'title', 'h.title'
            );
        }

        parent::__construct($config);
    }


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     * @since    1.6
     */
    protected function populateState($ordering = 'h.title', $direction = 'asc')
    {
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_zhgooglemap'));

		// List state information.
		parent::populateState($ordering, $direction);

    }


    /**
     * Method to build an SQL query to load the list data.
     *
     * @return    string    An SQL query
     */
    protected function getListQuery() 
    {
        // Create a new query object.
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $user = Factory::getUser();

        $query->select('h.*'.
        '');
        $query->from('#__zhgooglemaps_log as h');
        $query->where('h.extension=\'csv_file_marker\'');
    
        
        
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->Quote('%'.$db->escape($search, true).'%', false);
            $query->where('(h.title LIKE '.$search.' OR h.kind LIKE '.$search.')');
        }
        
        
        // Add the list ordering clause.
        $orderCol    = $this->state->get('list.ordering');
        $orderDirn    = $this->state->get('list.direction');
        if ($orderCol == 'ordering' || $orderCol == 'category_title') {
            $orderCol = 'c.id';
        }
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
        return $query;
    }

      
}
