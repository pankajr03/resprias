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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\CMS\Pagination\Pagination;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\BaseModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class VulnerabilitiesModel extends BaseModel
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
	/**
	* Objeto Pagination
	* @var object
	*/
	var $_pagination = null;
	/**
	* Columnas de #__securitycheck
	* @var integer
	*/
	var $_dbrows = null;

	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;
			
		$mainframe = Factory::getApplication();	
		$jinput = $mainframe->input;
	 
		// Obtenemos las variables de paginación de la petición
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$jinput = Factory::getApplication()->input;
		$limitstart = $jinput->get('limitstart', 0, 'int');

		// En el caso de que los límites hayan cambiado, los volvemos a ajustar
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);	
	}

	/* 
	* Función para obtener todo los datos de la BBDD 'securitycheck' en forma de array 
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
	* Devuelve todos los componentes almacenados en la BBDD 'securitycheck'
	*/
	function _buildQuery()
	{
	$query = ' SELECT * '
	. ' FROM #__securitycheck '
	;
	return $query;
	}

	/*
	Obtiene la versión de un determinado componente en una de las BBDD. Pasamos como parámetro la BBDD donde buscar, el campo de la tabla sobre el que hacerlo y
	el nombre que buscamos.
	 */
	function version_componente($nombre,$database,$campo)
	{

	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
		
	// Sanitizamos las entradas
	$database = $db->escape($database);
	$campo = $db->escape($campo);
	$nombre = $db->Quote($db->escape($nombre));

	// Construimos la consulta
	$query->select('Installedversion');
	$query->from('#__' .$database);
	$query->where($campo .'=' .$nombre);

	$db->setQuery( $query );
	$result = $db->loadResult();
	return $result;
	}

	/*
	* Compara los componentes de la BBDD de 'securitycheck' con los de 'securitycheck_db" y actualiza los componentes que sean vulnerables 
	*/
	function chequear_vulnerabilidades(){
		// Extraemos los componentes de 'securitycheck'
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		$query = $this->_buildQuery();
        $db->setQuery($query);
        $components = $db->loadAssocList();
		
		// Versión de Joomla instalada
		$local_joomla_branch = explode(".", JVERSION);
		if ( (is_array($local_joomla_branch)) && (array_key_exists('0',$local_joomla_branch)) ) {
			$local_joomla_branch = $local_joomla_branch[0];
		} else {
			$local_joomla_branch = 5;
		}
		
        // Extraemos los componentes vulnerables para nuestra versión de Joomla
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__securitycheck_db'))
            ->where($db->quoteName('Joomlaversion').' = '.$db->quote($local_joomla_branch));
        $db->setQuery($query);
		$vuln_components = $db->loadAssocList();		
		
		foreach ($vuln_components as $vulnerable_product)
        {
			$valor_campo_vulnerable = "Si"; // Valor que tendrá el campo 'Vulnerable' cuando se actualice. También puede tener el valor 'Indefinido'.
			$components_key = array_search($vulnerable_product['Product'], array_column($components, 'Product'));
			
			if ($components_key === false) {
				// El producto vulnerable no está instalado
			} else {			
				if ( $components[$components_key]['Type'] == $vulnerable_product['Type'] ) {
					$modvulnversion = $vulnerable_product['modvulnversion']; //Modificador sobre la versión de la extensión
                    $db_version = $components[$components_key]['Installedversion']; // Versión de la extensión instalada
                    $vuln_version = $vulnerable_product['Vulnerableversion']; // Versión de la extensión vulnerable
					                
                    // Usamos la funcion 'version_compare' de php para comparar las versiones del producto instalado y la del componente vulnerable					
                    $version_compare = version_compare($db_version, $vuln_version, $modvulnversion);
					if ( ($version_compare) || ($vuln_version == '---') ) {
						                                                                    
                        try
                        {
							$res_actualizar = $this->actualizar_registro($vulnerable_product['Product'], 'securitycheck', 'Product', $valor_campo_vulnerable, 'Vulnerable');
							if ( $res_actualizar ) { // Se ha actualizado la BBDD correctamente                            
							} else {                            
								Factory::getApplication()->enqueueMessage('COM_SECURITYCHECK_UPDATE_VULNERABLE_FAILED' ."'" . $vulnerable_product['Product'] ."'", 'error');
							}
                        } catch (Exception $e)
                        {    
                            Factory::getApplication()->enqueueMessage('COM_SECURITYCHECK_UPDATE_VULNERABLE_FAILED' ."'" . $vulnerable_product['Product'] ."'", 'error');                         
                        }						
					} else {
						$res_actualizar = $this->actualizar_registro($vulnerable_product['Product'], 'securitycheck', 'Product', 'No', 'Vulnerable');
					}
				}
			}
		}		
		
	}


	/*
	Actualiza el campo '$campo_set'  de un registro en la BBDD pasada como parámetro.
	 */
	function actualizar_registro($nombre,$database,$campo,$nuevo_valor,$campo_set)
	{

	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
		
	// Sanitizamos las entradas
	$nombre = $db->Quote($db->escape($nombre));
	$database = $db->escape($database);
	$campo = $db->escape($campo);
	$nuevo_valor = $db->Quote($db->escape($nuevo_valor));
	$campo_set = $db->escape($campo_set);


	// Construimos la consulta
	$query->update('#__' .$database);
	$query->set($campo_set .'=' .$nuevo_valor);
	$query->where($campo .'=' .$nombre);

	$db->setQuery( $query );
	$result = $db->execute();
	return $result;

	}


	/*
	Busca el nombre de un registro en la BBDD pasada como parámetro. Devuelve true si existe y false en caso contrario.
	 */
	function buscar_registro($nombre,$database,$campo)
	{
	$encontrado = false;

	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	$query = $db->getQuery(true);
		
	// Sanitizamos las entradas
	$database = $db->escape($database);
	$campo = $db->escape($campo);
	$nombre = $db->Quote($db->escape($nombre));

	// Construimos la consulta
	$query->select('*');
	$query->from('#__' .$database);
	$query->where($campo .'=' .$nombre);

	$db->setQuery( $query );
	$result = $db->loadAssocList();

	if ( $result ){
	$encontrado = true;
	}

	return $encontrado;
	}

	/*
	Inserta un registro en la BBDD. Devuelve true si ha tenido éxito y false en caso contrario.
	 */
	function insertar_registro($nombre,$version,$tipo)
	{
	$db = Factory::getContainer()->get(DatabaseInterface::class);

	// Sanitizamos las entradas
	$nombre = $db->escape($nombre);
	$version = $db->escape($version);
	$tipo = $db->escape($tipo);

	$valor = (object) array(
	'Product' => $nombre,
	'Installedversion' => $version,
	'Type' => $tipo
	);

	$result = $db->insertObject('#__securitycheck', $valor, 'id');
	return $result;
	}

	/*
	Compara la BBDD #_securitycheck con #_extensions para eliminar componentes desinstalados del sistema y que figuran en dicha BBDD. Los componentes que 
	figuran en #_securitycheck se pasan como variable */
	function eliminar_componentes_desinstalados()
	{
	$db = Factory::getContainer()->get(DatabaseInterface::class);
	$query = 'SELECT * FROM #__securitycheck';
	$db->setQuery( $query );
	$db->execute();
	$regs_securitycheck = $db->loadAssocList();
	$i = 0;
	$comp_eliminados = 0;
	foreach ($regs_securitycheck as $indice){
		$nombre = $regs_securitycheck[$i]['Product'];
		$database = 'extensions';
		$buscar_componente = $this->buscar_registro( $nombre, $database, 'element' );
		if ( !($buscar_componente) ){ /*Si el componente no existe en #_extensions, lo eliminamos  de #_securitycheck */
			if ($nombre != 'Joomla!'){ /* Este componente no existe como extensión*/
				$db = Factory::getContainer()->get(DatabaseInterface::class);
				// Sanitizamos las entradas
				$nombre = $db->Quote($db->escape($nombre));
				$query = 'DELETE FROM #__securitycheck WHERE Product=' .$nombre;
				$db->setQuery( $query );
				$db->execute();
				$comp_eliminados++;			
			}
		}	
		$i++;
	} 
	$mensaje_eliminados = Text::_('COM_SECURITYCHECK_DELETED_COMPONENTS');
	$jinput->set('comp_eliminados', $mensaje_eliminados .$comp_eliminados);
	}

	/*
	Extrae los nombres de los componentes instalados y actualiza la BBDD de nuestro componente con dichos nombres.
	Un ejemplo de cómo almacena Joomla esta información es el siguiente:

	{"legacy":false,"name":"Securitycheck","type":"component","creationDate":"2011-04-12","author":"Jose A. Luque","copyright":"Copyright Info",
	"authorEmail":"contacto@protegetuordenador.com","authorUrl":"http:\/\/www.protegetuordenador.com","version":"1.00",
	"description":"COM_SECURITYCHECK_DESCRIPTION","group":""} 

	Esta función debe extraer la información que nos interesa truncando substrings.
	 */
	function actualizarbbdd($registros)
	{
	$i = 0;
	/* Obtenemos y guardamos la versión de Joomla */
	$jversion = new Version();
	$joomla_version = $jversion->getShortVersion();
	$buscar_componente = $this->buscar_registro( 'Joomla!', 'securitycheck', 'Product' );
	if ( $buscar_componente ){ 
		$version_componente = $this->version_componente( 'Joomla!', 'securitycheck', 'Product' );	
		if ($joomla_version <> $version_componente){
		 /* Si la versión instalada en el sistema es distinta de la de la bbdd, actualizamos la bbdd. Esto sucede cuando se actualiza la versión de Joomla */
		 $resultado_update = $this->actualizar_registro('Joomla!', 'securitycheck', 'Product', $joomla_version, 'InstalledVersion');
		 $mensaje_actualizados = Text::_('COM_SECURITYCHECK_CORE_UPDATED');
		 $jinput->set('core_actualizado', $mensaje_actualizados);	 
		}
	} else {  /* Hacemos un insert en la base de datos con el nombre y la versión del componente */
	$resultado_insert = $this->insertar_registro( 'Joomla!', $joomla_version, 'core' );
	} 
	$componentes_actualizados = 0;
	foreach ($registros as $indice){
		$nombre = $registros[$i]['element'];
		/*Sobre el ejemplo, 'sub_str1' contiene    "version":"1.00","description":"COM_SECURITYCHECK_DESCRIPTION","group":""} */
		$sub_str1 = strstr($registros[$i]['manifest_cache'], "version");
		/*Sobre el ejemplo, 'sub_str2' contiene   :"1.00","description":"COM_SECURITYCHECK_DESCRIPTION","group":""} */
		$sub_str2 = strstr($sub_str1, ':');
		/*Sobre el ejemplo, 'sub_str3' contiene   "1.00" */	
		$sub_str3 = substr($sub_str2, 1, strpos($sub_str2,',')-1); 
		/*Sobre el ejemplo, 'version' contiene   1.00 */
		$version = trim($sub_str3,'"');
		$buscar_componente = $this->buscar_registro( $nombre, 'securitycheck', 'Product' );
		if ( $buscar_componente )
		{ /* El componente existe en la BBD; hacemos un update de la versión .  */
			$version_componente = $this->version_componente( $nombre, 'securitycheck', 'Product' );
			if ($version <> $version_componente){		
				/* Si la versión instalada en el sistema es distinta de la de la bbdd, actualizamos la bbdd. Esto sucede cuando se actualiza el componente */
				$resultado_update = $this->actualizar_registro($nombre, 'securitycheck', 'Product', $version, 'InstalledVersion');
				$componentes_actualizados++;			
			}
		} else {  /* Hacemos un insert en la base de datos con el nombre y la versión del componente */
			$resultado_insert = $this->insertar_registro( $nombre, $version, 'component');	
			$componentes_actualizados++;
		} 
		$i++;
	}

	if ($componentes_actualizados > 0){
		$mensaje_actualizados = Text::_('COM_SECURITYCHECK_COMPONENTS_UPDATED');
		$jinput = Factory::getApplication()->input;
		$jinput->set('componentes_actualizados', $mensaje_actualizados .$componentes_actualizados);
	}

	/* Chequeamos si existe algún componente el la BBDD que haya sido desinstalado. Esto se comprueba comparando el número de registros en #_securitycheck ($dbrows)
	y el de #_extensions  ($registros)*/
	$query = $this->_buildQuery();
	$this->_dbrows = $this->_getListCount($query);
	$registros_long = count($registros);

	if ( $this->_dbrows == $registros_long + 1)  /* $dbrows siempre contiene un elemento más que $registros_long porque incluye el core de Joomla */
	{
	} else {
	$this->eliminar_componentes_desinstalados();
	}

	/* Chequeamos los componentes instalados con la lista de vulnerabilidades conocidas y actualizamos los componentes vulnerables */
	$this->chequear_vulnerabilidades();
	}

	/*
	Busca los componentes instaladas en el equipo. 
	 */
	function buscar()
	{
		
		$jinput = Factory::getApplication()->input;
		
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = 'SELECT * FROM #__extensions WHERE (state=0 and type="component")';
		$db->setQuery( $query );
		$db->execute();
		$num_rows = $db->getNumRows();
		$result = $db->loadAssocList();
		$this->actualizarbbdd( $result );
		$eliminados = $jinput->get('comp_eliminados',0,'int');
		$jinput->set('eliminados', $eliminados);
		$core_actualizado = $jinput->get('core_actualizado',0,'int');
		$jinput->set('core_actualizado', $core_actualizado);
		$comps_actualizados = $jinput->get('componentes_actualizados',0,'int');
		$jinput->set('comps_actualizados', $comps_actualizados);
		$comp_ok = Text::_( 'COM_SECURITYCHECK_CHECK_OK' );
		$jinput->set('comp_ok', $comp_ok);
		return true;
	}
		
	/*
	* Obtiene los datos de la BBDD 'securitycheck'
	*/
	function getData()
	{
	// Cargamos el contenido si es que no existe todavía
	if (empty( $this->_data ))
	{
	$this-> buscar();
	$query = $this->_buildQuery();
	$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}
	return $this->_data;
	}

	/* Función que obtiene el id del plugin de: '1' -> Securitycheck Pro Update Database  */
	function get_plugin_id($opcion) {

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		if ( $opcion == 1 ) {
			$query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro Update Database" and type="plugin"';
		} 
		$db->setQuery( $query );
		$db->execute();
		$id = $db->loadResult();
		
		return $id;
	}

}