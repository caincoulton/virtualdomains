 <?php
/**
* @version		$Id:virtualdomain.php 1 2014-02-26 11:56:55Z mliebler $
* @package		Virtualdomains
* @subpackage 	Views
* @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
* @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class VirtualdomainsViewVirtualdomain  extends JViewLegacy {

	
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
		
		$doc = Factory::getDocument();
		
		HTMLHelper::stylesheet( 'fields.css', 'administrator/components/com_virtualdomains/assets/' );
		if(version_compare(JVERSION, '3', 'lt')) {
			HTMLHelper::stylesheet( 'bootstrap-forms.css', 'administrator/components/com_virtualdomains/assets/' );
		}
		
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		$this->paramFields = $this->get('ParamFields');
				
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$code = $this->_getJs();
		
		$doc->addScriptDeclaration($code);
		$this->_tabs();

		if(version_compare(JVERSION,'4','<')){
			$this->sidebar = JHtmlSidebar::render();
			$tpl = "3";
		}

		parent::display($tpl);	
	}	
	

	private function _tabs() {
		$this->tabs = array();
		if(version_compare(JVERSION,'3.0','lt')) {
			$this->tabsstart = HTMLHelper::_('tabs.start','vd-sliders-'.$this->item->id, array('useCookie'=>1));
			$this->tabsend = HTMLHelper::_('tabs.end');
			$this->endtab = "";
			$this->tabs['details'] = HTMLHelper::_('tabs.panel',JText::_('Details'), 'details');
			$this->tabs['siteconfig'] = HTMLHelper::_('tabs.panel',JText::_('Main Config'), 'advanced-config');
			$this->tabs['menufilter'] = HTMLHelper::_('tabs.panel',JText::_('Menu_Filter'), 'advanced-menus');
			$this->tabs['accesslevels'] = HTMLHelper::_('tabs.panel',JText::_('Access_Level_Inheritance'), 'advanced-accesslevel');
			$this->tabs['components'] = HTMLHelper::_('tabs.panel',JText::_( 'COMPONENTS_FILTER' ), 'components');
			$this->tabs['translation'] = HTMLHelper::_('tabs.panel',JText::_('Translation'), 'advanced-translation');
	
		} else {
			$this->tabsstart = HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details'));
			$this->tabsend = HTMLHelper::_('uitab.endTabSet');
			$this->endtab = HTMLHelper::_('uitab.endTab');
			$this->tabs['details'] = HTMLHelper::_('uitab.addTab', 'myTab', 'details', JText::_('Details'));
			$this->tabs['siteconfig'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-config', JText::_('Site_Config'));
			$this->tabs['menufilter'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-menus', JText::_('Menu_Filter'));
			$this->tabs['accesslevels'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-accesslevel', JText::_('Access_Level_Inheritance'));
			$this->tabs['components'] = HTMLHelper::_('uitab.addTab', 'myTab', 'components', JText::_( 'COMPONENTS_FILTER' ));
			$this->tabs['translation'] = HTMLHelper::_('uitab.addTab', 'myTab', 'advanced-translation', JText::_('Translation'));	
		}
		 
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
?>