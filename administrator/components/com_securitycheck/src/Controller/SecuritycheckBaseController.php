<?php
/**
 * @Securitycheckpro component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */
 
namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller;

// No Permission
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\DisplayController;

class SecuritycheckBaseController extends DisplayController
{
    /* Redirecciona las peticiones al Panel de Control */
	function redireccion_control_panel()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck' );
	}

	/* Redirecciona las peticiones a System Info */
	function redireccion_system_info()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=sysinfo&'. Session::getFormToken() .'=1' );
	}

}
