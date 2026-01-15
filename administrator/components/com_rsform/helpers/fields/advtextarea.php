<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldAdvTextarea extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-advtextarea" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFATEXTAREA_LABEL');
	}

	public function getFormInput() {
		$value 			= (string) $this->getValue();
		$name			= $this->getName();
		$id				= $this->getId();
		$cols  			= $this->getProperty('COLS', 50);
		$rows 			= $this->getProperty('ROWS', 5);
		$placeholder 	= $this->getProperty('PLACEHOLDER', '');
		$attr			= $this->getAttributes();
		$additional 	= '';

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/fseditor/jquery.fseditor.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/fseditor/fseditor.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");

        $this->addScriptDeclaration('RSFormPro.Editors['.json_encode($name).'] = function() { try { return jQuery(document.getElementById(' . json_encode($id) . ')).fseditor(\'setValue\'); } catch (e) {} };');

		// Start building the HTML input
		$html = '<textarea';
		// Parse Additional Attributes
		if ($attr) {
			foreach ($attr as $key => $values) {
				// @new feature - Some HTML attributes (type, size, maxlength) can be overwritten
				// directly from the Additional Attributes area
				if (($key == 'cols' || $key == 'rows') && strlen($values)) {
					${$key} = $values;
					continue;
				}
				$additional .= $this->attributeToHtml($key, $values);
			}
		}
		if ($cols) {
			$html .= ' cols="'.(int) $cols.'"';
		}
		if ($rows) {
			$html .= ' rows="'.(int) $rows.'"';
		}
		// Placeholder
		if (!empty($placeholder)) {
			$html .= ' placeholder="'.$this->escape($placeholder).'"';
		}
		// Name & id
		$html .= ' name="'.$this->escape($name).'"'.
				 ' id="'.$this->escape($id).'"';

		$html .= ' data-rsfp-type="advtextarea"';
        $html .= ' data-rsfp-max-width="' . (int) $this->getProperty('MAXWIDTH') . '"';
        $html .= ' data-rsfp-max-height="' . (int) $this->getProperty('MAXHEIGHT') . '"';

		// Additional HTML
		$html .= $additional;
		$html .= '>';
		
		// Add the value
		$html .= $this->escape($value);
		
		// Close the tag
		$html .= '</textarea>';
		
		return $html;
	}
	
	// @desc Overridden here because we need to make sure VALIDATIONRULE is not 'password'
	//		 Passwords shouldn't be shown as a default value
	public function getValue() {
		$rule = $this->getProperty('VALIDATIONRULE', 'none');
		if ($rule == 'password') {
			return '';
		}

		return parent::getValue();
	}
	
	// @desc For easy styling
	public function getAttributes() {
		$attr = parent::getAttributes();
		if (strlen($attr['class'])) {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= 'rsform-advtext-box';

		return $attr;
	}
}