<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Janguo\Module\VDLanguages\Site\Helper\VDLanguagesHelper;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$headerText	= StringHelper::trim($params->get('header_text'));
$footerText	= StringHelper::trim($params->get('footer_text'));
Factory::getLanguage()->load('mod_languages');
$list = VDLanguagesHelper::getList($params);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_languages', $params->get('layout', 'default'));
