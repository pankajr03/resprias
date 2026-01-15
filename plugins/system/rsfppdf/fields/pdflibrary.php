<?php
/**
 * @package    RSForm! Pro
 *
 * @copyright  (c) 2019 RSJoomla!
 * @link       https://www.rsjoomla.com
 * @license    GNU General Public License http://www.gnu.org/licenses/gpl-3.0.en.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

if (version_compare(JVERSION, '4.0', '<'))
{
	JLoader::registerAlias('Joomla\\CMS\\Form\\Field\\ListField', 'JFormFieldList');
}

class JFormFieldPdflibrary extends ListField
{
	protected function getOptions()
	{
		static $done;

		if (!$done)
		{
			$done = true;
			$dir = JPATH_ADMINISTRATOR . '/components/com_rsform/helpers/pdf/';

			$this->addOption('RSFP_PDF_DOMPDF20', array('value' => 'dompdf20', 'disabled' => !is_dir($dir . 'dompdf20')));
			$this->addOption('RSFP_PDF_MPDF', array('value' => 'mpdf', 'disabled' => !is_dir($dir . 'mpdf')));
		}

		return parent::getOptions();
	}
}