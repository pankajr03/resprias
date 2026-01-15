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
	$count = $helper->getRows('title');
	$column = 12/($helper->get('columns'));
	$titleAlign = '';

	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

	if($helper->get('title-align')) {
		$titleAlign = 'col-md-offset-6 col-md-4';
	}
?>

<div class="acm-features <?php echo $helper->get('features-style'); ?> style-2">
	<?php if($module->showtitle || $moduleSub) : ?>
	<div class="section-title <?php echo $titleAlign ;?>">
	<!-- Module Title -->
		<?php if ($moduleSub): ?>
			<div class="sub-heading fdfdsfdsfds">
				<span><?php echo $moduleSub; ?></span>		
			</div>
		<?php endif; ?>

		<?php if($module->showtitle) : ?>
		<h2><?php echo $moduleTitle ?></h2>
		<?php endif; ?>
	<!-- // Module Title -->
	</div>
	<?php endif ; ?>
	<div class="row equal-height">
		<?php for ($i=0; $i<$count; $i++) : ?>
			<div class="features-item ja-animate col col-sm-6 col-md-<?php echo $column ?>" data-animation="move-from-left" data-delay="item-<?php echo $i ;?>">
				
				<?php if($helper->get('font-icon', $i)) : ?>
					<div class="font-icon">
						<span class="<?php echo $helper->get('font-icon', $i) ; ?>"></span>
					</div>
				<?php endif ; ?>

				<?php if($helper->get('img-icon', $i)) : ?>
					<div class="img-icon">
						<img src="<?php echo $helper->get('img-icon', $i) ?>" alt="<?php echo $helper->get('title', $i) ?>" />
					</div>
				<?php endif ; ?>
				
				<?php if($helper->get('title', $i)) : ?>
					<h4><?php echo $helper->get('title', $i) ?></h4>
				<?php endif ; ?>
				
				<?php if($helper->get('description', $i)) : ?>
					<p><?php echo $helper->get('description', $i) ?></p>
				<?php endif ; ?>

				<?php if($helper->get('link', $i)) : ?>
					<a class="action" href="<?php echo $helper->get('link', $i) ?>"><span class="ion-arrow-right-c"></span></a>
				<?php endif ; ?>
			</div>
		<?php endfor ?>
	</div>
</div>