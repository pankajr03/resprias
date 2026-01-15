<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2019 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/fields/advradio.php';

class RSFormProFieldBootstrap3Advradio extends RSFormProFieldAdvradio
{
	public function setFlow() {
		$flow = $this->getProperty('FLOW', 'HORIZONTAL');
			
		if ($flow != 'HORIZONTAL') {
			$this->blocks = array('1' => 'col-sm-12', '2' => 'col-sm-6', '3' => 'col-sm-4', '4' => 'col-sm-3', '6' => 'col-sm-2');
			$this->gridStart = '<div class="row">';
			$this->gridEnd = '</div>';
			$this->splitterStart = '<div class="{block_size}">';
			$this->splitterEnd = '</div>';
		}
	}
}