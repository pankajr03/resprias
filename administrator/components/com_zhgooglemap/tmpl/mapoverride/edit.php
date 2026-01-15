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

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');
   
?>
<form action="<?php echo Route::_('index.php?option=com_zhgooglemap&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="main-card">

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_DETAIL')); ?>
    <div class="row" id="tab1">
        <fieldset class="adminform">
                <?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    <?php 
                        if ($field->id == 'jform_ordering')
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'placemarkdetail', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_MAP')); ?>
    <div class="row" id="tab7">
            
                
                <fieldset class="options-form">
                    <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_MAP_CONTROLS'); ?></legend>
                </fieldset>
                
                <fieldset class="options-form">
                    <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_MAP_CONTROL_CIRCLE'); ?></legend>
                    <?php foreach($this->form->getFieldset('map_control') as $field): ?>
                    <div class="control-group">
                        <?php 
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
                        ?>
                    </div>
                    <?php endforeach; ?>
                </fieldset>
                            
    </div>  
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'map_control', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_PLACEMARK')); ?>   
    <div class="row" id="tab6">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('placemarkdetail') as $field): ?>
                <div class="control-group">
                    <?php 
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
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>  
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'placemarklist', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_PLACEMARKLIST')); ?>
    <div class="row" id="tab2">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('placemarklist') as $field): ?>
                    <?php 
                        if ($field->id == 'jform_placemark_list_accent'
                         || $field->id == 'jform_placemark_list_mapping'
                         )
                        {
                        ?>
                            <div class="col-md-12">
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
                            </div>
                        <?php 
                        }
                        else
                        {
                        ?>
                            <div class="control-group">
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
                            </div>
                        <?php 
                        }
                        ?>
                        <?php 
                    ?>
                <?php endforeach; ?>
            
        </fieldset>
    </div>    
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'grouplist', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_GROUPLIST')); ?>
    <div class="row" id="tab3">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('grouplist') as $field): ?>
                    <?php 
                        if ($field->id == 'jform_group_list_accent'
                         || $field->id == 'jform_group_list_mapping'
                         )
                        {
                        ?>
                            <div class="col-md-12">
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
                            </div>
                        <?php 
                        }
                        else
                        {
                        ?>
                            <div class="control-group">
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
                            </div>
                        <?php 
                        }
                        ?>
                        <?php 
                    ?>
                <?php endforeach; ?>
                
        </fieldset>
    </div>      
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'panel', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_PANEL')); ?>
    <div class="row" id="tab4">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('panel') as $field): ?>
                <div class="control-group">
                    <?php 
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
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>       
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'integration', Text::_('COM_ZHGOOGLEMAP_MAPOVERRIDE_INTEGRATION')); ?>
    <div class="row" id="tab5">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('integration') as $field): ?>
                <div class="control-group">
                    <?php 
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
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>        
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>



</div>

<div class="row-fluid">
    <input type="hidden" name="task" value="mapoverride.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>


</form>


