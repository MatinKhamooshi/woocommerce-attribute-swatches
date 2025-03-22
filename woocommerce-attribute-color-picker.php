<?php
/**
 * Plugin Name: Woocommerce Attribute Color Picker
 * Plugin URI:  https://github.com/MatinKhamooshi/woocommerce-attribute-color-picker
 * Description: Add color picker field to color attributes (pa_colors) in WooCommerce.
 * Version:     1.0
 * Author:      Matin Khamooshi
 * Author URI:  https://matinkhamooshi.ir
 * License:     GPL2
 * Text Domain: wc-attribute-color-picker
 * Domain Path: /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin textdomain.
 */
function wcacp_load_textdomain() {
	load_plugin_textdomain( 'wc-attribute-color-picker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wcacp_load_textdomain' );

/**
 * Add color picker field to add term form for taxonomy pa_colors
 */
add_action( 'pa_colors_add_form_fields', 'wcacp_add_color_field_to_pa_colors' );
function wcacp_add_color_field_to_pa_colors() {
	?>
	<div class="form-field term-color-wrap">
		<label for="term_color"><?php esc_html_e( 'Color', 'wc-attribute-color-picker' ); ?></label>
		<input name="term_color" id="term_color" type="text" value="" class="colorpicker-field" />
		<p class="description"><?php esc_html_e( 'Select a color for this attribute.', 'wc-attribute-color-picker' ); ?></p>
	</div>
	<?php
}

/**
 * Add color picker field to edit term form for taxonomy pa_colors
 */
add_action( 'pa_colors_edit_form_fields', 'wcacp_edit_color_field_to_pa_colors', 10, 2 );
function wcacp_edit_color_field_to_pa_colors( $term, $taxonomy ) {
	$term_color = get_term_meta( $term->term_id, '_product_attribute_color', true );
	?>
	<tr class="form-field term-color-wrap">
		<th scope="row"><label for="term_color"><?php esc_html_e( 'Color', 'wc-attribute-color-picker' ); ?></label></th>
		<td>
			<input name="term_color" id="term_color" type="text" value="<?php echo esc_attr( $term_color ); ?>" class="colorpicker-field" />
			<p class="description"><?php esc_html_e( 'Select a color for this attribute.', 'wc-attribute-color-picker' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Save color picker field value when creating or editing a term
 */
add_action( 'created_pa_colors', 'wcacp_save_color_field_to_pa_colors', 10, 2 );
add_action( 'edited_pa_colors', 'wcacp_save_color_field_to_pa_colors', 10, 2 );
function wcacp_save_color_field_to_pa_colors( $term_id, $tt_id ) {
	if ( isset( $_POST['term_color'] ) ) {
		update_term_meta( $term_id, '_product_attribute_color', sanitize_text_field( $_POST['term_color'] ) );
	}
}

/**
 * Load wp-color-picker on admin pages for taxonomy
 */
add_action( 'admin_enqueue_scripts', 'wcacp_enqueue_color_picker' );
function wcacp_enqueue_color_picker( $hook_suffix ) {
	// Only run on term edit or add new term pages
	if ( ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php' ) ) ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	
	// Add inline script to activate color picker
	add_action( 'admin_print_footer_scripts', 'wcacp_print_color_picker_script', 25 );
}

function wcacp_print_color_picker_script() {
	?>
	<script>
	jQuery(document).ready(function($){
		$('.colorpicker-field').wpColorPicker();
	});
	</script>
	<?php
}