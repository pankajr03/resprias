<?php

/**
 * @ package Track Actions
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
 
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
 
class Pkg_SecuritycheckInstallerScript
{
	
	public function postflight($type, $parent)
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
                 
        $tableExtensions = $db->quoteName("#__extensions");
		$columnElement   = $db->quoteName("element");
		$columnType      = $db->quoteName("type");
		$columnEnabled   = $db->quoteName("enabled");
            
		// Enable plugin
		$db->setQuery(
			"UPDATE 
				$tableExtensions
			SET
				$columnEnabled=1
			WHERE
				$columnElement='securitycheck'
			AND
				$columnType='plugin'"
		);
		
		$db->execute();
	}
	
		
}