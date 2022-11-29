<?php

namespace Janguo\Component\VirtualDomains\Site\Library;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

/**
 *
 * Dummy JMenu Class
 * @author michel
 */
class VdMenuFilter extends AbstractMenu {

	public function load() {}
	
	/**
	 *
	 * Method to Filter Menu Items
	 * @param array $items - Array of menu item id's
	 * @param string $filter - show/hide
	 */

	function filterMenues($params, $default) {
		//Menu filter settings for current domain
		$filter = $params->get( 'menumode' );
		$items = $params->get( 'menufilter' );
		$translatations = $params->get( 'translatemenu' );

		$lang =  Factory::getLanguage()->getTag() ;

		//Get the instance
		$menu = parent::getInstance('site',array());

		//Set all defaults on default
		//TODO: Allow language specific home items
		if($default) {
			$menu->setDefault($default, $lang);
			$menu->setDefault($default,'*');
			$menu->setDefault($default);
		}

		//Check each item
		foreach($menu->_items  as $item) {
			//Translate if translation available
			if ($item->home) {
				if(isset($translatations->$lang) && ($menutranslation = trim($translatations->$lang))) {
					$item->title = $menutranslation;
				}
			}

			switch($filter) {
				case "hide":
					//Delete menu item, if the item id  is in the items list
					if(in_array($item->id, $items)) {
						unset($menu->_items[$item->id]);
					}
					break;
				case "show":
					//Delete menu item, if the item id  is not in the items list
					if(!in_array($item->id, $items)) {
						unset($menu->_items[$item->id]);
					}
			}
		}
	}
}