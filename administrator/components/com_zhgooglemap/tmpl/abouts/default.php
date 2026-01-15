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

$wa->registerAndUseStyle('zhgooglemaps.form.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/utils_about.css');

?>

<table class="zhgm-about-table">

<tr>
    <td>
        <?php echo Text::_('COM_ZHGOOGLEMAP_ABOUT_AUTHOR'); ?>
    </td>
    <td>Dmitry Zhuk</td>            
</tr>
<tr>
    <td>
        <?php echo Text::_('COM_ZHGOOGLEMAP_ABOUT_SITE'); ?>
    </td>
    <td><a href="http://zhuk.cc" target="_blank">zhuk.cc</a></td>            
</tr>
<tr>
    <td>
        <?php echo Text::_('COM_ZHGOOGLEMAP_ABOUT_LICENSE'); ?>
    </td>
    <td><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU/GPLv2 or later</td>            
</tr>
</table>
<br />
<br />
<h2>
<?php echo Text::_('COM_ZHGOOGLEMAP_THANKS'); ?>
</h2>
<table class="zhgm-about-table">
    <thead><?php echo $this->loadTemplate('head'); ?></thead>
    <tbody><?php echo $this->loadTemplate('body'); ?></tbody>
    <tfoot><?php echo $this->loadTemplate('foot'); ?></tfoot>
</table>
</div>