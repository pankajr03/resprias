<?php
/**
 * @Securitycheck component
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace SecuritycheckExtensions\Component\Securitycheck\Administrator\Model;

// No Permission
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class BaseModel extends BaseDatabaseModel
{

    /**
     Array de datos
     *
     @var array
     */
    var $_data;
    /**
     Total items
     *
     @var integer
     */
    var $_total = null;
    /**
     Objeto Pagination
     *
     @var object
     */
    var $_pagination = null;
    /**
     Columnas de #__securitycheck
     *
     @var integer
     */
    var $_dbrows = null;

    private $config = null;

    private $defaultConfig = array(
    	'blacklist'			=> '',
		'whitelist'		=> '',
		'dynamic_blacklist'		=> 1,
		'dynamic_blacklist_time'		=> 600,
		'dynamic_blacklist_counter'		=> 5,
		'blacklist_email'		=> 0,
		'priority'		=> 'Blacklists first',
		'methods'			=> 'GET,POST,REQUEST',
		'mode'			=> 1,
		'logs_attacks'			=> 1,
		'log_limits_per_ip_and_day'			=> 0,
		'redirect_after_attack'			=> 1,
		'redirect_options'			=> 1,
		'second_level'			=> 1,
		'second_level_redirect'			=> 1,
		'second_level_limit_words'			=> 3,
		'second_level_words'			=> 'drop,update,set,admin,select,user,password,concat,login,load_file,ascii,char,union,from,group by,order by,insert,values,pass,where,substring,benchmark,md5,sha1,schema,version,row_count,compress,encode,information_schema,script,javascript,img,src,input,body,iframe,frame',
		'email_active'			=> 0,
		'email_subject'			=> 'Securitycheck Pro alert!',
		'email_body'			=> 'Securitycheck Pro has generated a new alert. Please, check your logs.',
		'email_add_applied_rule'			=> 1,
		'email_to'			=> 'youremail@yourdomain.com',
		'email_from_domain'			=> 'me@mydomain.com',
		'email_from_name'			=> 'Your name',
		'email_max_number'			=> 20,
		'check_header_referer'			=> 1,
		'check_base_64'			=> 1,
		'base64_exceptions'			=> 'com_hikashop',
		'strip_tags_exceptions'			=> 'com_jdownloads,com_hikashop,com_phocaguestbook',
		'duplicate_backslashes_exceptions'			=> 'com_kunena',
		'line_comments_exceptions'			=> 'com_comprofiler',
		'sql_pattern_exceptions'			=> '',
		'if_statement_exceptions'			=> '',
		'using_integers_exceptions'			=> 'com_dms,com_comprofiler,com_jce,com_contactenhanced',
		'escape_strings_exceptions'			=> 'com_kunena,com_jce',
		'lfi_exceptions'			=> '',
		'second_level_exceptions'			=> '',	
		'session_protection_active'			=> 1,
		'session_hijack_protection'			=> 1,
		'tasks'			=> 'alternate',
		'launch_time'			=> 2,
		'periodicity'			=> 1,
		'control_center_enabled'	=> '0',
		'secret_key'	=> '',
		'exclude_exceptions_if_vulnerable'	=>	1,
    );


    function __construct()
    {
        parent::__construct();

        global $mainframe, $option;
        
        $mainframe = Factory::getApplication();
		
		// This is needed to avoid errors getting the file from cli
		if ( (!empty($mainframe)) && (method_exists($mainframe,"getUserStateFromRequest")) ) {
			$jinput = $mainframe->input;
	 
			// Obtenemos las variables de paginación de la petición
			$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		
			$data = $jinput->get('post');
			$limitstart = $jinput->get('limitstart', 0, 'int');
		
			// En el caso de que los límites hayan cambiado, los volvemos a ajustar
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);     
		}
    }
	
	/* Obtiene el valor de una opción de configuración */
    public function getValue($key, $default = null, $key_name = 'cparams')
    {
        if (is_null($this->config)) { $this->load($key_name);
        }
    
        return $this->config->get($key, $default);
        
    }
	
	/* Establece el valor de una opción de configuración ' */
    public function setValue($key, $value, $save = false, $key_name = 'cparams')
    {
        if (is_null($this->config)) {
            $this->load($key_name);
        }
        
        $x = $this->config->set($key, $value);			
           
        if($save) { $this->save($key_name);
        }
        return $x;
    }
	
	/* Obtiene la configuración de los parámetros del Firewall Web */
    function getConfig()
    {            
        $config = array();
        foreach($this->defaultConfig as $k => $v)
        {			
            $config[$k] = $this->getValue($k, $v, 'plugin');
        }		
        return $config;    
    }
	
	/* Hace una consulta a la tabla espacificada como parámetro ' */
    public function load($key_name)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query 
            ->select($db->quoteName('storage_value'))
            ->from($db->quoteName('#__securitycheck_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
        $db->setQuery($query);
        $res = $db->loadResult();
        
        $this->config = new Registry();       
        if (!empty($res)) {
            $res = json_decode($res, true);
            $this->config->loadArray($res);
        }
    }
	
	/* Guarda la configuración en la tabla pasada como parámetro */
    public function save($key_name)
    {
        if (is_null($this->config)) {
            $this->load($key_name);
        }
        
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
		$data = $this->config->toArray();
		$data = json_encode($data);
		
		$query
			->delete($db->quoteName('#__securitycheck_storage'))
			->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
		$db->setQuery($query);
		$db->execute();
			
		$object = (object)array(
			'storage_key'		=> $key_name,
			'storage_value'		=> $data
		);
		$db->insertObject('#__securitycheck_storage', $object);
        
    }
	
	/* Función que consulta el valor de una bbdd pasados como argumentos */
    function get_campo_bbdd($bbdd,$campo)
    {
        // Creamos el nuevo objeto query
        $db = Factory::getContainer()->get(DatabaseInterface::class);
    
        $bbdd = htmlspecialchars($bbdd);
        $campo = htmlspecialchars($campo);
        
		try {
			// Consultamos el campo de la bbdd
			$query = $db->getQuery(true)
				->select($db->quoteName($campo))
				->from($db->quoteName('#__' . $bbdd));
			$db->setQuery($query);
			$valor = $db->loadResult();
		} catch (Exception $e)
        {    			
            $valor = null;
        }       
    
        return $valor;
    }
	
	/* Función para determinar si el plugin pasado como argumento ('1' -> Securitycheck Pro, '2' -> Securitycheck Pro Cron, '3' -> Securitycheck Pro Update Database) está habilitado o deshabilitado. También determina si el plugin Securitycheck Pro Update Database (opción 4)  está instalado */
    function PluginStatus($opcion)
    {
        
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        if ( $opcion == 1 ) {
			$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck"';
		} else if ( $opcion == 2 ) {
			$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Pro Cron"';
		} else if ( $opcion == 3 ) {
			$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
		} else if ( $opcion == 4 ) {
			$query = 'SELECT COUNT(*) FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
		} else if ( $opcion == 5 ) {
			$query = 'SELECT enabled FROM #__extensions WHERE name="System - Securitycheck Spam Protection"';
		} else if ( $opcion == 6 ) {
			$query = 'SELECT COUNT(*) FROM #__extensions WHERE name="System - Securitycheck Spam Protection"';
		}
		try {
			$db->setQuery($query);
			$db->execute();
			$enabled = $db->loadResult();
		} catch (Exception $e)
        {    			
            $enabled = 0;
        }      
    
        return $enabled;
    }
		
		
	/* Función para chequear si una ip pertenece a una lista en la que podemos especificar rangos. Podemos tener una ip del tipo 192.168.*.* y una ip 192.168.1.1 entraría en ese rango */
    function chequear_ip_en_lista($ip,$lista)
    {
        $aparece = false;
		$array_ip_peticionaria = explode('.',$ip);
			
		if (strlen($lista) > 0) {
			// Eliminamos los caracteres en blanco antes de introducir los valores en el array
			$lista = str_replace(' ','',$lista);
			$array_ips = explode(',',$lista);
			if ( is_int(array_search($ip,$array_ips)) ){	// La ip aparece tal cual en la lista
				$aparece = true;
			} else {
				foreach ($array_ips as &$indice){
						if (strrchr($indice,'*')){ // Chequeamos si existe el carácter '*' en el string; si no existe podemos ignorar esta ip
						$array_ip_lista = explode('.',$indice); // Formato array:  $array_ip_lista[0] = '192' , $array_ip_lista[1] = '168'
						$k = 0;
						$igual = true;
						while (($k <= 3) && ($igual)) {
							if ($array_ip_lista[$k] == '*') {
								$k++;
							}else {
								if ($array_ip_lista[$k] == $array_ip_peticionaria[$k]) {
									$k++;
								} else {
									$igual = false;
								}
							}
						}
						if ($igual) { // $igual será true cuando hayamos recorrido el array y todas las partes del mismo coincidan con la ip que realiza la petición
							$aparece = true;
							return $aparece;
						}
					}
				}
			}
		}
		return $aparece;
    }
		
	/* Guarda la modificación de los parámetros de la opción 'Mode' */
    function saveConfig($newParams, $key_name = 'cparams')
    {
        foreach($newParams as $key => $value)
        {
            // Do not save unnecessary parameters
            if(!array_key_exists($key, $this->defaultConfig)) { continue;
            }        
            $this->setValue($key, $value, '', $key_name);
        }
    
        $this->save($key_name);    
    }
	
	/* Limpia un string de caracteres no válidos según la opción especificada */
    function clearstring($string_to_clear, $option = 1)
    {
        // Eliminamos espacios y retornos de carro entre los elementos
        switch ($option)
        {
        case 1:
            // Transformamos el string array para poder manejarlo mejor
            $string_to_array = explode(',', $string_to_clear);
            // Eliminamos los espacios en blanco al principio y al final de cada elemento
            $string_to_array = array_map(
                function ($element) {
                    return trim($element); 
                }, $string_to_array
            );
            // Eliminamos los retornos de carro, nuevas líneas y tabuladores de cada elemento
            $string_to_array = array_map(
                function ($element) {
                    return str_replace(array("\n", "\t", "\r"), '', $element); 
                }, $string_to_array
            );
            // Volvemos a convertir el array en string
            $string_to_clear = implode(',', $string_to_array);
            break;
        case 2:
            $string_to_clear = str_replace(array(" ", "\n", "\t", "\r"), '', $string_to_clear);
            break;
        } 
        
        return $string_to_clear;
    }
	
	/**
		* Encrypt data using OpenSSL (AES-256-CBC)
		* Based on code from: https://stackoverflow.com/questions/3422759/php-aes-encrypt-decrypt
	*/
    function encrypt($plaindata, $encryption_key)
	{
		$method = "AES-256-CBC";
		
		if (empty($encryption_key))
		{
			return;
		}
			
		$iv = openssl_random_pseudo_bytes(16);
			
		$hash_pbkdf2 = hash_pbkdf2("sha512", $encryption_key, "", 5000);
		$key = substr($hash_pbkdf2, 0, 256);
		$hashkey = substr($hash_pbkdf2, 256, 512);
			
		$cipherdata = openssl_encrypt($plaindata, $method, $key, OPENSSL_RAW_DATA, $iv);

		if ($cipherdata === false)
		{
			$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: openssl_encrypt() failed:".openssl_error_string());
		}

		$hash = hash_hmac('sha256', $cipherdata.$iv, $hashkey, true);

		if ($hash === false)
		{
			$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: hash_hmac() failed");
		}

		return base64_encode($iv.$hash.$cipherdata);
	}
	
	/**
		* Decrypt data using OpenSSL (AES-256-CBC)
		* Based on code from: https://stackoverflow.com/questions/3422759/php-aes-encrypt-decrypt
	*/
	function decrypt($encrypteddata, $encryption_key)
	{
		$method = "AES-256-CBC";
			
		$encrypteddata = base64_decode($encrypteddata);
			
		$iv = substr($encrypteddata, 0, 16);
		$hash = substr($encrypteddata, 16, 32);
		$cipherdata = substr($encrypteddata, 48);
							
		$hash_pbkdf2 = hash_pbkdf2("sha512", $encryption_key, "", 5000);
		$key = substr($hash_pbkdf2, 0, 256);
		$hashkey = substr($hash_pbkdf2, 256, 512);
			
		if (!hash_equals(hash_hmac('sha256', $cipherdata.$iv, $hashkey, true), $hash))
		{
			/*$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: Hash verification failed");*/
			return "Internal error: Hash verification failed";
		}

		$plaindata = openssl_decrypt($cipherdata, $method, $key, OPENSSL_RAW_DATA, $iv);

		if ($plaindata === false)
		{
			/*$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: openssl_decrypt() failed:".openssl_error_string());*/
			return "Internal error: openssl_decrypt() failed";
		}

		return $plaindata;
	}
	
	/* Función que obtiene el download id de la tabla update_sites. */
    function get_extra_query_update_sites_table($element)
    {
		$db = Factory::getContainer()->get(DatabaseInterface::class);    
		$query = $db->getQuery(true);
		
					
		try {
			$query->select($db->quoteName('extension_id'));
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote($element));
            $db->setQuery($query);
            $db->execute();
            $extension_id = $db->loadResult();
						
			$query = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName('update_site_id'));
			$query->from($db->quoteName('#__update_sites_extensions'));
			$query->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));
            $db->setQuery($query);
			$db->execute();
            $update_site_id = $db->loadResult();
						
			$query = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('extra_query', 'update_site_id')));
			$query->from($db->quoteName('#__update_sites'));
			$query->where($db->quoteName('update_site_id') . ' = ' . $db->quote($update_site_id));
            $db->setQuery($query);
            $db->execute();
            $update_site_data = $db->loadObject();
						
			// Remove the 'dlid=' part of the string
			if ( !empty($update_site_data) ) {
				$update_site_data->extra_query = str_replace("dlid=", "",$update_site_data->extra_query);
			}						
			
		} catch (Exception $e)		
        {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return "error";
		}	
		
		return $update_site_data;
		
	}
	
	
}
