<?php

/**
* @version		$Id:virtualdomain.php 1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Views
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

namespace Janguo\Component\VirtualDomains\Administrator\View\VirtualDomain;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\HTML\HTMLHelper;
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
	
	/**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $state;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null) 
	{
		
		Factory::getApplication()->input->set('hidemainmenu', true);
		
		$doc = Factory::getDocument();
		
		// Initialiase variables.
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
		$this->state = $model->getState();
		$this->paramFields = $this->get('ParamFields');
				
		// Check for errors.
		if (null != ($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$code = $this->_getJs();
		
		$doc->addScriptDeclaration($code);
		$this->_tabs();

		parent::display($tpl);	
	}	
	

	private function _tabs() {
		$this->tabs = array();
		$this->tabsstart = HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details'));
		$this->tabsend = HTMLHelper::_('uitab.endTabSet');
		$this->endtab = HTMLHelper::_('uitab.endTab');
		$this->tabs['details'] = HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('Details'));
		$this->tabs['siteconfig'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-config', Text::_('Site_Config'));
		$this->tabs['menufilter'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-menus', Text::_('Menu_Filter'));
		$this->tabs['accesslevels'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-accesslevel', Text::_('Access_Level_Inheritance'));
		$this->tabs['components'] = HTMLHelper::_('uitab.addTab', 'myTab', 'components', Text::_( 'COMPONENTS_FILTER' ));
		$this->tabs['translation'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-translation', Text::_('Translation'));
	}
	
	private function _getJs() {
		$js = "
    		function switchMenuMode () {
     				var form = $('jform_params_menumode');
     				if(form.value == 'show' || form.value == 'hide') {
     					$('jform_params_menufilter').disabled=false;
     				} else {
     					$('jform_params_menufilter').disabled=true;
    				}
    		}
	
    		window.addEvent('domready', function() {
    			switchMenuMode ();
    			$('jform_params_menumode').addEvent('change',function(){
					switchMenuMode ();
				});
    		});
    	";
		return $js;
	}
	
}
