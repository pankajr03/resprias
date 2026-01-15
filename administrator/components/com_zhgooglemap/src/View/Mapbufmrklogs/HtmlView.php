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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\MapBufmrklogs;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

use ZhukDL\Component\ZhGoogleMap\Administrator\Model\MapBufmrklogsModel;

/**
 * View class for the ZhGOOGLE MapBufmrks Component
 */
class HtmlView extends BaseHTMLView
{

    protected $state;
    protected $items = [];
    protected $pagination;

    // Overwriting JView display method
    function display($tpl = null): void 
    {$model = $this->getModel();
		
        // Get data from the model
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters(); 
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
 
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

        // Set the document
		/* 18.10.2023 for Joomla!4.4
        $this->setDocument();
		*/

    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() 
    {
        $canDo = ContentHelper::getActions('com_zhgooglemap');
        ToolbarHelper::title(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKLOGS_MANAGER'), 'mapbufmrklog');
		
		$bar = Toolbar::getInstance('toolbar');    
        
        ToolbarHelper::custom('mapbufmrklogs.back', 'exit.png', 'exit.png', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_BUTTON_CLOSE'), false);

    }
    /**
     * Method to set up the document properties
     *
     * @return void
     */
	 /* 18.10.2023 for Joomla!4.4
    protected function setDocument() 
    {
        $document = Factory::getDocument();
        $document->setTitle(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKLOGS_ADMINISTRATION'));
    }
	*/

    
}
