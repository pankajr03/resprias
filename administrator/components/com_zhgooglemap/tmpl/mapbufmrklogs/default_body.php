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
use Joomla\CMS\Uri\Uri;

?>
<?php 

    $listOrder    = $this->escape($this->state->get('list.ordering'));
    $listDirn    = $this->escape($this->state->get('list.direction'));

    foreach($this->items as $i => $item): 
    $ordering  = ($listOrder == 'ordering');
    

    $saveOrder    = $listOrder == 'ordering';
    
    
?>
    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
        <td>
            <?php echo $item->id; ?>
        </td>
        <td>
            <?php echo $this->escape($item->kind); ?>
        </td>
        <td>
            <?php echo $this->escape($item->title); ?>
        </td>
        <td>
            <?php echo $this->escape($item->id_target); ?>
        </td>    
        <td>
            <?php echo $this->escape($item->id_source); ?>
        </td>    
        <td>
            <?php echo $this->escape($item->id_find); ?>
        </td>        
    </tr>
<?php endforeach; ?>

