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
// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');

  
    $imgpath = URI::root() .'components/com_zhgooglemap/assets/icons/';
    $utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';     
?>
<form action="<?php echo Route::_('index.php?option=com_zhgooglemap&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="main-card">

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPMARKERGROUP_DETAIL')); ?>
    <div class="row" id="tab1">
        <fieldset class="adminform">
                <?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    <?php 
                        if ($field->id == 'jform_icontype')
                        {
                            ?>
                            <div class="control-label">
                            <?php 
                                echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 

                                $iconTypeJS = " onchange=\"javascript:
                                if (document.forms.adminForm.jform_icontype.options[selectedIndex].value!='') 
                                {document.image.src='".$imgpath."' + document.forms.adminForm.jform_icontype.options[selectedIndex].value.replace(/#/g,'%23') + '.png'}
                                else 
                                {document.image.src=''}\"";


                                $scriptPosition = ' name=';

                                echo str_replace($scriptPosition, $iconTypeJS.$scriptPosition, $field->input);
                                echo '<img name="image" src="'.$imgpath .str_replace("#", "%23", $this->item->icontype).'.png" alt="" />';
                                
                                echo '<div class="clr"></div>';
                                echo '<a class="btn btn-primary" href="http://wiki.zhuk.cc/index.php?title=Zh_GoogleMap_Credits_Icons" target="_blank">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).' <img src="'.$utilspath.'info.png" alt="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).'" style="margin: 0;" /></a>';
                                echo '<div class="clr"></div>';
                                echo '<br />';
                            ?>
                            </div>
                            <?php 
                        }
                        else if ($field->id == 'jform_ordering')
                        {
                        }
                        else
                        {
                            ?>
                            <div class="control-label">
                            <?php 
                                echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 
                                echo $field->input;
                            ?>
                            </div>
                            <?php 
                        }
                        ?>
                </div>
                 <?php endforeach; ?>

            
        </fieldset>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>


</div>


<div class="row-fluid">
    <input type="hidden" name="task" value="mapmarkergroup.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>


</form>


