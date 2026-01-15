<?php
/**
 * @Securitycheck plugin
 * @copyright Copyright (c) 2011 - Jose A. Luque / Securitycheck Extensions
 * @license   GNU General Public License version 3, or later
 */

namespace Joomla\Plugin\System\Securitycheck\Extension;

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class Securitycheck extends CMSPlugin 
{
	
	/* Función para grabar los logs en la BBDD */
	function grabar_log($ip,$tag_description,$description,$type,$uri,$original_string,$component){
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		// Sanitizamos las entradas
		$ip = htmlspecialchars($ip);
		$ip = $db->escape($ip);
		$tag_description = htmlspecialchars($tag_description);
		$tag_description = $db->escape($tag_description);
		$description = htmlspecialchars($description);
		$description = $db->escape($description);
		$type = htmlspecialchars($type);
		$type = $db->escape($type);
		$uri = htmlspecialchars($uri);
		$uri = $db->escape($uri);
		// Truncate the uri string
		$uri = substr($uri,0,100);
		$component = htmlspecialchars($component);
		$component = $db->escape($component);
		// Guardamos el string original en formato base64 para evitar problemas de seguridad
		$original_string = htmlspecialchars($original_string);
		$original_string = base64_encode($original_string);
		
		// Consultamos el último log para evitar duplicar entradas
		$query = "SELECT tag_description,original_string,ip from `#__securitycheck_logs` WHERE id=(SELECT MAX(id) from `#__securitycheck_logs`)" ;			
		$db->setQuery( $query );
		$row = $db->loadRow();
		
		if (!empty($row)) {
			$result_tag_description = $row['0'];
			$result_original_string = $row['1'];
			$result_ip = $row['2'];
				
			if ( (!($result_tag_description == $tag_description )) || (!($result_original_string == $original_string )) || (!($result_ip == $ip )) ){
				$sql = "INSERT INTO `#__securitycheck_logs` (`ip`, `time`, `tag_description`, `description`, `type`, `uri`, `component`, `original_string` ) VALUES ('{$ip}', now(), '{$tag_description}', '{$description}', '{$type}', '{$uri}', '{$component}', '{$original_string}')";
				$db->setQuery($sql);
				$db->execute();
			}		
			
		} else {
			$sql = "INSERT INTO `#__securitycheck_logs` (`ip`, `time`, `tag_description`, `description`, `type`, `uri`, `component`, `original_string` ) VALUES ('{$ip}', now(), '{$tag_description}', '{$description}', '{$type}', '{$uri}', '{$component}', '{$original_string}')";
			$db->setQuery($sql);
			$db->execute();
		}	
		
	}
		
	/* Determina si un valor está codificado en base64 */	
	function is_base64($value){
		$res = false; // Determines if any character of the decoded string is between 32 and 126, which should indicate a non valid european ASCII character
	
		$min_len = mb_strlen($value)>7;
		if ($min_len) {
			
			$decoded = base64_decode(chunk_split($value));
			$string_caracteres = str_split($decoded); 
			if ( empty($string_caracteres) ) {
				return false;  // It´s not a base64 string!
			} else {
				foreach ($string_caracteres as $caracter) {
					if ( (empty($caracter)) || (ord($caracter)<32) || (ord($caracter)>126) ) { // Non-valid ASCII value
						return false; // It´s not a base64 string!
					}
				}
			}
			
		$res = true; // It´s a base64 string!
		}
		
		return $res;
	}
	
	/* Determina si un string tiene caracteres ascii no válidos */	
	function is_ascii_valid($string){
		$res = true; // Determines if any character of the decoded string is between 32 and 126, which should indicate a non valid european ASCII character
	
			
		$string_caracteres = str_split($string); 
		if ( empty($string_caracteres) ) {
			return true;  // There are no chars
		} else {
			foreach ($string_caracteres as $caracter) {
				if ( (empty($caracter)) || (ord($caracter)<32) || (ord($caracter)>126) ) { // Non-valid ASCII value
					return false; // There are non-valid chars
				}
			}
		}
						
		return $res;
	}
	
	/* Función para convertir en string una cadena hexadecimal */
	function hexToStr($hex){
		
		$hex = trim(preg_replace("/(\%|0x)/","",$hex));
				
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;		 
	}
	
	/* Función que realiza la misma función que mysql_real_escape_string() pero sin necesidad de una conexión a la BBDD */
	function escapa_string($value){
		$search = array("\x00", "'", "\"", "\x1a");
		$replace = array("\\x00", "\'", "\\\"", "\\\x1a");
	
		return str_replace($search, $replace, $value);
	}
	
	// Chequea si la extensión pasada como argumento es vulnerable
	private function check_extension_vulnerable($option) {
		
		// Inicializamos las variables
		$vulnerable = false;
		
		// Creamos el nuevo objeto query
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
	
		// Sanitizamos el argumento
		$sanitized_option = $db->Quote($db->escape($option));
	
		// Construimos la consulta
		$query = "SELECT COUNT(*) from `#__securitycheck_db` WHERE (type = {$sanitized_option})" ;		
				
		$db->setQuery( $query );
		$result = $db->loadResult();
		
		if ( $result > 0 ) {
			$vulnerable = true;
		} 
		
		// Devolvemos el resultado
		return $vulnerable;
	
	}	
	
	/* Función para 'sanitizar' un string. Devolvemos el string "sanitizado" y modificamos la variable "modified" si se ha modificado el string */
	function cleanQuery($ip,$string,$methods_options,$a,$request_uri,&$modified,$check,$option){
		$string_sanitized='';
		$base64=false;
		$pageoption='';
		$existe_componente = false;
		$extension_vulnerable = false;
		
		if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = 'Not set';
		}
		
		if ( isset($_SERVER['HTTP_REFERER']) ) {
			$referer = $_SERVER['HTTP_REFERER'];
		} else {
			$referer = 'Not set';
		}
		
		$app = Factory::getApplication();
		$is_admin = $app->isClient('administrator');
		
		$user = Factory::getUser();
		
		/* Excepciones */
		$base64_exceptions = $this->params->get('base64_exceptions','com_hikashop');
		$strip_tags_exceptions = $this->params->get('strip_tags_exceptions','com_jdownloads,com_hikashop,com_phocaguestbook');
		$duplicate_backslashes_exceptions = $this->params->get('duplicate_backslashes_exceptions','com_kunena');
		$line_comments_exceptions = $this->params->get('line_comments_exceptions','com_comprofiler');
		$sql_pattern_exceptions = $this->params->get('sql_pattern_exceptions','');
		$if_statement_exceptions = $this->params->get('if_statement_exceptions','');
		$using_integers_exceptions = $this->params->get('using_integers_exceptions','com_dms,com_comprofiler,com_jce,com_contactenhanced');
		$escape_strings_exceptions = $this->params->get('escape_strings_exceptions','com_kunena,com_jce');
		$lfi_exceptions = $this->params->get('lfi_exceptions','');
		$check_header_referer = $this->params->get('check_header_referer',1);
		$exclude_exceptions_if_vulnerable = $this->params->get('exclude_exceptions_if_vulnerable',1);
						
		if ( !($is_admin) ){  // No estamos en la parte administrativa
		
		/* Chequeamos si el usuario tiene permisos para instalar en el sistema. Si no los tiene, continuamos con el script. Esto nos permite tener más control para
		determinar si estamos en el backend, ya que algunos componentes/plugins no nos permiten discernir
		en qué parte estamos, obteniendo errores al continuar con el script. Así, si un usuario tiene permisos para instalar podemos obviar la ejecución del script, 
		puesto que suponemos que sus permisos están bien configurados */		
		if (!($user->authorise('com_installer'))) { 
		
		$pageoption = $option;
		
		// Si hemos podido extraer el componente implicado en la petición, vemos si la versión instalada es vulnerable
		if ( (!empty($option)) && ($exclude_exceptions_if_vulnerable) ) {
			$extension_vulnerable = $this->check_extension_vulnerable($option);										
		}
		
		if ( (!(is_array($string))) && (mb_strlen($string)>0) && ($pageoption != '') ){
			/* Base64 check */
			if ($check) {
				/* Chequeamos si el componente está en la lista de excepciones */
				if ( !(strstr($base64_exceptions,$pageoption)) ){
					$is_base64 = $this->is_base64($string);
						if ($is_base64) {
							$decoded = base64_decode(chunk_split($string));
							$base64=true;
							$string = $decoded;
						}
				}
			}
								
			/* XSS Prevention */
				//Strip html and php tags from string
			if ( ( !(strstr($strip_tags_exceptions,$pageoption)) || $extension_vulnerable ) && !(strstr($strip_tags_exceptions,'*')) ){
				if (preg_match("/(\%[a-zA-Z0-9]{2}|0x{4,})/", $string)) {
					// Is this an encoding attack?
					$encoding_array = array("%3C","%253C","%3E","%253E","%2F","%252F","%2525");
					foreach($encoding_array as $encoded_word) {
						if ((is_string($string)) && (!empty($encoded_word))) {
							if (substr_count(strtolower($string), strtolower($encoded_word))) {
								$this->grabar_log($ip,'TAGS_STRIPPED','[' .$methods_options .':' .$a .']','XSS_BASE64',$request_uri,$string,$pageoption);
								// Mostramos el código establecido por el administrador, una cabecera de Forbidden y salimos 
								$lang = Factory::getApplication()->getLanguage();
								$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);
								$error_400 = $lang->_('COM_SECURITYCHECK_400_ERROR');								
								header('HTTP/1.1 403 Forbidden');								
								die($error_400);												
							}
						}
					}
				}
			
				$string_sanitized = strip_tags($string);
				if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
					if ($base64){
						$this->grabar_log($ip,'TAGS_STRIPPED','[' .$methods_options .':' .$a .']','XSS_BASE64',$request_uri,$string,$pageoption);
					}else {
						$this->grabar_log($ip,'TAGS_STRIPPED','[' .$methods_options .':' .$a .']','XSS',$request_uri,$string,$pageoption);
					}
					
					$string = $string_sanitized;	
					$modified = true;
				}
			}
			
			/* SQL Injection Prevention */
			if (!$modified) {
				if ( !(strstr($duplicate_backslashes_exceptions,$pageoption)) && !(strstr($duplicate_backslashes_exceptions,'*')) ){
					// Prevents duplicate backslashes
					if (PHP_VERSION_ID < 50400 && get_magic_quotes_gpc())
					{
						$string_sanitized = stripslashes($string);
						if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
							if ($base64){
								$this->grabar_log($ip,'DUPLICATE_BACKSLASHES','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
							}else {
								$this->grabar_log($ip,'DUPLICATE_BACKSLASHES','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
							}
							
							if ( strlen($string_sanitized)>0 ){
								$string = $string_sanitized;
							}
						}
					}
				}
				
				if ( !(strstr($line_comments_exceptions,$pageoption)) && !(strstr($line_comments_exceptions,'*')) && ($pageoption != 'com_users') ){
					// Line Comments
					$lineComments = array("/--/","/[^\=]#/","/\/\*/","/\*\//");
					$string_sanitized = preg_replace($lineComments, "", $string);
										
					if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
						if ($base64){
							$this->grabar_log($ip,'LINE_COMMENTS','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'LINE_COMMENTS','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
						}
						
						$string = $string_sanitized;
						$modified = true;
					}
				}
				
				$sqlpatterns = array("/delete(?=(\s|\+|%20|%u0020|%uff00))(.\b){1,3}(from)\b(?=(\s|\+|%20|%u0020|%uff00))/i","/update(?=(\s|\+|%20|%u0020|%uff00)).+\b(set)\b(?=(\s|\+|%20|%u0020|%uff00))/i",
				"/drop(?=(\s|\+|%20|%u0020|%uff00)).+\b(database|user|table|index)\b(?=(\s|\+|%20|%u0020|%uff00))/i",
				"/insert(?=(\s|\+|%20|%u0020|%uff00)).+\b(values|set|select)\b(?=(\s|\+|%20|%u0020|%uff00))/i", "/union(?=(\s|\+|%20|%u0020|%uff00)).+\b(select)\b(?=(\s|\+|%20|%u0020|%uff00))/i",
				"/select(?=(\s|\+|%20|%u0020|%uff00))(.\b|.\B)(from|ascii|char|concat|case)\b(?=(\s|\+|%20|%u0020|%uff00))/i","/benchmark\(.*\)/i",
				"/md5\(.*\)/i","/sha1\(.*\)/i","/ascii\(.*\)/i","/concat\(.*\)/i","/char\(.*\)/i",
				"/substring\(.*\)/i","/where(\s|\+|%20|%u0020|%uff00)(or|and)(\s|\+|%20|%u0020|%uff00)(\w+)(=|<|>|<=|>=)(\w+)/i","/(or|and)(\s|\+|%20|%u0020|%uff00)(sleep)/i","/(\s|\+|%20|%u0020|%uff00)(pg_sleep)/i","/waitfor(\s|\+|%20|%u0020|%uff00)(delay)/i","/(or|and)(\s|\+|%20|%u0020|%uff00)(\()?(\')?((\d+=\d+)|(\D+=\D+))/i","/=dbms_pipe\.receive_message/i","/order by \d+/i");
					
				if ( ( !(strstr($sql_pattern_exceptions,$pageoption)) || $extension_vulnerable ) && !(strstr($sql_pattern_exceptions,'*')) ){							
					$string_sanitized = preg_replace($sqlpatterns, "", $string);
											
					if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log	
						if ($base64){
							$this->grabar_log($ip,'SQL_PATTERN','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'SQL_PATTERN','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
						}
						
						$string = $string_sanitized;
						$modified = true;					
					}	
				}
				
				//IF Statements
				$ifStatements = array("/if\(.*,.*,.*\)/i");
					
				if ( ( !(strstr($if_statement_exceptions,$pageoption)) || $extension_vulnerable ) && !(strstr($if_statement_exceptions,'*')) ){		
					$string_sanitized = preg_replace($ifStatements, "", $string);
					
					if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
						if ($base64){
							$this->grabar_log($ip,'IF_STATEMENT','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'IF_STATEMENT','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
						}						
						
						$string = $string_sanitized;
						$modified = true;
					}
				}
				
				//Using Integers
				$usingIntegers = array("/select(?=(\s|\+|%20|%u0020|%uff00)).+(0x)/i","/@@/i","/||/i");
					
				if ( !(strstr($using_integers_exceptions,$pageoption)) && !(strstr($using_integers_exceptions,'*')) ){	
					$string_sanitized = preg_replace($usingIntegers, "", $string);
					
					if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
						if ($base64){
							$this->grabar_log($ip,'INTEGERS','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'INTEGERS','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
						}
						
						$string = $string_sanitized;
						$modified = true;
					}
					
				}
				
				if ( !(strstr($escape_strings_exceptions,$pageoption)) && !(strstr($escape_strings_exceptions,'*')) && ($modified) ){
					$string_sanitized = $this->escapa_string($string);
										
					if (strcmp($string_sanitized,$string) <> 0){ //Se han añadido barras invertidas a ciertos caracteres; escribimos en el log							
						if ($base64){
							$this->grabar_log($ip,'BACKSLASHES_ADDED','[' .$methods_options .':' .$a .']','SQL_INJECTION_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'BACKSLASHES_ADDED','[' .$methods_options .':' .$a .']','SQL_INJECTION',$request_uri,$string,$pageoption);
						}
						if ( strlen($string_sanitized)>0 ){
							$string = $string_sanitized;
						}
					}
				}
					
			}	
			
			/* LFI  Prevention */
			$lfiStatements = array("/\.\.\//");
			if ( ( !(strstr($lfi_exceptions,$pageoption)) || $extension_vulnerable ) && !(strstr($lfi_exceptions,'*')) ){
				if (!$modified) {
					$string_sanitized = preg_replace($lfiStatements,'', $string);
					if (strcmp($string_sanitized,$string) <> 0){ //Se han eliminado caracteres; escribimos en el log
						if ($base64){
							$this->grabar_log($ip,'LFI','[' .$methods_options .':' .$a .']','LFI_BASE64',$request_uri,$string,$pageoption);
						}else {
							$this->grabar_log($ip,'LFI','[' .$methods_options .':' .$a .']','LFI',$request_uri,$string,$pageoption);
						}
						
						$string = $string_sanitized;
						$modified = true;
					}
				}
			}
			
			/* Header and user-agent check */
			if ( (!$modified) && ($check_header_referer) ) {
				$modified = $this->check_header_and_user_agent($user,$user_agent,$referer,$ip,$methods_options,$a,$request_uri,$sqlpatterns,$ifStatements,$usingIntegers,$lfiStatements,$pageoption);
			}
		}
		}
		}
		return $string;
		
	}
	
	/* Función que chequea el 'Header' y el 'user-agent' en busca de ataques */
	function check_header_and_user_agent($user,$user_agent,$referer,$ip,$methods_options,$a,$request_uri,$sqlpatterns,$ifStatements,$usingIntegers,$lfiStatements,$pageoption) {
		$modified = false; 
		
		if ( $user->guest ) {
			/***** User-agent checks *****/
			if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
				/* XSS Prevention in USER_AGENT*/
				//Strip html and php tags from string
				$header_sanitized = strip_tags($user_agent);
				if (strcmp($header_sanitized,$user_agent) <> 0){ //Se han eliminado caracteres; escribimos en el log
					$this->grabar_log($ip,'TAGS_STRIPPED','[' .$methods_options .':' .$a .']','USER_AGENT_MODIFICATION',$request_uri,$user_agent,$pageoption);
					
					$modified = true;
				} 
				/* SQL Injection in USER_AGENT*/
				$header_sanitized = preg_replace($sqlpatterns, "", $user_agent);
				if (strcmp($header_sanitized,$user_agent) <> 0){ //Se han eliminado caracteres; escribimos en el log
					$this->grabar_log($ip,'SQL_PATTERN','[' .$methods_options .':' .$a .']','USER_AGENT_MODIFICATION',$request_uri,$user_agent,$pageoption);
					
					$modified = true;
				}
				/* SQL Injection in USER_AGENT*/
				$header_sanitized = preg_replace($ifStatements, "", $user_agent);
				if (strcmp($header_sanitized,$user_agent) <> 0){ //Se han eliminado caracteres; escribimos en el log
					$this->grabar_log($ip,'IF_STATEMENT','[' .$methods_options .':' .$a .']','USER_AGENT_MODIFICATION',$request_uri,$user_agent,$pageoption);
					
					$modified = true;
				} 
				/* SQL Injection in USER_AGENT*/
				$header_sanitized = preg_replace($usingIntegers, "", $user_agent);
				if (strcmp($header_sanitized,$user_agent) <> 0){ //Se han eliminado caracteres; escribimos en el log
					$this->grabar_log($ip,'INTEGERS','[' .$methods_options .':' .$a .']','USER_AGENT_MODIFICATION',$request_uri,$user_agent,$pageoption);
				
					$modified = true;
				} 
				/* LFI in USER_AGENT*/
				$header_sanitized = preg_replace($lfiStatements, '', $user_agent);
				if (strcmp($header_sanitized,$user_agent) <> 0){ //Se han eliminado caracteres; escribimos en el log
					$this->grabar_log($ip,'LFI','[' .$methods_options .':' .$a .']','USER_AGENT_MODIFICATION',$request_uri,$user_agent,$pageoption);
					
					$modified = true;
				}
			}
			/***** Referer checks *****/
			if (!$modified) {
				if ( isset($_SERVER['HTTP_REFERER']) ) {
					/* XSS Prevention in REFERER*/
					//Strip html and php tags from string
					$header_sanitized = strip_tags($referer);
					if (strcmp($header_sanitized,$referer) <> 0){ //Se han eliminado caracteres; escribimos en el log
						$this->grabar_log($ip,'TAGS_STRIPPED','[' .$methods_options .':' .$a .']','REFERER_MODIFICATION',$request_uri,$referer,$pageoption);
					
						$modified = true;
					} 				
					/* SQL Injection in REFERER*/
					$header_sanitized = preg_replace($sqlpatterns, "", $referer);
					if (strcmp($header_sanitized,$referer) <> 0){ //Se han eliminado caracteres; escribimos en el log
						$this->grabar_log($ip,'SQL_PATTERN','[' .$methods_options .':' .$a .']','REFERER_MODIFICATION',$request_uri,$referer,$pageoption);
						
						$modified = true;
					}
					/* SQL Injection in REFERER*/
					$header_sanitized = preg_replace($ifStatements, "", $referer);
					if (strcmp($header_sanitized,$referer) <> 0){ //Se han eliminado caracteres; escribimos en el log
						$this->grabar_log($ip,'IF_STATEMENT','[' .$methods_options .':' .$a .']','REFERER_MODIFICATION',$request_uri,$referer,$pageoption);
						
						$modified = true;
					} 
					/* LFI in REFERER*/
					$header_sanitized = preg_replace($lfiStatements, '', $referer);
					if (strcmp($header_sanitized,$referer) <> 0){ //Se han eliminado caracteres; escribimos en el log
						$this->grabar_log($ip,'LFI','[' .$methods_options .':' .$a .']','REFERER_MODIFICATION',$request_uri,$referer,$pageoption);
						
						$modified = true;
					}
				}
			}
		}
		return $modified;
	}
	
	/* Función para contar el número de palabras "prohibidas" de un string*/
	function second_level($request_uri,$string,$a,&$found,$option){
		$occurrences=0;
		$string_sanitized=$string;
		$application = Factory::getApplication();
		$user = Factory::getUser();
		$dbprefix = $application->getCfg('dbprefix');
		$pageoption='';
		$existe_componente = false;
		$extension_vulnerable = false;
		
		$app = Factory::getApplication();
		$is_admin = $app->isClient('administrator');
		
		$pageoption = $option;
		
		/* Excepciones */
		$second_level_exceptions = $this->params->get('second_level_exceptions','');
		
		// Chequeamos si hemos de excluir los componentes vulnerables de las excepciones
		$exclude_exceptions_if_vulnerable = $this->params->get('exclude_exceptions_if_vulnerable',1);
		
		// Si hemos podido extraer el componente implicado en la peticin, vemos si la versin instalada es vulnerable
		if ( (!empty($option)) && ($exclude_exceptions_if_vulnerable) ) {
			$extension_vulnerable = $this->check_extension_vulnerable($option);										
		}
		
		
		if (!($user->authorise('com_installer'))) { 
			if ( ( !($is_admin) ) && ($pageoption != '') && !(is_array($string)) ){  // No estamos en la parte administrativa
				if ( !(strstr($second_level_exceptions,$pageoption)) || $extension_vulnerable ){
					/* SQL Injection Prevention */
					// Prevents duplicate backslashes
					if (PHP_VERSION_ID < 50400 && get_magic_quotes_gpc())
					{
						$string_sanitized = stripslashes($string);
					}
					// Line Comments
					$lineComments = array("/--/","/[^\=\s]#/","/\/\*/","/\*\//","/(?=(%2f|\/)).+\*\*/i");
					$string_sanitized = preg_replace($lineComments,"", $string_sanitized);
				
					$string_sanitized = $this->escapa_string($string);
										
					$suspect_words = array("drop","update","set","admin","select","user","password","concat",
					"login","load_file","ascii","char","union","from","group by","order by","insert","values",
					"pass","where","substring","benchmark","md5","sha1","schema","version","row_count",
					"compress","encode","information_schema","script","javascript","img","src","input","body",
					"iframe","frame");
					
					foreach ($suspect_words as $word){
						if ( (is_string($string_sanitized)) && (!empty($word)) && (!empty($string_sanitized)) ) {
							if (substr_count(strtolower($string_sanitized),$word)){
								$found = $found .', ' .$word;
								$occurrences++;
							}
						}
					}
				}
			}
		}
		return $occurrences;
		
	}
	
	/* Función para chequear si una ip pertenece a una lista */
	function chequear_ip_en_lista($ip,$lista){
		$aparece = false;
				
		if ( (!empty($lista)) && (strlen($lista) > 0) ) {
			// Eliminamos los caracteres en blanco antes de introducir los valores en el array
			$lista = str_replace(' ','',$lista);
			$array_ips = explode(',',$lista);
						
			if ( is_int(array_search($ip,$array_ips)) ){	// La ip aparece tal cual en la lista
				$aparece = true;
			} 
		}
		return $aparece;
	}
		
	/*  Función que chequea el número de sesiones activas del usuario y, si existe más de una, toma el comportamiento pasado como argumento*/
	function sesiones_activas($attack_ip,$request_uri){
		/* Cargamos el lenguaje del sitio */
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);
		
		// Chequeamos si la opción de compartir sesiones está activa; en este caso no aplicaremos esta opción para evitar una denegación de entrada
		$params          = Factory::getApplication()->getConfig();		
		$shared_session_enabled = $params->get('shared_session');
		
		if ( $shared_session_enabled ) {
			return;
		}
		
		$user = Factory::getUser();
		$user_id = (int) $user->id;
		if ( $user->guest ) {
			/* El usuario no se ha logado; no hacemos nada */
		} else {
			// Creamos el nuevo objeto query
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true);
			
			// Construimos la consulta
			$query = "SELECT COUNT(*) from `#__session` WHERE (userid = {$user_id})" ;
			
			$db->setQuery( $query );
			$result = $db->loadResult();
			
			if ( $result > 1 ) {  // Ya existe una sesión activa del usuario
					/*Cerramos todas las sesiones activas del usuario, tanto del frontend (clientid->0) como del backend (clientid->1); este código es
					necesario porque no queremos modificar los archivos de Joomla , pero esta comprobación podría incluirse en la función onUserLogin*/
					$mainframe= Factory::getApplication();
					$mainframe->logout( $user_id,array("clientid" => 0) );
					$mainframe->logout( $user_id,array("clientid" => 1) ); 
					
					$session_protection_description = $lang->_('COM_SECURITYCHECK_SESSION_PROTECTION_DESCRIPTION');
					$username = $lang->_('COM_SECURITYCHECK_USERNAME');
					
					// Grabamos el log correspondiente...
					$this->grabar_log($attack_ip,'SESSION_PROTECTION',$session_protection_description,'SESSION_PROTECTION',$request_uri,$username .$user->username,'---');
									
					// ... y redirigimos la petición para realizar las acciones correspondientes
					$session_protection_error = $lang->_('COM_SECURITYCHECK_SESSION_PROTECTION_ERROR');
					Factory::getApplication()->enqueueMessage($session_protection_error, 'error');
			}										
		}
	}
	
	function onAfterRoute(){
	
		/* Cargamos el lenguaje del sitio */
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);
		$not_applicable = $lang->_('COM_SECURITYCHECK_NOT_APPLICABLE');

		$methods = $this->params->get('methods','GET,POST,REQUEST');
		$blacklist_ips = $this->params->get('blacklist','');
		$whitelist_ips = $this->params->get('whitelist','');
		$secondlevel = $this->params->get('second_level',1);
		$check_base_64 = $this->params->get('check_base_64',1);
		$session_protection_active = $this->params->get('session_protection_active',1);
		
		
		$attack_ip = $this->get_ip();
		$request_uri = $_SERVER['REQUEST_URI'];

		
		$aparece_lista_negra = $this->chequear_ip_en_lista($attack_ip,$blacklist_ips);
		$aparece_lista_blanca = $this->chequear_ip_en_lista($attack_ip,$whitelist_ips);
		
	/* Protección de la sesión del usuario */	
	if ( $session_protection_active ) {
		$this->sesiones_activas($attack_ip,$request_uri);
	}
	
	/* Chequeamos si la ip remota se encuentra en la lista negra */
	if ( $aparece_lista_negra ){
			/* Grabamos una entrada en el log con el intento de acceso de la ip prohibida */
			$access_attempt = $lang->_('COM_SECURITYCHECK_ACCESS_ATTEMPT');
			$this->grabar_log($attack_ip,'IP_BLOCKED',$access_attempt,'IP_BLOCKED',$request_uri,$not_applicable,'---');
									
			// Mostramos el mensaje de prohibido, una cabecera de Forbidden y salimos			
			$error_403 = $lang->_('COM_SECURITYCHECK_403_ERROR');			
			header('HTTP/1.1 403 Forbidden');
			die($error_403);
	} else {
	
		/* Chequeamos si la ip remota se encuentra en la lista blanca */
		if ( $aparece_lista_blanca ){
			/* Grabamos una entrada en el log con el acceso de la ip permitida 
				$access = $lang->_('COM_SECURITYCHECK_ACCESS');
				$this->grabar_log($attack_ip,'IP_PERMITTED',$access,'IP_PERMITTED',$request_uri,$not_applicable); */
		} else {
			
			foreach(explode(',', $methods) as $methods_options){
				switch ($methods_options){
					case 'GET':
						$method = $_GET;
						break;
					case 'POST':
						$method = $_POST;
						break;
					case 'COOKIE':
						$method = $_COOKIE;
						break;
					case 'REQUEST':
						$method = $_REQUEST;
						break;
				}
			
			foreach($method as $a => &$req){
			
				if(is_numeric($req)) continue;
				
				$modified = false;
				
				$entradas = Factory::getApplication()->input;
				$option = $entradas->get('option','com_notfound');
				
				$req = $this->cleanQuery($attack_ip,$req,$methods_options,$a,$request_uri,$modified,$check_base_64,$option);
																
				if ($modified) {
					/* Redirección a nuestra página de "Hacking Attempt" */
					$error_400 = $lang->_('COM_SECURITYCHECK_400_ERROR');					
					header('HTTP/1.1 403 Forbidden');								
					die($error_400);
				} else if ($secondlevel) {  // Second level protection
					$words_found='';
					$num_keywords = $this->second_level($request_uri,$req,$a,$words_found,$option);
					if ($num_keywords > 2) {
						$this->grabar_log($attack_ip,'FORBIDDEN_WORDS',$words_found,'SECOND_LEVEL',$request_uri,$not_applicable,$option);
						$error_401 = $lang->_('COM_SECURITYCHECK_401_ERROR');						
						header('HTTP/1.1 403 Forbidden');								
						die($error_401);
					}
				} 
			}
			}
		}
	}
	 

	}
	
	public function onAfterDispatch() {
		// ¿Tenemos que eliminar el meta tag?
		$params = ComponentHelper::getParams('com_securitycheck');
		$remove_meta_tag = $params->get('remove_meta_tag',1);
		
		$code  = Factory::getDocument();
		if ( $remove_meta_tag ) {
			$code->setGenerator('');
		}
	}	
	
	
	/* Obtiene la IP remota que realiza las peticiones */
	public function get_ip(){
		// Inicializamos las variables 
		$clientIpAddress = 'Not set';
		$ip_valid = false;
		
		// Ignoramos las cabeceras X-Forwarded-For; en la versión Pro podemos elegir otra opción
		if ( isset($_SERVER['REMOTE_ADDR']) ) {
			$clientIpAddress = $_SERVER['REMOTE_ADDR'];			
		}
			
		$ip_valid = filter_var($clientIpAddress, FILTER_VALIDATE_IP);
		// Si la ip no es válida entonces bloqueamos la petición y mostramos un error 403
		if ( !$ip_valid ) {
			/* Cargamos el lenguaje del sitio */
			$lang = Factory::getApplication()->getLanguage();
			$lang->load('com_securitycheck',JPATH_ADMINISTRATOR);
			$error_403 = $lang->_('COM_SECURITYCHECK_403_ERROR');
			Factory::getApplication()->enqueueMessage($error_403, 'error');				
		} else {
			return $clientIpAddress;
		}
	}
		
}