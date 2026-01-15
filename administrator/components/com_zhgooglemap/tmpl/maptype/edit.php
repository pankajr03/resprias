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


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPTYPE_DETAIL')); ?>
    <div class="tab-pane active" id="tab1">
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('details') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'projectionglobal', Text::_('COM_ZHGOOGLEMAP_MAPTYPE_DETAIL_PROJECTION_GLOBAL_LABEL')); ?>
    <div class="tab-pane" id="tab2">

        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('projectionglobal') as $field): ?>
                <div class="col-md-12">
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'projectiondefinition', Text::_('COM_ZHGOOGLEMAP_MAPTYPE_DETAIL_PROJECTION_DEFINITION_LABEL')); ?>
    <div class="tab-pane" id="tab3">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('projectiondefinition') as $field): ?>
                <div class="col-md-12">
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'projectionllp', Text::_('COM_ZHGOOGLEMAP_MAPTYPE_DETAIL_PROJECTION_LLP_LABEL')); ?>
    <div class="tab-pane" id="tab4">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('projectionllp') as $field): ?>
                <div class="col-md-12">
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'projectionpll', Text::_('COM_ZHGOOGLEMAP_MAPTYPE_DETAIL_PROJECTION_PLL_LABEL')); ?>
    <div class="tab-pane" id="tab5">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('projectionpll') as $field): ?>
                <div class="col-md-12">
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
    <input type="hidden" name="task" value="maptype.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>

</form>


