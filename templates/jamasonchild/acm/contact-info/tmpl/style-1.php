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

	$contacInfoMap 			= $helper->get('contact-info-googlemap');
	$contacInfoImage 		= $helper->get('contact-info-image');
	$contacInfoPosition = $helper->get('contact-info-position');

	$moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

?>

  
<div id="acm-contact-<?php echo $module->id; ?>" class="acm-contact-info style-1">
 	<div class="row">
			<?php if($contacInfoImage || $contacInfoMap): ?>
 		<div class="col-sm-12 col-md-8">
		  <div class="info-bg">
		  	<?php echo $contacInfoMap; ?>
		  	<?php if($contacInfoImage): ?>
		  		<img src="<?php echo $contacInfoImage; ?>" alt="" />
		  	<?php endif; ?>
		  </div>
 		</div>
		<?php endif; ?>

 		<div class="col-sm-12 col-md-4 ja-animate" data-delay="item-1" data-animation="move-from-right">
 			<?php if($module->showtitle || $moduleSub) : ?>
			<div class="section-title">
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

 			<div class="info <?php echo $helper->get('contact-info-position'); ?>">
				<dl class="info-list">
				  <?php $count= $helper->getRows('contact-info-item.contact-info-name'); ?>
				  
				  <?php for ($i=0; $i<$count; $i++) : ?>
				  
					<dt>
						<?php if($helper->get ('contact-info-item.contact-info-icon', $i)): ?><span class="fa <?php echo $helper->get ('contact-info-item.contact-info-icon', $i); ?>"></span><?php endif; ?>
						<span><?php echo $helper->get ('contact-info-item.contact-info-name', $i) ?></span>
					</dt>
				
			  	<?php if ($helper->get ('contact-info-item.contact-info-value', $i)) : ?>
			    <dd><?php echo $helper->get ('contact-info-item.contact-info-value', $i) ?></dd>
			  	<?php endif; ?>
			  	
					<?php endfor; ?>
				</dl>

				<?php if($helper->get('more-link')) : ?>
					<a class="action btn btn-default btn-lg btn-sp" href="<?php echo $helper->get('more-link') ?>">
						<?php echo $helper->get('more-title') ?>
						<span class="ion-arrow-right-c"></span>
					</a>
				<?php endif ; ?>
			</div>
 		</div>
 	</div>

</div>