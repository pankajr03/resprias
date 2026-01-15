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
$count  = $helper->getRows('data.title');
$column = $helper->get('columns');

$moduleTitle = $module->title;
$moduleSub   = $params->get('sub-heading');
?>

<div class="acm-features style-3">
	<?php if ($module->showtitle) : ?>
	<div class="container">
		<div class="section-title">
			<?php if ($moduleSub): ?>
				<div class="sub-heading">
					<span><?php echo $moduleSub; ?></span>
				</div>
			<?php endif; ?>
			<h2><?php echo $moduleTitle ?></h2>
		</div>
	</div>
	<?php endif; ?>

	<div id="acm-feature-<?php echo $module->id; ?>" class="owl-slide container">
		<div class="owl-carousel owl-theme" <?php if ($module->id == 265) echo 'id="emotion-carousel-265"'; ?>>
			<?php for ($i=0; $i<$count; $i++) : ?>
				<?php if ($helper->get('data.link', $i)) : ?>
					<a href="<?php echo $helper->get('data.link', $i) ?>" title="">
				<?php endif; ?>

				<div class="features-item ja-animate col"
					 data-index="<?php echo $i; ?>"
					 data-animation="move-from-bottom"
					 data-delay="item-<?php echo $i; ?>">
					<div class="features-item-inner">
						<?php if ($helper->get('data.img', $i)) : ?>
							<div class="features-img">
								<img src="<?php echo $helper->get('data.img', $i) ?>" alt="<?php echo $helper->get('data.title', $i) ?>" />
							</div>
						<?php endif; ?>

						<?php if ($helper->get('data.title', $i) || $helper->get('data.description', $i)) : ?>
						<div class="features-text">
							<?php if ($helper->get('data.title', $i)) : ?>
								<h4><?php echo $helper->get('data.title', $i) ?></h4>
							<?php endif; ?>

							<?php if ($helper->get('data.description', $i)) : ?>
								<p><?php echo $helper->get('data.description', $i) ?></p>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ($helper->get('data.link', $i)) : ?>
					</a>
				<?php endif; ?>
			<?php endfor; ?>
		</div>

		<?php if ($module->id == 369) : ?>
			<div class="blog-slider-btn">
				<a href="/blogs" class="btn btn-default btn-lg btn-sp" title="">
					Read More <span class="ion-arrow-right-c"></span>
				</a>
			</div>
		<?php endif; ?>

		<!-- Navigation Arrows -->
		<div class="owl-nav-custom">
			<button class="owl-prev-custom"><span class="ion-chevron-left"></span></button>
			<button class="owl-next-custom"><span class="ion-chevron-right"></span></button>
		</div>
	</div>
</div>

<script>
(function($){
	jQuery(document).ready(function($) {
		var $carouselContainer = $("#acm-feature-<?php echo $module->id; ?> .owl-carousel");

		// Store clicked index before navigation
		$carouselContainer.on("click", ".features-item", function() {
			var clickedIndex = $(this).data("index");
			localStorage.setItem("carouselLastClicked", clickedIndex);
		});

		// Init carousel
		$carouselContainer.owlCarousel({
			addClassActive: true,
			items: <?php echo $column; ?>,
			margin: 10,
			stagePadding: 0,
			responsive : {
				0 : { items: 1 },
				768 : { items: 2 },
				979 : { items: 2 },
				1199 : { items: <?php echo $column; ?> }
			},
			loop: true,
			nav: false,
			dots: true,
			autoplay: true
		});

		// Custom Navigation
		$('.owl-prev-custom').click(function() {
			$carouselContainer.trigger('prev.owl.carousel');
		});
		$('.owl-next-custom').click(function() {
			$carouselContainer.trigger('next.owl.carousel');
		});

		// Limit dots to 5
		var $dots = $('#acm-feature-<?php echo $module->id; ?> .owl-dots .owl-dot');
		if ($dots.length > 5) {
			$dots.slice(5).hide();
		}

		// Jump to next item if clicked previously
		var lastClicked = localStorage.getItem("carouselLastClicked");
		if (lastClicked !== null) {
			var totalItems = $carouselContainer.find(".owl-item").length;
			var nextIndex = (parseInt(lastClicked) + 1) % totalItems;
			$carouselContainer.trigger("to.owl.carousel", [nextIndex, 0, true]);
			localStorage.removeItem("carouselLastClicked");
		}
	});
})(jQuery);
</script>
