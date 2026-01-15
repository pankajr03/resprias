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

	$columns						= $helper->get('columns');
	$count 							= $helper->getRows('client-item.client-logo');
	$gray								= $helper->get('img-gray');
	$opacity						= $helper->get('img-opacity');
	$float = 0;
	
	if ($opacity=="") {
		$opacity = 100;
	}
	
	if (100%$columns) {
		$float = 0.01;
	}

	$titleAlign = '';
	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

	if($helper->get('title-align')) {
		$titleAlign = 'col-md-offset-6 col-md-4';
	}
?>

<div id="acm-cliens-<?php echo $module->id; ?>" class="acm-cliens style-1 <?php if($gray): ?> img-grayscale <?php endif; ?>">
	<?php if($module->showtitle || $moduleSub) : ?>
	<div class="section-title <?php echo $titleAlign ;?>">
	<!-- Module Title -->
		<?php if ($moduleSub): ?>
			<div class="sub-heading">
				<span><?php echo $moduleSub; ?></span>		
			</div>
		<?php endif; ?>
		
		<?php if($module->showtitle) : ?>
		<h2><?php echo $moduleTitle ?></h2>
		<?php endif; ?>
	<!-- // Module Title -->
	</div>
	<?php endif ; ?>

	 <?php 
	 	for ($i=0; $i<$count; $i++) : 
	 	
		$clientName = $helper->get('client-item.client-name',$i);
		$clientLink = $helper->get('client-item.client-link',$i);
		$clientLogo = $helper->get('client-item.client-logo',$i);
		
		if ($i%$columns==0) echo '<div class="row">'; 
	?>
	
		<div class="col-xs-12 client-item ja-animate" style="width:<?php echo number_format(100/$columns, 2, '.', ' ') - $float;?>%;" data-animation="pop-up" data-delay="item-<?php echo $i ;?>">
			<div class="client-img">
				<?php if($clientLink):?><a href="<?php echo $clientLink; ?>" title="<?php echo $clientName; ?>" ><?php endif; ?>
					<img class="img-responsive" alt="<?php echo $clientName; ?>" src="<?php echo $clientLogo; ?>">
				<?php if($clientLink):?></a><?php endif; ?>
			</div>
		</div> 
		
	 	<?php if ( ($i%$columns==($columns-1)) || $i==($count-1) )  echo '</div>'; ?>
	 	
 	<?php endfor ?>

 	<?php if($helper->get('more-link')) : ?>
		<a class="action btn btn-default btn-lg btn-sp" href="<?php echo $helper->get('more-link') ?>">
			<?php echo $helper->get('more-title') ?>
			<span class="ion-arrow-right-c"></span>
		</a>
	<?php endif ; ?>
</div>
	
<?php if($opacity>=0 && $opacity<=100): ?>
<script>
(function ($) {
	$(document).ready(function(){ 
		$('#acm-cliens-<?php echo $module->id ?> .client-img img.img-responsive').css({
			'filter':'alpha(opacity=<?php echo $opacity ?>)', 
			'zoom':'1', 
			'opacity':'<?php echo $opacity/100 ?>'
		});
	});
})(jQuery);
</script>
<?php endif; ?>