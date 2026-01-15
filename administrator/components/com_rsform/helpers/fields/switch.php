<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldSwitch extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-switch" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFASWITCH_LABEL');
	}

	public function processValidation($validationType = 'form', $submissionId = 0)
	{
		return $this->getProperty('REQUIRED') && !$this->getValue() ? false : true;
	}

	public function getValue()
	{
		$value = false;

		// If we have an empty request, grab default value.
		if (empty($this->value) || empty($this->value['formId']))
		{
			$value = $this->getProperty('SWITCHSTATE') == 'ON';
		}

		if (isset($this->value[$this->name]) && is_array($this->value[$this->name]) && in_array(1, $this->value[$this->name]))
		{
			$value = true;
		}

		return $value;
	}

	// functions used for rendering in front view
	public function getFormInput() {
		$name 			= $this->getName();
		$id 			= $this->getId();
		$attr 			= $this->getAttributes();
		$state          = $this->getValue();
		$additional 	= '';

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/formplate/formplate.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/formplate/formplate.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}')");

        $html = '<div class="formplate">';
		$html .= '<input';
		if ($attr) {
			foreach ($attr as $key => $values) {
				// @new feature - Some HTML attributes (type, size, maxlength) can be overwritten
				// directly from the Additional Attributes area
				if (($key == 'size' || $key == 'maxlength') && strlen($values)) {
					${$key} = $values;
					continue;
				}
				$additional .= $this->attributeToHtml($key, $values);
			}
		}
		// Set the type & value
		$html .= ' type="checkbox"' .
				 ' data-rsfp-type="switch"';

		if ($state)
		{
			$html .= ' checked';
		}
        $html .= ' value="1"';

		// Name & id
		$html .= ' name="'.$this->escape($name).'[]"'.
				 ' id="'.$this->escape($id).'"';
		// Additional HTML
		$html .= $additional;
		// Close the tag
		$html .= ' />';
		$html .= '</div>';

		// Get the price instance
		require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/prices.php';
		RSFormProPrices::getInstance($this->formId)->addPrice($id, 1, $this->getProperty('ONPRICE', 1));

		return $html;
	}

    public function processBeforeStore($SubmissionId, &$post, &$files)
    {
        if (!isset($post[$this->name]))
        {
            $post[$this->name] = $this->getProperty('OFFVALUE');
        }
        else
        {
        	if (!is_array($post[$this->name]))
	        {
	        	$post[$this->name] = array($post[$this->name]);
	        }

        	if (in_array(1, $post[$this->name]))
	        {
		        $post[$this->name] = $this->getProperty('ONVALUE');
	        }
        	elseif (in_array(0, $post[$this->name]))
	        {
		        $post[$this->name] = $this->getProperty('OFFVALUE');
	        }
        }
    }
	
	public function getAttributes() {
		$attr = parent::getAttributes();
		if (strlen($attr['class'])) {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= 'rsform-switcher-box rsfp-toggler toggler';
		
		return $attr;
	}
}