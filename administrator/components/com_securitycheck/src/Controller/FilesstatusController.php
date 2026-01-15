<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */
 
namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller;

// Protección frente a accesos no autorizados
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\SecuritycheckBaseController;

class FilesstatusController extends SecuritycheckBaseController
{

	/* Redirecciona las peticiones al Panel de Control de la Gestión de Archivos */
	function redireccion_file_manager_control_panel()
	{		
		$this->setRedirect( 'index.php?option=com_securitycheck&view=filemanager&'. Session::getFormToken() .'=1' );
	}

	public function getEstado() {
		$model = $this->getModel("filemanager");
		$message = $model->get_campo_filemanager('estado_cambio_permisos');
		$message = Text::_('COM_SECURITYCHECK_FILEMANAGER_' .$message);
		echo $message;
	}

}