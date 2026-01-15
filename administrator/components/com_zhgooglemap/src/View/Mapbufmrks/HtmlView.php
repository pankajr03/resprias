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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\Mapbufmrks;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Exception;
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

use ZhukDL\Component\ZhGoogleMap\Administrator\Helper\ZhgooglemapHelper;
use ZhukDL\Component\ZhGoogleMap\Administrator\Model\MapbufmrksModel;


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
    {
        $model = $this->getModel();
        
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();     

		$this->loadjquery = ZhgooglemapHelper::getLoadJQuery(); 		

        
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
        ToolbarHelper::title(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_MANAGER'), 'mapbufmrk');
                
        $bar = Toolbar::getInstance('toolbar');                
		
		ToolbarHelper::custom('mapbufmrks.back', 'exit.png', 'exit.png', Text::_('COM_ZHGOOGLEMAP_UTILS_BUTTON_CLOSE'), false);    	
                
        if ($canDo->get('core.create')) 
        {

			$bar->popupButton('upload', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_FILELOAD'))
				      ->selector('uploadCSVModal')
					  ->listCheck(false);
		
        }
        if ($canDo->get('core.admin')) 
        {

			$bar->popupButton('box-add', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_IMPORT_ALL'))
				      ->selector('uploadPlacemarkModal')
					  ->listCheck(false);

			ToolbarHelper::divider();
        }

		ToolbarHelper::custom('mapbufmrks.marker_log', 'warning-2', 'warning-2', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG'), false);
        if ($canDo->get('core.admin')) 
        {
			ToolbarHelper::custom('mapbufmrks.marker_delete_log', 'purge', 'purge', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_DELETE'), false);
		}


		if ($canDo->get('core.create')) 
        {
            $bar->addNew('mapbufmrk.add')
                          ->text('JTOOLBAR_NEW');
        }

        $dropdown = $bar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);

        $childBar = $dropdown->getChildToolbar();

        /*
        if ($canDo->get('core.edit')) 
        {
            $childBar->edit('mapbufmrk.edit', 'JTOOLBAR_EDIT')
                          ->listCheck(true);
        }
        */
        if ($canDo->get('core.edit.state')) 
        {
                $childBar->publish('mapbufmrks.publish')
                                ->text('JTOOLBAR_PUBLISH')
                                ->listCheck(true);
                $childBar->unpublish('mapbufmrks.unpublish')
                                ->text('JTOOLBAR_UNPUBLISH')
                                ->listCheck(true);
        }
        if ($canDo->get('core.delete')) 
        {
            $childBar->delete('mapbufmrks.delete')
                            ->text('JTOOLBAR_DELETE')
                            ->message('JGLOBAL_CONFIRM_DELETE')
                            ->listCheck(true);
        }

		
        
        if ($canDo->get('core.delete')) 
        {
			ToolbarHelper::custom('mapbufmrks.marker_delete_all', 'delete', 'delete', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DELETE_ALL'), false);
			ToolbarHelper::custom('mapbufmrks.marker_delete_processed', 'delete', 'delete', Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DELETE_PROCESSED'), false);
        }
                
        if ($canDo->get('core.admin')) 
        {
            $bar->preferences('com_zhgooglemap');
        }
                
       
		$help_url = 'http://wiki.zhuk.cc/index.php/Zh_GoogleMap_Description#Import_CSV';
		ToolbarHelper::help('', false, $help_url);

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
        $document->setTitle(Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_ADMINISTRATION'));
    }
	*/

    
}
