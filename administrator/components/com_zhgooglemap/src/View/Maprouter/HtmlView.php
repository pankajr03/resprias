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
namespace ZhukDL\Component\ZhGoogleMap\Administrator\View\Maprouter;

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

use ZhukDL\Component\ZhGoogleMap\Administrator\Model\MaprouterModel;

class HtmlView extends BaseHTMLView
{
    /**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

    protected $canDo;


    public function display($tpl = null): void 
    {
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
        
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
        ToolBarHelper::title($isNew ? Text::_('COM_ZHGOOGLEMAP_MAPROUTER_NEW') : Text::_('COM_ZHGOOGLEMAP_MAPROUTER_EDIT'), 'maprouter');
        
        // Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
        $toolbarButtons = [];
        // Built the actions for new and existing records.
        if ($isNew) 
        {
            // For new records, check the create permission.
            if ($canDo->get('core.create')) 
            {
                $toolbar->apply('maprouter.apply');
                $toolbarButtons[] = ['save', 'maprouter.save'];
                $toolbarButtons[] = ['save2new', 'maprouter.save2new'];
            }
        }
        else
        {
            if ($canDo->get('core.edit'))
            {
                $toolbar->apply('maprouter.apply');
                $toolbarButtons[] = ['save', 'maprouter.save'];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) 
                {
                    $toolbarButtons[] = ['save2new', 'maprouter.save2new'];
                }
            }
            if ($canDo->get('core.create')) 
            {
                $toolbarButtons[] = ['save2copy', 'maprouter.save2copy'];
            }
        }
        
        ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

        $toolbar->cancel('maprouter.cancel');
        
        $help_url = 'http://wiki.zhuk.cc/index.php/Zh_GoogleMap_Description#Creating_Route';
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
        $isNew = $this->item->id == 0;
        $document = Factory::getDocument();
        $document->setTitle($isNew ? Text::_('COM_ZHGOOGLEMAP_ADMINISTRATION_MAPROUTER_CREATING') : Text::_('COM_ZHGOOGLEMAP_ADMINISTRATION_MAPROUTER_EDITING'));
    }
	*/
}
