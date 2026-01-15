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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$document    = Factory::getDocument();

$wa  = $document->getWebAssetManager();


if (isset($this->loadjquery))
{
	if ((int)$this->loadjquery == 1) {
		$wa->useScript('jquery');
	}
}

$user        = Factory::getUser();
$userId        = $user->get('id');
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
$canOrder    = $user->authorise('core.edit.state', 'com_zhgooglemap.category');
$saveOrder    = $listOrder == 'ordering';

HTMLHelper::_('behavior.multiselect');

?>

<form action="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mapbufmrks'); ?>" method="post" name="adminForm" id="adminForm">

<div class="row zhgm-form-container">
    <div class="col-md-12">
        <div id="j-main-container" class="j-main-container">
            <?php
            // Search tools bar
            echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
            ?>
            <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
            <?php else : ?>

			<table class="table table-striped" id="mapbufmrkList">
				<thead><?php echo $this->loadTemplate('head');?></thead>
				<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
				<tbody><?php echo $this->loadTemplate('body');?></tbody>
			</table>
			<?php endif; ?>
				
        <?php // Load the import form ?>
        <?php  
                    echo HTMLHelper::_(
            'bootstrap.renderModal',
            'uploadCSVModal',
            array(
                'title'       => Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_CSV'),
                'url'         => Route::_('index.php?option=com_zhgooglemap&amp;view=uploadcsv&amp;tmpl=component&amp;'. JSession::getFormToken().'=1'),
                'height'      => '400px',
                'width'       => '300px',
                                'backdrop'    => 'static',
                                'closeButton' => false,
                                'modalWidth'  => '40',
                'footer'      => '<a class="btn" data-bs-dismiss="modal" type="button"'
                                                .' id="load_csv_file_cancel"'
                        . ' onclick="jQuery(\'#uploadCSVModal iframe\').contents().find(\'#closeBtn\').click();jQuery(\'#load_csv_file_do\').show();jQuery(\'#load_csv_file_cancel\').html(\''.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CANCEL').'\');">'
                        . Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CANCEL') . '</a>'
                        . '<button class="btn btn-success" type="button"'
                                                .' id="load_csv_file_do"'
                        . ' onclick="jQuery(\'#uploadCSVModal iframe\').contents().find(\'#uploadBtn\').click();jQuery(\'#load_csv_file_do\').hide();jQuery(\'#load_csv_file_cancel\').html(\''.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CLOSE').'\');">'
                        . Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD') . '</button>',
            )
                    ); 

                    echo HTMLHelper::_(
            'bootstrap.renderModal',
            'uploadPlacemarkModal',
            array(
                'title'       => Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_UPLOAD_PLACEMARK'),
                'url'         => Route::_('index.php?option=com_zhgooglemap&amp;view=uploadplacemark&amp;tmpl=component&amp;'. JSession::getFormToken().'=1'),
                'height'      => '200px',
                'width'       => '300px',
                                'backdrop'    => 'static',
                                'closeButton' => false,
                                'modalWidth'  => '40',
                'footer'      => '<a class="btn" data-bs-dismiss="modal" type="button"'
                                                .' id="load_import_placemark_cancel"'
                        . ' onclick="jQuery(\'#uploadPlacemarkModal iframe\').contents().find(\'#closeBtn\').click();jQuery(\'#load_import_placemark_do\').show();jQuery(\'#load_import_placemark_cancel\').html(\''.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CANCEL').'\');">'
                        . Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CANCEL') . '</a>'
                        . '<button class="btn btn-success" type="button"'
                                                .' id="load_import_placemark_do"'
                        . ' onclick="jQuery(\'#uploadPlacemarkModal iframe\').contents().find(\'#importBtn\').click();jQuery(\'#load_import_placemark_do\').hide();jQuery(\'#load_import_placemark_cancel\').html(\''.Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_CLOSE').'\');">'
                        . Text::_('COM_ZHGOOGLEMAP_MAPBUFMRKS_IMPORT') . '</button>',
            )
                    ); 

                    
                ?>

        
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
        </div>
    </div>
</div>
</form>
