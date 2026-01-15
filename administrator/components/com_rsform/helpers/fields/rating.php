<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldRating extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-rating" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFARATING_LABEL');
	}

	public function processValidation($validationType = 'form', $submissionId = 0)
	{
		$value = $this->getValue();

		return empty($value) && $this->getProperty('REQUIRED') ? false : true;
	}

	// functions used for rendering in front view
	public function getFormInput() {
		$value 			= (float) $this->getValue();
		$name 			= $this->getName();
		$id 			= $this->getId();
		$attr 			= $this->getAttributes();
		$stars          = (int) $this->getProperty('NUMBERSTARS');
		$type           = $this->getProperty('RATINGTYPE');
		$baseColor      = $this->getProperty('BASECOLOR');
		$fillColor      = $this->getProperty('FILLCOLOR');
		$startColor     = $this->getProperty('STARTCOLOR');
		$endColor       = $this->getProperty('ENDCOLOR');
		$halfStar       = $this->getProperty('HALFSTAR');
		$additional 	= '';

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/rateYo/jquery.rateyo.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/rateYo/jquery.rateyo.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");

        $html =
            '<div' .
            ' id="' . $id . '"' .
            ' data-rsfp-type="rating"' .
            ' data-rsfp-halfstar="' . ($halfStar ? 'true' : 'false') . '"' .
            ' data-rsfp-rating="' . $value . '"' .
            ' data-rsfp-nrstars="' . $stars . '"' .
            ' data-rsfp-ratingtype="' . $this->escape($type) . '"' .
            ' data-rsfp-basecolor="' . $this->escape($baseColor) . '"';

        switch ($type)
        {
            case 'singlecolor':
                $html .= ' data-rsfp-fillcolor="'. $this->escape($fillColor) .'"';
                break;

            case 'multicolor':
                $html .= ' data-rsfp-startcolor="'. $this->escape($startColor) .'"';
                $html .= ' data-rsfp-endcolor="'. $this->escape($endColor) .'"';
                break;
        }

        if ($attr) {
            foreach ($attr as $key => $values) {
                $additional .= $this->attributeToHtml($key, $values);
            }
        }

        $html .= $additional;
        $html .= '>';
        $html .= '</div>';

        $html .= '<input '.
            ' name="'.$this->escape($name).'"' .
            ' id="'.$this->escape($id).'-value"' .
            ' value="' . $value . '"' .
            ' type="hidden"' .
            ' />';

        return $html;
	}
	
	public function getAttributes() {
		$attr = parent::getAttributes();
		if (strlen($attr['class'])) {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= 'rsfp-star-rating';
		
		return $attr;
	}
}