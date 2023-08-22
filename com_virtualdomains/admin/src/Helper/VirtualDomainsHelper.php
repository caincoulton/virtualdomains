<?php

namespace Janguo\Component\VirtualDomains\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

/**
 * @version		$Id$
 * @package		Virtualdomain
 * @subpackage 	Helpers
 * @copyright	Copyright (C) 2010, . All rights reserved.
 * @author     	Michael Liebler {@link http://www.janguo.de}
 * @copyright	Copyright (C) 2008 - 2013 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Virtualdomains is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
abstract class VirtualDomainsHelper
{
    /**
     * Gets a list of the actions that can be performed.
     *
     * @param   integer  The category ID.
     *
     * @return  CMSObject
     * @since   1.6
     */
    public static function getActions($categoryId = 0)
    {
    	$user	= Factory::getUser();
    	$result	= new CMSObject;
    
    	if (empty($categoryId))
    	{
    		$assetName = 'com_virtualdomains';
    		$level = 'component';
    	}
    	else
    	{
    		$assetName = 'com_virtualdomains.category.'.(int) $categoryId;
    		$level = 'category';
    	}
    
		$actions = Access::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/com_virtualdomains/access.xml',
			"/access/section[@name='$level']/"
		);
    
    	foreach ($actions as $action)
    	{
    		$result->set($action->name,	$user->authorise($action->name, $assetName));
    	}
    
    	return $result;
    }
}
