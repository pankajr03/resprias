<?php
/**
* @package RSForm! Pro
* @copyright (C) 2020 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/fields/datepicker.php';

class RSFormProFieldUikit3Datepicker extends RSFormProFieldDatepicker
{
	public function getAttributes()
	{
		$attr = parent::getAttributes();
		if (strlen($attr['class'])) {
			$attr['class'] .= ' ';
		}
		$attr['class'] .= 'uk-input';

		return $attr;
	}
}