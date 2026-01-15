<?php
/**
* @package RSForm!Pro
* @copyright (C) 2007-2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

class plgSystemRsfppdfInstallerScript
{
	protected static $minJoomla = '3.10.0';
	protected static $minComponent = '3.1.10';
	protected static $minPHP = '7.1';

	public function preflight($type, $parent)
    {
		if ($type == 'uninstall')
		{
			return true;
		}

		try
        {
	        if (version_compare(PHP_VERSION, static::$minPHP, '<'))
	        {
		        throw new Exception('To use the RSForm! Pro PDF Plugin, a minimum PHP version of ' . static::$minPHP . ' is required. Please update your PHP version.', 'warning');
	        }

	        if (!function_exists('mb_internal_encoding') || !is_callable('mb_internal_encoding'))
	        {
		        throw new Exception('Please install and enable the Multibyte String library in your PHP installation: http://php.net/manual/en/book.mbstring.php');
	        }

			if (!class_exists('\\Joomla\\CMS\\Version'))
			{
				throw new Exception(sprintf('Please upgrade to at least Joomla! %s before continuing!', static::$minJoomla));
			}

			$jversion = new \Joomla\CMS\Version;
			if (!$jversion->isCompatible(static::$minJoomla))
	        {
		        throw new Exception('Please upgrade to at least Joomla! ' . static::$minJoomla . ' before continuing!');
	        }

	        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/rsform.php'))
	        {
		        throw new Exception('Please install the RSForm! Pro component before continuing.');
	        }

	        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/assets.php') || !file_exists(JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/version.php'))
	        {
		        throw new Exception('Please upgrade RSForm! Pro to at least version ' . static::$minComponent . ' before continuing!');
	        }

	        require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/version.php';
	        if (!class_exists('RSFormProVersion') || version_compare((string) new RSFormProVersion, static::$minComponent, '<'))
	        {
		        throw new Exception('Please upgrade RSForm! Pro to at least version ' . static::$minComponent . ' before continuing!');
	        }
        }
        catch (Exception $e)
        {
			if (class_exists('\\Joomla\\CMS\\Factory'))
			{
				$app = \Joomla\CMS\Factory::getApplication();
			}
			elseif (class_exists('JFactory'))
			{
				$app = JFactory::getApplication();
			}

			if (!empty($app))
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}

			return false;
        }

        return true;
	}
	
	public function update($parent)
	{
		$this->copyFiles($parent);
		
		$db = Factory::getDbo();
		$columns = $db->getTableColumns('#__rsform_pdfs');
		
		if (!isset($columns['useremail_userpass'])) {
			$db->setQuery("ALTER TABLE `#__rsform_pdfs` ADD `useremail_userpass` VARCHAR( 255 ) NOT NULL AFTER `useremail_layout`,".
						  "ADD `useremail_ownerpass` VARCHAR( 255 ) NOT NULL AFTER `useremail_userpass`,".
						  "ADD `adminemail_userpass` VARCHAR( 255 ) NOT NULL AFTER `adminemail_layout`,".
						  "ADD `adminemail_ownerpass` VARCHAR( 255 ) NOT NULL AFTER `adminemail_userpass`,".
						  "ADD `useremail_options` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'print,modify,copy,add' AFTER `useremail_ownerpass`,".
						  "ADD `adminemail_options` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'print,modify,copy,add' AFTER `adminemail_ownerpass`");
			$db->execute();
		}

		// Let's make some columns mediumtext
		$changed = array('useremail_php', 'useremail_layout', 'adminemail_php', 'adminemail_layout');
		foreach ($changed as $column)
		{
			if (isset($columns[$column]) && $columns[$column] == 'text')
			{
				$db->setQuery("ALTER TABLE #__rsform_pdfs CHANGE " . $db->qn($column) . " " . $db->qn($column) . ' mediumtext');
				$db->execute();
			}
		}
		
		// Run our SQL file
		$this->runSQL($parent->getParent()->getPath('source'), 'install');

		// Need to select a different PDF library since we've removed these.
		$query = $db->getQuery(true)
			->update('#__rsform_config')
			->set($db->qn('SettingValue') . ' = ' . $db->q('dompdf20'))
			->where($db->qn('SettingName') . ' = ' . $db->q('pdf.library'))
			->where($db->qn('SettingValue') . ' IN (' . implode(',', $db->q(array('dompdf', 'dompdf10'))) . ')');
		$db->setQuery($query)->execute();
	}
	
	public function install($parent) {
		$this->copyFiles($parent);
	}
	
	protected function copyFiles($parent) {
		$app = Factory::getApplication();
		$installer = $parent->getParent();
		$src = $installer->getPath('source').'/admin';
		$dest = JPATH_ADMINISTRATOR.'/components/com_rsform';
		
		if (!Folder::copy($src, $dest, '', true))
		{
			$app->enqueueMessage('Could not copy to '.str_replace(JPATH_SITE, '', $dest).', please make sure destination is writable!', 'error');
		}
	}

	protected function runSQL($source, $file)
	{
		$db = Factory::getDbo();
		$sqlfile = $source . '/sql/mysql/' . $file . '.sql';

		if (file_exists($sqlfile))
		{
			$buffer = file_get_contents($sqlfile);
			if ($buffer !== false)
			{
				$queries = $db->splitSql($buffer);
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '')
					{
						$db->setQuery($query)->execute();
					}
				}
			}
		}
	}
}