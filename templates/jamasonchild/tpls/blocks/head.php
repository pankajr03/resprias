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

<!-- META FOR IOS & HANDHELD -->
<?php if ($this->getParam('responsive', 1)): ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
		<style type="text/stylesheet">
		@-webkit-viewport   { width: device-width; }
		@-moz-viewport      { width: device-width; }
		@-ms-viewport       { width: device-width; }
		@-o-viewport        { width: device-width; }
		@viewport           { width: device-width; }
	</style>
	<script type="text/javascript">
		//<![CDATA[
		if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
			var msViewportStyle = document.createElement("style");
			msViewportStyle.appendChild(
				document.createTextNode("@-ms-viewport{width:auto!important}")
			);
			document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
		}
		//]]>
	</script>
<?php endif ?>
<!-- Social Preview -->
<meta property="og:title" content="Breathing Education for Wellness, Performance & Longevity – Respiras Breathing" />
<meta property="og:description" content="Respiras teaches breathing as a trainable skill that supports nervous system adaptability, regulation, and sustainable performance under stress." />
<meta property="og:image" content="https://www.respiras.com/images/home/images_1200-healthy-aging-happy-couple-respiras-breathing.jpg" />
<meta property="og:url" content="https://www.respiras.com/" />
<meta property="og:type" content="website" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />

<meta name="HandheldFriendly" content="true"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<!-- //META FOR IOS & HANDHELD -->

<?php
// SYSTEM CSS
//$this->addStyleSheet(JUri::base(true) . '/templates/system/css/system.css');
?>

<?php
// T3 BASE HEAD
$this->addHead();
?>

<!-- ✅ Lazy-load Slick CSS -->
<link rel="preload" href="./templates/jamasonchild/fonts/ionicons/fonts/ionicons.woff" as="font" type="font/woff" crossorigin>
<link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css" onload="this.onload=null;this.rel='stylesheet';">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css"></noscript>

<link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css" onload="this.onload=null;this.rel='stylesheet';">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css"></noscript>

<!-- ✅ Non-blocking JS -->
<script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@6.8.4/swiper-bundle.min.js" defer></script> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@6.8.4/swiper-bundle.min.css" as="style"> 

<?php

// CUSTOM CSS
$timestamp = time();
if (is_file(T3_TEMPLATE_PATH . '/css/custom.css')) {
    echo '<link rel="stylesheet" href="' . T3_TEMPLATE_URL . '/css/custom.css?v=' . $timestamp . '" type="text/css" />';
}
if (is_file(T3_TEMPLATE_PATH . '/js/custom.js')) {
	$this->addScript(T3_TEMPLATE_URL . '/js/custom.js?v='.$timestamp);
}
?>

<!-- Le HTML5 shim and media query for IE8 support -->
<!--[if lt IE 9]>
<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
<script type="text/javascript" src="<?php echo T3_URL ?>/js/respond.min.js"></script>
<![endif]-->

<!-- link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" -->

<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>-->


<!-- You can add Google Analytics here or use T3 Injection feature -->

<script>
    $(document).ready(function () {
    $(".navbar-toggle").click(function () {
        $(".navbar-toggle").toggleClass("show");
    });
});

        $(document).ready(function () {
    // Initialize Swiper
    var swiper = new Swiper('.wcr-swiper', {
        loop: false,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        slidesPerView: 3.1,
        pagination: {
            el: '.swiper-pagination', // Add this element in your HTML
            clickable: true,           // Make the dots clickable
        },
        spaceBetween: 24,
        breakpoints: {
            300: {
                slidesPerView: 1,
                spaceBetween: 15,
            },
            1028: {
                slidesPerView: 2,
                spaceBetween: 15,
            },
            1920: {
                slidesPerView: 3.1,
            }
        }
    });

    // --- Add .prev class to the previous dot ---
    swiper.on('slideChange', function () {
        var $dots = $('.wcr-swiper .swiper-pagination-bullet');
        $dots.removeClass('prev');
        $dots.filter('.swiper-pagination-bullet-active').prev().addClass('prev');
    });
});

</script>
