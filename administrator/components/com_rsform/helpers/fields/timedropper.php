<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldTimeDropper extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-timedropper" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFATIMEDROPPER_LABEL');
	}

	public function getFormInput() {
		$value 			= (string) $this->getValue();
		$name 			= $this->getName();
		$id 			= $this->getId();
		$attr 			= $this->getAttributes();
		$additional 	= '';

        $animation           = strtolower($this->getProperty('TIME_INIT_ANIMATION'));
        $format              = $this->getProperty('TIME_FORMAT');
        $meridians           = $this->getProperty('MERIDIANS');
        $setcurrenttime      = $this->getProperty('SETCURRENTTIME') ? 'true' : 'false';
        $primary             = $this->getProperty('PRIMARYCOLOR');
        $text                = $this->getProperty('TEXTCOLOR');
        $bg                  = $this->getProperty('BACKGROUNDCOLOR');
        $border              = $this->getProperty('BORDERCOLOR');

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/timedropper/timedropper.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/timedropper/timedropper.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");
		
		$html = '<input' .
                 ' type="text"' .
                 ' name="'.$this->escape($name).'"' .
                 ' value="'.$this->escape($value).'"' .
                 ' id="'.$this->escape($id).'"' .
                 ' data-rsfp-type="timedropper"' .
                 ' data-rsfp-init-animation="' . $this->escape($animation) . '"' .
                 ' data-rsfp-format="' . $this->escape($format) . '"' .
                 ' data-rsfp-meridians="' . $this->escape($meridians) . '"' .
                 ' data-rsfp-setcurrenttime="' . $this->escape($setcurrenttime) . '"' .
                 ' data-rsfp-primary-color="' . $this->escape($primary) . '"' .
                 ' data-rsfp-text-color="' . $this->escape($text) . '"' .
                 ' data-rsfp-background-color="' . $this->escape($bg) . '"' .
                 ' data-rsfp-border-color="' . $this->escape($border) . '"';

        if ($attr) {
            foreach ($attr as $key => $values) {
                $additional .= $this->attributeToHtml($key, $values);
            }
        }

		// Additional HTML
		$html .= $additional;
		// Close the tag
		$html .= ' />';
		
		return $html;
	}
	
	public function getAttributes() {
		$attr = parent::getAttributes();
		if (strlen($attr['class'])) {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= 'rsfp-timedropper rsform-timedropper-box';
		
		return $attr;
	}
}