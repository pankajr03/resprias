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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Table;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;


/**
 * zhgooglemaps_marker_buffer Table class
 */
class MapbufmrkTable extends Table
{

	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
    protected $_supportNullValue = true;
	
    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function __construct(DatabaseDriver $db) 
    {
        parent::__construct('#__zhgooglemaps_marker_buffer', 'id', $db);
        
        //TableObserverTags::createObserver($this, array('typeAlias' => 'com_zhgooglemap.mapbufmrk'));
    }
    
    /**
     * Overloaded check function
     *
     * @return  boolean
     * @see     JTable::check
     * @since   1.5
     */
    public function check()
    {

        if (!$this->createddate)
		{
			$this->createddate = null;
		}
        
        /*
            if (empty($this->alias))
            {
                $this->alias = $this->title;
            }
            $this->alias = JApplication::stringURLSafe($this->alias);
            if (trim(str_replace('-', '', $this->alias)) == '')
            {
                $this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
            }
        */

        return true;
    }
    
    /**
     * Overloaded bind function
     *
     * @param       array           named array
     * @return      null|string     null is operation was satisfactory, otherwise returns an error
     * @see JTable:bind
     * @since 1.5
     */
    public function bind($array, $ignore = '') 
    {
        if (isset($array['params']) && is_array($array['params'])) 
        {
            // Convert the params field to a string.
            $parameter = new Registry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string)$parameter;
        }
        return parent::bind($array, $ignore);
    }

    
    /**
     * Overriden JTable::store to set modified data and user id.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function store($updateNulls = true)
    {       
        return parent::store($updateNulls);
    }

    public function getTypeAlias()
	{
		return $this->typeAlias;
	}
	
}
