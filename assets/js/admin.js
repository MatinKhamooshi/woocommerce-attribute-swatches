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
    });
    
})(jQuery);