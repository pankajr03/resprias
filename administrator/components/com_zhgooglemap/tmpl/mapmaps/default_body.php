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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

?>
<?php 
    $user   = Factory::getUser();
    $userId = $user->id;
    
    $listOrder    = $this->escape($this->state->get('list.ordering'));
    $listDirn    = $this->escape($this->state->get('list.direction'));
    $saveOrder = $listOrder == 'h.ordering';

    foreach($this->items as $i => $item): 

    $canDo = ContentHelper::getActions('com_zhgooglemap');
    
    $canEdit    = $canDo->get('core.edit');
    $canEditOwn = $canDo->get('core.edit.own') && 1==2; //$item->createdbyuser == $userId;
    $canChange  = $canDo->get('core.edit.state');
    
?>
    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->catid?>">
        <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
        </td>
        <td>     
            <div>
            <?php if ($canEdit || $canEditOwn) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&task=mapmap.edit&id=' . $item->id); ?>">
                    <?php echo $this->escape($item->title); ?></a>
            <?php else : ?>
                    <?php echo $this->escape($item->title); ?>
            <?php endif; ?>
            </div>
            <?php 
            if ($item->category != "")
            {
            ?>
            <div>
            <span class="zhgm-form-item-tv-label">
                <?php echo HTMLHelper::_('searchtools.sort', 'COM_ZHGOOGLEMAP_MAP_HEADING_CATEGORY', 'category_title', $listDirn, $listOrder) . ": ";?>
            </span>
            <span class="zhgm-form-item-tv-value">
            <?php 
                if ($item->category_language !== '*')
                {
                    echo $this->escape($item->category) . ' (' .$this->escape($item->category_language) . ')';
                }
                else
                {
                    echo $this->escape($item->category); 
                }  
            ?>
            </span>
            </div>
            <?php
            }
            ?>
        </td>
        <td>
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>

