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
/**
* Commented out to allow custom meta tags in index.php
*/
{% comment %} <?php if ($this->getParam('responsive', 1)): ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<title>Advanced Breathing Therapy in Colorado Springs | Respiras Breathing</title>
    <meta name="description" content="Respiras offers advanced breathing therapy to restore CO₂ balance, improve oxygen delivery, and calm an overactive nervous system. Drug-free solutions for anxiety, pain, and sleep issues.">
    <meta name="keywords" content="breathing therapy Colorado Springs, science-based breathing therapy, CO₂ breathing retraining, anxiety breathing therapy, natural anxiety treatment, dysautonomia therapy, sleep optimization breathing, non-drug anxiety solution, restore CO₂ balance">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Respiras Breathing">
    <link rel="canonical" href="https://respiras.com/">
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
<meta name="HandheldFriendly" content="true"/>
<meta name="apple-mobile-web-app-capable" content="YES"/> {% endcomment %}
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
<link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css" onload="this.onload=null;this.rel='stylesheet';">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css"></noscript>

<link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css" onload="this.onload=null;this.rel='stylesheet';">
<noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css"></noscript>

<!-- ✅ Non-blocking JS -->
<script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@6.8.4/swiper-bundle.min.js"></script> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@6.8.4/swiper-bundle.min.css">

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

<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

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
