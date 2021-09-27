<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
 * @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

if(version_compare(JVERSION,'4','<')){
	HTMLHelper::_('behavior.core');
	HTMLHelper::_('behavior.tooltip');
	HTMLHelper::_('behavior.formvalidation');
	$tabFramework = 'bootstrap';
} else {
	/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
	$wa = $this->document->getWebAssetManager();
	$wa->useScript('keepalive')
		->useScript('form.validate');
	$tabFramework = 'uitab';
}


// Set toolbar items for the page
$edit		= Factory::getApplication()->input->get('edit', true);
$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
JToolBarHelper::title(   JText::_( 'Params' ).': <small><small>[ ' . $text.' ]</small></small>' );
JToolBarHelper::apply('param.apply');
JToolBarHelper::save('param.save');
JToolbarHelper::save2new('param.save2new');
JToolbarHelper::save2copy('param.save2copy');
if (!$edit) {
	JToolBarHelper::cancel('param.cancel');
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel( 'param.cancel');
}
?>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_virtualdomains&layout=edit&id='.(int) $this->item->id);  ?>" id="adminForm" name="adminForm" aria-label="<?php echo Text::_('COM_VIRUTALDOMAINS_PARAM_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<?php //echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_($tabFramework . '.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_($tabFramework . '.addTab', 'myTab', 'details', Text::_('Details')); ?>
			<?php echo $this->form->renderFieldset('details'); ?>
		<?php echo HTMLHelper::_($tabFramework . '.endTab'); ?>

	<?php echo HTMLHelper::_($tabFramework . '.endTabSet'); ?>	        

	</div>                   
	<input type="hidden" name="option" value="com_virtualdomains" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="param" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>