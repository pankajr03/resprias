<?php
/**
* @package RSForm!Pro
* @copyright (C) 2007-2018 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class RSFormPDF
{
	public $dompdf;

	public $instance;
	
	public function __construct()
	{
		$library = RSFormProHelper::getConfig('pdf.library');

		if (file_exists(__DIR__ . '/' . $library . '.php'))
		{
			require_once __DIR__ . '/' . $library . '.php';

			$class = 'RSFormProPDF' . $library;

			if (class_exists($class))
			{
				$this->instance = new $class;
			}

			// For legacy support
			if (strpos($library, 'dompdf') === 0)
			{
				$this->dompdf = $this->instance->pdf;
			}
		}
	}

	public function __call($func, $args)
	{
		if ($this->instance)
		{
			try
			{
				// Use our own classes first
				if (is_callable(array($this->instance, $func)))
				{
					return call_user_func_array(array($this->instance, $func), $args);
				}
				// If we don't have a function in our class, try to passthru to the actual PDF class
				elseif (is_callable(array($this->instance->pdf, $func)))
				{
					return call_user_func_array(array($this->instance->pdf, $func), $args);
				}
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(),'error');
			}
		}

		return null;
	}
	
	public function render($filename, $html)
	{
		// suppress errors
		if (strlen($html) > 0)
		{
			if (preg_match_all('#[^\x00-\x7F]#u', $html, $matches))
			{
				foreach ($matches[0] as $match)
				{
					$html = str_replace($match, $this->_convertASCII($match), $html);
				}
			}

			$this->renderPDF($filename, $html);
		}
	}

	public function write($filename, $html, $output = false)
	{
		// Render PDF
		$this->render($filename, $html);

		if ($output)
		{
			if (ob_get_contents())
			{
				ob_end_clean();
			}

			$this->stream($filename);
		}
		else
		{
			return $this->getContents();
		}
	}
	
	protected function _convertASCII($str)
	{
		$count	= 1;
		$out	= '';
		$temp	= array();
		
		for ($i = 0, $s = strlen($str); $i < $s; $i++)
		{
			$ordinal = ord($str[$i]);
			if ($ordinal < 128)
			{
				$out .= $str[$i];
			}
			else
			{
				if (count($temp) == 0)
				{
					$count = ($ordinal < 224) ? 2 : 3;
				}
			
				$temp[] = $ordinal;
			
				if (count($temp) == $count)
				{
					$number = ($count == 3) ? (($temp['0'] % 16) * 4096) + (($temp['1'] % 64) * 64) + ($temp['2'] % 64) : (($temp['0'] % 32) * 64) + ($temp['1'] % 64);

					$out .= '&#'.$number.';';
					$count = 1;
					$temp = array();
				}
			}
		}
		
		return $out;
	}
}