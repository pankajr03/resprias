<?php
/**
 * Securitycheck package
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Script file of Securitycheck component
 */
class com_SecuritycheckInstallerScript
{
		
	/** @var array Obsolete files and folders to remove  */
	private $ObsoleteFilesAndFolders = array(
		'files'	=> array(
			// Outdated media files
			'media/com_securitycheck/images/blocked.jpg',
			'media/com_securitycheck/images/http.jpg',
			'media/com_securitycheck/images/no_read.jpg',
			'media/com_securitycheck/images/oval_blue_left.gif',
			'media/com_securitycheck/images/oval_blue_right.gif',
			'media/com_securitycheck/images/oval_green_left.gif',
			'media/com_securitycheck/images/oval_green_right.gif',
			'media/com_securitycheck/images/permitted.jpg',
			'media/com_securitycheck/images/read.jpg',
			'media/com_securitycheck/images/second_level.jpg',
			'media/com_securitycheck/images/session_protection.jpg',
			'media/com_securitycheck/images/task_running.gif',			
			'media/com_securitycheck/javascript/excanvas.js',
			'media/com_securitycheck/javascript/jquery.flot.min.js',
			'media/com_securitycheck/javascript/jquery.flot.pie.min.js',
			'media/com_securitycheck/javascript/jquery.flot.stack.js',
			'media/com_securitycheck/javascript/jquery.flot.resize.min.js',
			'media/com_securitycheck/javascript/bootstrap-tab.js',
			'media/com_securitycheck/javascript/charisma.js',
			'media/com_securitycheck/javascript/jquery.js',
			'media/com_securitycheck/javascript/bootstrap-modal.js',
			'media/com_securitycheck/javascript/jquery.percentageloader-0.1.js',
			'media/com_securitycheck/javascript/jquery.percentageloader-0.1_license.txt',			
			'media/com_securitycheck/stylesheets/BebasNeue-webfont.eot',
			'media/com_securitycheck/stylesheets/BebasNeue-webfont.svg',
			'media/com_securitycheck/stylesheets/BebasNeue-webfont.ttf',
			'media/com_securitycheck/stylesheets/BebasNeue-webfont.woff',
			'media/com_securitycheck/stylesheets/bootstrap.min.css',
			'media/com_securitycheck/stylesheets/jquery.percentageloader-0.1.css',
			'media/com_securitycheck/stylesheets/opa-icons.css',			
		),
		'folders'	=> array(
			// Removed views
			'administrator/components/com_securitycheck/views/initialize_data',
		)
	);
	
	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $ObsoleteFilesAndFolders
	 */
	private function _removeObsoleteFilesAndFolders($ObsoleteFilesAndFolders)
	{
		// Remove files
		if(!empty($ObsoleteFilesAndFolders['files'])) foreach($ObsoleteFilesAndFolders['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if(!file_exists($f)) continue;
			File::delete($f);
		}
		
		//Remove folders
		if(!empty($ObsoleteFilesAndFolders['folders'])) foreach($ObsoleteFilesAndFolders['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if(!file_exists($f)) continue;
			Folder::delete($f);
		}
	}
	
	/**
	 * Joomla! pre-flight event
	 * 
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	public function preflight($type, $parent)
	{
		// Only allow to install on PHP 5.3.0 or later
		if ( !version_compare(PHP_VERSION, '5.3.0', 'ge') ) {
			Factory::getApplication()->enqueueMessage('Securitycheck Pro requires, at least, PHP 5.3.0', 'error');
			return false;
		} else if (version_compare(JVERSION, '4.0.0', 'lt')) {
			// Only allow to install on Joomla! 4.0.0 or later
			Factory::getApplication()->enqueueMessage("This version only works in Joomla! 4 or higher", 'error');
			return false;
		} 
		
		// Check if the 'mb_strlen' function is enabled
		if ( !function_exists("mb_strlen") ) {
			Factory::getApplication()->enqueueMessage("The 'mb_strlen' function is not installed in your host. Please, ask your hosting provider about how to install it", 'warning');
			return false;
		}
		
		$this->_removeObsoleteFilesAndFolders($this->ObsoleteFilesAndFolders);
	}
	
	
}
