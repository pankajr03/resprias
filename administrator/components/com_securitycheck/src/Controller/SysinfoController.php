<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */
 
namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller;

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Session\Session;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\SecuritycheckBaseController;

class SysinfoController extends SecuritycheckBaseController
{
	/* Redirecciona a la opción htaccess protection */
	function GoToHtaccessProtection()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=protection&'. Session::getFormToken() .'=1' );	
	}
	
	/* Redirecciona a la opción de mostrar las vulnerabilidades */
	function GoToVuln()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=vulnerabilities&'. Session::getFormToken() .'=1' );	
	}
	
	/* Redirecciona a la opción de permisos */
	function GoToPermissions()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=filemanager&'. Session::getFormToken() .'=1' );	
	}
}