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
  $moduleTitle = $module->title;
  $moduleSub = $params->get('sub-heading');
?>

<div class="container">
	<div class="acm-features style-1">
		<div class="features-content">
			<div class="row">
				<div class="features-item col-xs-12 col-md-12">
					<?php if ($moduleSub): ?>
						<div class="sub-heading">
							<span><?php echo $moduleSub; ?></span>		
						</div>
					<?php endif; ?>

						<h2>
							<?php echo $moduleTitle ?>
						</h2>
			
					<?php if($helper->get('description')) : ?>
						<p><?php echo $helper->get('description') ?></p>
					<?php endif ; ?>
					
					<?php if($helper->get('button')) : ?>
						<a class="btn btn-default btn-lg btn-sp" href="<?php echo $helper->get('title-link'); ?>"><?php echo $helper->get('button') ?>
							<span class="ion-arrow-right-c"></span>
						</a>
					<?php endif ; ?>
				</div>
			</div>
		</div>
	</div>
</div>