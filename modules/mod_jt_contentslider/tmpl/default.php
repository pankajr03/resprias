<?php
/**
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) http://www.joomlatema.net, Inc. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      JoomlaTema.Net
 * @link        http://www.joomlatema.net
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Module\JTContentSlider\Site\Helper\JTContentSliderHelper;

if ($params->get('autoPlay') == 'true') {
    $autoplayscreenTimeout = $params->get('autoplayTimeout');
} else {
    $autoplayscreenTimeout = 9900000000000;
}

// Sanitize navigation text (allow only specific icon classes)
$navTextLeft =$params->get('navTextLeft', '<i class="fas fa-angle-left"></i>');
$navTextRight =$params->get('navTextRight', '<i class="fas fa-angle-right"></i>');
?>
<div class="jtcs_item_wrapper jt-cs" style="padding:<?php echo htmlspecialchars($params->get('content_padding'), ENT_QUOTES, 'UTF-8'); ?>;">
<?php if ($params->get('show_pretext') == 1): ?>
<div class="jt-pretext">
<span class="pretext_title"><?php echo htmlspecialchars($params->get('pretext_title'), ENT_QUOTES, 'UTF-8'); ?></span>
<span class="pretext"><?php echo htmlspecialchars($params->get('pretext'), ENT_QUOTES, 'UTF-8'); ?></span>
</div>
<?php endif; ?>
<div class="jtcs<?php echo $module->id; ?> <?php echo htmlspecialchars($params->get('NavPosition'), ENT_QUOTES, 'UTF-8'); ?> owl-carousel owl-theme slides_container">
<?php 
    $n = 1;
    $morecatlinks = array();
    foreach ($list as $i => $item):
        // More Category List and Blog
        $item->categlist = Route::_('index.php?option=com_content&view=category&id=' . $item->catid);

        // Get the thumbnail 
        $thumb_img = JTContentSliderHelper::getThumbnail(
            $item->id,
            $item->images,
            $thumb_folder,
            $show_default_thumb,
            $thumb_width,
            $thumb_height,
            $item->title,
            $item->introtext,
            $modulebase
        );
        $org_img = JTContentSliderHelper::getOrgImage(
            $item->id,
            $item->images,
            $item->title,
            $item->introtext,
            $modulebase
        );
        $caption_text = JTContentSliderHelper::getCaption(
            $item->id,
            $item->images,
            $item->introtext,
            $use_caption
        );
?>
<div class="slide" style="padding:<?php echo htmlspecialchars($params->get('article_block_padding'), ENT_QUOTES, 'UTF-8'); ?>;margin:<?php echo htmlspecialchars($params->get('article_block_margin'), ENT_QUOTES, 'UTF-8'); ?>" data-slide-index="<?php echo $i; ?>">
    <div class="jt-inner">
    <?php if ($params->get('show_thumbnail') == 1): ?>
        <div class="jt-imagecover" style="float:<?php echo htmlspecialchars($params->get('thumb_align'), ENT_QUOTES, 'UTF-8'); ?>;margin<?php 
            if ($params->get('thumb_align') == "left") {
                echo "-right";
            } elseif ($params->get('thumb_align') == "right") {
                echo "-left";
            } else {
                echo "-bottom";
            }
        ?>:<?php echo htmlspecialchars($params->get('thumb_margin'), ENT_QUOTES, 'UTF-8'); ?>">
        <?php if ($params->get('link_image') == 1): ?>
            <a class="link-image" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo $item->link; ?>"><?php echo $thumb_img; ?></a>
        <?php else: ?>
            <?php echo $thumb_img; ?>
        <?php endif; ?>
        <?php if ($params->get('hover_icons') == 1): ?>
            <div class="hover-icons">
                <a class="jt-icon icon-url" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo $item->link; ?>">
                    <i class="fa fa-link"></i>
                </a>
                <a class="jt-icon icon-lightbox jt-image-link" href="<?php echo htmlspecialchars($org_img, ENT_QUOTES, 'UTF-8'); ?>" data-lightbox="jt-1">
                    <i class="fa fa-search"></i>
                </a>
            </div>
        <?php endif; ?>
        <?php if ($params->get('use_caption') == 1 && !empty($caption_text)): ?>
            <span class="jt-caption"><?php echo htmlspecialchars($caption_text, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('show_category') == 1): ?>
        <?php if ($params->get('show_category_link') == 1): ?>
            <span class="jt-category">
                <a href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $item->catid); ?>" class="cat-link">
                    <?php if ($params->get('ShowCategoryIcon') == 1): ?>
                        <i class="<?php echo htmlspecialchars($params->get('CategoryIcon'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </span>
        <?php else: ?>
            <span class="jt-category">
                <?php if ($params->get('ShowCategoryIcon') == 1): ?>
                    <i class="<?php echo htmlspecialchars($params->get('CategoryIcon'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($params->get('show_title') == 1): ?>
        <<?php echo htmlspecialchars($params->get('TitleClass'), ENT_QUOTES, 'UTF-8'); ?>>
            <a class="jt-title" href="<?php echo $item->link; ?>" itemprop="url">
                <?php 
                if ($limit_title_by == 'word' && $limit_title > 0) {
                    $limit_title = (int)$limit_title;
                    $item->title = JTContentSliderHelper::substrword($item->title, $strip_tags, $allowed_tags, $replacertitle, $limit_title);
                } elseif ($limit_title_by == 'char' && $limit_title > 0) {
                    $limit_title = (int)$limit_title;
                    $item->title = JTContentSliderHelper::substring($item->title, $strip_tags, $allowed_tags, $replacertitle, $limit_title);
                }
                echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
                ?>
            </a>
        </<?php echo htmlspecialchars($params->get('TitleClass'), ENT_QUOTES, 'UTF-8'); ?>>
    <?php endif; ?>
    
    <?php if (($params->get('show_date') == 1) || ($params->get('show_author') == 1) || ($params->get('show_hits') == 1)): ?>
        <div class="jt-author-date">
        <?php if ($params->get('show_date') == 1): ?>
            <?php if ($params->get('ShowDateIcon') == 1): ?>
                <i class="<?php echo htmlspecialchars($params->get('DateIcon'), ENT_QUOTES, 'UTF-8'); ?>"></i>
            <?php endif; ?>
            <span class="jt-date">
                <?php JTContentSliderHelper::getDate($show_date, $show_date_type, $item->created, $custom_date_format); ?>
            </span>
        <?php endif; ?>
        <?php if ($params->get('show_author') == 1): ?>
            <span class="jt-author">
                <?php if ($params->get('ShowAuthorIcon') == 1): ?>
                    <i class="<?php echo htmlspecialchars($params->get('AuthorIcon'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($item->author, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        <?php endif; ?>
        <?php if ($params->get('show_hits') == 1): ?>
            <span class="jt-hits">
                <?php if ($params->get('ShowHitIcon') == 1): ?>
                    <i class="<?php echo htmlspecialchars($params->get('HitIcon'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                <?php endif; ?>
                <?php echo Text::sprintf('COM_CONTENT_ARTICLE_HITS', $item->hits); ?>
            </span>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('show_introtext') == 1): ?>
        <div class="jt-introtext">
            <?php
            if ($limit_intro_by == 'word' && $introtext_truncate > 0) {
                $item->introtext = JTContentSliderHelper::substrword($item->introtext, $strip_tags, $allowed_tags, $replacer_text, $introtext_truncate);
                echo $item->introtext;
            } elseif ($limit_intro_by == 'char' && $introtext_truncate > 0) {
                $item->introtext = JTContentSliderHelper::substring($item->introtext, $strip_tags, $allowed_tags, $replacer_text, $introtext_truncate);
                echo $item->introtext;
            }
            ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('showReadmore') == 1): ?>
        <p class="jt-readmore">
            <a class="btn btn-primary jt-readmore" target="<?php echo htmlspecialchars($openTarget, ENT_QUOTES, 'UTF-8'); ?>"
                title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>"
                href="<?php echo $item->link; ?>">
                <i class="<?php echo htmlspecialchars($params->get('ReadMoreIcon'), ENT_QUOTES, 'UTF-8'); ?>">&nbsp;</i>
                <?php echo htmlspecialchars($params->get('ReadMoreText', 'Read More'), ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </p>
    <?php endif; ?>
    <div></div>
    <div style="clear:both"></div>
    </div>
</div>
<?php 
        // More category links
        $morecatlink = "<a href=" . Route::_('index.php?option=com_content&view=category&id=' . $item->catid) . ">" . htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8') . "</a>";
        if (!isset($morecatlinks[$morecatlink])) {
            $morecatlinks[$morecatlink] = true;
        }
    endforeach; 
?></div>
<?php 
if ($show_morecat_links) {
    echo "<div class='jtcs_more_cat'>" . htmlspecialchars($params->get('morein_text', 'More In'), ENT_QUOTES, 'UTF-8') . " ";
    foreach ($morecatlinks as $morecatlink => $val) {
        echo $morecatlink . '&nbsp;&nbsp;';
    }
    echo "</div>";
}
?>
</div>
<style type="text/css">
.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav{justify-content:<?php echo htmlspecialchars($params->get('NavAlignment'), ENT_QUOTES, 'UTF-8'); ?>}
.jtcs<?php echo $module->id; ?>.positiontop.owl-carousel .owl-nav{width:100%;position:absolute;top:<?php echo htmlspecialchars($params->get('NavTopPos'), ENT_QUOTES, 'UTF-8'); ?>; bottom:auto;justify-content:<?php echo htmlspecialchars($params->get('NavAlignment'), ENT_QUOTES, 'UTF-8'); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?>.positioncenter.owl-carousel .owl-nav{width:100%;position:absolute;top:50%; bottom:auto;transform:translateY(-50%);justify-content:space-between; z-index:1; height:0px;}
.jtcs<?php echo $module->id; ?>.positionbottom.owl-carousel .owl-nav{width:100%;position:absolute;top:auto; bottom:<?php echo htmlspecialchars($params->get('NavBotPos'), ENT_QUOTES, 'UTF-8'); ?>;justify-content:<?php echo htmlspecialchars($params->get('NavAlignment'), ENT_QUOTES, 'UTF-8'); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?> .owl-dots {position: relative;bottom:<?php echo htmlspecialchars($params->get('DotsBottomPos'), ENT_QUOTES, 'UTF-8'); ?>;}
.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav.disabled{ display:none}
.jtcs<?php echo $module->id; ?>.owl-carousel .jt-introtext{ text-align:<?php echo htmlspecialchars($params->get('IntroTextAlign'), ENT_QUOTES, 'UTF-8'); ?>}
</style>
<script defer type="text/javascript">
jQuery(document).ready(function() {
  var el = jQuery('.jtcs<?php echo $module->id; ?>.owl-carousel');
  var carousel;
  var carouselOptions = {
    margin: <?php echo (int)$params->get('marginRight', 20); ?>,
    stagePadding: <?php echo (int)$params->get('stagePadding', 0); ?>,
    center: <?php echo $params->get('centerItems') == 'true' ? 'true' : 'false'; ?>,
    loop: <?php echo $params->get('infiniteLoop') == 'true' ? 'true' : 'false'; ?>,
    nav: <?php echo $params->get('show_navigation') == 'true' ? 'true' : 'false'; ?>,
    navText:["<?php echo $navTextLeft; ?>","<?php echo $navTextRight; ?>"],
    dots: <?php echo $params->get('showDots') == 'true' ? 'true' : 'false'; ?>,
    rtl: <?php echo $params->get('rtl') == 'true' ? 'true' : 'false'; ?>,
    slideBy: '<?php echo htmlspecialchars($params->get('slideBy', 'page'), ENT_QUOTES, 'UTF-8'); ?>',
    autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
    autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
    smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
    autoplayTimeout:<?php echo (int)$params->get('autoplayTimeout', 4000); ?>,
    autoplayHoverPause:<?php echo $params->get('PauseOnHover') == 'true' ? 'true' : 'false'; ?>,
    mouseDrag: <?php echo $params->get('mouseDrag') == 'true' ? 'true' : 'false'; ?>,
    touchDrag: <?php echo $params->get('touchDrag') == 'true' ? 'true' : 'false'; ?>,
    navSpeed:<?php echo (int)$params->get('navSpeed', 600); ?>,
    dotsSpeed:<?php echo (int)$params->get('dotsSpeed', 600); ?>,
    responsive: {
      0: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items: <?php echo (int)$params->get('slideColumnxs', 1); ?>,
        rows: <?php echo (int)$params->get('slideRowxs', 4); ?>
      },
      768: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items: <?php echo (int)$params->get('slideColumnsm', 2); ?>,
        rows:<?php echo (int)$params->get('slideRowsm', 3); ?>
      },
      991: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items:<?php echo (int)$params->get('slideColumn', 3); ?>,
        rows:<?php echo (int)$params->get('slideRow', 1); ?>
      }
    }
  };

  var viewport = function() {
    var width;
    if (carouselOptions.responsiveBaseElement && carouselOptions.responsiveBaseElement !== window) {
      width = jQuery(carouselOptions.responsiveBaseElement).width();
    } else if (window.innerWidth) {
      width = window.innerWidth;
    } else if (document.documentElement && document.documentElement.clientWidth) {
      width = document.documentElement.clientWidth;
    } else {
      console.warn('Can not detect viewport width.');
    }
    return width;
  };

  var severalRows = false;
  var orderedBreakpoints = [];
  for (var breakpoint in carouselOptions.responsive) {
    if (carouselOptions.responsive[breakpoint].rows > 1) {
      severalRows = true;
    }
    orderedBreakpoints.push(parseInt(breakpoint));
  }
  
  if (severalRows) {
    orderedBreakpoints.sort(function (a, b) {
      return b - a;
    });
    var slides = el.find('[data-slide-index]');
    var slidesNb = slides.length;
    if (slidesNb > 0) {
      var rowsNb;
      var previousRowsNb = undefined;
      var colsNb;
      var previousColsNb = undefined;

      var updateRowsColsNb = function () {
        var width =  viewport();
        for (var i = 0; i < orderedBreakpoints.length; i++) {
          var breakpoint = orderedBreakpoints[i];
          if (width >= breakpoint || i == (orderedBreakpoints.length - 1)) {
            var breakpointSettings = carouselOptions.responsive['' + breakpoint];
            rowsNb = breakpointSettings.rows;
            colsNb = breakpointSettings.items;
            break;
          }
        }
      };

      var updateCarousel = function () {
        updateRowsColsNb();

        if (rowsNb != previousRowsNb || colsNb != previousColsNb) {
          var reInit = false;
          if (carousel) {
            carousel.trigger('destroy.owl.carousel');
            carousel = undefined;
            slides = el.find('[data-slide-index]').detach().appendTo(el);
            el.find('.fake-col-wrapper').remove();
            reInit = true;
          }

          var perPage = rowsNb * colsNb;
          var pageIndex = Math.floor(slidesNb / perPage);
          var fakeColsNb = pageIndex * colsNb + (slidesNb >= (pageIndex * perPage + colsNb) ? colsNb : (slidesNb % colsNb));

          var count = 0;
          for (var i = 0; i < fakeColsNb; i++) {
            var fakeCol = jQuery('<div class="fake-col-wrapper"></div>').appendTo(el);
            for (var j = 0; j < rowsNb; j++) {
              var index = Math.floor(count / perPage) * perPage + (i % colsNb) + j * colsNb;
              if (index < slidesNb) {
                slides.filter('[data-slide-index=' + index + ']').detach().appendTo(fakeCol);
              }
              count++;
            }
          }

          previousRowsNb = rowsNb;
          previousColsNb = colsNb;

          if (reInit) {
            carousel = el.owlCarousel(carouselOptions);
          }
        }
      };

      jQuery(window).on('resize', updateCarousel);
      updateCarousel();
    }
  }

  carousel = el.owlCarousel(carouselOptions);
});

if (typeof lightbox !== 'undefined') {
  lightbox.option({
    fadeDuration:<?php echo (int)$params->get('fadeDuration', 300); ?>,
    fitImagesInViewport:<?php echo $params->get('fitImagesInViewport') == 'true' ? 'true' : 'false'; ?>,
    imageFadeDuration: <?php echo (int)$params->get('imageFadeDuration', 300); ?>,
    positionFromTop: <?php echo (int)$params->get('positionFromTop', 150); ?>,
    resizeDuration: <?php echo (int)$params->get('resizeDuration', 150); ?>
  });
}
</script>