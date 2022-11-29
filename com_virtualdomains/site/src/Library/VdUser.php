<?php

namespace Janguo\Component\VirtualDomains\Site\Library;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;

/**
 *
 * Dummy User Class
 * Override authlevels
 * @author michel
 *
 */
class VdUser extends User {

    function __construct($identifier) {
        parent::__construct($identifier);
    }

    /**
     *
     * This method pushs additional auth levels to the user object
     *
     * @param array $viewlevels
     */
    public function addAuthLevel($viewlevels) {
        // No access levels assigned to this domain? return...

        if(!count($viewlevels)) return;
        // Is the user not logged in

        $user = Factory::getUser();

        if(!$this->id) {
            $user->guest = 1;
        }

        $user->_authLevels=  $user->getAuthorisedViewLevels();

        // Now add all access levels assigned to this domain
        foreach($viewlevels as $viewlevel) {
            if($viewlevel && !in_array($viewlevel, $user->_authLevels)) {
                $user->_authLevels[] = (int) $viewlevel;
            }
        }

        //put this to the session
        $session = Factory::getSession();
        $session->set('user', $user);
    }
}
