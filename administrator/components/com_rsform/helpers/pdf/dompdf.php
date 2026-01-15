<?php
/**
 * @package RSForm!Pro
 * @copyright (C) 2007-2018 www.rsjoomla.com
 * @license GPL, http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

class RSFormProPDFdompdf
{
	/* @var $pdf \Dompdf\Dompdf */
	public $pdf;

	public function __construct()
	{
		if (!class_exists('Dompdf\Dompdf', false))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_rsform/helpers/pdf/dompdf8/autoload.inc.php';
		}

		$options = new \Dompdf\Options();
		$options->set('defaultFont', RSFormProHelper::getConfig('pdf.font', 'serif'));
		$options->set('defaultPaperSize', RSFormProHelper::getConfig('pdf.paper', 'a4'));
		$options->set('defaultPaperOrientation', RSFormProHelper::getConfig('pdf.orientation', 'portrait'));
		$options->set('isRemoteEnabled', (bool) RSFormProHelper::getConfig('pdf.remote', '0'));

		$this->pdf = new \Dompdf\Dompdf($options);
	}

	public function setSecurity($user = '', $owner = '', $options = array())
	{
		$this->pdf->get_canvas()->get_cpdf()->setEncryption($user, $owner, $options);
	}

	public function stream($filename)
	{
		$this->pdf->stream($filename);
	}

	public function renderPDF($filename, $html)
	{
		$this->pdf->load_html($html, 'utf-8');
		$this->pdf->render();
	}

	public function getContents()
	{
		return $this->pdf->output();
	}
}