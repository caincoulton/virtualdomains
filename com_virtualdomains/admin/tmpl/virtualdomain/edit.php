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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

// Set toolbar items for the page
$edit		= Factory::getApplication()->input->get('edit', true);
$text = !$edit ? Text::_( 'New' ) : Text::_( 'Edit' );
ToolBarHelper::title(   Text::_( 'Virtualdomain' ).': <small><small>[ ' . $text.' ]</small></small>' );
ToolBarHelper::apply('virtualdomain.apply');
ToolBarHelper::save('virtualdomain.save');
ToolBarHelper::save2new('virtualdomain.save2new');
ToolBarHelper::save2copy('virtualdomain.save2copy');
if (!$edit) {
	ToolBarHelper::cancel('virtualdomain.cancel');
} else {
	// for existing items the button is renamed `close`
	ToolBarHelper::cancel( 'virtualdomain.cancel');
}
?>

<form action="<?php echo Route::_('index.php?option=com_virtualdomains&layout=edit&id='.(int) $this->item->id);  ?>" method="post" id="adminForm" name="adminForm" aria-label="<?php echo Text::_('COM_VIRTUALDOMAINS_VIRTUALDOMAIN_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('Details')); ?>
				<?php echo $this->form->renderFieldset('details'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'siteconfig', Text::_( 'Site_Config' )); ?>
				<?php echo $this->form->renderFieldset('siteconfig'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>		
			
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'menufilter', Text::_('Menu_Filter')); ?>
				<?php echo $this->form->renderFieldset('menus'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'accesslevels', Text::_('Access_Level_Inheritance')); ?>
				<?php echo $this->form->renderFieldset('accesslevels'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'components', Text::_('COMPONENTS_FILTER')); ?>
				<?php echo $this->form->renderFieldset('components'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'translation', Text::_('Translation')); ?>
				<?php echo $this->form->renderFieldset('translation'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	</div>	 	              
	<input type="hidden" name="option" value="com_virtualdomains" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput( 'viewlevel' ); ?>
	<input type="hidden" name="view" value="VirtualDomain" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>