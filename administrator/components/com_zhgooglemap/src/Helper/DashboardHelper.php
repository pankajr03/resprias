<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
namespace ZhukDL\Component\ZhGoogleMap\Administrator\Helper;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * Helper.
 *
 * @since  1.6
 */
class DashboardHelper extends ContentHelper
{
    
	public static function translateExtensionName(&$item)
	{
		// ToDo: Cleanup duplicated code. from com_installer/models/extension.php
		$lang = Factory::getLanguage();
		$path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

		$extension = $item->element;
		$source = JPATH_SITE;

		switch ($item->type)
		{
			case 'component':
				$extension = $item->element;
				$source = $path . '/components/' . $extension;
				break;
			case 'module':
				$extension = $item->element;
				$source = $path . '/modules/' . $extension;
				break;
			case 'file':
				$extension = 'files_' . $item->element;
				break;
			case 'library':
				$extension = 'lib_' . $item->element;
				break;
			case 'plugin':
				$extension = 'plg_' . $item->folder . '_' . $item->element;
				$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
				break;
			case 'template':
				$extension = 'tpl_' . $item->element;
				$source = $path . '/templates/' . $item->element;
		}

		$lang->load("$extension.sys", JPATH_ADMINISTRATOR)
		|| $lang->load("$extension.sys", $source);
		$lang->load($extension, JPATH_ADMINISTRATOR)
		|| $lang->load($extension, $source);

		// Translate the extension name if possible
		$item->name = strip_tags(Text::_($item->name));
	}


	public static function getExtensionList()
	{

        $db   = Factory::getDbo();
        $query = $db->getQuery(true)
                    ->select('h.name, h.manifest_cache, h.type, h.enabled, h.element, h.folder, h.client_id ')
                    ->from('#__extensions as h')
                    ->where('h.element LIKE \'%zhgoogle%\'')
                    ->order('h.package_id, h.folder, h.element');
                    
        $db->setQuery($query);
        
        try
        {
            $extList = $db->loadObjectList();
            // Load translation for installer
            $lang = Factory::getLanguage();		
            $currentLangTag = $lang->getTag();
            $lang->load('com_installer', JPATH_ADMINISTRATOR, $currentLangTag, true);    

            // Load translation for other extensions
            foreach($extList as $i => $item) { 
                DashboardHelper::translateExtensionName($item);
            }
            
        
        }
        catch (RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
                      
        return $extList;

	}
    
}
