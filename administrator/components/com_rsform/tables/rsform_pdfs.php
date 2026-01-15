<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2015 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableRSForm_PDFs extends Table
{
	public $form_id 			 	= null;
	public $useremail_send		 	= 0;
	public $useremail_filename		= '';
	public $useremail_php		 	= '';
	public $useremail_layout	 	= '';
	public $useremail_userpass		= null;
	public $useremail_ownerpass		= null;
	public $useremail_options		= '';
	public $adminemail_send	 		= 0;
	public $adminemail_filename 	= null;
	public $adminemail_php		 	= '';
	public $adminemail_layout   	= '';
	public $adminemail_userpass		= null;
	public $adminemail_ownerpass	= null;
	public $adminemail_options		= '';
	
	public function __construct(& $db)
	{
		parent::__construct('#__rsform_pdfs', 'form_id', $db);
	}

	public function check()
	{
		if (is_array($this->adminemail_options))
		{
			$this->adminemail_options = implode(',', $this->adminemail_options);
		}

		if (is_array($this->useremail_options))
		{
			$this->useremail_options = implode(',', $this->useremail_options);
		}

		return true;
	}

	public function hasPrimaryKey()
	{
		$db 	= $this->getDbo();
		$key 	= $this->getKeyName();
		$table	= $this->getTableName();

		$query = $db->getQuery(true)
			->select($db->qn($key))
			->from($db->qn($table))
			->where($db->qn($key) . ' = ' . $db->q($this->{$key}));

		return $db->setQuery($query)->loadResult() !== null;
	}
}