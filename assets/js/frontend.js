(function($) {
    'use strict';

    $(document).ready(function() {
        // Thumbnail switcher in Single Property Detail view
        $('.zabun-gallery-thumbs').on('click', '.zabun-thumb-item', function(e) {
            e.preventDefault();
            var $thumb = $(this);
            var $gallery = $thumb.closest('.zabun-detail-gallery');
            var fullUrl = $thumb.data('full-img') || $thumb.find('img').attr('src');

            $gallery.find('.zabun-gallery-main img').attr('src', fullUrl);
            $gallery.find('.zabun-thumb-item').removeClass('active');
            $thumb.addClass('active');
        });
    });
})(jQuery);
