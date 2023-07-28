<?php

/**
 * @package     Virtualdomains.Site
 * @subpackage  mod_vdlanguage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Janguo\Module\VDLanguages\Site\Helper\VDLanguagesHelper;

$headerText	= StringHelper::trim($params->get('header_text'));
$footerText	= StringHelper::trim($params->get('footer_text'));
$list = VDLanguagesHelper::getList($params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_vdlanguages', $params->get('layout', 'default'));
