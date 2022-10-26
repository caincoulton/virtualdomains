<?php
/**
 * @version		$Id: default.php 147 2013-10-06 08:58:34Z michel $
 * @copyright	Copyright (C) 2014, Michael Liebler. All rights reserved.
 * @license #http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$app = Factory::getApplication();

HTMLHelper::_('behavior.multiselect');

$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$params		= (isset($this->state->params)) ? $this->state->params : new JObject;
$saveOrder	= $listOrder == 'ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_virtualdomains&task=params.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>

<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="index.php?option=com_virtualdomains&view=param"
	method="post" name="adminForm" id="adminForm">

	<div class="row">
		<div class="col-md-12">

			<div id="j-main-container" class="j-main-container">

				<?php
					// Search tools bar
					echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>

				<?php if (empty($this->items)) : ?> 
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<div id="editcell">
						<table class="adminlist table table-striped" id="articleList">
							<thead>
								<tr>
									<th width="20"><input type="checkbox" name="checkall-toggle"
										value="" title="(<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
										onclick="Joomla.checkAll(this)" />
									</th>
									<th class="title"><?php echo HTMLHelper::_('grid.sort', 'Name', 'a.name', $listDirn, $listOrder ); ?>
									</th>
									<th class="title"><?php echo HTMLHelper::_('grid.sort', 'Id', 'a.id', $listDirn, $listOrder ); ?>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="4"><?php echo $this->pagination->getListFooter(); ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
							<?php
								if (count($this->items)) :
								foreach ($this->items as $i => $item) :
														
									$canCreate  = $user->authorise('core.create');
									$canEdit    = $user->authorise('core.edit');
									$canChange  = $user->authorise('core.edit.state');
									
									$disableClassName = '';
									$disabledLabel	  = '';
									if (!$saveOrder) {
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									}

						
									$link = JRoute::_( 'index.php?option=com_virtualdomains&view=param&task=param.edit&id='. $item->id );
									$checked = HTMLHelper::_('grid.id', $i, $item->id);

							?>
								<tr class="row<?php echo $i % 2; ?>"">

									<td><?php echo $checked;  ?></td>

									<td class="nowrap has-context">
										<div class="pull-left">
											<?php if ($canEdit) : ?>
											<a href="<?php  echo $link; ?>"> <?php  echo $this->escape($item->name); ?>
											</a>
											<?php  else : ?>
											<?php  echo $this->escape($item->name); ?>
											<?php  endif; ?>

										</div>
										<div class="pull-left">
											<?php
											// Create dropdown items
											HTMLHelper::_('dropdown.edit', $item->id, 'param.');

											// render dropdown list
											echo HTMLHelper::_('dropdown.render');
											?>
										</div>
									</td>

									<td><?php echo $item->id; ?></td>
								</tr>
								<?php

								endforeach;
								else:
								?>
								<tr>
									<td colspan="12"><?php echo Text::_( 'There are no items present' ); ?>
									</td>
								</tr>
								<?php
								endif;
								?>
							</tbody>
						</table>
					</div>
					
				<?php endif; ?>
				
				<input type="hidden" name="option" value="com_virtualdomains" />
				<input type="hidden" name="task" value="param" /> 
				<input type="hidden" name="view" value="params" /> 
				<input type="hidden" name="boxchecked" value="0" /> 
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" /> 
				<input type="hidden" name="filter_order_Dir" value="" />
				<?php echo HTMLHelper::_( 'form.token' ); ?>

			</div>
		</div>
	</div>
</form>
