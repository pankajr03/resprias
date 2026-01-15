<?php
/**
* @package RSForm!Pro
* @copyright (C) 2007-2018 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class plgSystemRsfppdf extends CMSPlugin
{
	protected $autoloadLanguage = true;

	protected $row;

	public function onRsformFormSave($form)
	{
		$app             = Factory::getApplication();
		$data 			 = $app->input->get('pdf', array(), 'array');
		$data['form_id'] = $form->FormId;
		
		if ($row = $this->getTable())
		{
			if (!$row->save($data))
			{
				$app->enqueueMessage($row->getError(), 'error');
			}
		}
	}

	protected function getTable()
	{
		return Table::getInstance('RSForm_PDFs', 'Table');
	}
	
	public function onRsformBackendFormCopy($args)
	{
		$formId 	= $args['formId'];
		$newFormId 	= $args['newFormId'];

		if ($row = $this->getTable() )
		{
			if ($row->load($formId))
			{
				if (!$row->bind(array('form_id' => $newFormId)))
				{
					return false;
				}

				$row->check();

				return $row->store();
			}
		}
	}
	
	protected function _createPDF($type, $args, $output=false)
	{
		$id  = $this->_createId($type, $args['submissionId']);
		$tmp = $this->_getTmp();
		
		// $args['form'], $args['placeholders'], $args['values'], $args['submissionId'], $args['userEmail']
		require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/pdf/pdf.php';
		
		$cached_info = $this->_getInfo($args['form']->FormId);
        if (empty($cached_info))
        {
            return false;
        }

		$info = clone $cached_info;

		$pdf = new RSFormPDF();

		if (!empty($info->{$type.'email_php'}))
		{
			eval($info->{$type.'email_php'});
		}

		if (strpos($info->{$type.'email_layout'}, '{/if}') !== false)
		{
			require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/scripting.php';
			RSFormProScripting::compile($info->{$type.'email_layout'}, $args['placeholders'], $args['values']);
		}

		$info->{$type.'email_layout'}   = str_replace($args['placeholders'], $args['values'], $info->{$type.'email_layout'});
		$info->{$type.'email_filename'} = $this->_getFilename($info->{$type.'email_filename'}, $args['placeholders'], $args['values']);

		if (strlen($info->{$type.'email_userpass'}) && strpos($info->{$type.'email_userpass'}, '{') !== false)
		{
			$info->{$type.'email_userpass'} = str_replace($args['placeholders'], $args['values'], $info->{$type.'email_userpass'});
		}

		if (strlen($info->{$type.'email_ownerpass'}) && strpos($info->{$type.'email_ownerpass'}, '{') !== false)
		{
			$info->{$type.'email_ownerpass'} = str_replace($args['placeholders'], $args['values'], $info->{$type.'email_ownerpass'});
		}

		// Sitepath placeholder
		$info->{$type.'email_layout'} = str_replace('{sitepath}', JPATH_SITE, $info->{$type.'email_layout'});

		// Create the allowed options
		$options = array();
		if (strlen($info->{$type.'email_options'}))
		{
			$options = explode(',', $info->{$type.'email_options'});
		}

		if (!strlen($info->{$type.'email_layout'}))
		{
			return false;
		}

		// Render the PDF
		$pdf->render($info->{$type.'email_filename'}, $info->{$type.'email_layout'});

		if ($info->{$type.'email_userpass'} || $info->{$type.'email_ownerpass'} || count($options) < 4)
		{
			$pdf->setSecurity($info->{$type.'email_userpass'}, $info->{$type.'email_ownerpass'}, $options);
		}

		if ($output)
		{
			ob_end_clean();
			$pdf->stream($info->{$type.'email_filename'});
			Factory::getApplication()->close();
		}
        elseif ($info->{$type.'email_send'})
		{
			$path 	= $tmp.'/'.$id.'/'.$info->{$type.'email_filename'};
			$buffer = $pdf->getContents();

			// Let's make a new writable path
			Folder::create($tmp.'/'.$id, 0777);

			// Ok so this is for messed up servers which return (true) when using File::write() with FTP but don't really work
			$written = File::write($path, $buffer) && file_exists($path);
			if (!$written)
			{
				// Let's try streams now?
				$written = File::write($path, $buffer, true) && file_exists($path);
			}

			if (!$written)
			{
				// Old fashioned file_put_contents
				$written = file_put_contents($path, $buffer) && file_exists($path);
			}

			if ($written)
			{
				$args[$type.'Email']['files'][] = $path;
			}
		}
	}
	
	public function onRsformBeforeUserEmail($args)
	{
		if ($info = $this->_getInfo($args['form']->FormId))
		{
			if ($info->useremail_send)
			{
				$this->_createPDF('user', $args);
			}
		}
	}
	
	public function onRsformBeforeAdminEmail($args)
	{
		if ($info = $this->_getInfo($args['form']->FormId))
		{
			if ($info->adminemail_send)
			{
				$this->_createPDF('admin', $args);
			}
		}
	}
	
	protected function _getInfo($formId)
	{
		static $cache;
		if (!is_array($cache))
		{
			$cache = array();
		}
		
		$formId = (int) $formId;
		
		if (!isset($cache[$formId]))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__rsform_pdfs'))
				->where($db->qn('form_id') . ' = ' . $db->q($formId));
			$cache[$formId] = $db->setQuery($query)->loadObject();
		}
		
		return $cache[$formId];
	}
	
	protected function _getFilename($filename, $replace, $with)
	{
		$filename = str_replace($replace, $with, $filename);
		$filename = str_replace(array('\\', '/'), '', $filename);
		if (empty($filename))
		{
			$filename = 'attachment';
		}
		
		return $filename.'.pdf';
	}
	
	protected function _createId($suffix, $sid)
	{
		static $hash;
		if (!is_array($hash))
		{
			$hash = array();
		}
		if (!isset($hash[$sid]))
		{
			$hash[$sid] = md5(uniqid($sid));
		}
		
		return $hash[$sid] . '_' . $suffix;
	}
	
	protected function _getTmp()
	{
		static $tmp;
		if (!$tmp)
		{
			$tmp = Factory::getConfig()->get('tmp_path');
		}
		
		return $tmp;
	}

	public function onRsformAfterUserEmail($args)
	{
		$info = $this->_getInfo($args['form']->FormId);

		if (!empty($info) && $info->useremail_send)
		{
			list($replace, $with) = RSFormProHelper::getReplacements($args['submissionId']);
			$tmp = $this->_getTmp();

			$id = $this->_createId('user', $args['submissionId']);
			$filename = $this->_getFilename($info->useremail_filename, $replace, $with);
			$dir  = $tmp.'/'.$id;
			$path = $dir.'/'.$filename;
			if (file_exists($path) && is_file($path))
			{
				File::delete($path);
			}

			if (is_dir($dir))
			{
				Folder::delete($dir);
			}
		}
	}

	public function onRsformAfterAdminEmail($args)
	{
		$info = $this->_getInfo($args['form']->FormId);

		if (!empty($info) && $info->adminemail_send)
		{
			list($replace, $with) = RSFormProHelper::getReplacements($args['submissionId']);
			$tmp = $this->_getTmp();

			$id = $this->_createId('admin', $args['submissionId']);
			$filename = $this->_getFilename($info->adminemail_filename, $replace, $with);
			$dir  = $tmp.'/'.$id;
			$path = $dir.'/'.$filename;
			if (file_exists($path) && is_file($path))
			{
				File::delete($path);
			}

			if (is_dir($dir))
			{
				Folder::delete($dir);
			}
		}
	}
	
	public function onRsformBackendAfterShowFormScriptsTabsTab()
	{
		?>
		<li><a href="javascript: void(0);" id="rsfppdf"><span class="rsficon rsficon-file-pdf-o"></span><span class="inner-text"><?php echo Text::_('RSFP_PHP_PDF_SCRIPTS'); ?></span></a></li>
		<?php
	}
	
	public function onRsformBackendAfterShowFormScriptsTabs()
	{
		if (!$this->_loadRow())
		{
			return;
		}

		$form = $this->getTabForm();
		$form->bind($this->row->getProperties());
		?>
		<div id="pdf_scripts">
			<fieldset>
			<?php
			foreach ($form->getFieldset('scripts') as $field)
			{
				?>
				<legend class="rsfp-legend"><?php echo $field->title; ?></legend>
				<div class="alert alert-info"><?php echo Text::_($field->description); ?></div>
				<?php
				echo $field->input;
			}
			?>
			</fieldset>
		</div>
		<?php
	}
	
	public function onRsformBackendAfterShowUserEmail()
	{
		if (!$this->_loadRow())
		{
			return;
		}

		$form = $this->getTabForm();
		$form->bind($this->row->getProperties());
		?>
		<fieldset>
			<!-- this workaround is needed because browsers no longer honor autocomplete="off" -->
			<input type="text" style="display:none">
			<input type="password" style="display:none">
		<legend class="rsfp-legend"><?php echo Text::_('RSFP_PDF_ATTACHMENT'); ?></legend>
			<?php echo $form->renderFieldset('useremail'); ?>
		</fieldset>
		<?php
	}

	protected function getTabForm()
	{
		Form::addFormPath(__DIR__ . '/forms');

		return Form::getInstance( 'plg_system_rsfppdf.tab', 'tab', array('control' => 'pdf'), false, false);
	}
	
	public function onRsformBackendAfterShowAdminEmail()
	{
		if (!$this->_loadRow())
		{
			return;
		}

		$form = $this->getTabForm();
		$form->bind($this->row->getProperties());
		?>
		<fieldset>
		<legend class="rsfp-legend"><?php echo Text::_('RSFP_PDF_ATTACHMENT'); ?></legend>
			<?php echo $form->renderFieldset('adminemail'); ?>
		</fieldset>
		<?php
	}
	
	public function onRsformBackendAfterShowConfigurationTabs($tabs)
	{
		$tabs->addTitle(Text::_('RSFP_PDF_CONFIG'), 'page-pdf');
		$tabs->addContent($this->configurationScreen());
	}

	private function loadFormData()
	{
		$data = array();
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__rsform_config'))
			->where($db->qn('SettingName') . ' LIKE ' . $db->q('pdf.%', false));
		if ($results = $db->setQuery($query)->loadObjectList())
		{
			foreach ($results as $result)
			{
				$data[$result->SettingName] = $result->SettingValue;
			}
		}

		return $data;
	}

		/**
	 * @return string
	 */
	private function configurationScreen()
	{
		ob_start();

		Form::addFormPath(__DIR__ . '/forms');
		Form::addFieldPath(__DIR__ . '/fields');

		$form = Form::getInstance( 'plg_system_rsfppdf.configuration', 'configuration', array('control' => 'rsformConfig'), false, false );
		$form->bind($this->loadFormData());

		?>
        <div id="page-pdf" class="form-horizontal">
			<?php
			foreach ($form->getFieldsets() as $fieldset)
			{
				if ($fields = $form->getFieldset($fieldset->name))
				{
					foreach ($fields as $field)
					{
						// This is a workaround because our fields are named "pdf." and Joomla! uses the dot as a separator and transforms the JSON into [pdf][font] instead of [pdf.font].
						echo str_replace('"rsformConfig[pdf][', '"rsformConfig[pdf.', $form->renderField($field->fieldname));
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
	
	public function onRsformAfterCreatePlaceholders($args)
	{
		$hash = md5($args['submission']->SubmissionId.'{user}'.$args['submission']->DateSubmitted);
		$args['placeholders'][] = '{user_pdf}';
		$args['values'][] = Uri::root().'index.php?option=com_rsform&task=plugin&plugin_task=user_pdf&hash='.$hash;

		$hash = md5($args['submission']->SubmissionId.'{admin}'.$args['submission']->DateSubmitted);
		$args['placeholders'][] = '{admin_pdf}';
		$args['values'][] = Uri::root().'index.php?option=com_rsform&task=plugin&plugin_task=admin_pdf&hash='.$hash;
	}

	public function onRsformBackendSwitchTasks()
	{
        $this->onRsformFrontendSwitchTasks();
	}
	
	public function onRsformFrontendSwitchTasks()
	{
	    $input 	= Factory::getApplication()->input;
		$task 	= $input->getCmd('plugin_task');
		if ($task === 'user_pdf' || $task === 'admin_pdf')
		{
            $result = false;
			$hash = $input->getCmd('hash');
			if (strlen($hash) === 32)
			{
				$type 	= $task == 'user_pdf' ? 'user' : 'admin';
				$db 	= Factory::getDbo();
				$query	= $db->getQuery(true)
					->select($db->qn('SubmissionId'))
					->select($db->qn('FormId'))
					->from($db->qn('#__rsform_submissions'))
					->where('MD5(CONCAT(' . $db->qn('SubmissionId') . ',' . $db->q('{' . $type . '}') . ',' . $db->qn('DateSubmitted') . ')) = ' . $db->q($hash));
				if ($submission = $db->setQuery($query)->loadObject())
				{
					$form = new stdClass();
					$form->FormId = $submission->FormId;
					
					list($placeholders, $values) = RSFormProHelper::getReplacements($submission->SubmissionId);
					
					$args = array(
						'SubmissionId' 	=> $submission->SubmissionId,
						'submissionId' 	=> $submission->SubmissionId,
						'form' 			=> $form,
						'placeholders' 	=> $placeholders,
						'values' 		=> $values,
					);

                    $result = $this->_createPDF($type, $args, true);
				}
			}

			if ($result === false)
			{
				Factory::getApplication()->enqueueMessage(Text::_('RSFP_PDF_DOES_NOT_EXIST'), 'error');
			}
		}
	}
	
	protected function _loadRow()
	{
		if (empty($this->row))
		{
			$this->row = $this->getTable();
			if (empty($this->row))
			{
				return false;
			}

			$formId = Factory::getApplication()->input->getInt('formId');
			$this->row->load($formId);
		}
		
		return true;
	}
	
	public function onRsformFormDelete($formId)
	{
		if ($row = $this->getTable())
		{
			$row->delete($formId);
		}
	}
	
	public function onRsformFormBackup($form, $xml, $fields)
	{
		if ($row = $this->getTable())
		{
			if ($row->load($form->FormId))
			{
				$row->check();

				$data = $row->getProperties();
				unset($data['form_id']);

				$xml->add('pdf');
				foreach ($data as $property => $value)
				{
					$xml->add($property, $value);
				}
				$xml->add('/pdf');
			}
		}
	}
	
	public function onRsformFormRestore($form, $xml, $fields)
	{
		if (isset($xml->pdf))
		{
			$data = array(
				'form_id' => $form->FormId
			);
			
			foreach ($xml->pdf->children() as $property => $value)
			{
				$data[$property] = (string) $value;
			}
			
			$row = $this->getTable();
			$row->save($data);
		}
	}
	
	public function onRsformBackendFormRestoreTruncate()
	{
		Factory::getDbo()->truncateTable('#__rsform_pdfs');
	}

	public function onRsformPdfView($contents, $filename)
    {
        /**
         *	DOMPDF Library
         */
        require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/pdf/pdf.php';
        $pdf = new RSFormPDF();

        // Write PDF
        $pdf->write($filename, $contents, true);

        Factory::getApplication()->close();
    }

	public function onRsformAfterCreateQuickAddGlobalPlaceholders(& $placeholders, $type)
	{
		if ($type === 'display')
		{
			$placeholders[] = '{user_pdf}';
			$placeholders[] = '{admin_pdf}';
		}
	}

	public function onRsformBackendGetEditFields(&$return, $submission)
	{
		$isPDF  = Factory::getApplication()->input->get('format') == 'pdf';

        if (!$isPDF)
        {
	        $userHash = md5($submission->SubmissionId . '{user}' . $submission->DateSubmitted);
            $adminHash = md5($submission->SubmissionId . '{admin}' . $submission->DateSubmitted);
	        $new_field = array();
	        $new_field[0] = Text::_('RSFP_PDF');
	        $new_field[1] = '<a href="' . Route::_('index.php?option=com_rsform&task=plugin&plugin_task=user_pdf&hash=' . $userHash) . '" class="btn btn-primary">' . Text::_('RSFP_PDF_DOWNLOAD_USER_PDF') . '</a> &mdash; <a href="' . Route::_('index.php?option=com_rsform&task=plugin&plugin_task=admin_pdf&hash=' . $adminHash) . '" class="btn btn-primary">' . Text::_('RSFP_PDF_DOWNLOAD_ADMIN_PDF') . '</a>';
            $return[] = $new_field;
        }
	}
}