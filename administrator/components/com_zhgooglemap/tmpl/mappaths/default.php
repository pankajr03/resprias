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

$wa  = $this->document->getWebAssetManager();

$wa->registerAndUseStyle('zhgooglemaps.form.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/utils_lists.css');

HTMLHelper::_('behavior.multiselect');

$user       = Factory::getUser();
$userId     = $user->get('id');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'h.ordering';

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_zhgooglemap&task=mappaths.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>

<form action="<?php echo Route::_('index.php?option=com_zhgooglemap&view=mappaths'); ?>" method="post" name="adminForm" id="adminForm">
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
            <table id="tableList"  class="table table-striped">
                <thead><?php echo $this->loadTemplate('head');?></thead>
                <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                <?php echo $this->loadTemplate('body');?>
                </tbody>
                <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
            </table>
            <?php endif; ?>
            <div>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</div>
</form>
