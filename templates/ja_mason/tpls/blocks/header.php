<?php
/**
 * ------------------------------------------------------------------------
 * JA Mason Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

// get params
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

$menuVertical = $this->params->get('nav-type');
$logoVertical = $this->params->get('logo-vertical');

$headright = (($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) || $this->getParam('addon_offcanvas_enable') || $this->countModules('head-search') || $this->countModules('languageswitcherload'));
?>

<header id="t3-header" class="t3-header">
	<div class="container container-hd">
		<!-- LOGO -->
		<div class="logo logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
			<a href="<?php echo JUri::base() ?>" title="<?php echo strip_tags($sitename) ?>">
				<?php if($logotype == 'image'): ?>
					<img class="logo-img" src="<?php echo JUri::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
				<?php endif ?>

				<?php if(($logotype == 'image') && $logoVertical): ?>
					<img class="logo-img hide logo-vertical" src="<?php echo JUri::base(true) . '/' . $logoVertical ?>" alt="<?php echo strip_tags($sitename) ?>" />
				<?php endif ?>

				<?php if($logoimgsm) : ?>
					<img class="logo-img-sm" src="<?php echo JUri::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
				<?php endif ?>
				<span><?php echo $sitename ?></span>
			</a>
			<small class="site-slogan"><?php echo $slogan ?></small>
		</div>
		<!-- // LOGO -->

		<!-- MAIN NAVIGATION -->
		<nav id="t3-mainnav" class="wrap navbar navbar-default t3-mainnav">
				<?php if ($this->getParam('navigation_collapse_enable')) : ?>
					<div class="t3-navbar-collapse navbar-collapse collapse"></div>
				<?php endif ?>

				<div class="t3-navbar navbar-collapse collapse">
					<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
				</div>
		</nav>
		<!-- //MAIN NAVIGATION -->

		<?php if($headright) :?>
		<!-- HEADRIGHT -->
		<div class="headright">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<?php if ($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) : ?>
					<?php $this->addScript(T3_URL.'/js/nav-collapse.js'); ?>
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
						<span class="fa fa-bars"></span>
					</button>
				<?php endif ?>

				<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
					<?php $this->loadBlock ('off-canvas') ?>
				<?php endif ?>
			</div>

			<?php if ($this->countModules('languageswitcherload')) : ?>
				<!-- LANGUAGE SWITCHER -->
				<div class="languageswitcherload">
					<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
				</div>
				<!-- //LANGUAGE SWITCHER -->
			<?php endif ?>

			<?php if ($this->countModules('head-search')) : ?>
				<!-- HEAD SEARCH -->
				<div class="head-search <?php $this->_c('head-search') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('head-search') ?>" style="raw" />
				</div>
				<!-- //HEAD SEARCH -->
			<?php endif ?>
		</div>
		<!-- // HEADRIGHT -->
		<?php endif ?>
	</div>

	<?php if ($this->countModules('bottom-vertical') && $menuVertical) : ?>
		<!-- BOTTOM VERTICAL -->
		<div class="bottom-vertical hide <?php $this->_c('bottom-vertical') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('bottom-vertical') ?>" style="raw" />
		</div>
		<!-- //BOTTOM VERTICAL -->
	<?php endif ?>
</header>
<!-- //HEADER -->

<?php if($headright && $menuVertical) :?>
<!-- HEADRIGHT -->
<div class="headright on-vertical hide">
	<!-- Brand and toggle get grouped for better mobile display -->
	<div class="navbar-header">
		<?php if ($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) : ?>
			<?php $this->addScript(T3_URL.'/js/nav-collapse.js'); ?>
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
				<span class="fa fa-bars"></span>
			</button>
		<?php endif ?>

		<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
			<?php $this->loadBlock ('off-canvas') ?>
		<?php endif ?>
	</div>

	<?php if ($this->countModules('languageswitcherload')) : ?>
		<!-- LANGUAGE SWITCHER -->
		<div class="languageswitcherload">
			<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
		</div>
		<!-- //LANGUAGE SWITCHER -->
	<?php endif ?>

	<?php if ($this->countModules('head-search')) : ?>
		<!-- HEAD SEARCH -->
		<div class="head-search <?php $this->_c('head-search') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('head-search') ?>" style="raw" />
		</div>
		<!-- //HEAD SEARCH -->
	<?php endif ?>
</div>
<!-- // HEADRIGHT -->
<?php endif ?>

