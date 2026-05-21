<?php

namespace Janguo\Component\VirtualDomains\Site\Library;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;

/**
 * Dummy Language class
 */
class VdLanguage extends Language {

	public function setDefault($lang) {
		/** @var SiteApplication */
		$app = Factory::getApplication();
		$refresh = $app->getLanguage();
		$refresh->metadata['tag'] = $lang;

		$refresh->default = $lang;
		$new = $app->getLanguage(); //???
	}
}
