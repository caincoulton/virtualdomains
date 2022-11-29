<?php

namespace Janguo\Component\VirtualDomains\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Router\Route;

abstract class VirtualDomainsHelper
{
    /**
     * Retrieves absolute URL for menu item that belongs to a menu tree associated with another domain, but that domain is
     * contained within the current multi-site Joomla installation
     * 
     * @param $itemId Menu item id
     * 
     * @return string
     */
    public static function getCrossDomainLink($itemId) {
        $menu = Factory::getApplication()->getMenu();
        $item = $menu->getItem($itemId);

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('`domain`')
            ->from('#__virtualdomain AS vd')
            ->join('INNER', '#__menu AS m ON m.id = vd.menuid')
            ->where('`menutype` = \'' . $item->menutype . '\'')
            ->where('vd.published > 0');

        // echo str_replace('#_', 'j25', $query->__toString());
        
        $db->setQuery($query);
        $domain = $db->loadResult();

        return (strlen($domain) > 0 ? '//' . $domain : '') . Route::_('index.php?Itemid=' . $item->id);
    }
}