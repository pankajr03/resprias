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
<tr>
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_ID', 'h.id', $listDirn, $listOrder); ?>
    </th>        
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_OBJECT', 'h.kind', $listDirn, $listOrder); ?>
    </th>
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_ERROR', 'h.title', $listDirn, $listOrder); ?>
    </th>
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_TARGET_ID'); ?>
    </th>
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_SOURCE_ID'); ?>
    </th>    
    <th scope="col" class="w-1 d-none d-md-table-cell">
        <?php echo Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_LOG_IMPORT_SOURCE_OBJECT_ID'); ?>
    </th>       
</tr>


