<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$user       = JFactory::getUser();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;

$filter_project = (int) $this->state->get('filter.project');
$filter_topic   = (int) $this->state->get('filter.topic');

$date_format = JText::_('DATE_FORMAT_LC4');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

if (!$this->is_j25) :
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
    ?>
    <script type="text/javascript">
    Joomla.orderTable = function()
    {
        table     = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order     = table.options[table.selectedIndex].value;

        if (order != '<?php echo $list_order; ?>') {
            dirn = 'asc';
        }
        else {
            dirn = direction.options[direction.selectedIndex].value;
        }

        Joomla.tableOrdering(order, dirn, '');
    }
    </script>
    <?php
endif;
?>
<form action="<?php echo JRoute::_('index.php?option=com_pfforum&view=replies&filter_topic=' . $filter_topic); ?>" method="post" name="adminForm" id="adminForm">
    <?php
    if (!$this->is_j25) :
        if (!empty($this->sidebar)) :
            ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
            <div id="j-main-container" class="span10">
        <?php else : ?>
                <div id="j-main-container">
            <?php
        endif;
    endif;

    echo $this->loadTemplate('filter_' . ($this->is_j25 ? 'j25' : 'j30'));
    ?>
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                </th>
                <th width="5%" class="center">
                    <?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $list_dir, $list_order); ?>
                </th>
                <th>
                    <?php echo JText::_('JGLOBAL_DESCRIPTION'); ?>
                </th>
                <th width="15%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
                </th>
                <th width="10%" class="hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap hidden-phone">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $access = PFforumHelper::getReplyActions($item->id);

            $can_create   = $access->get('core.create');
            $can_edit     = $access->get('core.edit');
            $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
            $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
            $can_change   = ($access->get('core.edit.state') && $can_checkin);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center hidden-phone">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'replies.', $can_change, 'cb'); ?>
                </td>
                <td class="has-context">
                    <div class="pull-left">
                        <?php if ($item->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'replies.', $can_checkin); ?>
                        <?php endif; ?>

                        <?php if ($can_edit || $can_edit_own) : ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_pfforum&task=reply.edit&id=' . $item->id);?>">
                                <?php echo $this->escape(JHtml::_('pf.html.truncate', $item->description, 80)); ?>
                            </a>
                        <?php else : ?>
                            <?php echo $this->escape(JHtml::_('pf.html.truncate', $item->description, 80)); ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!$this->is_j25) : ?>
                        <div class="pull-left">
                            <?php
                                // Create dropdown items
                                JHtml::_('dropdown.edit', $item->id, 'reply.');
                                JHtml::_('dropdown.divider');

                                if ($item->state) :
                                    JHtml::_('dropdown.unpublish', 'cb' . $i, 'replies.');
                                else :
                                    JHtml::_('dropdown.publish', 'cb' . $i, 'replies.');
                                endif;

                                JHtml::_('dropdown.divider');

                                if ($archived) :
                                    JHtml::_('dropdown.unarchive', 'cb' . $i, 'replies.');
                                else :
                                    JHtml::_('dropdown.archive', 'cb' . $i, 'replies.');
                                endif;

                                if ($item->checked_out) :
                                    JHtml::_('dropdown.checkin', 'cb' . $i, 'replies.');
                                endif;

                                if ($trashed) :
                                    JHtml::_('dropdown.untrash', 'cb' . $i, 'replies.');
                                else :
                                    JHtml::_('dropdown.trash', 'cb' . $i, 'replies.');
                                endif;

                                // Render dropdown list
                                echo JHtml::_('dropdown.render');
                            ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="nowrap">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="nowrap">
                    <?php echo JHtml::_('date', $item->created, $date_format); ?>
                </td>
                <td class="hidden-phone small">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="center hidden-phone small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($this->is_j25) : ?>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!$this->is_j25) : echo $this->pagination->getListFooter(); endif; ?>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="filter_project" value="<?php echo (int) $this->state->get('filter.project');?>" />
    <input type="hidden" name="filter_topic" value="<?php echo (int) $this->state->get('filter.topic');?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
