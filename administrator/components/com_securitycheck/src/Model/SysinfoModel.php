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
use Joomla\CMS\Version;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\BaseModel;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\CpanelModel;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\ProtectionModel;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\FilemanagerModel;
use SecuritycheckExtensions\Component\Securitycheck\Administrator\Model\DatabaseupdatesModel;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

class SysinfoModel extends BaseModel
{

/* @var array somme system values  */
protected $info = null;
private $backupinfo = array('product'=> '', 'latest'=>'', 'latest_status'=>	'', 'latest_type'=>'');

/**
 * method to get the system information
 *
 * @return array system information values
 */
public function getInfo()
{
	if (is_null($this->info)){
		$this->info = array();
		$version = new Version;
		$db = Factory::getContainer()->get(DatabaseInterface::class);
				
		// Obtenemos el tamaño de la variable 'max_allowed_packet' de Mysql
		$db->setQuery('SHOW VARIABLES LIKE \'max_allowed_packet\'');
		$keys = $db->loadObjectList();
		$array_val = get_object_vars($keys[0]);
		$tamanno_max_allowed_packet = (int) ($array_val["Value"]/1024/1024);
		
		$this->info['max_allowed_packet']		= $tamanno_max_allowed_packet;
		
		// Obtenemos el tamaño máximo de memoria establecido
		$params = ComponentHelper::getParams('com_securitycheck');
		$memory_limit = $params->get('memory_limit','128M');
		
		$this->info['memory_limit']		= $memory_limit;
		
		if (isset($_SERVER['SERVER_SOFTWARE']))
		{
			$sf = $_SERVER['SERVER_SOFTWARE'];
		}
		else
		{
			$sf = getenv('SERVER_SOFTWARE');
		}
		
		if (function_exists('php_uname'))
		{
			$this->info['php'] = php_uname();
		} else {
			$this->info['php'] = PHP_OS;
		}
		$this->info['dbversion']	= $db->getVersion();
		$this->info['dbcollation']	= $db->getCollation();
		$this->info['phpversion']	= phpversion();
		$this->info['server']		= $sf;
		$this->info['sapi_name']	= php_sapi_name();
		$this->info['version']		= $version->getLongVersion();
		$this->info['useragent']	= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
			
		// Obtenemos las opciones de configuración
		$values = $this->getStatus(false);				
	}
	
	return $this->info;
}

// Obtiene el estado del segundo factor de autenticación de Joomla (Google y Yubikey)
function get_two_factor_status() {
	$enabled = 0;
	
	$db = $this->getDbo();
	$query = $db->getQuery(true)
		->select(array($db->quoteName('enabled')))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('name').' = '.$db->quote('plg_twofactorauth_totp'));
	$db->setQuery($query);
	$enabled = $db->loadResult();
	
	if ( $enabled == 0 ) {
		$query = $db->getQuery(true)
			->select(array($db->quoteName('enabled')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name').' = '.$db->quote('plg_twofactorauth_yubikey'));
		$db->setQuery($query);
		$enabled = $db->loadResult();
	}
	
	return $enabled;
}

// Chequea si el fichero kickstart.php existe en la raíz del sitio. Esto sucede cuando se restaura un sitio y se olvida (junto con algún backup) eliminarlo.
public function check_kickstart() {
	$found = false;	
	$akeeba_kickstart_file = JPATH_ROOT . DIRECTORY_SEPARATOR . "kickstart.php";
	
	if ( file_exists($akeeba_kickstart_file) ){
		if ( strpos(file_get_contents($akeeba_kickstart_file),"AKEEBA") !== false ) {
			$found = true;
		}		
	}
	
	return $found;
	
}

// Obtiene el porcentaje general de cada una de las barras de progreso
public function getOverall($info,$opcion) {
	// Inicializamos variables
	$overall = 0;
		
	switch ($opcion) {
		// Porcentaje de progreso de  Joomla Configuration
		case 1:
			if ( $info['kickstart_exists'] ) {
				return 2;
			}
			if ( (array_key_exists('coreinstalled',$info)) && (array_key_exists('corelatest',$info)) && (version_compare($info['coreinstalled'],$info['corelatest'],'==')) ) {
				$overall = $overall + 20;
			}
			if ( $info['files_with_incorrect_permissions'] == 0 ) {
				$overall = $overall + 20;
			}			
			if ( $info['vuln_extensions'] == 0 ) {
				$overall = $overall + 40;
			}
			if ( $info['backend_protection'] ) {
				$overall = $overall + 10;
			}
			if ( $info['twofactor_enabled'] == 1 ) {
				$overall = $overall + 10;
			}
			break;
		case 2:
			if ( $info['firewall_plugin_enabled'] ) {
				$overall = $overall + 20;				
				// Configuración del firewall				
				if ( $info['firewall_options']['logs_attacks'] ) {
					$overall = $overall + 6;					
				}
				if ( $info['firewall_options']['second_level'] ) {
					$overall = $overall + 6;					
				}
				if ( !(strstr($info['firewall_options']['strip_tags_exceptions'],'*')) ) {
					$overall = $overall + 12;					
				}
				if ( !(strstr($info['firewall_options']['sql_pattern_exceptions'],'*')) ) {
					$overall = $overall + 10;										
				}
				if ( !(strstr($info['firewall_options']['lfi_exceptions'],'*')) ) {
					$overall = $overall + 12;										
				}
				if ( $info['firewall_options']['session_protection_active'] ) {
					$overall = $overall + 2;					
				}
				if ( $info['spam_protection_plugin_enabled'] ) {
					$overall = $overall + 2;					
				}
				
				$now = new \DateTime(date('Y-m-d',strtotime(date('Y-m-d H:i:s'))));
				
				if (empty($this->info['last_check'])){
					$this->info['last_check'] = $now->format('Y-m-d H:i:s');
				}				
				// Cron 
				$last_check = new \DateTime(date('Y-m-d',strtotime($this->info['last_check'])));
									
				// Extraemos los días que han pasado desde el último chequeo
				(int) $interval = $now->diff($last_check)->format("%a");
																		
				if ( $interval < 2 ) {
					$overall = $overall + 30;					
				} else {
					
				}	
				
					
			} else {
				return 2;
			}
			break;		
	}
	return $overall;
}
	/* Función que obtiene información del estado del backup  */
	private function getBackupInfo() {
		
		// Instanciamos la consulta
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		// Consultamos si Akeeba Backup está instalado
		$query = 'SELECT COUNT(*) FROM #__extensions WHERE element="com_akeeba"';
		$db->setQuery( $query );
		$db->execute();	
		$akeeba_installed = $db->loadResult();
		
		if ( $akeeba_installed == 1 ) {
			$this->backupinfo['product'] = 'Akeeba Backup';
			$this->AkeebaBackupInfo();
		} else {
			
			// Consultamos si Xcloner Backup and Restore está instalado
			$query = 'SELECT COUNT(*) FROM #__extensions WHERE element="com_xcloner-backupandrestore"';
			$db->setQuery( $query );
			$db->execute();	
			$xcloner_installed = $db->loadResult();
			
			if ( $xcloner_installed == 1 ) {
				$this->backupinfo['product'] = 'Xcloner - Backup and Restore';
				$this->XclonerbackupInfo();				
			} else {
			
				// Consultamos si Easy Joomla Backup está instalado
				$query = 'SELECT COUNT(*) FROM #__extensions WHERE element="com_easyjoomlabackup"';
				$db->setQuery( $query );
				$db->execute();	
				$ejb_installed = $db->loadResult();
				
				if ( $ejb_installed == 1 ) {
					$this->backupinfo['product'] = 'Easy Joomla Backup';
					$this->EjbInfo();				
				} 
			}
		}
		
	}
	
	/* Función que obtiene información del estado del último backup creado por Akeeba Backup  */
	private function AkeebaBackupInfo() {
		
		// Instanciamos la consulta
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select('MAX('.$db->qn('id').')')
			->from($db->qn('#__ak_stats'))
			->where($db->qn('origin') .' != '.$db->q('restorepoint'));
		$db->setQuery($query);
		$id = $db->loadResult();
		
		// Hay al menos un backup creado
		if ( !empty($id) ) {
			$query = $db->getQuery(true)
				->select(array('*'))
				->from($db->quoteName('#__ak_stats'))
				->where('id = '.$id);				
			$db->setQuery($query);
			$backup_statistics = $db->loadAssocList();			
						
			// Almacenamos el resultado
			$this->backupinfo['latest'] = $backup_statistics[0]['backupend'];
			$this->backupinfo['latest_status'] = $backup_statistics[0]['status'];
			$this->backupinfo['latest_type'] = $backup_statistics[0]['type'];
		}
	}
	
	/* Función que obtiene información del estado del último backup creado por Xcloner - Backup and Restore  */
	private function XclonerbackupInfo() {
		
		// Incluimos el fichero de configuración de la extensión
		include JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_xcloner-backupandrestore" . DIRECTORY_SEPARATOR . "cloner.config.php";
		
		// Extraemos el directorio donde se encuentran almacenados los backups...
		$backup_dir = $_CONFIG['clonerPath'];
		
		// ... y buscamos dentro los ficheros existentes, ordenándolos por fecha
		$files_name = Folder::files($backup_dir,'.',true,true);
		$files_name = array_combine($files_name, array_map("filemtime",$files_name));
		arsort($files_name);
		
		// El primer elemento del array será el que se ha creado el último. Formateamos la fecha para guardarlo en la BBDD.
		$latest_backup = date("Y-m-d H:i:s",filemtime(key($files_name)));
		
		// Almacenamos el resultado
		$this->backupinfo['latest'] = $latest_backup;
		$this->backupinfo['latest_status'] = 'complete';
		
	}
	
	/* Función que obtiene información del estado del último backup creado por Easy Joomla Backup  */
	private function EjbInfo() {
		
		// Instanciamos la consulta
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true)
			->select('MAX('.$db->qn('id').')')
			->from($db->qn('#__easyjoomlabackup'));
		$db->setQuery($query);
		$id = $db->loadResult();
		
		// Hay al menos un backup creado
		if ( !empty($id) ) {
			$query = $db->getQuery(true)
				->select(array('*'))
				->from($db->quoteName('#__easyjoomlabackup'))
				->where('id = '.$id);				
			$db->setQuery($query);
			$backup_statistics = $db->loadAssocList();			
						
			// Almacenamos el resultado
			$this->backupinfo['latest'] = $backup_statistics[0]['date'];
			$this->backupinfo['latest_status'] = 'complete';
			$this->backupinfo['latest_type'] = $backup_statistics[0]['type'];
		}
		
	}
	
	/* Función que verifica una fecha */
	public function verifyDate($date, $strict = true)
	{
		$dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
		if ($strict) {
			$errors = \DateTime::getLastErrors();
			if (!empty($errors['warning_count'])) {
				return false;
			}
		}
		return $dateTime !== false;
	}

/* Función que devuelve el estado de la extensión remota  */
	public function getStatus($opcion=true) {
	
		// Inicializamos las variables
		$extension_updates = null;
		$installed_version = "0.0.0";
		$hasUpdates = 0;		
		
		$cpanel_model = new CpanelModel();
		$filemanager_model = new FilemanagerModel();
		$update_model = new DatabaseupdatesModel();
				
		// Comprobamos el estado del plugin Update Database
		$update_database_plugin_installed = $update_model-> PluginStatus(4);
		$update_database_plugin_version = $update_model->get_database_version();
		$update_database_plugin_last_check = $update_model->last_check();
		
		$firewall_plugin_enabled = $cpanel_model->PluginStatus(1);
		$this->info['firewall_plugin_enabled']		= $firewall_plugin_enabled;
		
		$plugin = PluginHelper::getPlugin('system','securitycheck');
		$plugin = new Registry($plugin->params);
		$exclude_exceptions_if_vulnerable = $plugin->get('exclude_exceptions_if_vulnerable',0);
		$logs_attacks = $plugin->get('logs_attacks',1);
		$second_level = $plugin->get('second_level',1);
		$strip_tags_exceptions = $plugin->get('strip_tags_exceptions','');
		$sql_pattern_exceptions = $plugin->get('sql_pattern_exceptions','');
		$lfi_exceptions = $plugin->get('lfi_exceptions','');
		$session_protection_active = $plugin->get('session_protection_active','');
		$FirewallOptions = array(
			'exclude_exceptions_if_vulnerable'	=>	0,
			'logs_attacks'	=>	0,
			'second_level'	=>	0,
			'strip_tags_exceptions'	=>	'',
			'sql_pattern_exceptions'	=>	'',
			'lfi_exceptions'	=>	'',
			'session_protection_active'	=>	0
		);
		$FirewallOptions['exclude_exceptions_if_vulnerable'] = $exclude_exceptions_if_vulnerable;
		$FirewallOptions['logs_attacks'] = $logs_attacks;
		$FirewallOptions['second_level'] = $second_level;
		$FirewallOptions['strip_tags_exceptions'] = $strip_tags_exceptions;
		$FirewallOptions['sql_pattern_exceptions'] = $sql_pattern_exceptions;
		$FirewallOptions['lfi_exceptions'] = $lfi_exceptions;
		$FirewallOptions['session_protection_active'] = $session_protection_active;
		$this->info['firewall_options']		= $FirewallOptions;
		
		$ConfigApplied = new ProtectionModel();
		$ConfigApplied = $ConfigApplied->GetConfigApplied();
		$this->info['backend_protection']		= $ConfigApplied['hide_backend_url'];
		
		$spam_protection_plugin_enabled = $cpanel_model->PluginStatus(5);
		$this->info['spam_protection_plugin_enabled']		= $spam_protection_plugin_enabled;
		
		$this->info['kickstart_exists']		= $this->check_kickstart();
				
		$this->info['twofactor_enabled']	= $this->get_two_factor_status();	
				
		// Vulnerable components
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = 'SELECT COUNT(*) FROM #__securitycheck WHERE Vulnerable="Si"';
		$db->setQuery( $query );
		$db->execute();	
		$vuln_extensions = $db->loadResult();
		
		$this->info['vuln_extensions']		= $vuln_extensions;
		
		// Check for unread logs
		(int) $logs_pending = $cpanel_model->LogsPending();
		
		// Get files with incorrect permissions from database
		$files_with_incorrect_permissions = $filemanager_model->loadStack("filemanager_resume","files_with_incorrect_permissions");
		
		// If permissions task has not been launched, set a '0' value.
		if ( is_null($files_with_incorrect_permissions) ) {
			$files_with_incorrect_permissions = 0;
		}
		
		// FileManager last check
		$last_check = $filemanager_model->loadStack("filemanager_resume","last_check");
		
		// Get files with incorrect permissions from database
		$files_with_bad_integrity = 0;
		
		// If permissions task has not been launched, set a '0' value.
		if ( is_null($files_with_bad_integrity) ) {
			$files_with_bad_integrity = 0;
		}
		
		// FileIntegrity last check
		$last_check_integrity = 0;
		
		// Comprobamos el estado del backup
		$this->getBackupInfo();
	
		// Verificamos si el core está actualizado (obviando la caché) 
		$updatemodel = new UpdateModel();
		$updatemodel->refreshUpdates(true);
		$coreInformation = $updatemodel->getUpdateInformation();
		
		// Si el plugin 'Update Batabase' está instalado, comprobamos si está actualizado
		if ( $update_database_plugin_installed ) {
			//$this->update_database_plugin_needs_update = $this->checkforUpdate();
		} else {
			$this->update_database_plugin_needs_update = null;
		}
		
		// Añadimos la información del sistema
		$this->getInfo();
		
		// Añadimos la información sobre las extensiones no actualizadas. Esta opción no es necesaria cuando escogemos la opción 'System Info'
		if ( $opcion ) {
			$extension_updates = $this->getNotUpdatedExtensions();
			$outdated_extensions = json_decode($extension_updates, true);
			$sc_to_find = "Securitycheck Pro";
			$key_sc = array_search($sc_to_find, array_column($outdated_extensions, 2));	
			
			if ( $key_sc !== false ) {
				$installed_version = $outdated_extensions[$key_sc][4];
				$hasUpdates = 1;
			}	
		}
				
		// Si no hay backup establecemos la fecha actual para evitar un error en la bbdd al insertar el valor
		$is_valid_date = $this->verifyDate($this->backupinfo['latest']);
		if ( !$is_valid_date ) {
			$this->backupinfo['latest'] = "0000-00-00 00:00:00";
		}
		
		$this->info['overall_web_firewall']		= $this->getOverall($this->info,2);	
		$this->info['coreinstalled']		= $coreInformation['installed'];	
		$this->info['corelatest']		= $coreInformation['latest'];	
		$this->info['vuln_extensions']		= $vuln_extensions;
		$this->info['files_with_incorrect_permissions']		= $files_with_incorrect_permissions;
		
		
		$this->info['overall_joomla_configuration'] = $this->getOverall($this->info,1);
				
		$data = array(
			'vuln_extensions'		=> $vuln_extensions,
			'logs_pending'	=> $logs_pending,
			'files_with_incorrect_permissions'		=> $files_with_incorrect_permissions,
			'last_check' => $last_check,
			'files_with_bad_integrity'		=> $files_with_bad_integrity,
			'last_check_integrity' => "0000-00-00 00:00:00",
			'installed_version'	=> $installed_version,
			'hasUpdates'	=> $hasUpdates,
			'coreinstalled'	=>	$coreInformation['installed'],
			'corelatest'	=>	$coreInformation['latest'],
			'last_check_malwarescan' => null,
			'suspicious_files'		=> 0,
			'update_database_plugin_installed'	=>	$update_database_plugin_installed,
			'update_database_plugin_version'	=>	$update_database_plugin_version,
			'update_database_plugin_last_check'	=>	$update_database_plugin_last_check,
			'update_database_plugin_needs_update'	=>	$this->update_database_plugin_needs_update,
			'backup_info_product'	=>	$this->backupinfo['product'],
			'backup_info_latest'	=>	$this->backupinfo['latest'],
			'backup_info_latest_status'	=>	$this->backupinfo['latest_status'],
			'backup_info_latest_type'	=>	$this->backupinfo['latest_type'],
			'php_version'	=>	$this->info['phpversion'],
			'database_version'	=>	$this->info['dbversion'],
			'web_server'	=>	$this->info['server'],
			'extension_updates'	=>	$extension_updates,
			'last_check_database_optimization'	=> null
		);
		
		return $data;	
	}

}