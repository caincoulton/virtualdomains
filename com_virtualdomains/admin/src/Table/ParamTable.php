<?php

/**
* @version		$Id:param.php  1 2014-02-26 11:56:55Z mliebler $
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

/**
* Jimtawl TableParam class
*
* @package		Virtualdomains
* @subpackage	Tables
*/
class ParamTable extends Table
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	public function __construct(& $db) 
	{
		parent::__construct('#__virtualdomain_params', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	public function bind($array, $ignore = '')
	{ 
		
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

		if (trim($this->name) == '') {
			$this->setError(Text::_('Your Param must contain a name.')); 
			return false;
		}
	

		return true;
	}
}
 