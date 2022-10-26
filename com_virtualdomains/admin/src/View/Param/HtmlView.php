<?php

/**
* @version		$Id:view.html.php 1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Views
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\View\Param;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

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
	
	protected $form;
	
	protected $item;
	
	protected $state;
	
	
	/**
	 *  Displays the list view
 	 * @param string $tpl   
     */
	public function display($tpl = null) 
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		
		parent::display($tpl);	
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->id == 0);

		// Get the toolbar object instance
        ToolbarHelper::title(Text::_( 'Params' ) . ': <small>[ ' . ($isNew ? Text::_( 'New' ) : Text::_( 'Edit' )) . ' ]</small>', 'generic.png');

		ToolBarHelper::apply('param.apply');
		ToolBarHelper::save('param.save');
		ToolBarHelper::save2new('param.save2new');
		ToolBarHelper::save2copy('param.save2copy');
		if ($isNew) {
			ToolBarHelper::cancel('param.cancel');
		} else {
			// for existing items the button is renamed `close`
			ToolBarHelper::cancel( 'param.cancel');
		}

        ToolBarHelper::help('index', true);
    }
}
