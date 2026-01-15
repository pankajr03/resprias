<?php
/**
 * @package    RSForm! Pro
 *
 * @copyright  (c) 2019 RSJoomla!
 * @link       https://www.rsjoomla.com
 * @license    GNU General Public License http://www.gnu.org/licenses/gpl-3.0.en.html
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldDompdffont extends JFormFieldList
{
	protected function getOptions()
	{
		static $done;

		if (!$done)
		{
			$done = true;
			$dir = JPATH_ADMINISTRATOR . '/components/com_rsform/helpers/pdf/';

			$this->addOption('RSFP_PDF_DEJAVU_SANS', array('value' => 'dejavu sans', 'disabled' => !file_exists($dir . 'dompdf8/lib/fonts/DejaVuSans.ufm')));
			$this->addOption('RSFP_PDF_FIREFLYSUNG', array('value' => 'fireflysung', 'disabled' => !file_exists($dir . 'dompdf8/lib/fonts/fireflysung.ufm')));
			$this->addOption('RSFP_PDF_COURIER', array('value' => 'courier'));
			$this->addOption('RSFP_PDF_HELVETICA', array('value' => 'helvetica'));
			$this->addOption('RSFP_PDF_TIMES', array('value' => 'times'));
		}

		return parent::getOptions();
	}
}