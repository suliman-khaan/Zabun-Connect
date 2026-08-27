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

        // 4. Reset Filters Link
        $(document).on('click', '.zabun-link-reset', function(e) {
            var $form = $(this).closest('form');
            setTimeout(function() {
                // Reset tab to first tab or all
                var $statusTabs = $form.find('.zabun-status-tabs');
                $statusTabs.find('button').removeClass('active');
                $statusTabs.find('button:first-child').addClass('active');
                $form.find('input[name="zabun_status"]').val($statusTabs.find('button:first-child').data('status') || '');

                // Reset button groups to 'Any'
                $form.find('.zabun-btngroup').each(function() {
                    var $g = $(this);
                    $g.find('button').removeClass('active');
                    $g.find('button[data-val=""], button:first-child').addClass('active');
                    var groupName = $g.data('group');
                    $form.find('input[name="zabun_' + groupName + '"]').val('');
                });
            }, 50);
        });
    });
})(jQuery);
