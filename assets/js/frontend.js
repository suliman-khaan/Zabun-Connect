(function($) {
    'use strict';

    $(document).ready(function() {
        // Thumbnail switcher in Single Property Detail view
        $('.zabun-gallery-thumbs').on('click', '.zabun-thumb-item', function(e) {
            e.preventDefault();
            var $thumb = $(this);
            var $gallery = $thumb.closest('.zabun-detail-gallery');
            var fullUrl = $thumb.data('full-img') || $thumb.find('img').attr('src');

            $gallery.find('.zabun-gallery-main img, .zabun-detail-gallery .main-img').attr('src', fullUrl);
            $gallery.find('.zabun-thumb-item').removeClass('active');
            $thumb.addClass('active');
        });

        // -------------------------------------------------------------
        // Hero Search Bar Interactivity
        // -------------------------------------------------------------
        // 1. Status Tabs
        $(document).on('click', '.zabun-status-tabs button', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $tabs = $btn.closest('.zabun-status-tabs');
            var $form = $btn.closest('form');
            var statusVal = $btn.data('status') || '';

            $tabs.find('button').removeClass('active');
            $btn.addClass('active');
            $form.find('input[name="zabun_status"]').val(statusVal);
        });

        // 2. Button Groups (Bedrooms, Bathrooms)
        $(document).on('click', '.zabun-btngroup button', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $group = $btn.closest('.zabun-btngroup');
            var groupName = $group.data('group');
            var val = $btn.data('val') || '';

            $group.find('button').removeClass('active');
            $btn.addClass('active');
            $group.closest('form').find('input[name="zabun_' + groupName + '"]').val(val);
        });

        // 3. More Filters Drawer Toggle
        $(document).on('click', '.zabun-btn-more', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $panel = $btn.closest('.zabun-search-hero').find('.zabun-expanded-drawer');
            var isHidden = $panel.is(':hidden');

            if (isHidden) {
                $panel.slideDown(200);
                $btn.addClass('open').attr('aria-expanded', 'true');
            } else {
                $panel.slideUp(200);
                $btn.removeClass('open').attr('aria-expanded', 'false');
            }
        });

        // -------------------------------------------------------------
        // 4. Reset & Clear Search Filters
        // -------------------------------------------------------------
        $(document).on('click', '.zabun-btn-clear, .zabun-link-reset', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            var actionUrl = $form.attr('action') || window.location.pathname;

            // Clear text and numeric inputs
            $form.find('input[type="text"], input[type="number"]').val('');
            
            // Reset dropdown selects
            $form.find('select').each(function() {
                $(this).prop('selectedIndex', 0).val('');
            });

            // Reset tab to first tab
            var $statusTabs = $form.find('.zabun-status-tabs');
            $statusTabs.find('button').removeClass('active');
            var $firstTab = $statusTabs.find('button:first-child');
            $firstTab.addClass('active');
            $form.find('input[name="zabun_status"]').val($firstTab.data('status') || 'for_sale');

            // Reset button groups to 'Any'
            $form.find('.zabun-btngroup').each(function() {
                var $g = $(this);
                $g.find('button').removeClass('active');
                $g.find('button[data-val=""], button:first-child').addClass('active');
                var groupName = $g.data('group');
                $form.find('input[name="zabun_' + groupName + '"]').val('');
            });

            // If URL already has active search parameters, reload clean page to immediately refresh results
            if (window.location.search && window.location.search.indexOf('zabun_') !== -1) {
                window.location.href = actionUrl;
            }
        });

        // -------------------------------------------------------------
        // 5. Fullscreen Photo Lightbox Modal
        // -------------------------------------------------------------
        var activeGalleryImages = [];
        var currentLightboxIndex = 0;

        function openLightbox(images, startIndex) {
            if (!images || !images.length) return;
            activeGalleryImages = images;
            currentLightboxIndex = startIndex >= 0 && startIndex < images.length ? startIndex : 0;
            updateLightbox();
            $('#zabun-lightbox').fadeIn(200).attr('aria-hidden', 'false');
            $('body').addClass('zabun-lightbox-open');
        }

        function closeLightbox() {
            $('#zabun-lightbox').fadeOut(200).attr('aria-hidden', 'true');
            $('body').removeClass('zabun-lightbox-open');
        }

        function updateLightbox() {
            var total = activeGalleryImages.length;
            if (!total) return;
            var src = activeGalleryImages[currentLightboxIndex];
            $('#zabun-lightbox .zabun-lightbox-img').attr('src', src);
            $('#zabun-lightbox .curr-index').text(currentLightboxIndex + 1);
            $('#zabun-lightbox .total-count').text(total);

            // Highlight active thumbnail
            $('#zabun-lightbox .zabun-thumb').removeClass('active');
            var $activeThumb = $('#zabun-lightbox .zabun-thumb[data-index="' + currentLightboxIndex + '"]');
            $activeThumb.addClass('active');

            // Scroll thumbnail into view
            if ($activeThumb.length) {
                var $container = $('#zabun-lightbox .zabun-lightbox-thumbs');
                if ($container.length && $container[0].scrollWidth > $container.innerWidth()) {
                    var scrollLeft = $activeThumb.position().left + $container.scrollLeft() - ($container.width() / 2) + ($activeThumb.outerWidth() / 2);
                    $container.animate({ scrollLeft: scrollLeft }, 150);
                }
            }
        }

        // Open Lightbox on Gallery Image, Overlay, or Count Badge click
        $(document).on('click', '.zabun-detail-gallery img, .zabun-detail-gallery .gallery-more-overlay, .zabun-detail-gallery .photo-count-badge', function(e) {
            e.preventDefault();
            var $gallery = $(this).closest('.zabun-detail-gallery');
            var rawData = $gallery.attr('data-images');
            var images = [];
            try {
                images = JSON.parse(rawData);
            } catch (err) {
                images = [];
            }
            if (!images || !images.length) {
                $gallery.find('img').each(function() {
                    var s = $(this).attr('src');
                    if (s && images.indexOf(s) === -1) {
                        images.push(s);
                    }
                });
            }
            var clickedIndex = parseInt($(this).attr('data-index'), 10);
            if (isNaN(clickedIndex)) {
                clickedIndex = 0;
            }
            openLightbox(images, clickedIndex);
        });

        // Close Lightbox
        $(document).on('click', '.zabun-lightbox-close, .zabun-lightbox-overlay', function(e) {
            e.preventDefault();
            closeLightbox();
        });

        // Next / Prev navigation
        $(document).on('click', '.zabun-lightbox-next', function(e) {
            e.preventDefault();
            if (activeGalleryImages.length <= 1) return;
            currentLightboxIndex = (currentLightboxIndex + 1) % activeGalleryImages.length;
            updateLightbox();
        });

        $(document).on('click', '.zabun-lightbox-prev', function(e) {
            e.preventDefault();
            if (activeGalleryImages.length <= 1) return;
            currentLightboxIndex = (currentLightboxIndex - 1 + activeGalleryImages.length) % activeGalleryImages.length;
            updateLightbox();
        });

        // Thumbnail Click in Lightbox
        $(document).on('click', '.zabun-lightbox-thumbs .zabun-thumb', function(e) {
            e.preventDefault();
            var idx = parseInt($(this).attr('data-index'), 10);
            if (!isNaN(idx)) {
                currentLightboxIndex = idx;
                updateLightbox();
            }
        });

        // Keyboard navigation (Esc, Left, Right)
        $(document).on('keydown', function(e) {
            if ($('#zabun-lightbox').is(':visible')) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    closeLightbox();
                } else if (e.key === 'ArrowRight' || e.keyCode === 39) {
                    $('.zabun-lightbox-next').trigger('click');
                } else if (e.key === 'ArrowLeft' || e.keyCode === 37) {
                    $('.zabun-lightbox-prev').trigger('click');
                }
            }
        });
    });
})(jQuery);
