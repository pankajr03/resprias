<?php
/**
 * @Securitycheckpro component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Model;

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Date\Date;

class LogsModel extends ListModel
{
	/**
	* Array de datos
	* @var array
	*/
	var $_data;
	/**
	/**
	* Total items
	* @var integer
	*/
	var $_total = null;
	/**
	* Objeto Pagination
	* @var object
	*/
	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
		
		
		$mainframe = Factory::getApplication();
		
		// Obtenemos las variables de paginación de la petición
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$jinput = Factory::getApplication()->input;
		$limitstart = $jinput->get('limitstart', 0, 'int');
		
		// En el caso de que los límites hayan cambiado, los volvemos a ajustar
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		
	}

	/***/
	protected function populateState($ordering = null,$direction = null)
	{
		// Inicializamos las variables
		$app		= Factory::getApplication();
		
		$search = $app->getUserStateFromRequest('filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$description = $app->getUserStateFromRequest('filter.description', 'filter_description');
		$this->setState('filter.description', $description);
		$type = $app->getUserStateFromRequest('filter.type', 'filter_type');
		$this->setState('filter.type', $type);
		$leido = $app->getUserStateFromRequest('filter.leido', 'filter_leido');
		$this->setState('filter.leido', $leido);
		$datefrom = $app->getUserStateFromRequest('datefrom', 'datefrom');
		$this->setState('datefrom', $datefrom);
		$dateto = $app->getUserStateFromRequest('dateto', 'dateto');
		$this->setState('dateto', $dateto);
			
		parent::populateState();
	}


	/* 
	* Función para obtener el número de registros de la BBDD 'securitycheck_logs'
	*/
	function getTotal()
	{
	// Cargamos el contenido si es que no existe todavía
	if (empty($this->_total)) {
		$query = $this->_buildQuery();
		$this->_total = $this->_getListCount($query);
	}
	return $this->_total;
	}

	/* 
	* Función para obtener el número de registros de la BBDD 'securitycheck_logs' según la opción escogida por el usuario
	*/
	function getFilterTotal()
	{
	// Cargamos el contenido si es que no existe todavía
	if (empty($this->_total)) {
		$query = $this->_buildFilterQuery();
		$this->_total = $this->_getListCount($query);
	}
	return $this->_total;
	}

	/* 
	* Función para la paginación 
	*/
	function getPagination()
	{
	// Cargamos el contenido si es que no existe todavía
	if (empty($this->_pagination)) {		
		$this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
	}
	return $this->_pagination;
	}

	/* 
	* Función para la paginación filtrada según la opción escogida por el usuario
	*/
	function getFilterPagination()
	{
	// Cargamos el contenido si es que no existe todavía
	if (empty($this->_pagination)) {
		$this->_pagination = new Pagination($this->getFilterTotal(), $this->getState('limitstart'), $this->getState('limit') );
	}
	return $this->_pagination;
	}

	/*
	* Devuelve todos los componentes almacenados en la BBDD 'securitycheck_logs'
	*/
	function _buildQuery()
	{
	$query = ' SELECT * '
	. ' FROM #__securitycheck_logs where `marked` = "0" ORDER BY id DESC'
	;
	return $query;
	}

	/*
	* Devuelve todos los componentes almacenados en la BBDD 'securitycheck_logs' filtrados según las opciones establecidas por el usuario
	*/
	function _buildFilterQuery()
	{
		// Creamos el nuevo objeto query
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Sanitizamos la entrada
		$search = $this->state->get('filter.search');
		$search = $db->Quote('%'.$db->escape($search, true).'%');
			
		$query->select('*');
		$query->from('#__securitycheck_logs AS a');
		$query->where('(a.ip LIKE '.$search.' OR a.time LIKE '.$search.' OR a.description LIKE '.$search.' OR a.uri LIKE '.$search.')');
		
		// Filtramos la descripcion
		if ($description = $this->getState('filter.description')) {
			$query->where('a.tag_description = '.$db->quote($description));
		}
		
		// Filtramos el tipo
		if ($log_type = $this->getState('filter.type')) {
			$query->where('a.type = '.$db->quote($log_type));
		}
			
		// Filtramos leido/no leido
		$leido = $this->getState('filter.leido');
		
		if (empty($leido)) {
			$leido = 0;
		}
		
		if (is_numeric($leido)) {
			$query->where('a.marked = '.(int) $leido);
		}	
		
		// Ordenamos el resultado
		$query = $query . ' ORDER BY a.id DESC';
	return $query;
	}

	/**
	 * Método para cargar todas las vulnerabilidades de los componentes
	 */
	function getData()
	{
		// Cargamos los datos
		if (empty( $this->_data )) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
			
		return $this->_data;
	}

	/**
	 * Método para cargar todas las vulnerabilidades de los componentes especificadas en los términos de búsqueda
	 */
	function getFilterData()
	{
		// Cargamos los datos
		if (empty( $this->_data )) {
			$query = $this->_buildFilterQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
				
		return $this->_data;
	}

	/* Función para cambiar el estado de un array de logs de no leído a leído */
	function mark_read(){
		$jinput = Factory::getApplication()->input;
		$uids = $jinput->get('cid', 0,'array');
		
		ArrayHelper::toInteger($uids, array());
		
		$db = $this->getDbo();
		foreach($uids as $uid) {
			$sql = "UPDATE `#__securitycheck_logs` SET marked=1 WHERE id='{$uid}'";
			$db->setQuery($sql);
			$db->execute();	
		}
	}

	/* Función para cambiar el estado de un array de logs de leído a no leído */
	function mark_unread(){
		$jinput = Factory::getApplication()->input;
		$uids = $jinput->get('cid', 0,'array');
		
		ArrayHelper::toInteger($uids, array());
		
		$db = $this->getDbo();
		foreach($uids as $uid) {
			$sql = "UPDATE `#__securitycheck_logs` SET marked=0 WHERE id='{$uid}'";
			$db->setQuery($sql);
			$db->execute();	
		}
	}

	/* Función para borrar un array de logs */
	function delete(){
		$jinput = Factory::getApplication()->input;
		$uids = $jinput->get('cid', 0,'array');
		
		ArrayHelper::toInteger($uids, array());
		
		$db = $this->getDbo();
		foreach($uids as $uid) {
			$sql = "DELETE FROM `#__securitycheck_logs` WHERE id='{$uid}'";
			$db->setQuery($sql);
			$db->execute();	
		}
	}

	/* Función para borrar todos los logs */
	function delete_all(){
		
		$db = $this->getDbo();
		$sql = "TRUNCATE `#__securitycheck_logs`";
		$db->setQuery($sql);
		$db->execute();	
	}

}