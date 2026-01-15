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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Helper\TagsHelper;

class MapmarkerModel extends AdminModel
{
    
    public $typeAlias = 'com_zhgooglemap.mapmarker';

    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return    mixed    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_zhgooglemap.mapmarker', 'mapmarker', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) 
        {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData() 
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_zhgooglemap.edit.mapmarker.data', array());
        if (empty($data)) 
        {
            $data = $this->getItem();
        }
        
        $this->preprocessData('com_zhgooglemap.mapmarker', $data);
        
        return $data;
    }
    
    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk))
        {

            if (!empty($item->id))
            {
                $item->tags = new TagsHelper;
                $item->tags->getTagIds($item->id, 'com_zhgooglemap.mapmarker');
            }
        }

        return $item;
    }
    
	protected function prepareTable($table)
	{
		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
	}

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */

    public function save($data)
    {
        $app = Factory::getApplication();

        // Alter the title for save as copy
        if ($app->input->get('task') == 'save2copy')
        {
            list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
            $data['title']    = $title;
            $data['alias']    = $alias;
        }

        if (parent::save($data))
        {
            return true;
        }

        return false;
    }
    
}
