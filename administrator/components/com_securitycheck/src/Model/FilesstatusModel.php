<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Model;

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class FilesstatusModel extends ListModel
{
	/** @var object Pagination */
	var $_pagination = null;

	/** @var int Total number of files of Pagination */
	var $total = 0;

	/** @var array The files to process */
	private $Stack = array();

	/** @var int Total numbers of file/folders in this site */
	public $files_scanned = 0;

	/** @var int Numbers of files/folders with  incorrect permissions*/
	public $files_with_incorrect_permissions = 0;

	/** @var string Path to the folder where scans will be stored */
	private $folder_path = '';

	/** @var string 10 chars strings to add to the filemanager's name */
	private $filemanager_name = '';


	public function __construct($config = array())
    {
		
		// Establecemos la ruta donde se almacenarán los escaneos
		$this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheck'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
		
		// Obtenemos el nombre de los escaneos anteriores
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select(array($db->quoteName('storage_value')))
			->from($db->quoteName('#__securitycheck_storage'))
			->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
		$db->setQuery($query);
		$stack = $db->loadResult();	
		$stack = json_decode($stack, true);
		
		if(!empty($stack)) {
			$this->filemanager_name = $stack['filename'];
		}
		
		// Establecemos el tamaño máximo de memoria que el script puede consumir
		$params = ComponentHelper::getParams('com_securitycheck');
		$memory_limit = $params->get('memory_limit','128M');
		ini_set('memory_limit',$memory_limit);

		$mainframe = Factory::getApplication();
	 
		// Obtenemos las variables de paginación de la petición
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$jinput = Factory::getApplication()->input;
		$limitstart = $jinput->get('limitstart', 0, 'int');

		// En el caso de que los límites hayan cambiado, los volvemos a ajustar
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		/* Limitamos a 100 el nmero de archivos mostrados para evitar que el array desborde la memoria mxima establecida por PHP */
		if ( $limit == 0 ){
			$this->setState('limit', 100);
			$this->setState('showall', 1);
		} else {
			$this->setState('limit', $limit);
		}
		$this->setState('limitstart', $limitstart);	
		
		parent::__construct($config);
	}

	/* Función que obtiene un array con los datos que serán mostrados en la opción 'filestatus' */
	function loadStack($opcion,$field)
	{
		(int) $lower_limit = 0; 
		
		// Establecemos el tamaño máximo de memoria que el script puede consumir
		$params = ComponentHelper::getParams('com_securitycheck');
		$memory_limit = $params->get('memory_limit','128M');
		ini_set('memory_limit',$memory_limit);
		
		$db = $this->getDbo();
		
		switch ($opcion) {
			case "permissions":
				// Leemos el contenido del fichero
				if ( file_exists($this->folder_path.$this->filemanager_name) ) {
					$stack = file_get_contents($this->folder_path.$this->filemanager_name);
					// Eliminamos la parte del fichero que evita su lectura al acceder directamente
					$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
				}
				
				if(empty($stack)) {
					$this->Stack = array();
					return;
				}
				break;
			case "filemanager_resume":
				$query = $db->getQuery(true)
					->select(array($db->quoteName('storage_value')))
					->from($db->quoteName('#__securitycheck_storage'))
					->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
				$db->setQuery($query);
				$stack = $db->loadResult();
				
				if(empty($stack)) {
					$this->files_scanned = 0;
					$this->files_with_incorrect_permissions = 0;
					return;
				}
				break;
		
		}

		$stack = json_decode($stack, true);
		
		/* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array */
		$upper_limit = $this->getState('limitstart');
		$lower_limit = $this->getState('limit');
							
		/* Obtenemos los valores de los filtros */
		$filter_permissions_status = $this->state->get('filter.filemanager_permissions_status');
		$filter_permissions_status = Text::_($filter_permissions_status);
		$filter_kind_original = $this->state->get('filter.filemanager_kind');
		$filter_kind = Text::_($filter_kind_original);
		$search = "";
		if (!empty($this->state->get('filter.search'))){
			$search = htmlentities($this->state->get('filter.search'));
		}		
			
		switch ($field) {
			case "file_manager":
				$filtered_array = array();
				/* Si el campo 'search' no está vacío, buscamos en todos los campos del array */			
				if (!empty($search) ) {
					if ($filter_kind_original == "COM_SECURITYCHECK_FILEMANAGER_TITLE_FILE") {
						$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind,$search) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) && ( (strstr($element['path'],$search)) || (strstr($element['name'],$search)) || (strstr($element['extension'],$search)) || (strstr($element['size'],$search)) || (strstr($element['last_modified'],$search)) || (strstr($element['permissions'],$search)) ) );} ));
					} else if ($filter_kind_original == "COM_SECURITYCHECK_FILEMANAGER_TITLE_FOLDER") {
						$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind,$search) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) && ( (strstr($element['path'],$search)) || (strstr($element['name'],$search)) || (strstr($element['last_modified'],$search)) || (strstr($element['permissions'],$search)) ) );} ));
					}
				} else {
					$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) );} ));
				}
				$this->total = count($filtered_array);
				/* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
				$this->Stack = array_splice($filtered_array, $upper_limit, $lower_limit);
				return ($this->Stack);
			case "files_scanned":
				$this->files_scanned = $stack['files_scanned'];
				return ($this->files_scanned);
			case "files_with_incorrect_permissions":
				$this->files_with_incorrect_permissions = $stack['files_with_incorrect_permissions'];
				return ($this->files_with_incorrect_permissions);
		}

	}

	protected function populateState($ordering = null,$direction = null)
	{
		// Inicializamos las variables
		$app		= Factory::getApplication();

		$search = $app->getUserStateFromRequest('filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$filemanager_kind = $app->getUserStateFromRequest('filter.filemanager_kind', 'filter_filemanager_kind');
		$this->setState('filter.filemanager_kind', $filemanager_kind);
		$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
		$this->setState('filter.filemanager_permissions_status', $filemanager_permissions_status);
		$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
			
							
		parent::populateState();
	}

	/* Función que obtiene el valor del campo 'last_check' de la tabla '#__securitycheck_file_manager' */
	function get_last_timestamp()
	{
		// Creamos el nuevo objeto query
		$db = $this->getDbo();
			
		// Construimos la consulta...
		$query = ' SELECT last_check FROM #__securitycheck_file_manager WHERE id=1 ';

		// ... y la lanzamos
		$db->setQuery( $query );	
		$result = $db->loadResult();
		
		// Devolvemos el resultado
		return $result;	
	}

	/* Función para establecer el valor de un campo de la tabla '#_securitycheck_file_manager' */
	function set_campo_filemanager($campo,$valor)
	{
		// Creamos el nuevo objeto query
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Sanitizamos las entradas
		$campo_sanitizado = $db->escape($campo);
		$valor_sanitizado = $db->Quote($db->escape($valor));

		// Construimos la consulta...
		$query->update('#__securitycheck_file_manager');
		$query->set($campo_sanitizado .'=' .$valor_sanitizado);
		$query->where('id=1');

		// ... y la lanzamos
		$db->setQuery( $query );
		$db->execute();
	}

	/* Función para obtener el valor de un campo de la tabla '#_securitycheck_file_manager' */
	function get_campo_filemanager($campo)
	{
		// Creamos el nuevo objeto query
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Sanitizamos las entradas
		$campo_sanitizado = $db->Quote($db->escape($campo));
		
		// Construimos la consulta...
		$query->select($campo);
		$query->from('#__securitycheck_file_manager');
		$query->where('id=1');
		
		// ... y la lanzamos
		$db->setQuery( $query );
		$result = $db->loadResult();
		
		// Devolvemos el resultado
		return $result;	
	}

}