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

/**
 * Model
 */
class MappathsModel extends ListModel
{
    
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'h.id',
                'title', 'h.title',
                'mapid', 'h.mapid',
                'markergroup', 'h.markergroup', 
                'published', 'h.published',
                'catid', 'h.catid', 'category_title',
                'tag',
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
		$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
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

        $query->select('h.id,h.title,h.mapid,h.published,h.publish_up,h.publish_down,h.catid,h.path,c.title as category, c.language as category_language,m.title as mapname,'.
                       'g.title as markergroupname');
        $query->from('#__zhgooglemaps_paths as h');
        $query->leftJoin('#__categories as c on h.catid=c.id');
        $query->leftJoin('#__zhgooglemaps_maps as m on h.mapid=m.id');
        $query->leftJoin('#__zhgooglemaps_markergroups as g on h.markergroup=g.id');


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
        
        
        // Filter by a single or group of tags.
		$tag = $this->getState('filter.tag');

		// Run simplified query when filtering by one tag.
		if (is_array($tag) && count($tag) === 1)
		{
			$tag = $tag[0];
		}

		if ($tag && is_array($tag))
		{
			$tag = ArrayHelper::toInteger($tag);

			$subQuery = $db->getQuery(true)
				->select('DISTINCT ' . $db->quoteName('content_item_id'))
				->from($db->quoteName('#__contentitem_tag_map'))
				->where(
					[
						$db->quoteName('tag_id') . ' IN (' . implode(',', $query->bindArray($tag)) . ')',
						$db->quoteName('type_alias') . ' = ' . $db->quote('com_zhgooglemap.mappath'),
					]
				);

			$query->join(
				'INNER',
				'(' . $subQuery . ') AS ' . $db->quoteName('tagmap'),
				$db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('h.id')
			);
		}
		elseif ($tag = (int) $tag)
		{
			$query->join(
				'INNER',
				$db->quoteName('#__contentitem_tag_map', 'tagmap'),
				$db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('h.id')
			)
				->where(
					[
						$db->quoteName('tagmap.tag_id') . ' = :tag',
						$db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_zhgooglemap.mappath'),
					]
				)
				->bind(':tag', $tag, ParameterType::INTEGER);
		}

        // Filter by mapid.
        $mapId = $this->getState('filter.mapid');
        if (is_numeric($mapId)) {
            $mapId = (int) $mapId;
 			$query->where($db->quoteName('h.mapid') . ' = :mapid')
				  ->bind(':mapid', $mapId, ParameterType::INTEGER);
        }

        // Filter by markergroup.
        $markerGroup = $this->getState('filter.markergroup');
        if (is_numeric($markerGroup)) {
            $markerGroup = (int) $markerGroup;
 			$query->where($db->quoteName('h.markergroup') . ' = :markergroup')
				  ->bind(':markergroup', $markerGroup, ParameterType::INTEGER);
        }
        
        // Add the list ordering clause.
        $orderCol    = $this->state->get('list.ordering', 'h.title');
        $orderDirn    = $this->state->get('list.direction', 'ASC');
        
        if ($orderCol === 'h.ordering' || $orderCol === 'category_title')
		{
			$ordering = [
				$db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
				//$db->quoteName('h.ordering') . ' ' . $db->escape($orderDirn),
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
