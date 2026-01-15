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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\Utils;

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

/**
 * Main Admin View
 */
class HtmlView extends BaseHtmlView {

     /**
     * Display the main view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null): void 
    {
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
        ToolbarHelper::title(Text::_('COM_ZHGOOGLEMAP_UTIL_MANAGER'), 'util');

        // Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
        
        if ($canDo->get('core.admin')) 
        {
            $toolbar->preferences('com_zhgooglemap');
        }
        
        $help_url = 'http://wiki.zhuk.cc/index.php/Zh_GoogleMap_Description#Utilities';
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
        $document->setTitle(Text::_('COM_ZHGOOGLEMAP_UTIL'));
    }
	*/

}
