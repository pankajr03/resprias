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

$count = $helper->getRows('data.title');

?>

<div class="acm-slideshow acm-owl">
	<div id="acm-slideshow-<?php echo $module->id; ?>">
		<div class="owl-carousel owl-theme">
				<?php 
          for ($i=0; $i<$count; $i++) : 
        ?>
				<div class="item">

          <?php if($helper->get('data.image', $i)): ?>
            <img class="img-bg" src="<?php echo $helper->get('data.image', $i); ?>" alt="Item Slideshow" />
          <?php endif; ?>
          <div class="slider-content">
            <div class="slider-content-inner container container-hd">

				      <?php if($helper->get('data.title', $i)): ?>
                <div class="title ja-animate" data-animation="move-from-left">
		              <h1>
                    <?php if($helper->get('data.btn-link', $i)): ?>
                      <a href="<?php echo $helper->get('data.btn-link', $i) ;?>" title="<?php echo $helper->get('data.btn-title', $i) ;?>">
                    <?php endif ;?>

                    <?php echo $helper->get('data.title', $i) ;?>

                    <?php if($helper->get('data.btn-link', $i)): ?>
                      </a>
                    <?php endif ;?>
                  </h1>
                </div>
              <?php endif ;?>

              <?php if($helper->get('data.btn-link', $i)): ?>
                <a class="btn btn-primary btn-lg btn-sp hidden-xs ja-animate" data-animation="move-from-left" data-delay="item-1" href="<?php echo $helper->get('data.btn-link', $i) ;?>" title="<?php echo $helper->get('data.btn-title', $i) ;?>">
                  <?php echo $helper->get('data.btn-title', $i) ;?>
                  <span class="ion-arrow-right-c"></span>
                </a>
              <?php endif ;?>
            </div>
          </div>
				</div>
			 	<?php endfor ;?>
		</div>
	</div>
</div>

<script>
(function($){
  jQuery(document).ready(function($) {
    $("#acm-slideshow-<?php echo $module->id; ?> .owl-carousel").owlCarousel({
      items: 1,
      loop: true,
      nav: true,
      dots: false,
      autoplay: <?php echo $helper->get('auto-play') ;?>,
      navText: ["<span class='ion-chevron-left'></span>", "<span class='ion-chevron-right'></span>"]
    });
  });
})(jQuery);
</script>