<?php
/**
 * Variation Swatches Class
 * 
 * Handles the display of color swatches on the frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCACP_Variation_Swatches {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Replace dropdown with color swatches for variation attributes
        add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'variation_attribute_options_html' ), 10, 2 );
    }
    
    /**
     * Replace dropdown with color swatches or labels
     */
    public function variation_attribute_options_html( $html, $args ) {
        // Get attribute info
        $attribute = $args['attribute'];
        
        // Check if this is a product attribute taxonomy
        if ( 0 !== strpos( $attribute, 'pa_' ) ) {
            return $html; // Return original HTML for custom attributes
        }
        
        // Get the attribute ID
        $attribute_name = str_replace( 'pa_', '', $attribute );
        $attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
        
        // Check if this attribute has a special display type set
        $display_type = get_option( 'wc_attribute_display_type_' . $attribute_id, 'default' );
        
        // Only modify if it's a color or label attribute
        if ( 'default' === $display_type ) {
            return $html; // Return original dropdown for default display type
        }
        
        // Extract arguments
        $options = $args['options'];
        $product = $args['product'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
        $id = $args['id'] ? $args['id'] : sanitize_title( $attribute );
        $selected = $args['selected'] ? $args['selected'] : '';
        
        // Output container div based on display type
        $container_class = 'color' === $display_type ? 'variation-colors' : 'variation-labels';
        $output = '<div class="' . esc_attr($container_class) . '">';
        
        if ( empty( $options ) ) {
            return $html; // Return original dropdown if no options
        }
        
        if ( $product && taxonomy_exists( $attribute ) ) {
            // Get terms for this attribute
            $terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
            
            foreach ( $terms as $term ) {
                if ( in_array( $term->slug, $options, true ) ) {
                    $selected_class = ( $selected == $term->slug ) ? 'selected' : '';
                    
                    if ( 'color' === $display_type ) {
                        // Get the color value for this term
                        $color = get_term_meta( $term->term_id, '_product_attribute_color', true );
                        
                        // Output color swatch
                        $output .= sprintf(
                            '<label class="color-swatch %s" title="%s" data-value="%s" style="background-color:%s;">
                                <input type="radio" name="%s" value="%s" %s class="swatch-input" />
                                <span class="swatch-label">%s</span>
                            </label>',
                            esc_attr($selected_class),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            esc_attr($color),
                            esc_attr($name),
                            esc_attr($term->slug),
                            checked($selected, $term->slug, false),
                            esc_html($term->name)
                        );
                    } else if ( 'label' === $display_type ) {
                        // Output text label
                        $output .= sprintf(
                            '<label class="label-swatch %s" title="%s" data-value="%s">
                                <input type="radio" name="%s" value="%s" %s class="swatch-input" />
                                <span class="label-text">%s</span>
                            </label>',
                            esc_attr($selected_class),
                            esc_attr($term->name),
                            esc_attr($term->slug),
                            esc_attr($name),
                            esc_attr($term->slug),
                            checked($selected, $term->slug, false),
                            esc_html($term->name)
                        );
                    }
                }
            }
        }
        
        $output .= '</div>';
        
        // Add hidden default dropdown as fallback and for form submission
        $output .= '<div class="variation-default" style="display:none;">' . $html . '</div>';
        
        return $output;
    }
}
