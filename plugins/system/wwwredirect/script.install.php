<?php
/* ======================================================
 # www Redirect for Joomla! - v1.2.8 (free version)
 # -------------------------------------------------------
 # For Joomla! CMS (v4.x)
 # Author: Web357 (Yiannis Christodoulou)
 # Copyright: (©) 2014-2024 Web357. All rights reserved.
 # License: GNU/GPLv3, https://www.gnu.org/licenses/gpl-3.0.html
 # Website: https://www.web357.com
 # Demo: 
 # Support: support@web357.com
 # Last modified: Monday 27 October 2025, 04:02:06 PM
 ========================================================= */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemWwwredirectInstallerScript extends PlgSystemWwwredirectInstallerScriptHelper
{
	public $name           = 'WWW Redirect';
	public $alias          = 'wwwredirect';
	public $extension_type = 'plugin';
	public $plugin_folder  = 'system';
}