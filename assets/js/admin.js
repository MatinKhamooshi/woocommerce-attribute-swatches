/**
 * Admin scripts for color picker settings
 */
(function($) {
    'use strict';
    
    // When document is ready
    $(document).ready(function() {
        // Initialize color pickers
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.colorpicker-field').wpColorPicker();
        }

        // Handle image upload
        $('.upload_image_button').on('click', function(e) {
            e.preventDefault();
            var button = $(this);
            var custom_uploader = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                button.prev('input').val(attachment.id);
            }).open();
        });

        // Handle image removal
        $('.remove_image_button').on('click', function(e) {
            e.preventDefault();
            $(this).prev('input').val('');
        });
    });
    
})(jQuery);