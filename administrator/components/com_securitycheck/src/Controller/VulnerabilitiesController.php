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

class VulnerabilitiesController extends SecuritycheckBaseController
{

	/**
	* Muestra los componentes de la BBDD
	*/
	function mostrar()
	{
	$msg = Text::_( 'COM_SECURITYCHECK_SHOW_MSG' );
	$this->setRedirect( 'index.php?option=com_securitycheck', $msg );
	}

	/**
	 * Busca cambios entre los componentes almacenados en la BBDD y la BBDD de vulnerabilidades
	 */
	function buscar()
	{
	$model = $this->getModel('securitychecks');
	if(!$model->buscar()) {
		$msg = Text::_( 'COM_SECURITYCHECK_CHECK_FAILED' );
		Factory::getApplication()->enqueueMessage($msg, 'notice');
	} else {
		$eliminados = $jinput->get('comp_eliminados',0,int);
		$core_actualizado = $jinput->get('core_actualizado',0,int);
		$comps_actualizados = $jinput->get('componentes_actualizados',0,int);
		$comp_ok = Text::_( 'COM_SECURITYCHECK_CHECK_OK' );
		$msg = Text::_( $eliminados ."</li><li>" .$core_actualizado ."</li><li>" .$comps_actualizados ."</li><li>" .$comp_ok );
	}
	$this->setRedirect( 'index.php?option=com_securitycheck', $msg );
	}
}