<?php

/**
* @version		$Id:virtualdomain.php  1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Tables
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
* Jimtawl TableVirtualdomain class
*
* @package		Virtualdomains
* @subpackage	Tables
*/
class VirtualDomainTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	public function __construct(& $db) 
	{
		parent::__construct('#__virtualdomain', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see Table:bind
	* @since 1.5
	*/
	public function bind($array, $ignore = '')
	{
		if ( isset( $array['params'] ) && is_array( $array['params'] ) )
        {
            $registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
        }		
		return parent::bind($array, $ignore);		
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	public function check()
	{
		if ($this->id === 0) {
			//get next ordering

			$this->ordering = $this->getNextOrder();
		}
		
		/** No www */		
		if (strpos($this->domain,'www.') ===0) {
			$this->domain = substr($this->domain,4);			
		}
		
	    /** check for valid name */

		if (trim($this->domain) == '') {
			$this->setError(Text::_('Your Domain must have a name.'));
			return false;
		}

		return true;
	}
}
 