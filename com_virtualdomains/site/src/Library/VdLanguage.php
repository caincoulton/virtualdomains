<?php

namespace Janguo\Component\VirtualDomains\Site\Library;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;

/**
 * Dummy JLanguage class
 */
class VdLanguage extends Language {

	public function setDefault($lang) {
		$refresh = Factory::getLanguage();
		$refresh->metadata['tag'] = $lang;

		$refresh->default	= $lang;
		$new = Factory::getLanguage();
	}
}
