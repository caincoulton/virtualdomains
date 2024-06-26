<?php

defined('_JEXEC') or die;

/**
* @package    Virtualdomains
* @subpackage Plugins
* @author     	Michael Liebler {@link http://www.janguo.de}
* @copyright	Copyright (C) 2008 - 2013 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Virtualdomains is free software. This version may have been modified pursuant to the
* GNU General Public License, and as distributed it includes or is derivative
* of works licensed under the GNU General Public License or other free or open
* source software licenses. See COPYRIGHT.php for copyright notices and
* details.
*/

use Joomla\CMS\Factory;

class plgSystemVirtualdomainsInstallerScript
{
	public function postflight($route, $adapter)
	{
		if (stripos($route, 'install') !== false)
		{			
			$db = Factory::getDBo();
			$db->setQuery('UPDATE #__extensions set enabled = 1 WHERE `type` = "plugin" AND element = "virtualdomains"');
			$db->execute();
		}
	}
}