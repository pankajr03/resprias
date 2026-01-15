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
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldDomaindetection extends FormField
{
    protected $type = 'domaindetection';

    protected function getLabel()
    {
        return '<label for="' . $this->id . '">' . Text::_($this->element['label']) . '</label>';
    }

    protected function getInput()
    {
        // Get current domain
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Check if we're in a safe environment (not localhost)
        $exclude_list = array('127.0.0.1', '::1', 'localhost', 'yourdomain.com', 'www.yourdomain.com');
        $isLocalhost = in_array($currentDomain, $exclude_list);
        
        $html = array();
        
        if ($isLocalhost) {
            $html[] = '<div class="alert alert-info">';
            $html[] = '<strong>' . Text::_('JGLOBAL_NOTE') . ':</strong> ';
            $html[] = Text::_('J357_PLG_SYSTEM_LOCALHOST_DETECTION_DISABLED');
            $html[] = '</div>';
        } else {
            $html[] = '<div class="control-group">';
            $html[] = '<div class="alert alert-info">';
            $html[] = '<strong>' . Text::sprintf('J357_PLG_SYSTEM_CURRENT_DOMAIN', $currentDomain) . '</strong>';
            $html[] = '</div>';
            
            $html[] = '<div class="controls" style="position: relative; top: 20px; left: 20px;">';
            $html[] = '<button type="button" class="btn btn-primary" onclick="setPreferredDomain(\'' . $currentDomain . '\')">';
            $html[] = Text::_('J357_PLG_SYSTEM_SET_AS_PREFERRED');
            $html[] = '</button>';
            $html[] = '</div>';
            $html[] = '</div>';
            
            // Add JavaScript
            $html[] = '<script type="text/javascript">';
            $html[] = 'function setPreferredDomain(domain) {';
            $html[] = '    var preferredDomainField = document.querySelector(\'input[name="jform[params][preferred_domain]"]\');';
            $html[] = '    if (preferredDomainField) {';
            $html[] = '        preferredDomainField.value = domain;';
            $html[] = '        preferredDomainField.focus();';
            $html[] = '        var event = new Event(\'change\', { bubbles: true });';
            $html[] = '        preferredDomainField.dispatchEvent(event);';
            $html[] = '    }';
            $html[] = '}';
            $html[] = '</script>';
        }
        
        return implode('', $html);
    }
} 