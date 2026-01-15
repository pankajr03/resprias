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
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

/**
* Modelo Securitycheck
*/
class UploadModel extends BaseDatabaseModel
{

	/* Función que sube un fichero de configuración de la extensión Securitycheck (previamente exportado) y estabelce esa configuración sobreescribiendo la actual */
	function read_file()
	{
		$res = true;
		$secret_key = "";
		
		$jinput = Factory::getApplication()->input;
		
		// Get the uploaded file information
		$userfile = $jinput->files->get('file_to_import');
			
		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'), 'warning');
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'), 'warning');
			return false;
		}
		
			
		//First check if the file has the right extension, we need txt only
		if ( !(strtolower(File::getExt($userfile['name']) ) == 'txt') ) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_SECURITYCHECKPRO_INVALID_FILE_EXTENSION'), 'warning');
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1 || !($userfile['type'] == "text/plain")) 
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'), 'warning');
			return false;
		}

		// Build the appropriate paths
		$config		= Factory::getApplication()->getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		$upload_res = File::upload($tmp_src, $tmp_dest);
		
			
		// El fichero se ha subido correctamente
		if ($upload_res) {
			// Leemos el contenido del fichero, que ha de estar en formato json
			$file_content = file_get_contents($tmp_dest);
			$file_content_json = json_decode($file_content,true);
					
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			
			// Si hay contenido...
			if ( !empty($file_content_json) ) {
				// ... y lo recorremos y extraemos los pares 'storage_key' y 'storage_value'
				foreach ($file_content_json as $entry) {
					$object = (object)array(
						'storage_key'	=> $entry["storage_key"],
						'storage_value'	=> $entry["storage_value"]
					);
					
					// Borramos las entradas existentes en la BBDD
					$query = $db->getQuery(true)
						->delete($db->quoteName('#__securitycheck_storage'))
						->where($db->quoteName('storage_key').' = '.$db->quote($entry["storage_key"]));
					$db->setQuery($query);
					$res = $db->execute();
							
					try {
						// Añadimos los datos a la BBDD
						$res = $db->insertObject('#__securitycheck_storage', $object);						
						if ( !$res ) {
							Factory::getApplication()->enqueueMessage(Text::_('COM_SECURITYCHECKPRO_ERROR_IMPORTING_DATA'), 'error');
							return false;
						}
					} catch (Exception $e) {	
						Factory::getApplication()->enqueueMessage(Text::_('COM_SECURITYCHECKPRO_ERROR_IMPORTING_DATA'), 'error');
						return false;
					}
				}
				// Borramos el archivo subido...
				if ( file_exists($tmp_dest) ) {
					File::delete($tmp_dest);
				}
				// ... y mostramos un mensaje de éxito
				Factory::getApplication()->enqueueMessage(Text::_('COM_SECURITYCHECKPRO_IMPORT_SUCCESSFULLY'));
			
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'), 'warning');
				return false;			
			}		
		}
		
		return $res;
	}
}