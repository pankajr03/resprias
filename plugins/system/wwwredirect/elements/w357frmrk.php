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

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Form\FormField;

class JFormFieldw357frmrk extends FormField
{
    protected $type = 'w357frmrk';

    protected function getLabel()
    {
        return ''; // No label required
    }

    protected function getInput()
    {
        // Check if the Web357 Framework plugin is enabled
        if (!PluginHelper::isEnabled('system', 'web357framework')) {
            Factory::getApplication()->enqueueMessage(Text::_('WEB357FRAMEWORK_PLUGIN_IS_REQUIRED'), 'error');
            return ''; // Exit if the plugin is not enabled
        }


        return ''; // Return empty as no additional input is required
    }
}
