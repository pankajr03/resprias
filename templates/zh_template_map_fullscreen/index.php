<?php
/*------------------------------------------------------------------------
# tpl_zh_template_map_fullscreen - Zh Template Map FullScreen
# ------------------------------------------------------------------------
# author    Dmitry Zhuk
# copyright Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# Websites: http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();

$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);

$wa->registerAndUseStyle("zh_template_fullscreen.common", "common.css");

$tplAddJS = $this->params->get( 'js2load' );
$tplListJS = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $tplAddJS));


for($i = 0; $i < count($tplListJS); $i++) 
{
    $currJS = trim($tplListJS[$i]);
    if ($currJS != "")
    {
        $wa->registerAndUseScript("zh_template_fullscreen.js.".$i, $currJS);
    }
}


$tplAddCSS = $this->params->get( 'css2load' );
$tplListCSS = explode(';', str_replace(array("\r", "\r\n", "\n"), ';', $tplAddCSS));


for($i = 0; $i < count($tplListCSS); $i++) 
{
    $currCSS = trim($tplListCSS[$i]);
    if ($currCSS != "")
    {
        $wa->registerAndUseStyle("zh_template_fullscreen.css.".$i, $currCSS);
    }
}
?>	

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>

<body>
    <?php if ($this->countModules('top-a', true)) : ?>
    <div class="grid-child container-top-a">
        <jdoc:include type="modules" name="top-a" style="card" />
    </div>
    <?php endif; ?>

    <?php if ($this->countModules('top-b', true)) : ?>
    <div class="grid-child container-top-b">
        <jdoc:include type="modules" name="top-b" style="card" />
    </div>
    <?php endif; ?>

    <div class="grid-child container-content">
        <jdoc:include type="modules" name="main-top" style="card" />
        <main>
            <jdoc:include type="component" />
        </main>
        <jdoc:include type="modules" name="main-bottom" style="card" />
    </div>

    <?php if ($this->countModules('bottom-a', true)) : ?>
    <div class="grid-child container-bottom-a">
        <jdoc:include type="modules" name="bottom-a" style="card" />
    </div>
    <?php endif; ?>

    <?php if ($this->countModules('bottom-b', true)) : ?>
    <div class="grid-child container-bottom-b">
        <jdoc:include type="modules" name="bottom-b" style="card" />
    </div>
		<?php endif; ?>
        
	<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>