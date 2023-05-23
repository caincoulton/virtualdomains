<?php

/**
 * @version		$Id:view.html.php 1 2014-02-26 11:56:55Z mliebler $
 * @package		Virtualdomains
 * @subpackage 	Views
 * @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
 * @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Janguo\Component\VirtualDomains\Administrator\View\VirtualDomains;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
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
		HTMLHelper::_('jquery.framework');

		$doc = Factory::getDocument();
		$doc->addScript('/media/com_virtualdomains/js/hostcheck.min.js');

		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');

		$this->params = ComponentHelper::getParams( 'com_virtualdomains' );
		
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

		$canDo = VirtualDomainsHelper::getActions();
		$user = Factory::getUser();

		$lang = Factory::getLanguage()->getTag();
		if($lang != 'de-DE') {
			$lang = 'en-GB';
		}
		
		ToolbarHelper::title( Text::_( 'Virtual Domains' ), 'generic.png' );

		// $help_url = 'http://help.janguo.de/vd-mccoy/'.$lang.'/#Virtualdomains-Manager';
		// ToolbarHelper::help('#', false, $help_url);

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew('virtualdomain.add');
		}

		if (($canDo->get('core.edit')))
		{
			ToolbarHelper::editList('virtualdomain.edit');
		}


		if ($this->state->get('filter.state') != 2)
		{
			ToolbarHelper::publish('virtualdomains.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('virtualdomains.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		ToolbarHelper::deleteList('', 'virtualdomains.delete');

		ToolbarHelper::preferences('com_virtualdomains', '550');	
	}


	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
				'a.domain' => Text::_('Domain'),
				'a.template' => Text::_('Template'),
				'a.home' => Text::_('Default_Domain'),
				'a.published' => Text::_('JSTATUS'),
				'a.id' => Text::_('JGRID_HEADING_ID'),
		);
	}
}

