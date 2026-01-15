<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Filesstatus;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

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
    function display($tpl = null) {
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_FILEMANAGER_PANEL_TEXT'), 'securitycheck' );
		ToolBarHelper::custom('redireccion_file_manager_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_FILE_MANAGER_CONTROL_PANEL',false);

		/* Cargamos el lenguaje del sitio */
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);
		
		$model               = $this->getModel();
		$this->items		 = $model->loadStack("permissions","file_manager");
		$this->pagination    = $model->getPagination();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();

				
		$this->files_with_incorrect_permissions = $model->loadStack("filemanager_resume","files_with_incorrect_permissions");
		$this->show_all = $this->state->get('showall',0);
		$this->database_error = $model->get_campo_filemanager("estado");
     
		
        parent::display($tpl);
    }


}