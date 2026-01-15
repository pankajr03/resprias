<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Vulnerabilities;

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
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_VULNERABILITIES'), 'securitycheck' );
		ToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL',false);
		ToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO',false);

		$jinput = Factory::getApplication()->input;

		// Obtenemos los datos del modelo...
		$model = $this->getModel();
		$update_database_plugin_enabled = $model->PluginStatus(3);
		$update_database_plugin_exists = $model->PluginStatus(4);
		$last_check = $model->get_campo_bbdd('securitycheckpro_update_database','last_check');
		$database_version = $model->get_campo_bbdd('securitycheckpro_update_database','version');
		$database_message = $model->get_campo_bbdd('securitycheckpro_update_database','message');
		if ( $update_database_plugin_exists ) {
			$plugin_id = $model->get_plugin_id(1);
		}

		// Obtenemos los datos del modelo
		$this->items = $this->get('Data');
		$this->pagination = $this->get('Pagination');
		$this->eliminados = $jinput->get('comp_eliminados','0','string');
		$this->core_actualizado = $jinput->get('core_actualizado','0','string');
		$this->comps_actualizados = $jinput->get('componentes_actualizados','0','string');
		$this->comp_ok = $jinput->get('comp_ok','0','string');
		$this->new_versions = $jinput->get('new_versions','0','string');
		$this->plugin_enabled = $jinput->get('plugin_enabled','0','string');
		$this->logs_pending = $jinput->get('logs_pending','0','string');

		// Ponemos los datos y la paginación en el template
		$this->update_database_plugin_exists = $update_database_plugin_exists;
		$this->update_database_plugin_enabled = $update_database_plugin_enabled;
		$this->last_check = $last_check;
		$this->database_version = $database_version;
		$this->database_message = $database_message;
		if ( $update_database_plugin_exists ) {
			$this->plugin_id = $plugin_id;
		}       
		
        parent::display($tpl);
    }


}