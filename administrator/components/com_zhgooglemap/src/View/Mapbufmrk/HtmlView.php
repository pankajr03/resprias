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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\Mapbufmrk;
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use ZhukDL\Component\ZhGoogleMap\Administrator\Model\MapbufmrkModel;

use ZhukDL\Component\ZhGoogleMap\Administrator\Helper\ZhgooglemapHelper;

/**
 * ZhGOOGLE MapBufmrk View
 */
class HtmlView extends BaseHTMLView
{
	
	        
    protected $form;

	protected $item;

    protected $canDo;
	
    /**
     * display method of ZhGOOGLE MapBufmrk view
     * @return void
     */
    public function display($tpl = null): void 
    {
        $model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();

        $this->mapapikey4map = ZhgooglemapHelper::getAPIKey(); 
        $this->mapapiversion = ZhgooglemapHelper::getAPIVersion(); 
        $this->mapTypeList = ZhgooglemapHelper::getMapTypeList(); 
        $this->mapMapTypeGoogle = ZhgooglemapHelper::getMapTypeGoogle();
        $this->mapMapTypeOSM = ZhgooglemapHelper::getMapTypeOSM();
        $this->mapMapTypeCustom = ZhgooglemapHelper::getMapTypeCustom();
        $this->httpsprotocol = ZhgooglemapHelper::getHttpsProtocol(); 
        $this->map_height = ZhgooglemapHelper::getMapHeight(); 
        $this->mapDefLat = ZhgooglemapHelper::getDefLat();
        $this->mapDefLng= ZhgooglemapHelper::getDefLng();
               
        $this->canDo = ContentHelper::getActions('com_zhgooglemap');		
        
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
        Factory::getApplication()->input->set('hidemainmenu', true);
        $user = Factory::getUser();
        $userId = $user->id;
        $isNew = $this->item->id == 0;		
                
        $canDo = ContentHelper::getActions('com_zhgooglemap');
        ToolbarHelper::title($isNew ? Text::_('COM_ZHGOOGLEMAP_MAPMARKER_NEW') : Text::_('COM_ZHGOOGLEMAP_MAPMARKER_EDIT'), 'mapbufmrk');
        
		
		 // Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
        $toolbarButtons = [];
        
        // Built the actions for new and existing records.
		if ($isNew) 
        {
            // For new records, check the create permission.
            if ($canDo->get('core.create')) 
            {
                $toolbar->apply('mapbufmrk.apply');
                $toolbarButtons[] = ['save', 'mapbufmrk.save'];
                $toolbarButtons[] = ['save2new', 'mapbufmrk.save2new'];
            }
        }
        else
        {
            if ($canDo->get('core.edit'))
            {
                $toolbar->apply('mapbufmrk.apply');
                $toolbarButtons[] = ['save', 'mapbufmrk.save'];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) 
                {
                    $toolbarButtons[] = ['save2new', 'mapbufmrk.save2new'];
                }
            }
            if ($canDo->get('core.create')) 
            {
                $toolbarButtons[] = ['save2copy', 'mapbufmrk.save2copy'];
            }
        }
        
        ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

        $toolbar->cancel('mapbufmrk.cancel');
				
		
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
        $isNew = $this->item->id == 0;
        $document = Factory::getDocument();
        $document->setTitle($isNew ? Text::_('COM_ZHGOOGLEMAP_ADMINISTRATION_MAPMARKER_CREATING') : Text::_('COM_ZHGOOGLEMAP_ADMINISTRATION_MAPMARKER_EDITING'));
    }
	*/
}
