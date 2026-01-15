
$(document).ready(function(){
  
    var owl = $('#emotion-carousel-265');
    owl.owlCarousel({
        items: 4,
        loop: true,
        nav: false,
        dots: true,
        stagePadding: 0,
        dotsEach: true,
        autoplay: true,
        autoplayTimeout: 7000,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        smartSpeed: 4000, 
        responsive: {
            0: {
                items: 1,
                smartSpeed: 2000, 
                autoplayTimeout: 5000,
            },
            768: {
                items: 4
            }
        }
    });
    
});

$(document).ready(function(){
  
    var owl = $('.wcr-carousel');
    owl.owlCarousel({
        items: 3,
        loop: false,
        nav: true,
        dots: true,
        margin: 20,
        // stagePadding: 0,
        dotsEach: true,
        // autoplay: true,
        // autoplayTimeout: 3000,
        // animateOut: 'fadeOut',
        // animateIn: 'fadeIn',
        // smartSpeed: 1000, 
        navText: ['', ''],
        responsive: {
            0: {
                items: 1,
                // smartSpeed: 1800, 
                // autoplayTimeout: 5000,
            },
            768: {
                items: 2
            },
            992: {
                items: 3
            }
        }
    });
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || window.performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload();
        }
    });

    
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > div');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > button');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
    
});
$(document).ready(function(){
  
    var owl = $('.wcr-carousel-sec');
    owl.owlCarousel({
        items: 3,
        loop: false,
        nav: true,
        dots: true,
        margin: 20,
        // stagePadding: 0,
        dotsEach: true,
        // autoplay: true,
        // autoplayTimeout: 3000,
        // animateOut: 'fadeOut',
        // animateIn: 'fadeIn',
        // smartSpeed: 1000, 
        navText: ['', ''],
        responsive: {
            0: {
                items: 1,
                // smartSpeed: 1800, 
                // autoplayTimeout: 5000,
            },
            768: {
                items: 2
            },
            992: {
                items: 3
            }
        }
    });
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || window.performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload();
        }
    });

    
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > div');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > button');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
    
});
$(document).ready(function(){
  
    var owl = $('.owl-carousel');
    owl.owlCarousel({
        items: 3,
        loop: true,
        nav: false,
        dots: true,
        // stagePadding: 0,
        dotsEach: true,
        // autoplay: true,
        // autoplayTimeout: 3000,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        // smartSpeed: 1000, 
        responsive: {
            0: {
                items: 1,
                // smartSpeed: 1800, 
                // autoplayTimeout: 5000,
            },
            768: {
                items: 3
            }
        }
    });
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || window.performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload();
        }
    });

    
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > div');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
      // Update previous dot for general .owl-carousel
    owl.on('changed.owl.carousel', function() {
        var dots = owl.find('.owl-dots > button');
        dots.removeClass('prev');
        dots.filter('.active').prev().addClass('prev');
    });
    
});


jQuery(document).ready(function($) {
    const items = $('.accordion button');
  
    function toggleAccordion() {
      const itemToggle = $(this).attr('aria-expanded');
  
      items.attr('aria-expanded', 'false');
  
      if (itemToggle === 'false') {
        $(this).attr('aria-expanded', 'true');
      }
    }
  
    items.on('click', toggleAccordion);
  });
  jQuery(document).ready(function() {
    function toggleCTA() {
        if ($(window).width() <= 768) {
            $('.desktop').hide();
            $('.mobile').show();
        } else {
            $('.desktop').show();
            $('.mobile').hide();
        }
    }

    // Run on initial load
    toggleCTA();

    // Run on window resize
    jQuery(window).resize(function() {
        toggleCTA();
    });
});


// slick slider

jQuery(window).on('load', function() {
    const $slider = jQuery('.modulewhat-we-treat .row');
    if ($slider.length > 0) {
        $slider.slick({
            dots: true,
            infinite: true,
            speed: 500,
            slidesToShow: 3, 
            adaptiveHeight: true,
            arrows: true,
            responsive: [
                {
                    breakpoint: 1024, 
                    settings: {
                        slidesToShow: 2 
                    }
                },
                {
                    breakpoint: 768, 
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        });
    }
});
jQuery(document).ready(function($){
  $('.n2-ss-slider source, .n2-ss-slider img').attr({
    loading: 'eager',
    fetchpriority: 'high'
  });
});


// jQuery(document).ready(function($) {
//     $('#Section183 .btn-default.btn-lg.btn-sp').click(function(e) {
//         e.preventDefault();

//         // Target the heading and content panel
//         let heading = $('#sppb-ac-heading-eda0e8cd-a673-46d5-bfa9-062df765012c-key-2');
//         let contentPanel = $('#sppb-ac-eda0e8cd-a673-46d5-bfa9-062df765012c-key-2');

//         // Remove 'collapsed' class from heading if present
//         heading.removeClass('collapsed');

//         // Add 'show' class to panel and set aria-expanded
//         contentPanel.addClass('show').attr('aria-expanded', 'true');

//         // Collapse other accordions if needed
//         $('.sppb-accordion .sppb-panel-collapse').not(contentPanel).removeClass('active').attr('aria-expanded', 'false');
//         $('.sppb-accordion .sppb-panel-heading').not(heading).addClass('collapsed');
//     });
// });


// slick slider end



// our values slider

$(document).ready(function () {
  // destroy existing instance if any (helps with double-init issues)
  if (window.wcrSwiper && window.wcrSwiper.destroy) {
    try { window.wcrSwiper.destroy(true, true); } catch(e){}
  }

  window.wcrSwiper = new Swiper('.our-values-swiper', {
    loop: true,
    // sensible mobile-first default
    slidesPerView: 3,
    spaceBetween: 16,
    allowSlideNext: true,
    allowSlidePrev: true,
    rewind: true,
    centeredSlides: false,
    // watchOverflow: true,        // disables navigation if not enough slides
    // observer: true,
    // observeParents: true,
    // resistanceRatio: 0.85,      // feel when swiping edges
    // touchAngle: 30,
    // threshold: 5,               // min px to trigger swipe
    // edgeSwipeDetection: true,
    // edgeSwipeThreshold: 20,
    // touchStartPreventDefault: false,
    // preventClicksPropagation: true,
    navigation: {
      nextEl: '.values-next',
      prevEl: '.values-prev',
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    autoplay: {
      delay: 5000,        // 3 seconds
      disableOnInteraction: false, // continue autoplay after interaction
    },
    speed: 2000,           
    breakpoints: {
      // when viewport >= 480px
      0: {
        slidesPerView: 1,
        spaceBetween: 16
      },
      // when viewport >= 1028px
      767: {
        slidesPerView: 2,
        spaceBetween: 20
      },
      // when viewport >= 1920px
      992: {
        slidesPerView: 3,
        spaceBetween: 24
      }
    }
  });
  
});    

jQuery('document').ready(function($){
     $('form#userForm .formSpan6 .formBody input#Phone').on('input', function () {
    let value = $(this).val();

    // Allow only + and digits
    value = value.replace(/[^+\d]/g, '');

    // Allow only one plus sign at the start
    value = value.replace(/(?!^)\+/g, '');

    // Remove dashes for processing
    let raw = value.replace(/-/g, '');

    let prefix = '';
    if (raw.startsWith('+')) {
        prefix = '+';
        raw = raw.substring(1);
    }

    // Limit to 13 digits maximum
    raw = raw.substring(0, 10);

    // Rebuild formatted number
    let formatted = prefix;

    if (raw.length > 0) formatted += raw.substring(0, 3);
    if (raw.length > 3) formatted += '-' + raw.substring(3, 6);
    if (raw.length > 6) formatted += '-' + raw.substring(6);


    $(this).val(formatted);
});

});


