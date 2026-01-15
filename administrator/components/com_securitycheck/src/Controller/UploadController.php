<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */
 
namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Session\Session;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\SecuritycheckBaseController;

class UploadController extends SecuritycheckBaseController
{
		
	/* Acciones al pulsar el botón 'Import settings' */
	function read_file(){
		$model = $this->getModel("upload");
		$res = $model->read_file();
		
		if ($res) {
			$this->setRedirect( 'index.php?option=com_securitycheck' );		
		} else {
			$this->setRedirect( 'index.php?option=com_securitycheck&controller=filemanager&view=upload&'. Session::getFormToken() .'=1' );	
		}
	}
			
}
