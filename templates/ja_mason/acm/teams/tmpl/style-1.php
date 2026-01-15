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

  $count = $helper->count('member-name');
  $col = $helper->get('number_col');

  $titleAlign = '';

  $moduleTitle = $module->title;
	$moduleSub = $params->get('sub-heading');

	if($helper->get('title-align')) {
		$titleAlign = 'col-md-6 col-md-offset-6 col-lg-4';
	}

?>
<div class="acm-teams style-1">
	<?php if($module->showtitle) : ?>
	<div class="section-title <?php echo $titleAlign ;?>">
	<!-- Module Title -->
	<?php if ($moduleSub): ?>
		<div class="sub-heading">
			<span><?php echo $moduleSub; ?></span>		
		</div>
	<?php endif; ?>

	<h2><?php echo $moduleTitle ?></h2>
	<!-- // Module Title -->
	</div>
	<?php endif ; ?>

	<div class="team-items col-<?php echo $col ;?>">
		<?php
      for ($i=0; $i < $count; $i++) :
        if ($i%$col==0) echo '<div class="row">'; 
    ?>
		<div class="col-sm-6 col-md-<?php echo (12/$col); ?>">
    	<div class="item ja-animate" data-animation="move-from-right" data-delay="item-<?php echo $i ;?>">
	      <div class="member-image">
	        <img src="<?php echo $helper->get('member-image', $i); ?>" alt="<?php echo $helper->get('member-name', $i); ?>" />
	      </div>

	      <div class="member-content">
		      <h4><?php echo $helper->get('member-name', $i); ?></h4>

		      <?php if($helper->get('member-position', $i)) :?>
			      <p class="member-title"><?php echo $helper->get('member-position', $i); ?></p>
			    <?php endif ;?>

			    <?php if($helper->get('member-desc', $i)) :?>
		      <p class="member-desc"><?php echo $helper->get('member-desc', $i); ?></p>
		      <?php endif ;?>

		      <?php if($helper->get('member-link', $i)) :?>
			      <a class="action" href="<?php echo $helper->get('member-link', $i); ?>" title="<?php echo $helper->get('member-name', $i); ?>">
			      	<span class="ion-arrow-right-c"></span>
			      </a>
		      <?php endif ;?>
	      </div>
     	</div>
		</div>
    
    <?php if ( ($i%$col==($col-1)) || $i==($count-1) )  echo '</div>'; ?>
		<?php endfor; ?>
	</div>

	<?php if($helper->get('member-more')) :?>
	<div class="action <?php echo $titleAlign ;?>">
		<a class="btn btn-default btn-lg btn-sp" href="<?php echo $helper->get('member-more-link'); ?>">
			<?php echo $helper->get('member-more'); ?>
			<span class="ion-arrow-right-c"></span>
		</a>
	</div>
	<?php endif ;?>
</div>
