<?php
/**
* @package RSForm! Pro
* @copyright (C) 2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';

class RSFormProFieldDateDropper extends RSFormProField
{
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-datedropper" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFADATEDROPPER_LABEL');
	}

	public function getFormInput() {
		$value 			= (string) $this->getValue();
		$name 			= $this->getName();
		$id 			= $this->getId();
		$attr 			= $this->getAttributes();
		$additional 	= '';

        $language            = $this->getLanguage();
        $animation           = strtolower($this->getProperty('INIT_ANIMATION'));
        $format              = $this->getProperty('DATE_FORMAT');
        $lock                = $this->getProperty('FORCE_DATE');
        $minyear             = $this->getProperty('MINYEAR');
        $maxyear             = $this->getProperty('MAXYEAR');
        $yearsrange          = $this->getProperty('YEARSRANGE');
        $primary             = $this->getProperty('DROPPRIMARYCOLOR');
        $text                = $this->getProperty('DROPTEXTCOLOR');
        $bg                  = $this->getProperty('DROPBACKGROUNDCOLOR');
        $border              = $this->getProperty('DROPBORDER');
        $radius              = $this->getProperty('DROPBORDERRADIUS');
        $dropshadow          = $this->getProperty('DROPSHADOW');
        $dropwidth           = $this->getProperty('DROPWIDTH');

        if ($lock == 'disabled')
        {
            $lock = 'false';
        }

        RSFormProAssets::addJquery();
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/datedropper/datedropper.js', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/datedropper/datedropper.css', array('pathOnly' => true, 'relative' => true)));
        RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");
		
		$html = '<input' .
                 ' type="text"' .
                 ' name="'.$this->escape($name).'"' .
                 ' value="'.$this->escape($value).'"' .
                 ' id="'.$this->escape($id).'"' .
                 ' data-rsfp-type="datedropper"' .
                 ' data-rsfp-init-animation="' . $this->escape($animation) . '"' .
                 ' data-rsfp-format="' . $this->escape($format) . '"' .
                 ' data-rsfp-lang="' . $this->escape($language) . '"' .
                 ' data-rsfp-lock="' . $this->escape($lock) . '"' .
                 ' data-rsfp-minyear="' . $this->escape($minyear) . '"' .
                 ' data-rsfp-maxyear="' . $this->escape($maxyear) . '"' .
                 ' data-rsfp-yearsrange="' . $this->escape($yearsrange) . '"' .
                 ' data-rsfp-primary-color="' . $this->escape($primary) . '"' .
                 ' data-rsfp-text-color="' . $this->escape($text) . '"' .
                 ' data-rsfp-background-color="' . $this->escape($bg) . '"' .
                 ' data-rsfp-border="' . $this->escape($border) . '"' .
                 ' data-rsfp-border-radius="' . $this->escape($radius) . '"' .
                 ' data-rsfp-dropshadow="' . $this->escape($dropshadow) . '"' .
                 ' data-rsfp-dropwidth="' . $this->escape($dropwidth) . '"';

        if ($attr) {
            foreach ($attr as $key => $values) {
                $additional .= $this->attributeToHtml($key, $values);
            }
        }

        try
        {
            if ($date = DateTime::createFromFormat($format, $value))
            {
                $date = JFactory::getDate($date->format('Y-m-d 12:00:00'));
                $html .= ' data-d="' . $date->day . '"';
                $html .= ' data-m="' . $date->month . '"';
                $html .= ' data-y="' . $date->year . '"';
            }
        }
        catch (Exception $e)
        {

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
		$attr['class'] .= 'rsfp-datedropper rsform-datedropper-box';
		
		return $attr;
	}

    protected function getLanguage()
    {
        $siteLanguage = JFactory::getLanguage()->getTag();
        $language = explode('-', $siteLanguage);

        return strtolower($language[0]);
    }
}