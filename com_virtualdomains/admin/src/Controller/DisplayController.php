<?php

/**
 * @version		$Id:controller.php 1 2014-02-26Z mliebler $
 * @author	   	Michael Liebler
 * @package    Virtualdomains
 * @subpackage Controllers
 * @copyright  	Copyright (C) 2014, Michael Liebler. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Janguo\Component\VirtualDomains\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Virtualdomains Standard Controller
 *
 * @package Virtualdomains   
 * @subpackage Controllers
 */
class DisplayController extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'VirtualDomains';
}
