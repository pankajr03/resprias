<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Filemanager;

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
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_CPANEL_FILE_MANAGER_CONTROL_PANEL_TEXT'), 'securitycheck' );
		ToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL',false);
		ToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO',false);

		// Obtenemos los datos del modelo
		$model = $this->getModel("filemanager");
		$last_check = $model->loadStack("filemanager_resume","last_check");
		$files_scanned = $model->loadStack("filemanager_resume","files_scanned");
		$incorrect_permissions = $model->loadStack("filemanager_resume","files_with_incorrect_permissions");

		$task_ended = $model->get_campo_filemanager("estado");

		// Si no se está ejecutando ninguna tarea, mostramos la opción 'view files integrity'
		if ( strtoupper($task_ended) != 'IN_PROGRESS' ) {
			ToolBarHelper::custom( 'view_file_permissions', 'eye', 'eye', 'COM_SECURITYCHECK_VIEW_FILE_PERMISSIONS',false );
		}

		// Ponemos los datos en el template
		$this->last_check = $last_check;
		$this->files_scanned = $files_scanned;
		$this->incorrect_permissions = $incorrect_permissions;        
		
        parent::display($tpl);
    }


}