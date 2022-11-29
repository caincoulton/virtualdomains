<?php

namespace Janguo\Component\VirtualDomains\Site\Library;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Access\Access;

/**
 *
 * Dummy JAccess class
 * Override viewlevels
 * @author michel
 *
 */
class VdAccess extends Access {

	public static function addAuthorisedViewLevels($userId, $viewlevels)
	{
		$guestUsergroup = ComponentHelper::getParams('com_users')->get('guest_usergroup', 1);

		// Get a database object.
		$db = Factory::getDbo();

		// Build the base query.
		$query = $db->getQuery(true)
		->select('id, rules')
		->from($db->quoteName('#__viewlevels'));

		// Set the query for execution.
		$db->setQuery($query);

		// Build the view levels array.
		foreach ($db->loadAssocList() as $level)
		{
			$rules = (array) json_decode($level['rules']);

			//The magic: guest usergroup must never be configured in database and is now added dynamically
			if(in_array($level['id'], $viewlevels) &! in_array($guestUsergroup, $rules)) {
				$rules[] = $guestUsergroup;
			}

			self::$viewLevels[$level['id']] = $rules;

		}
	}
}