<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldColorpicker extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-colorpicker" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFACOLORPICKER_LABEL');
	}

	public function processValidation($validationType = 'form', $submissionId = 0)
	{
		$value = $this->getValue();

		if (!strlen($value) && $this->getProperty('REQUIRED'))
		{
			return false;
		}

		if (strlen($value))
		{
			$value = ltrim($value, '#');
			if (strlen($value) != 6 || !preg_match('/([a-f0-9]{3}){1,2}\b/i', $value))
			{
				return false;
			}
		}

		return true;
	}

	public function getFormInput() {
		$value 			= (string) $this->getValue();
		$name 			= $this->getName();
		$id 			= $this->getId();
		$attr 			= $this->getAttributes();
		$showInput		= $this->getProperty('SHOWCOLORINPUT', false);
		$additional 	= '';

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/spectrum/spectrum.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/spectrum/spectrum.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");

        JText::script('RSFP_RSFA_COLOR_PICKER_CHOOSE');
        JText::script('RSFP_RSFA_COLOR_PICKER_CANCEL');
		
		$html = '<input';
		if ($attr) {
			foreach ($attr as $key => $values) {
				$additional .= $this->attributeToHtml($key, $values);
			}
		}

		if ($showInput)
		{
			$html .= ' data-rsfp-showinput="true"';
		}

		// Set the type & value
		$html .= ' type="text"'.
				 ' value="'.$this->escape($value).'"' .
		         ' name="'.$this->escape($name).'"'.
				 ' id="'.$this->escape($id).'"' .
                 ' data-rsfp-type="colorpicker"';
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
		$attr['class'] .= 'rsform-colorpicker-box';
		
		return $attr;
	}
}