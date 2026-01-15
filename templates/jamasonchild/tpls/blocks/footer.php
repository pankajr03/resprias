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
?>

<!-- BACK TOP TOP BUTTON -->
<div id="back-to-top" data-spy="affix" data-offset-top="200" class="back-to-top hidden-xs hidden-sm affix-top">
  <button class="btn btn-primary" title="Back to Top"><span class="ion-arrow-up-c"></span></button>
</div>

<script type="text/javascript">
(function($) {
  // Back to top
  $('#back-to-top').on('click', function(){
    $("html, body").animate({scrollTop: 0}, 500);
    return false;
  });
})(jQuery);
</script>
<!-- BACK TO TOP BUTTON -->


<!-- MAIN FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">
	<section class="t3-copyright">
		<div class="container">
			<div class="row">
				<div class="<?php echo $this->getParam('t3-rmvlogo', 1) ? 'col-md-8' : 'col-md-12' ?> copyright <?php $this->_c('footer') ?>">
					<!-- Footer main modules -->
					<jdoc:include type="modules" name="footer-main" style="none" />
				</div>
				<?php if ($this->getParam('t3-rmvlogo', 1)): ?>
					<div class="col-md-4 poweredby text-hide">
						<a class="t3-logo t3-logo-dark" href="http://t3-framework.org" 
						   title="<?php echo JText::_('T3_POWER_BY_TEXT') ?>"
						   target="_blank" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>>
						   <?php echo JText::_('T3_POWER_BY_HTML') ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<a href="tel:(719) 387-8389" class="sticky-call"><i class="fa fa-phone"></i></a>
</footer>

<!-- GREY FOOTER -->
<footer id="t3-footer-grey" class="wrap t3-footer">
	<section class="footer-grey">
		<div class="container">
			<div class="row">
				<div class="<?php echo $this->getParam('t3-rmvlogo', 1) ? 'col-md-8' : 'col-md-12' ?>">
					<!-- Grey footer modules -->
					<jdoc:include type="modules" name="footer-grey" style="none" />
				</div>
				<?php if ($this->getParam('t3-rmvlogo', 1)): ?>
					<div class="col-md-4 poweredby text-hide">
						<a class="t3-logo t3-logo-dark" href="http://t3-framework.org" 
						   title="<?php echo JText::_('T3_POWER_BY_TEXT') ?>"
						   target="_blank" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>>
						   <?php echo JText::_('T3_POWER_BY_HTML') ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<a href="tel:(719) 387-8389" class="sticky-call"><i class="fa fa-phone"></i></a>
</footer>
