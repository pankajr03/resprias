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
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Controller\SecuritycheckBaseController;

class LogsController extends SecuritycheckBaseController
{

	function mostrar()
	{
		$msg = Text::_( 'COM_SECURITYCHECK_SHOW_MSG' );
		$this->setRedirect( 'index.php?option=com_securitycheck', $msg );
	}
	
	function view_logs()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&view=logs' );
	}

	
	/* Redirecciona las peticiones al componente */
	function redireccion()
	{
		$this->setRedirect( 'index.php?option=com_securitycheck&controller=securitycheck' );
	}

	/* Filtra los logs según el término de búsqueda especificado*/
	function search()
	{
		$model = $this->getModel('logs');
		if(!$model->search()) {
			$msg = Text::_( 'COM_SECURITYCHECK_CHECK_FAILED' );
			Factory::getApplication()->enqueueMessage($msg, 'notice');		
		} else {
			$this->view_logs();
		}
		
	}

	/**
	 * Marcar log(s) como leídos
	 */
	function mark_read()
	{
		$model = $this->getModel('logs');
		$read = $model->mark_read();
		$this->view_logs();
	}

	/**
	 * Marcar log(s) como no leídos
	 */
	function mark_unread()
	{
		$model = $this->getModel('logs');
		$read = $model->mark_unread();
		$this->view_logs();
	}

	/**
	 * Borrar log(s) de la base de datos
	 */
	function delete()
	{
		$model = $this->getModel('logs');
		$read = $model->delete();
		$this->view_logs();
	}

	/**
	 * Borrar todos los log(s) de la base de datos
	 */
	function delete_all()
	{
		$model = $this->getModel('logs');
		$read = $model->delete_all();
		$this->view_logs();
	}

}