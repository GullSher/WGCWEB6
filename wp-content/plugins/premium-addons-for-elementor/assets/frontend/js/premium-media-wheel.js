(function ($) {

    var PremiumAdvCarouselHandler = function ($scope, $) {

        var $outerContainer = $scope.find('.premium-adv-carousel__container'),
            settings = $outerContainer.data('settings');

        if (!settings) {
            return;
        }

        var animationType = settings.type;

        if ('infinite' === animationType) {

            var $mediaItemsContainer = $outerContainer.find('.premium-adv-carousel__items'),
                direction = settings.dir,
                scrollDir = settings.reverse,
                duration = settings.speed * 1000 + 'ms',
                animeName = 'pa-scroll-' + $scope.data('id'),
                horAlignWidth = 10,
                verAlignWidth = 10;

            elementorFrontend.waypoint($scope, function () {

                cloneItems();

                var $mediaItem = $scope.find('.premium-adv-carousel__item'),
                    transformOffset = 'horizontal' === direction ? $scope.find('.premium-adv-carousel__inner-container').innerWidth() : $scope.find('.premium-adv-carousel__inner-container').innerHeight(),
                    start = 'transform: translateX(' + transformOffset + 'px)',
                    end = 'transform: translateX(-101%)';

                if ('horizontal' === direction) {

                    $mediaItem.each(function () {
                        horAlignWidth += $(this).outerWidth(true);
                    });

                    $mediaItemsContainer.css({ 'width': horAlignWidth });
                } else {

                    start = 'transform: translateY(' + transformOffset + 'px)';
                    end = 'transform: translateY(-101%)';

                    $mediaItem.each(function () {
                        verAlignWidth += $(this).outerHeight(true);
                    });

                    $mediaItemsContainer.css({ 'height': verAlignWidth });
                }

                var keyFrames = document.createElement("style");

                keyFrames.innerHTML = '@keyframes ' + animeName + ' { 0%{ ' + start + '} 100% {' + end + '} }';

                document.head.appendChild(keyFrames);

                $mediaItemsContainer.css('animation', animeName + ' ' + duration + ' linear 0s infinite ' + scrollDir + ' none');
            }, {
                offset: "100%",
                triggerOnce: true
            });

            // we need to set the animation on reaching viewpoint.
            if (settings.pauseOnHover) {
                setInfiniteAnimeState();
            }

            if (settings.scroll) {

                setInfiniteAnimeState();

                if (settings.dir === 'horizontal') {

                    $outerContainer.find('.premium-adv-carousel__inner-container').mousewheel(function (e, delta) {
                        this.scrollLeft -= (delta * 30);
                        e.preventDefault();
                    });
                }
            } else {
                $outerContainer.find('.premium-adv-carousel__inner-container').css({ 'overflow': 'hidden' });
            }

        } else {

            // flipster animations.
            var $flipContainer = $scope.find('.premium-adv-carousel__items'),
                $flipItem = $scope.find('.premium-adv-carousel__item-outer-wrapper'),
                $buttonPrev = $scope.find('.premium-adv-carousel__prev-icon').html(),
                $buttonNext = $scope.find('.premium-adv-carousel__next-icon').html(),
                isSmallDevice = ['mobile', 'mobile_extra', 'tablet', 'tablet_extra'].includes( elementorFrontend.getCurrentDeviceMode() );

            $scope.find('.premium-adv-carousel__icons-holder').remove();

            $scope.find('.premium-adv-carousel__inner-container').flipster({
                itemContainer: $flipContainer,
                itemSelector: $flipItem,
                style: settings.type,
                fadeIn: 0,
                start: settings.start,
                loop: settings.loop,
                autoplay: settings.autoPlay,
                scrollwheel: settings.scroll,
                pauseOnHover: settings.pauseOnHover,
                click: settings.loop ? false : settings.click,
                keyboard: settings.keyboard,
                touch: settings.touch,
                spacing: settings.spacing,
                buttons: settings.buttons ? 'custom' : false,
                buttonPrev: $buttonPrev,
                buttonNext: $buttonNext,
                onItemSwitch: function () {
                    resetVideos();
                }
            }).css('visibility', 'inherit');

            if (settings.keyboard && ! isSmallDevice ) {
                //Fix: keyboard nav won't start unless the elements is focused.
                elementorFrontend.waypoint( $scope.find('.premium-adv-carousel__inner-container'), function () {
                    $.fn.focusWithoutScrolling = function () {
                        var x = window.scrollX, y = window.scrollY;
                        this.focus();
                        window.scrollTo(x, y);
                    };

                    $scope.find('.premium-adv-carousel__inner-container').focusWithoutScrolling();
                });
            }

            // Fix: item click event when loop option is enabled navigates to the wrong slide.
            if ( settings.loop && settings.click ) {
                $scope.find('.premium-adv-carousel__item-outer-wrapper').on('click.paFlipClick', function() {
                    var itemIndex = $(this).index();
                    $scope.find('.premium-adv-carousel__inner-container').flipster('jump', itemIndex );
                });
            }
        }

        // play video.
        $scope.find('.premium-adv-carousel__item .premium-adv-carousel__video-wrap').each(function (index, item) {

            var type = $(item).data("type");

            $(item).closest(".premium-adv-carousel__item").on("click.paPlayVid" + index, function () {

                var _this = $(this);

                resetVideos();

                _this.find(".premium-adv-carousel__media-wrap").css("background", "#000");

                _this.find(".premium-adv-carousel__video-icon, .premium-adv-carousel__vid-overlay").css("visibility", "hidden");

                if ("hosted" !== type) {
                    var $iframeWrap = _this.find(".premium-adv-carousel__iframe-wrap"),
                        src = $iframeWrap.data("src");

                    src = src.replace("&mute", "&autoplay=1&mute");

                    var $iframe = $("<iframe/>");

                    $iframe.attr({ "src": src, "frameborder": "0", "allowfullscreen": "1", "allow": "autoplay;encrypted-media;" });

                    $iframeWrap.html($iframe);

                    $iframe.css("visibility", "visible");
                } else {
                    var $video = $(item).find("video");
                    $video.attr('pa-playing', 'true').get(0).play();
                    $video.css("visibility", "visible");
                }
            });
        });

        function cloneItems() {
            var itemLen = $mediaItemsContainer.children().length,
                docFragment = new DocumentFragment();

            for (var i = 0; i < 5; i++) {
                // $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper:lt(' + itemLen + ')').clone(true, true).appendTo($mediaItemsContainer);

                $mediaItemsContainer.find('.premium-adv-carousel__item-outer-wrapper:lt(' + itemLen + ')').clone(true, true).appendTo(docFragment);
            }

            $mediaItemsContainer.append( docFragment);
        }

        function setInfiniteAnimeState() {

            $outerContainer.on('mouseenter.paMediaWheel', function () {

                $mediaItemsContainer.css('animation-play-state', 'paused');
            }).on('mouseleave.paMediaWheel', function () {
                $mediaItemsContainer.css('animation-play-state', 'running');
            });
        }

        function resetVideos() {

            $scope.find('iframe').attr('src', ''); // reset youtube/vimeo videos
            // reset self hosted videos.
            $video = $scope.find("video[pa-playing='true']").each(function () {
                var media = $(this).get(0);
                media.pause();
                media.currentTime = 0;
            });

            $scope.find(".premium-adv-carousel__video-icon, .premium-adv-carousel__vid-overlay").css("visibility", "visible");
            $scope.find(".premium-adv-carousel__media-wrap").css("background", "unset");
        }
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/premium-media-wheel.default', PremiumAdvCarouselHandler);
    });

})(jQuery);
