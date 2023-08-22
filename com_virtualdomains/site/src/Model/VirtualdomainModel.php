<?php

/**
* @version		$Id:virtualdomain.php  1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Models
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Uri\Uri;

class VirtualDomainModel extends ItemModel {
    /**
     * Method to get an item.
     *
     * @param   integer  $pk  The id of the item
     *
     * @return  object
     *
     * @since 4.0.0
     * @throws \Exception
     */
    public function getItem($pk = null) {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($this->getTable('Virtualdomain')->getTableName())
            ->where('id = ' . $pk);

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Virtual Domain object
     *
     * @param string $host
     * 
     * @return object
     */
    public function getCurrentDomain() {
        $uri = Uri::getInstance();
        $host = str_replace( 'www.', '', $uri->getHost());

        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($this->getTable('Virtualdomain')->getTableName())
            ->where('domain = ' . $db->quote($host));

        $db->setQuery($query);
        return $db->loadObject();
    }
}