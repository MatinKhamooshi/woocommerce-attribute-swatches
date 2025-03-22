<?php
/**
 * Attribute Settings Class
 * 
 * Handles the display type settings for attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCACP_Attribute_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add display type field to attribute add form
        add_action( 'woocommerce_after_add_attribute_fields', array( $this, 'add_attribute_display_type_field' ) );
        
        // Add display type field to attribute edit form
        add_action( 'woocommerce_after_edit_attribute_fields', array( $this, 'edit_attribute_display_type_field' ) );
        
        // Save display type when attribute is added or edited
        add_action( 'woocommerce_attribute_added', array( $this, 'save_attribute_display_type' ), 10, 2 );
        add_action( 'woocommerce_attribute_updated', array( $this, 'save_attribute_display_type' ), 10, 2 );
    }
    
    /**
     * Add display type field to attribute add form
     */
    public function add_attribute_display_type_field() {
        ?>
        <div class="form-field">
            <label for="attribute_display_type"><?php esc_html_e( 'Display Type', 'wc-attribute-color-picker' ); ?></label>
            <select name="attribute_display_type" id="attribute_display_type">
                <option value="default"><?php esc_html_e( 'Default', 'wc-attribute-color-picker' ); ?></option>
                <option value="color"><?php esc_html_e( 'Color', 'wc-attribute-color-picker' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Determines how this attribute is displayed on product pages.', 'wc-attribute-color-picker' ); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add display type field to attribute edit form
     */
    public function edit_attribute_display_type_field() {
        // Get attribute ID from URL
        $attribute_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
        
        // Get attribute display type
        $display_type = get_option( 'wc_attribute_display_type_' . $attribute_id, 'default' );
        
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="attribute_display_type"><?php esc_html_e( 'Display Type', 'wc-attribute-color-picker' ); ?></label>
            </th>
            <td>
                <select name="attribute_display_type" id="attribute_display_type">
                    <option value="default" <?php selected( $display_type, 'default' ); ?>><?php esc_html_e( 'Default', 'wc-attribute-color-picker' ); ?></option>
                    <option value="color" <?php selected( $display_type, 'color' ); ?>><?php esc_html_e( 'Color', 'wc-attribute-color-picker' ); ?></option>
                </select>
                <p class="description"><?php esc_html_e( 'Determines how this attribute is displayed on product pages.', 'wc-attribute-color-picker' ); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save attribute display type
     */
    public function save_attribute_display_type( $attribute_id, $data ) {
        if ( isset( $_POST['attribute_display_type'] ) ) {
            $display_type = sanitize_text_field( $_POST['attribute_display_type'] );
            update_option( 'wc_attribute_display_type_' . $attribute_id, $display_type );
        }
    }
}
