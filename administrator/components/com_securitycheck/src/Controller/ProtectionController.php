<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */
 
namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\SecuritycheckBaseController;

class ProtectionController extends SecuritycheckBaseController
{

	/* Redirecciona las peticiones al componente */
	function redireccion()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=protection' );
	}


	/* Guarda los cambios y redirige al cPanel */
	public function save()
	{
		Session::checkToken() or die('Invalid Token');
		$model = $this->getModel('protection');
		$jinput = Factory::getApplication()->input;
		$data = $jinput->getArray($_POST);
		$model->saveConfig($data);

		$this->setRedirect('index.php?option=com_securitycheck&view=cpanel&'. Session::getFormToken() .'=1',Text::_('COM_SECURITYCHECK_CONFIGSAVED'));
	}

	/* Guarda los cambios */
	public function apply()
	{
		$this->save();
		$this->setRedirect('index.php?option=com_securitycheck&controller=protection&view=protection&'. Session::getFormToken() .'=1',Text::_('COM_SECURITYCHECK_CONFIGSAVED'));
	}

	/* Modifica o crear el archivo .htaccess con las configuraciones seleccionadas por el usuario */
	function protect()
	{
		$model = $this->getModel("protection");

		$status = $model->protect();
		$url = 'index.php?option=com_securitycheck&controller=protection&view=protection&'. Session::getFormToken() .'=1';
		if($status) {
			$this->setRedirect($url,Text::_('COM_SECURITYCHECK_PROTECTION_APPLIED'));
		} else {
			$this->setRedirect($url,Text::_('COM_SECURITYCHECK_PROTECTION_NOTAPPLIED'),'error');
		}
		
	}

	/* Borra el archivo .htaccess */
	function delete_htaccess()
	{
		$model = $this->getModel("protection");

		$status = $model->delete_htaccess();
		$url = 'index.php?option=com_securitycheck&controller=protection&view=protection&'. Session::getFormToken() .'=1';
		if($status) {
			$this->setRedirect($url,Text::_('COM_SECURITYCHECK_HTACCESS_DELETED'));
		} else {
			$this->setRedirect($url,Text::_('COM_SECURITYCHECK_HTACCESS_NOT_DELETED'),'error');
		}
		
	}

	/* Muestra las configuraciones escogidas en una ventana, en lugar de aplicarlas mediante un archivo .htaccess.  Esto es necesario en servidores NGINX*/
	function generate_rules()
	{
		$txt_content = '';
		
		$model = $this->getModel("protection");

		$rules = $model->generate_rules();
		
		$txt_content .= $rules;
		// Mandamos el contenido al navegador
		@ob_end_clean();	
		ob_start();	
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment;filename=securitycheck_nginx_rules.txt' );
		print $txt_content;
		exit();
			
	}
}