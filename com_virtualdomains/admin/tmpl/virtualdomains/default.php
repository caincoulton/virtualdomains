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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

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
	$saveOrderingUrl = 'index.php?option=com_virtualdomains&task=virtualdomains.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<style>.romacron {height:100%; width:100%;left:10%!important; top:10%!important;}</style>
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

<form action="<?php echo Route::_('index.php?option=com_virtualdomains&view=VirtualDomains');?>"
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
                                    <th width="1%" class="nowrap center hidden-phone"><?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                                    </th>

                                    <th width="20"><input type="checkbox" name="checkall-toggle" class="form-check-input"
                                        value="" title="(<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
                                        onclick="Joomla.checkAll(this)" />
                                    </th>
                                    <th class="title"><?php echo HTMLHelper::_('grid.sort', 'Domain', 'a.domain', $listDirn, $listOrder ); ?>
                                    </th>
                                    <th class="title"><?php echo HTMLHelper::_('grid.sort', 'Template', 'a.template', $listDirn, $listOrder ); ?>
                                    </th>
                                    <th class="title"><?php echo Text::_('HOST_CHECK');?></th>
                                    <th class="title"><?php echo HTMLHelper::_('grid.sort', 'Default_Domain', 'a.home', $listDirn, $listOrder ); ?>
                                    </th>
                                    <th class="title"><?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder ); ?>
                                    </th>
                                    <th width="13%" class="title"><?php echo Text::_('Preview');?> </th>
                                    <th class="title"><?php echo HTMLHelper::_('grid.sort', 'Id', 'a.id', $listDirn, $listOrder ); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="12"><?php echo $this->pagination->getListFooter(); ?>
                                    </td>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                if (count($this->items)) :
                                foreach ($this->items as $i => $item) :
                                $ordering  = ($listOrder == 'ordering');
                                $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                                $canCreate  = $user->authorise('core.create');
                                $canEdit    = $user->authorise('core.edit');
                                $canChange  = $user->authorise('core.edit.state');

                                $disableClassName = '';
                                $disabledLabel	  = '';
                                if (!$saveOrder) {
                            $disabledLabel    = Text::_('JORDERINGDISABLED');
                            $disableClassName = 'inactive tip-top';
                        }

                        $onclick = "";

                        if ($app->input->get('function', null)) {
                            $onclick= "onclick=\"window.parent.jSelectVirtualdomain_id('".$item->id."', '".$this->escape($item->domain)."', '','id')\" ";
                        }

                        $link = Route::_( 'index.php?option=com_virtualdomains&view=VirtualDomain&task=virtualdomain.edit&id='. $item->id );
                        $checked = HTMLHelper::_('grid.checkedout', $item, $i );
                        $preViewModalHandlerLink= "http://". $this->escape( $item->domain );


                        ?>
                                <tr class="row<?php echo $i % 2; ?>"">
                                    <td class="order nowrap center hidden-phone"><?php if ($canChange) : ?>
                                        <span
                                        class="sortable-handler hasTooltip <?php echo $disableClassName; ?>"
                                        title="<?php echo $disabledLabel; ?>"> <i class="icon-menu"></i>
                                    </span> <input type="text" style="display: none" name="order[]"
                                        size="5" value="<?php echo $item->ordering;?>"
                                        class="width-20 text-area-order " /> <?php else : ?> <span
                                        class="sortable-handler inactive"> <i class="icon-menu"></i>
                                    </span> <?php endif; ?>
                                    </td>

                                    <td><?php echo $checked;  ?></td>

                                    <td class="nowrap has-context">
                                        <div class="pull-left">

                                            <?php if ($canEdit) : ?>
                                            <a href="<?php  echo $link; ?>"> <?php  echo $this->escape($item->domain); ?>
                                            </a>
                                            <?php  else : ?>
                                            <?php  echo $this->escape($item->domain); ?>
                                            <?php  endif; ?>

                                        </div>
                                        <div class="pull-left">
                                            <?php
                                            // Create dropdown items
                                            HTMLHelper::_('dropdown.edit', $item->id, 'virtualdomain.');
                                            HTMLHelper::_('dropdown.divider');
                                            if ($item->published) :
                                            HTMLHelper::_('dropdown.unpublish', 'cb' . $i, 'virtualdomains.');
                                            else :
                                            HTMLHelper::_('dropdown.publish', 'cb' . $i, 'virtualdomains.');
                                            endif;
                                            HTMLHelper::_('dropdown.divider');
                                            HTMLHelper::_('dropdown.trash', 'cb' . $i, 'virtualdomains.');									
                                            HTMLHelper::_('dropdown.divider');
                                            // render dropdown list
                                            echo HTMLHelper::_('dropdown.render');
                                            ?>
                                        </div>
                                    </td>
                                    <td><?php echo $item->template; ?></td>
                                    <td ><span data-host="<?php echo $item->domain; ?>" class="hostcheck"></span></td>														
                                    <td><?php echo HTMLHelper::_('jgrid.isdefault', $item->home != '0' , $i, 'virtualdomains.', $item->home!='1');?></td>
                                    <td><?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'virtualdomains.', $canChange, 'cb'); ?>
                                    </td>
                                <td style="text-align:center"><a class="modal" title="<?php Text::_('TEST OUT DOMAIN')?>"  href="<?php echo $preViewModalHandlerLink;?>" rel="{classWindow:'testingFrame',handler: 'iframe', size:{x: <?php echo $this->params->get('framewidth',400) ?>, y:<?php echo $this->params->get('frameheight',400) ?>}}"><?php echo Text::_('Preview')?></a></td>
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
                <input type="hidden" name="task" value="virtualdomain" /> 
                <input type="hidden" name="view" value="virtualdomains" /> 
                <input type="hidden" name="boxchecked" value="0" /> 
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
                <input type="hidden" name="filter_order_Dir" value="" />
                <?php echo HTMLHelper::_( 'form.token' ); ?>
            </div>

        </div>
	</div>
</form>
