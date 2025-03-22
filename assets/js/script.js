/**
 * Frontend scripts for color swatches and label swatches
 */
(function($) {
    'use strict';
    
    // When document is ready
    $(document).ready(function() {
        // Initialize swatches functionality
        initSwatches();
        
        // Re-initialize on variation events
        $(document).on('woocommerce_update_variation_values', initSwatches);
    });
    
    /**
     * Initialize swatches functionality
     */
    function initSwatches() {
        // Handle click on swatches
        $('.swatch-input').off('change').on('change', function() {
            var $this = $(this);
            var value = $this.val();
            var $swatchContainer = $this.closest('.variation-colors, .variation-labels');
            var dropdown = $swatchContainer.next('.variation-default').find('select');
            
            // Update dropdown value
            dropdown.val(value).trigger('change');
            
            // Update swatch selection
            $swatchContainer.find('.color-swatch, .label-swatch').removeClass('selected');
            $this.parent('.color-swatch, .label-swatch').addClass('selected');
        });
        
        // Highlight the selected swatch when loading the page
        $('.variation-colors, .variation-labels').each(function() {
            var selectedValue = $(this).next('.variation-default').find('select').val();
            if (selectedValue) {
                $(this).find('.swatch-input[value="' + selectedValue + '"]')
                       .prop('checked', true)
                       .parent('.color-swatch, .label-swatch')
                       .addClass('selected');
            }
        });
        
        // Ensure non-available variations are disabled
        syncAvailableSwatches();
    }
    
    /**
     * Sync available variations with swatches
     */
    function syncAvailableSwatches() {
        $('.variation-colors, .variation-labels').each(function() {
            var $swatchContainer = $(this);
            var $defaultSelect = $swatchContainer.next('.variation-default').find('select');
            
            // Disable all swatches that correspond to unavailable options
            $swatchContainer.find('.color-swatch, .label-swatch').each(function() {
                var $swatch = $(this);
                var value = $swatch.data('value');
                var $option = $defaultSelect.find('option[value="' + value + '"]');
                
                if ($option.length && !$option.prop('disabled')) {
                    $swatch.removeClass('disabled');
                    $swatch.find('input').prop('disabled', false);
                } else {
                    $swatch.addClass('disabled');
                    $swatch.find('input').prop('disabled', true);
                }
            });
        });
    }
    
})(jQuery);
