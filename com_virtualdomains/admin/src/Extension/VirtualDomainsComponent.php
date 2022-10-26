<?php

namespace Janguo\Component\VirtualDomains\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

/**
 * Component class for com_virtualdomains
 *
 * @since  4.0.0
 */
class VirtualDomainsComponent extends MVCComponent implements
	RouterServiceInterface
{
	use RouterServiceTrait;
}
