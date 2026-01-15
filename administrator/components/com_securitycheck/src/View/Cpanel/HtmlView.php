<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\SysinfoModel;

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
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_CONTROLPANEL'), 'securitycheck' );
		
		
		// Obtenemos los datos del modelo...
		$model = $this->getModel();
		$firewall_plugin_enabled = $model->PluginStatus(1);
		$update_database_plugin_enabled = $model->PluginStatus(3);
		$update_database_plugin_exists = $model->PluginStatus(4);
		$spam_protection_plugin_enabled = $model->PluginStatus(5);
		$spam_protection_plugin_exists = $model->PluginStatus(6);
		$logs_pending = $model->LogsPending();
		$sc_plugin_id = $model->get_plugin_id(1);
		$params = ComponentHelper::getParams('com_securitycheck');
		// ... y el tipo de servidor web
		$mainframe = Factory::getApplication();
		$server = $mainframe->getUserState("server",'apache');
		// ... y las estadísticas de los logs
		$last_year_logs = $model->LogsByDate('last_year');
		$this_year_logs = $model->LogsByDate('this_year');
		$last_month_logs = $model->LogsByDate('last_month');
		$this_month_logs = $model->LogsByDate('this_month');
		$last_7_days = $model->LogsByDate('last_7_days');
		$yesterday = $model->LogsByDate('yesterday');
		$today = $model->LogsByDate('today');
		$total_firewall_rules = $model->LogsByType('total_firewall_rules');
		$total_blocked_access = $model->LogsByType('total_blocked_access');
		$total_user_session_protection = $model->LogsByType('total_user_session_protection');
		$easy_config_applied = $model->Get_Easy_Config();
		
		// Obtenemos el status de la seguridad
		
		$overall = new SysinfoModel();
		$overall = $overall->getInfo();		
		$overall = $overall['overall_joomla_configuration'];
		
		// Ponemos los datos en el template
		$this->firewall_plugin_enabled = $firewall_plugin_enabled;
		$this->update_database_plugin_enabled = $update_database_plugin_enabled;
		$this->update_database_plugin_exists = $update_database_plugin_exists;
		$this->spam_protection_plugin_enabled = $spam_protection_plugin_enabled;
		$this->spam_protection_plugin_exists= $spam_protection_plugin_exists;
		$this->logs_pending = $logs_pending;
		$this->sc_plugin_id = $sc_plugin_id;
		$this->server = $server;
		$this->last_year_logs = $last_year_logs;
		$this->this_year_logs = $this_year_logs;
		$this->last_month_logs = $last_month_logs;
		$this->this_month_logs = $this_month_logs;
		$this->last_7_days = $last_7_days;
		$this->yesterday = $yesterday;
		$this->today = $today;
		$this->total_firewall_rules = $total_firewall_rules;
		$this->total_blocked_access = $total_blocked_access;
		$this->total_user_session_protection = $total_user_session_protection;
		$this->easy_config_applied = $easy_config_applied;
		$this->overall = $overall;
        
		
        parent::display($tpl);
    }


}