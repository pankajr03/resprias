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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\Mappaths;

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

use ZhukDL\Component\ZhGoogleMap\Administrator\Model\MappathsModel;


/**
 * View
 */
class HtmlView extends BaseHTMLView
{

    protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 * @since  1.6
	 */
	protected $state;
    
    // Overwriting JView display method
    function display($tpl = null): void
    {
        // Get data from the model

        $model = $this->getModel();
        
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
        ToolbarHelper::title(Text::_('COM_ZHGOOGLEMAP_MAPPATH_MANAGER'), 'mappath');
        // Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

        if ($canDo->get('core.create')) 
        {
            $toolbar->addNew('mappath.add')
                          ->text('JTOOLBAR_NEW');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);

        $childBar = $dropdown->getChildToolbar();

        /*
        if ($canDo->get('core.edit')) 
        {
            $childBar->edit('mappath.edit', 'JTOOLBAR_EDIT')
                          ->listCheck(true);
        }
        */
        if ($canDo->get('core.edit.state')) 
        {
                $childBar->publish('mappaths.publish')
                                ->text('JTOOLBAR_PUBLISH')
                                ->listCheck(true);
                $childBar->unpublish('mappaths.unpublish')
                                ->text('JTOOLBAR_UNPUBLISH')
                                ->listCheck(true);
        }
        if ($canDo->get('core.delete')) 
        {
            $childBar->delete('mappaths.delete')
                            ->text('JTOOLBAR_DELETE')
                            ->message('JGLOBAL_CONFIRM_DELETE')
                            ->listCheck(true);
        }
        if ($canDo->get('core.admin')) 
        {
            $toolbar->preferences('com_zhgooglemap');
        }

        $help_url = 'http://wiki.zhuk.cc/index.php/Zh_GoogleMap_Description#Creating_Path';
        $toolbar->help('', false, $help_url);


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
        $document->setTitle(Text::_('COM_ZHGOOGLEMAP_MAPPATH_ADMINISTRATION'));
    }
	*/


}
