<?php
/**
* @package RSform!Pro
* @copyright (C) 2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemRsfpgoogle extends JPlugin
{
	protected $autoloadLanguage = true;
	
	public function onRsformBackendAfterShowConfigurationTabs($tabs)
	{
		$tabs->addTitle(JText::_('RSFP_GOOGLE_LABEL'), 'form-google');
		$tabs->addContent($this->configurationScreen());
	}
	
	public function onRsformFrontendBeforeFormDisplay($args)
	{
		$code = RSFormProHelper::getConfig('google.code');
		if (empty($code))
		{
			return false;
		}
		
		$script = "
<script type=\"text/javascript\">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', " . json_encode($code) . "]);
	_gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>";

		if (JFactory::getDocument()->getType() == 'html')
		{
			RSFormProAssets::addCustomTag($script);
		}
	}
	
	public function onRsformFrontendAfterShowThankyouMessage($args)
	{
		$code = RSFormProHelper::getConfig('google.code');
		if (empty($code))
		{
			return false;
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('FormName'))
			->from($db->qn('#__rsform_forms'))
			->where($db->qn('FormId') . ' = ' . $db->q($args['formId']));
		$formName = $db->setQuery($query)->loadResult();

		$script = "
<script type=\"text/javascript\">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', " . json_encode($code) . "]);
	_gaq.push(['_trackPageview', " . json_encode($formName) . "]);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>";

		if (JFactory::getDocument()->getType() == 'html')
		{
			RSFormProAssets::addCustomTag($script);
		}
	}
	
	protected function configurationScreen()
	{
		ob_start();

		JForm::addFormPath(__DIR__ . '/forms');

		$form = JForm::getInstance( 'plg_system_rsfpgoogle.configuration', 'configuration', array('control' => 'rsformConfig'), false, false );
		$form->bind($this->loadFormData());

		?>
		<div id="page-google" class="form-horizontal">
			<?php
			foreach ($form->getFieldsets() as $fieldset)
			{
				if ($fields = $form->getFieldset($fieldset->name))
				{
					foreach ($fields as $field)
					{
						echo $field->renderField();
					}
				}
			}
			?>
		</div>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	private function loadFormData()
	{
		$data 	= array();
		$db 	= JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__rsform_config'))
			->where($db->qn('SettingName') . ' LIKE ' . $db->q('google.%', false));
		if ($results = $db->setQuery($query)->loadObjectList())
		{
			foreach ($results as $result)
			{
				$data[$result->SettingName] = $result->SettingValue;
			}
		}

		return $data;
	}
}