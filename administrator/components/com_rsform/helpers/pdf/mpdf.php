<?php
/**
 * @package RSForm!Pro
 * @copyright (C) 2007-2018 www.rsjoomla.com
 * @license GPL, http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class RSFormProPDFMPDF
{
	/* @var $pdf \Mpdf\Mpdf */
	public $pdf;

	public function __construct()
	{
		if (!class_exists('\Mpdf\Mpdf', false))
		{
			require_once __DIR__ . '/mpdf/vendor/autoload.php';
		}

		try
		{
			$this->pdf = new \Mpdf\Mpdf([
				'default_font' => 'frutiger',
				'orientation' => RSFormProHelper::getConfig('pdf.orientation') === 'portrait' ? 'P' : 'L',
				'format' => RSFormProHelper::getConfig('pdf.paper'),
				'tempDir' => Factory::getApplication()->get('tmp_path')
			]);

			$paths = array(
				JPATH_SITE . '/libraries/vendor/composer/ca-bundle/res/cacert.pem',
				JPATH_SITE . '/libraries/src/Http/Transport/cacert.pem',
				JPATH_SITE . '/libraries/joomla/http/transport/cacert.pem'
			);
			foreach ($paths as $path)
			{
				if (file_exists($path))
				{
					$this->pdf->curlCaCertificate = $path;
					break;
				}
			}
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

	}

	public function setSecurity($user = '', $owner = '', $options = array())
	{
		$permissions = array('assemble');
		$print   = array('print', 'print-highres');
		$modify  = array('modify', 'fill-forms');
		$copy    = array('extract', 'copy');
		$add     = array('annot-forms');

		if ($options)
		{
			foreach ($options as $option)
			{
				if (isset($$option))
				{
					$permissions = array_merge($permissions, $$option);
				}
			}
		}

		if ($this->pdf)
		{
			$this->pdf->SetProtection($permissions, $user, $owner, 128);
		}
	}

	public function stream($filename)
	{
		if ($this->pdf)
		{
			$this->pdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
		}
	}

	public function renderPDF($filename, $html)
	{
		if ($this->pdf)
		{
			$this->pdf->WriteHTML($html);
		}
	}

	public function getContents()
	{
		if ($this->pdf)
		{
			return $this->pdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
		}
	}
}