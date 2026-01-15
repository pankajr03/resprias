<?php
/**
* @package RSForm! Pro
* @copyright (C) 2020 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/field.php';
require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/calendar.php';

class RSFormProFieldDatepicker extends RSFormProField
{
	protected $customId;

	protected $translationTable = array
	(
		'd' => 'dd',
		'j' => 'd',
		'D' => 'ddd',
		'l' => 'dddd',

		'F' => 'mmmm',
		'm' => 'mm',
		'M' => 'mmm',
		'n' => 'm',

		'Y' => 'yyyy',
		'y' => 'yy',
	);
	
	public function getPreviewInput()
	{
		return '<span class="rsfaicon-datepicker" style="font-size:28px; margin-right:5px"></span>' . JText::_('RSFP_RSFADATEPICKER_LABEL');
	}

	public function getFormInput() {

		$value 		= (string) $this->getValue();
		$name		= $this->getName();
		$format 	= $this->getProperty('DATE_FORMAT_PICKER');
		$readonly	= $this->getProperty('READONLY', 'NO');
		$attr		= $this->getAttributes();
		$additional = '';
		$position	= $this->getPosition($this->formId, $this->componentId);

		// Create a unique ID for this calendar.
		$id = 'rsfp_adv_datepicker_'.$this->formId.'_'.$position;

		$validationCalendar = $this->getProperty('VALIDATIONCALENDAR', '');
		$next = false;
		$previous = false;
		$calendarOffset = false;

		// set the validation calendar rules
		if (!empty($validationCalendar))
		{
			list($rule, $otherCalendarId) = explode(' ', $validationCalendar);

			$otherCalendarProperties = RSFormProHelper::getComponentProperties($otherCalendarId);

			if (!empty($otherCalendarProperties))
			{
				// get the position of the other calendar
				$otherCalendarPosition = $this->getPosition($this->formId, $otherCalendarProperties['componentId']);
				if ($rule == 'min')
				{
					$next = 'rsfp_adv_datepicker_'.$this->formId.'_'.$otherCalendarPosition;
				}
				else
				{
					$previous = 'rsfp_adv_datepicker_'.$this->formId.'_'.$otherCalendarPosition;
				}

				$offset = (int)$this->getProperty('VALIDATIONCALENDAROFFSET', 1);
				if ($offset != 0)
				{
					$calendarOffset = $offset;
				}
			}
		}

		$mindate 		= $this->isCode($this->getProperty('MINDATE', ''));
		$maxdate 		= $this->isCode($this->getProperty('MAXDATE', ''));
		$firstdayofweek = $this->getProperty('FIRSTDAYOFWEEK', 'day0');
		$firstdayofweek = (int)str_replace('day', '', $firstdayofweek);

		$weekdayformat 	= strtolower($this->getProperty('WEEKDAYFORMAT', 'full'));
		$weekdayformat 	= $weekdayformat == 'full';
		$monthsformat 	= strtolower($this->getProperty('MONTHSFORMAT', 'short'));
		$monthsformat	= $monthsformat == 'short';

		$selectyears 	= $this->getProperty('SELECTYEARS', 'NO');
		$selectmonths 	= $this->getProperty('SELECTMONTHS', 'NO');

		$disableall 	= $this->getProperty('DISABLEALL', 'NO');
		$daysdisabled 	= $this->getProperty('DAYSOFWEEKDISABLED', '');

		$exceptions	= $this->isCode($this->getProperty('DISABLEEXCEPTIONS', ''));

		if ($disableall) {
			$daysdisabled = false;
		}

		// do not use exceptions if there is no disabled options active
		if (!$disableall && empty($daysdisabled))
		{
			$exceptions = false;
		}

		if ($exceptions)
		{
			$all_exceptions = array();
			$exceptions_rows = RSFormProHelper::explode($exceptions);
			foreach ($exceptions_rows as $exceptions_row)
			{
				$exception = explode(',', $exceptions_row);

				foreach ($exception as $exp)
				{
					// remove any whitespaces before and after
					$exp = trim($exp);
					if (strpos($exp, '-') !== false)
					{
						list($date_from, $date_to) = explode('-', $exp);
						$date_from = trim($date_from);
						$date_to = trim($date_to);

						$date_from = DateTime::createFromFormat('m/d/Y', $date_from);
						// if the date doesn't have a proper format, skip this exception
						if (!$date_from)
						{
							continue 1;
						}

						$date_from = $date_from->format('Y-m-d');

						$date_to = DateTime::createFromFormat('m/d/Y', $date_to);
						// if the date doesn't have a proper format, skip this exception
						if (!$date_to)
						{
							continue 1;
						}

						$date_to = $date_to->format('Y-m-d');

						$all_exceptions[] = $date_from . '|' . $date_to;
					}
					else
					{
						if (!is_numeric($exp)) {
							$exp = DateTime::createFromFormat('m/d/Y', $exp);
							// if the date doesn't have a proper format, skip this exception
							if (!$exp)
							{
								continue 1;
							}

							$exp = $exp->format('Y-m-d');
						}
						$all_exceptions[] = $exp;
					}
				}
			}

			$exceptions = empty($all_exceptions) ? false : implode(',', $all_exceptions);
		}

		// verify that the min and max dates that are set, are proper dates
		if (!empty($mindate))
		{
			$mindate = DateTime::createFromFormat('m/d/Y', $mindate);
			$mindate = !$mindate ? '' : $mindate->format('Y-m-d');
		}

		if (!empty($maxdate))
		{
			$maxdate = DateTime::createFromFormat('m/d/Y', $maxdate);
			$maxdate = !$maxdate ? '' : $maxdate->format('Y-m-d');
		}
		
		
		// set the years range
		if (!empty($attr) && isset($attr['data-rsfp-select-years-range']) && strlen($attr['data-rsfp-select-years-range']))
		{
			$years_range = (int) $attr['data-rsfp-select-years-range'];
			
			// if there is a min and max date specified restrict the range to that interval
			if (!empty($mindate) && !empty($maxdate))
			{
				//var_dump($mindate);
				//var_dump($maxdate);
				list($mindate_y, $rest_min) = explode('-', $mindate, 2);
				list($maxdate_y,$rest_max) = explode('-', $maxdate, 2);
				
				$maxdate_y = (int) $maxdate_y;
				$mindate_y = (int) $mindate_y;
				$range = $maxdate_y - $mindate_y;
				
				// if the range that the user entered is grater than the maximum allowed we must change it
				if ($years_range > $range)
				{
					$attr['data-rsfp-select-years-range'] = $range;
				}
			}
		}

		// load dependencies and translations
		$this->loadDependencies();

		// add the field to the advanced form fields array
		RSFormProAssets::addScriptDeclaration("RSFormPro.AdvancedFormFields.elements.push('{$id}');");

		$html = '<input' .
			' type="text"' .
			' name="'.$this->escape($name).'"' .
			' value="'.$this->escape($value).'"' .
			' id="'.$this->escape($id).'"';

		$custom_attributes = array(
			'data-rsfp-type="datepicker"',
			'data-rsfp-format="' . $this->escape($this->processDateFormat($format)) . '"',
			'data-rsfp-min="' . $this->escape($mindate) . '"',
			'data-rsfp-max="' . $this->escape($maxdate) . '"',
			'data-rsfp-select-months="' . $this->escape($selectmonths) . '"',
			'data-rsfp-select-years="' . $this->escape($selectyears) . '"',
			'data-rsfp-first-day="' . $this->escape($firstdayofweek) . '"',
			'data-rsfp-show-weekdays-full="' . $this->escape($weekdayformat) . '"',
			'data-rsfp-show-months-short="' . $this->escape($monthsformat) . '"',
			'data-rsfp-disableall="' . $this->escape($disableall) . '"',
			'data-rsfp-container="#' . $this->escape($id) . '_container"'
		);

		if ($daysdisabled) {
			$custom_attributes[] = 'data-rsfp-daysdisabled="' . $this->escape($daysdisabled) . '"';
		}

		if ($exceptions) {
			$custom_attributes[] = 'data-rsfp-exceptions="' . $this->escape($exceptions) . '"';
		} else {
			$custom_attributes[] = 'readonly=""';
		}

		if (!$readonly)
		{
			$custom_attributes[] = 'data-rsfp-editable="true"';
		}

		// set the next attribute
		if ($next)
		{
			$custom_attributes[] = 'data-rsfp-next="' . $this->escape($next) . '"';
			// add the offset if any
			if ($calendarOffset)
			{
				$custom_attributes[] = 'data-rsfp-offset="' . $this->escape($calendarOffset) . '"';
			}
		}
		// set the next previous
		if ($previous)
		{
			$custom_attributes[] = 'data-rsfp-previous="' . $this->escape($previous) . '"';
			// add the offset if any
			if ($calendarOffset)
			{
				$custom_attributes[] = 'data-rsfp-offset="' . $this->escape($calendarOffset) . '"';
			}
		}

		// build the data-value
		if (!empty($value)) {
			$data_value = $value;
			if (JFactory::getLanguage()->getTag() != 'en-GB')
			{
				$data_value = RSFormProCalendar::fixValue($value, $format);
			}

			$data_value = DateTime::createFromFormat($format, $data_value);
			$data_value = !$data_value ? '' : $data_value->format('Y-m-d');

			if (!empty($data_value)) {
				$custom_attributes[] = 'data-value="' . $this->escape($data_value) . '"';
			}
		}

		// add the custom attributes
		$html .= ' '.implode(' ', $custom_attributes);

		if ($attr) {
			foreach ($attr as $key => $values) {
				// skip the autofocus if is the first element because it opens the calendar
				if ($key == 'autofocus') {
					continue;
				}
				$additional .= $this->attributeToHtml($key, $values);
			}
		}

		// Additional HTML
		$html .= $additional;
		// Close the tag
		$html .= ' /><div id="'.$this->escape($id) . '_container"></div>';

		return $html;
	}

	protected function loadDependencies(){
		static $are_loaded = false;

		if (!$are_loaded)
		{
			// load the jQuery framework
			RSFormProAssets::addJquery();
			RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/script.js', array('pathOnly' => true, 'relative' => true)));
			RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/datepicker/picker.js', array('pathOnly' => true, 'relative' => true)));
			RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/datepicker/picker.date.js', array('pathOnly' => true, 'relative' => true)));
			RSFormProAssets::addScript(JHtml::_('script', 'plg_system_rsfpadvancedformfields/datepicker/legacy.js', array('pathOnly' => true, 'relative' => true)));

			// get the language object
			$lang = JFactory::getLanguage();

			// load the proper language translations
			if ($current_lang = $lang->getTag())
			{
				$current_lang = str_replace('-', '_', $current_lang);
				$lang_exceptions = array(
					'ar_AA' => 'ar',
					'ka_GE' => 'ge_GEO',
					'sr_RS' => 'sr_RS_cy',
					'sr_YU' => 'sr_RS_lt',
				);

				$current_lang = isset($lang_exceptions[$current_lang]) ? $lang_exceptions[$current_lang] : $current_lang;
				$lang_script = JHtml::_('script', 'plg_system_rsfpadvancedformfields/datepicker/translations/'.$current_lang.'.js', array('pathOnly' => true, 'relative' => true));
				if (!is_null($lang_script))
				{
					RSFormProAssets::addScript($lang_script);

					// these translations are not in the translations js files
					$out = "\n".'
							jQuery.extend( jQuery.fn.pickadate.defaults, {
								labelMonthNext: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_NEXT').'",
								labelMonthPrev: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_PREV').'",
								labelMonthSelect: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_SELECT').'",
								labelYearSelect: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_YEAR_SELECT').'"
							});';

					RSFormProAssets::addScriptDeclaration($out);
				}
				else if ($current_lang != 'en_GB')
				{
					$m_short = $m_full = array();
					for ($i=1; $i<=12; $i++)
					{
						$m_short[] = '"'.JText::_('RSFP_CALENDAR_MONTHS_SHORT_'.$i, true).'"';
						$m_full[] = '"'.JText::_('RSFP_CALENDAR_MONTHS_LONG_'.$i, true).'"';
					}
					$w_short = $w_full = array();
					for ($i=0; $i<=6; $i++)
					{
						$w_short[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_SHORT_'.$i, true).'"';
						$w_full[] = '"'.JText::_('RSFP_CALENDAR_WEEKDAYS_LONG_'.$i, true).'"';
					}

					$out = "\n".'
							jQuery.extend( jQuery.fn.pickadate.defaults, {
								monthsFull: ['.implode(',', $m_full).' ],
								monthsShort: [ '.implode(',', $m_short).' ],
								weekdaysFull: [ '.implode(',', $w_full).' ],
								weekdaysShort: [ '.implode(',', $w_short).' ],
								today: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_TODAY').'",
								clear: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_CLEAR').'",
								close: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_CLOSE').'",
								labelMonthNext: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_NEXT').'",
								labelMonthPrev: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_PREV').'",
								labelMonthSelect: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_MONTH_SELECT').'",
								labelYearSelect: "'.JText::_('RSFP_ADV_FIELD_DATEPICKER_LABEL_YEAR_SELECT').'"
							});';

					RSFormProAssets::addScriptDeclaration($out);
				}
			}

			RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/datepicker/default.css', array('pathOnly' => true, 'relative' => true)));
			RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/datepicker/default.date.css', array('pathOnly' => true, 'relative' => true)));

			if ($lang->isRtl()) {
				RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/datepicker/rtl.css', array('pathOnly' => true, 'relative' => true)));
			}
			
			// some overrides
			RSFormProAssets::addStyleSheet(JHtml::_('stylesheet', 'plg_system_rsfpadvancedformfields/datepicker/overrides.css', array('pathOnly' => true, 'relative' => true)));

			// make this true so we do not need to load them again
			$are_loaded = true;
		}
	}

	protected function processDateFormat($dateFormat) {
		$newFormat = '';

		for ($i = 0; $i < strlen($dateFormat); $i++)
		{
			$current = $dateFormat[$i];

			if (isset($this->translationTable[$current]))
			{
				$newFormat .= $this->translationTable[$current];
			}
			else
			{
				$newFormat .= $current;
			}
		}

		return $newFormat;
	}

	protected function getPosition($formId, $componentId)
	{
		static $calendars = array();

		if (!isset($calendars[$formId]))
		{
			$calendars[$formId] = RSFormProHelper::componentExists($formId, RSFORM_FIELD_ADVANCED_DATEPICKER);
		}

		$position = (int) array_search($componentId, $calendars[$formId]);

		return $position;
	}

	public function processValidation($validationType = 'form', $submissionId = 0)
	{
		$validate 	= $this->getProperty('VALIDATIONDATE', true);
		$required 	= $this->getProperty('REQUIRED', false);
		$format 	= $this->getProperty('DATE_FORMAT_PICKER');
		$value 		= $this->getValue();

		if ($required && !strlen(trim($value)))
		{
			return false;
		}

		if ($validate && strlen(trim($value)))
		{
			if (JFactory::getLanguage()->getTag() != 'en-GB')
			{
				$value = RSFormProCalendar::fixValue($value, $format);
			}

			$validDate = DateTime::createFromFormat($format, $value);

			if ($validDate)
			{
				$validDate = $validDate->format($format);
			}

			if ($validDate !== $value)
			{
				return false;
			}
		}

		return true;
	}
}