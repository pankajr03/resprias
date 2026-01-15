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
use Joomla\CMS\Language\Text;

/**
 * ZhGOOGLEBufmrks Model
 */
class MapbufmrksModel extends ListModel
{
	var $inserted = 0;
	var $errors = 0;
	var $warnings = 0;
	var $skipped = 0;
    
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
                'title', 'h.title',
                'catid', 'h.catid', 'category_title',
                'markergroup', 'h.markergroup', 
				'markerstate', 'h.status',
				'published', 'h.published',
                'icontype', 'h.icontype',
                'h.createdbyuser'
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
		
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . $this->getState('filter.mapid');
		$id .= ':' . $this->getState('filter.markergroup');
		$id .= ':' . $this->getState('filter.icontype');
		
		
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
        $db = $this->getDBO();
        $query = $db->getQuery(true);

        $query->select('h.id,h.title,h.catid,h.icontype,h.createdbyuser,h.status,h.published,h.publish_up,h.publish_down,'.
        'c.title as category, c.language as category_language,g.title as markergroupname, usr.username, usr.name fullusername'.
        '');
        $query->from('#__zhgooglemaps_marker_buffer as h');
        $query->leftJoin('#__categories as c on h.catid=c.id');
        $query->leftJoin('#__zhgooglemaps_markergroups as g on h.markergroup=g.id');
        $query->leftJoin('#__users as usr on h.createdbyuser=usr.id');
        

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';				
            $query->where($db->quoteName('h.title') . ' LIKE :search')
                  ->bind(':search', $search);
        }
        
        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $published = (int)$published;
			$query->where($db->quoteName('h.published') . ' = :published')
				  ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where('h.published IN (0, 1)');
        }

        // Filter by categories and by level
		$categoryId = $this->getState('filter.category_id', array());
		$level      = (int) $this->getState('filter.level');

		if (!is_array($categoryId))
		{
			$categoryId = $categoryId ? array($categoryId) : array();
		}

		// Case: Using both categories filter and by level filter
		if (count($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = Table::getInstance('Category', 'JTable');
			$subCatItemsWhere = array();

			foreach ($categoryId as $key => $filter_catid)
			{
				$categoryTable->load($filter_catid);

				// Because values to $query->bind() are passed by reference, using $query->bindArray() here instead to prevent overwriting.
				$valuesToBind = [$categoryTable->lft, $categoryTable->rgt];

				if ($level)
				{
					$valuesToBind[] = $level + $categoryTable->level - 1;
				}

				// Bind values and get parameter names.
				$bounded = $query->bindArray($valuesToBind);

				$categoryWhere = $db->quoteName('c.lft') . ' >= ' . $bounded[0] . ' AND ' . $db->quoteName('c.rgt') . ' <= ' . $bounded[1];

				if ($level)
				{
					$categoryWhere .= ' AND ' . $db->quoteName('c.level') . ' <= ' . $bounded[2];
				}

				$subCatItemsWhere[] = '(' . $categoryWhere . ')';
			}

			$query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		}

		// Case: Using only the by level filter
		elseif ($level = (int) $level)
		{
			$query->where($db->quoteName('c.level') . ' <= :level')
				  ->bind(':level', $level, ParameterType::INTEGER);
		}
        

        // Filter by markergroup.
        $markerGroup = $this->getState('filter.markergroup');
        if (is_numeric($markerGroup)) {
            $markerGroup = (int) $markerGroup;
 			$query->where($db->quoteName('h.markergroup') . ' = :markergroup')
				  ->bind(':markergroup', $markerGroup, ParameterType::INTEGER);
        }
        
        
        // Filter by user
		$authorId = $this->getState('filter.createdbyuser');

		if (is_numeric($authorId))
		{
			$authorId = (int) $authorId;
			$query->where($db->quoteName('h.createdbyuser') . ' = :authorId')
				->bind(':authorId', $authorId, ParameterType::INTEGER);
		}
		elseif (is_array($authorId))
		{
			$authorId = ArrayHelper::toInteger($authorId);
			$query->whereIn($db->quoteName('h.createdbyuser'), $authorId);
		}
        
        // Filter by search by icontype
        $icontype = $this->getState('filter.icontype');
        if (!empty($icontype)) {
            $query->where($db->quoteName('h.icontype') . ' = :icontype')
                  ->bind(':icontype', $icontype);
        }
        

        // Add the list ordering clause.
        $orderCol    = $this->state->get('list.ordering', 'h.title');
        $orderDirn    = $this->state->get('list.direction', 'ASC');
        
        if ($orderCol === 'category_title')
		{
			$ordering = [
				$db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
			];
		}
		else
		{
			$ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
		}

		$query->order($ordering);
      
        return $query;
    }



}
