<?php
/**
 * Term Meta Class
 * 
 * Handles color picker fields for attribute terms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCACP_Term_Meta {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'init_attribute_color_fields' ) );
    }
    
    /**
     * Initialize color picker fields for attribute terms
     */
    public function init_attribute_color_fields() {
        global $pagenow;
        
        // Only run on term.php or edit-tags.php pages
        if ( ! in_array( $pagenow, array( 'term.php', 'edit-tags.php' ) ) ) {
            return;
        }
        
        // Get the taxonomy from the URL
        $taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( $_GET['taxonomy'] ) : '';
        if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
            return;
        }
        
        // Check if this is a product attribute taxonomy
        if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
            return;
        }
        
        // Get the attribute ID from the taxonomy name
        $attribute_name = str_replace( 'pa_', '', $taxonomy );
        $attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
        
        if ( $attribute_id ) {
            // Check if this attribute has display type set to 'color'
            $display_type = get_option( 'wc_attribute_display_type_' . $attribute_id, 'default' );
            
            if ( 'color' === $display_type ) {
                // Add color picker field to add form
                add_action( $taxonomy . '_add_form_fields', array( $this, 'add_color_field_to_term' ) );
                
                // Add color picker field to edit form
                add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_color_field_to_term' ), 10, 2 );
                
                // Save color value
                add_action( 'created_' . $taxonomy, array( $this, 'save_color_field_to_term' ), 10, 2 );
                add_action( 'edited_' . $taxonomy, array( $this, 'save_color_field_to_term' ), 10, 2 );
                
                // Add inline script to enable color picker
                add_action( 'admin_print_footer_scripts', array( $this, 'print_color_picker_script' ), 25 );
            }
            
            // Add image field to add form
            add_action( $taxonomy . '_add_form_fields', array( $this, 'add_image_field_to_term' ) );
            
            // Add image field to edit form
            add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_image_field_to_term' ), 10, 2 );
            
            // Save image value
            add_action( 'created_' . $taxonomy, array( $this, 'save_image_field_to_term' ), 10, 2 );
            add_action( 'edited_' . $taxonomy, array( $this, 'save_image_field_to_term' ), 10, 2 );
        }
    }
    
    /**
     * Add color picker field to add term form
     */
    public function add_color_field_to_term() {
        ?>
        <div class="form-field term-color-wrap">
            <label for="term_color"><?php esc_html_e( 'Color', 'wc-attribute-swatches' ); ?></label>
            <input name="term_color" id="term_color" type="text" value="" class="colorpicker-field" />
            <p class="description"><?php esc_html_e( 'Select a color for this attribute.', 'wc-attribute-swatches' ); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add color picker field to edit term form
     */
    public function edit_color_field_to_term( $term, $taxonomy ) {
        $term_color = get_term_meta( $term->term_id, '_product_attribute_color', true );
        ?>
        <tr class="form-field term-color-wrap">
            <th scope="row"><label for="term_color"><?php esc_html_e( 'Color', 'wc-attribute-swatches' ); ?></label></th>
            <td>
                <input name="term_color" id="term_color" type="text" value="<?php echo esc_attr( $term_color ); ?>" class="colorpicker-field" />
                <p class="description"><?php esc_html_e( 'Select a color for this attribute.', 'wc-attribute-swatches' ); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save term color value
     */
    public function save_color_field_to_term( $term_id, $tt_id ) {
        if ( isset( $_POST['term_color'] ) ) {
            update_term_meta( $term_id, '_product_attribute_color', sanitize_text_field( $_POST['term_color'] ) );
        }
    }
    
    /**
     * Print color picker script
     */
    public function print_color_picker_script() {
        ?>
        <script>
        jQuery(document).ready(function($){
            $('.colorpicker-field').wpColorPicker();
        });
        </script>
        <?php
    }
    
    /**
     * Add image field to attribute add form
     */
    public function add_image_field_to_term( $taxonomy ) {
        ?>
        <div class="form-field">
            <label for="attribute_image"><?php esc_html_e( 'Attribute Image', 'woocommerce-attribute-swatches' ); ?></label>
            <input type="hidden" id="attribute_image" name="attribute_image" value="" />
            <button class="button upload_image_button">Upload/Add Image</button>
            <button class="button remove_image_button">Remove Image</button>
        </div>
        <?php
    }
    
    /**
     * Add image field to attribute edit form
     */
    public function edit_image_field_to_term( $term, $taxonomy ) {
        $image_id = get_term_meta( $term->term_id, 'attribute_image', true );
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="attribute_image"><?php esc_html_e( 'Attribute Image', 'woocommerce-attribute-swatches' ); ?></label>
            </th>
            <td>
                <input type="hidden" id="attribute_image" name="attribute_image" value="<?php echo esc_attr( $image_id ); ?>" />
                <button class="button upload_image_button">Upload/Add Image</button>
                <button class="button remove_image_button">Remove Image</button>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save image field data
     */
    public function save_image_field_to_term( $term_id, $tt_id ) {
        if ( isset( $_POST['attribute_image'] ) ) {
            update_term_meta( $term_id, 'attribute_image', sanitize_text_field( $_POST['attribute_image'] ) );
        }
    }
}
