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
	$user   = Factory::getUser();
    $userId = $user->id;
    
    $listOrder    = $this->escape($this->state->get('list.ordering'));
    $listDirn    = $this->escape($this->state->get('list.direction'));
    $saveOrder = $listOrder == 'h.title';

    foreach($this->items as $i => $item): 
	
    $canDo = ContentHelper::getActions('com_zhgooglemap');
    
    $canEdit    = $canDo->get('core.edit');
    $canEditOwn = $canDo->get('core.edit.own') && 1==2; //$item->createdbyuser == $userId;
    $canChange  = $canDo->get('core.edit.state');
    
    $imgpath = URI::root() .'components/com_zhgooglemap/assets/icons/';
    $utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';
    
?>
    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
        <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
        </td>
        <td>

            <?php if ($canEdit || $canEditOwn) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_zhgooglemap&task=mapbufmrk.edit&id=' . $item->id); ?>">
                    <?php echo $this->escape($item->title); ?></a>
            <?php else : ?>
                    <?php echo $this->escape($item->title); ?>
            <?php endif; ?>

        </td>
        <td align="center">
            <?php echo '<img src="'.$imgpath.str_replace("#", "%23", $item->icontype).'.png" alt="" />'; ?>
        </td>
        <td align="center">
            <?php 
                echo HTMLHelper::_('jgrid.published', $item->published, $i, 'mapbufmrks.', $canChange, 'cb', $item->publish_up, $item->publish_down); 
                //echo '<img src="'.JURI::root() .'administrator/components/com_zhgooglemap/assets/utils/published'.$item->published.'.png" alt="" />'; 
            ?>            
        </td>                
        <td>
            <?php echo $this->escape($item->markergroupname); ?>
        </td>
        <td>
            <?php 
            if ($item->category != "")
            {
                if ($item->category_language !== '*')
                {
                    echo $this->escape($item->category) . ' (' .$this->escape($item->category_language) . ')';
                }
                else
                {
                    echo $this->escape($item->category); 
                }  
            }
            ?>
        </td>
        <td>
            <?php echo $this->escape($item->fullusername); ?>
        </td>    
                <td>
            <?php 
                            $statusText = "";
                            if ((int)$item->status == 0)
                            {
                                $statusText = Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DETAIL_STATUS_NEW');
                            } 
                            elseif ((int)$item->status == 1)
                            {
                                $statusText = Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DETAIL_STATUS_PROCESSED');
                            } 
                            elseif ((int)$item->status == 8)
                            {
                                $statusText = Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DETAIL_STATUS_SKIPPED');
                            }
                            elseif ((int)$item->status == 9)
                            {
                                $statusText = Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_DETAIL_STATUS_ERROR');
                            }
                            echo $this->escape($statusText); 
                        ?>
        </td>  
        <td>
            <?php echo $item->id; ?>
        </td>		
    </tr>
<?php endforeach; ?>

