<?php
/**
 * @copyright	Copyright (C) 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class com_virtualdomainsInstallerScript

{

	
	public function postflight($route, $adapter)
	{
		
		if (stripos($route, 'install') !== false)
		{			
			$host = $_SERVER['HTTP_HOST'];
			$db = Factory::getDBo();
			$db->setQuery('UPDATE #__virtualdomain set domain = '.$db->Quote($host).' WHERE `id` = 1 AND domain="replace-with-your-default-domain"');
			$db->execute();
		}
	}
	 

	
}