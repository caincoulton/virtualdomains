<?php

/**
* @version		$Id:view.html.php 1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Views
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\View\Params;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Janguo\Component\VirtualDomains\Administrator\Helper\VirtualDomainsHelper;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML View class for the Virtual Domains component
 *
 * @static
 * @package		Joomla
 * @subpackage	Virtual Domains
 * @since 1.0
 */
class HtmlView extends BaseHtmlView
{

	protected $items;

	protected $pagination;

	protected $state;
	
	
	/**
	 *  Displays the list view
 	 * @param string $tpl   
     */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$lang = Factory::getLanguage()->getTag();
		if($lang != 'de-DE') {
			$lang = 'en-GB';
		}
		
		// $help_url = 'http://help.janguo.de/vd-mccoy/'.$lang.'/#Parameters-Manager';
		// ToolBarHelper::help('#', false, $help_url);
		
		$canDo = VirtualDomainsHelper::getActions();
		$user = Factory::getUser();

		ToolBarHelper::title( Text::_( 'Params' ), 'generic.png' );
		if ($canDo->get('core.create')) {
			ToolBarHelper::addNew('param.add');
		}	
		
		if (($canDo->get('core.edit')))
		{
			ToolBarHelper::editList('param.edit');
		}
		
				
				

		if ($canDo->get('core.delete'))
		{
			ToolBarHelper::deleteList('', 'params.delete');
		}
				
		
		ToolBarHelper::preferences('com_virtualdomains', '550');  				
	}	
	

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
		 	          'a.name' => Text::_('Name'),
	     	          'a.id' => Text::_('JGRID_HEADING_ID'),
	     		);
	}	
}
