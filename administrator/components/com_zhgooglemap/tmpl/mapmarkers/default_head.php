<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

?>
<tr class="zhgm-form-header">
    <td class="w-1 text-center">
        <?php echo HTMLHelper::_('grid.checkall'); ?>
    </td>
    <th scope="col" class="w-1 text-center d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', '', 'h.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
    </th>
    <th scope="col" class="w-50 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPMARKER_HEADING_TITLE', 'h.title', $listDirn, $listOrder); ?>
    </th>
    <th scope="col" class="w-1 text-center d-none d-md-table-cell">
        <?php echo Text::_('COM_ZHGOOGLEMAP_MAPMARKER_HEADING_ICONTYPE'); ?>
    </th>
    <th scope="col" class="w-1 text-center d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPMARKER_HEADING_PUBLISHED', 'h.published', $listDirn, $listOrder); ?>
    </th>
    <th scope="col" class="w-5 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPMARKER_HEADING_RATING', 'h.rating_value', $listDirn, $listOrder); ?>
    </th>
    <th scope="col" class="w-5 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'h.access', $listDirn, $listOrder); ?>
    </th>
    <th  scope="col" class="w-1 d-none d-md-table-cell zhgm-form-title-wrap">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPMARKER_HEADING_USERORDER', 'h.userorder', $listDirn, $listOrder); ?>
    </th>    
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPMARKER_HEADING_ID', 'h.id', $listDirn, $listOrder); ?>
    </th>           
</tr>


