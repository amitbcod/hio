define(["jquery", "owlcarousel"], function ($) {
    $(document).ready(function () {
        // Megamenu
        $('.sm_megamenu_menu > li > div').parent().addClass('parent-item');

        // Box full width
        var full_width = $('body').innerWidth();
        $('.full-content').css({'width': full_width});

        $(window).resize(function () {
            var full_width = $('body').innerWidth();
            $('.full-content').css({'width': full_width});
        });

        // Fix hover on IOS
        $('body').bind('touchstart', function () {
        });

        // Go to top
        $('#yt-totop').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        $("#yt-totop-fix").hide();
        $(function () {
            var wh = $(window).height();
            var whtml = $(document).height();
            $(window).scroll(function () {
                if ($(this).scrollTop() > whtml / 10) {
                    $('#yt-totop-fix').fadeIn();
                } else {
                    $('#yt-totop-fix').fadeOut();
                }
            });
            $('#yt-totop-fix').click(function () {
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        });

        /**
         * Sidebar product slider
         */

        $(".list-slider-sidebar .owl-carousel").owlCarousel({
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 1
                },
                768: {
                    items: 1
                },
                992: {
                    items: 1
                },
                1200: {
                    items: 1
                },
                1600: {
                    items: 1
                }
            },

            autoplay: false,
            loop: false,
            nav: true,
            dots: false,
            autoplayHoverPause: true,
            margin: 20,
        });

        /**
         * Home page 1
         */

        $(".home-page-1 .slidershow .owl-carousel").owlCarousel({
            items: 1,
            autoplay: true,
            loop: true,
            nav: true,
            dots: true,
            autoplayHoverPause: true,
            margin: 1
        });

        $(".home-page-1 .brand-slider .owl-carousel").owlCarousel({
            responsive: {
                0: {
                    items: 2
                },
                480: {
                    items: 3
                },
                768: {
                    items: 4
                },
                992: {
                    items: 5
                },
                1200: {
                    items: 6
                },
                1600: {
                    items: 7
                }
            },

            autoplay: false,
            loop: false,
            nav: true,
            dots: false,
            autoplayHoverPause: true,
            margin: 20,
        });

        $(".home-page-1 .product-slider-1 .owl-carousel").owlCarousel({
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 4
                },
                1200: {
                    items: 4
                },
                1600: {
                    items: 6
                }
            },

            autoplay: false,
            loop: false,
            nav: true,
            dots: false,
            autoplayHoverPause: true,
            margin: 20,
        });

        $(".home-page-1 .product-slider-2 .owl-carousel").owlCarousel({
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 4
                },
                1200: {
                    items: 3
                },
                1600: {
                    items: 3
                }
            },

            autoplay: false,
            loop: false,
            nav: true,
            dots: false,
            autoplayHoverPause: true,
            margin: 0,
        });

        $(".home-page-1 .slider-post").owlCarousel({
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 2
                },
                992: {
                    items: 3
                },
                1200: {
                    items: 4
                },
            },
            autoplay: false,
            loop: false,
            nav: true,
            dots: false,
            autoplayHoverPause: true,
            margin: 30
        });


    });

});

