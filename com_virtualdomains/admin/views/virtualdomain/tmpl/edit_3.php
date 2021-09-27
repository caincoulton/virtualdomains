<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
 * @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Set toolbar items for the page
$edit		= Factory::getApplication()->input->get('edit', true);
$text = !$edit ? JText::_( 'New' ) : JText::_( 'Edit' );
JToolBarHelper::title(   JText::_( 'Virtualdomain' ).': <small><small>[ ' . $text.' ]</small></small>' );
JToolBarHelper::apply('virtualdomain.apply');
JToolBarHelper::save('virtualdomain.save');
JToolbarHelper::save2new('virtualdomain.save2new');
JToolbarHelper::save2copy('virtualdomain.save2copy');
if (!$edit) {
	JToolBarHelper::cancel('virtualdomain.cancel');
} else {
	// for existing items the button is renamed `close`
	JToolBarHelper::cancel( 'virtualdomain.cancel');
}
?>

<script language="javascript" type="text/javascript">


Joomla.submitbutton = function(task)
{
	if (task == 'virtualdomain.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
}

</script>

	 	<form method="post" action="<?php echo JRoute::_('index.php?option=com_virtualdomains&layout=edit&id='.(int) $this->item->id);  ?>" id="adminForm" name="adminForm">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12 form-horizontal">
		 <?php echo $this->tabsstart; ?>
			<?php echo $this->tabs['details']; ?>

                <?php echo $this->form->renderFieldset('details'); ?>
				
            <?php echo $this->endtab ?>
            <?php echo $this->tabs['siteconfig']; ?>
                <?php echo $this->form->renderFieldset('siteconfig'); ?>
            <?php echo $this->endtab ?>				
            
            <?php echo $this->tabs['menufilter'] ?>					
                <?php echo $this->form->renderFieldset('menus'); ?>
            <?php echo $this->endtab ?>
            
            <?php echo $this->tabs['accesslevels'] ?>
                <?php echo $this->form->renderFieldset('accesslevels'); ?>
            <?php echo $this->endtab ?>
            
            <?php echo $this->tabs['components'] ?>
                <?php echo $this->form->renderFieldset('components'); ?>
            <?php echo $this->endtab ?>
            
            <?php echo $this->tabs['translation'] ?>
                <?php echo $this->form->renderFieldset('translation'); ?>         		
            <?php echo $this->endtab ?>
            
            <?php echo $this->tabsend; ?>
        </div>
	</div>	 	              
		<input type="hidden" name="option" value="com_virtualdomains" />
	    <input type="hidden" name="cid[]" value="<?php echo $this->item->id ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo $this->form->getInput( 'viewlevel' ); ?>
		<input type="hidden" name="view" value="virtualdomain" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>