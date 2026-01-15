<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\View\Protection;

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
		
		ToolBarHelper::title( Text::_( 'Securitycheck' ).' | ' .Text::_('COM_SECURITYCHECK_CPANEL_HTACCESS_PROTECTION_TEXT'), 'securitycheck' );
		// Si existe el fichero .htaccess, mostramos la opción para borrarlo.
		// Obtenemos el modelo
		$model = $this->getModel();
		// ... y el tipo de servidor web
		$mainframe = Factory::getApplication();
		$server = $mainframe->getUserState("server",'apache');
		
		$exist_htaccess = false;
		
		if ( file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . ".htaccess") ) {
			$exist_htaccess = true;
		}

		if ( $server == 'apache' ) {
			if ( $exist_htaccess ) {
				ToolBarHelper::custom('delete_htaccess','file-remove','file-remove','COM_SECURITYCHECK_DELETE_HTACCESS',false);
			}
			ToolBarHelper::custom('protect','key','key','COM_SECURITYCHECK_PROTECT',false);
		} else if ( $server == 'nginx' ) {
			ToolBarHelper::custom('generate_rules','key','key','COM_SECURITYCHECK_GENERATE_RULES',false);
		}

		ToolBarHelper::save();
		ToolBarHelper::apply();
		ToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL',false);
		ToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO',false);

		// Obtenemos la configuración actual...
		$config = $model->getConfig();
		// ... y la que hemos aplicado en el fichero .htaccess existente
		$config_applied = $model->GetconfigApplied();

		$this->protection_config = $config;
		$this->config_applied = $config_applied;
		$this->ExistsHtaccess = $exist_htaccess;
		$this->server = $server;
		
        parent::display($tpl);
    }


}