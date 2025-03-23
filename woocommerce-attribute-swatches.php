<?php
/**
 * Plugin Name: WooCommerce Attribute Swatches
 * Plugin URI:  https://github.com/MatinKhamooshi/woocommerce-attribute-swatches
 * Description: Add color and label swatches to WooCommerce product attributes for better variation selection experience.
 * Version:     3.0
 * Author:      Matin Khamooshi
 * Author URI:  https://matinkhamooshi.ir
 * License:     GPL2
 * Text Domain: wc-attribute-swatches
 * Domain Path: /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'WCACP_PLUGIN_FILE', __FILE__ );
define( 'WCACP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCACP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCACP_PLUGIN_VERSION', '3.0' );

/**
 * Load plugin textdomain.
 */
function wcacp_load_textdomain() {
	// Make sure the locale is set properly
	$locale = determine_locale();
	
	// Unload existing translations first (if any)
	unload_textdomain( 'wc-attribute-swatches' );
	
	// Load MO file with standard name (without plugin prefix)
	$mofile = $locale . '.mo';
	$path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	
	// Try to load from the languages directory first (WordPress/site)
	if ( ! load_textdomain( 'wc-attribute-swatches', WP_LANG_DIR . '/plugins/' . $mofile ) ) {
		// Then try from the plugin's languages directory
		load_textdomain( 'wc-attribute-swatches', plugin_dir_path( __FILE__ ) . 'languages/' . $mofile );
	}
}
add_action( 'plugins_loaded', 'wcacp_load_textdomain', 1 );

/**
 * Translate plugin metadata in plugins list
 */
function wcacp_translate_plugin_metadata( $translated, $original, $domain ) {
    $plugin_name = 'WooCommerce Attribute Swatches';
    $plugin_desc = 'Add color and label swatches to WooCommerce product attributes for better variation selection experience.';
    $plugin_author = 'Matin Khamooshi';
    
    // Check if we're looking at our plugin data
    if ( $original === $plugin_name && $domain !== 'wc-attribute-swatches' ) {
        return __( 'WooCommerce Attribute Swatches', 'wc-attribute-swatches' );
    }
    
    if ( $original === $plugin_desc && $domain !== 'wc-attribute-swatches' ) {
        return __( 'Add color and label swatches to WooCommerce product attributes for better variation selection experience.', 'wc-attribute-swatches' );
    }
    
    if ( $original === $plugin_author && $domain !== 'wc-attribute-swatches' ) {
        return __( 'Matin Khamooshi', 'wc-attribute-swatches' );
    }
    
    return $translated;
}
add_filter( 'gettext', 'wcacp_translate_plugin_metadata', 10, 3 );

/**
 * Check if WooCommerce is active
 */
function wcacp_is_woocommerce_active() {
    $active_plugins = (array) get_option( 'active_plugins', array() );
    if ( is_multisite() ) {
        $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
    }
    return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
}

/**
 * Initialize the plugin
 */
function wcacp_init() {
    // Check if WooCommerce is active
    if ( ! wcacp_is_woocommerce_active() ) {
        add_action( 'admin_notices', 'wcacp_woocommerce_missing_notice' );
        return;
    }
    
    // Include required files
    require_once WCACP_PLUGIN_DIR . 'includes/admin/class-attribute-settings.php';
    require_once WCACP_PLUGIN_DIR . 'includes/admin/class-term-meta.php';
    require_once WCACP_PLUGIN_DIR . 'includes/frontend/class-variation-swatches.php';
    
    // Initialize classes
    new WCACP_Attribute_Settings();
    new WCACP_Term_Meta();
    new WCACP_Variation_Swatches();
    
    // Register scripts and styles
    add_action( 'wp_enqueue_scripts', 'wcacp_register_scripts' );
    add_action( 'admin_enqueue_scripts', 'wcacp_register_admin_scripts' );
}
add_action( 'plugins_loaded', 'wcacp_init', 10 );

/**
 * Admin notice for missing WooCommerce
 */
function wcacp_woocommerce_missing_notice() {
    ?>
    <div class="error notice">
        <p><?php esc_html_e( 'WooCommerce Attribute Color Picker requires WooCommerce to be installed and active.', 'wc-attribute-swatches' ); ?></p>
    </div>
    <?php
}

/**
 * Register frontend scripts and styles
 */
function wcacp_register_scripts() {
    wp_register_style( 'wcacp-style', WCACP_PLUGIN_URL . 'assets/css/style.css', array(), WCACP_PLUGIN_VERSION );
    wp_register_script( 'wcacp-script', WCACP_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), WCACP_PLUGIN_VERSION, true );
    
    wp_enqueue_style( 'wcacp-style' );
    wp_enqueue_script( 'wcacp-script' );
}

/**
 * Register admin scripts and styles
 */
function wcacp_register_admin_scripts( $hook_suffix ) {
    // Only load on needed pages
    if ( ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php', 'product_page_product_attributes' ) ) ) {
        return;
    }
    
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    
    wp_register_style( 'wcacp-admin-style', WCACP_PLUGIN_URL . 'assets/css/admin.css', array(), WCACP_PLUGIN_VERSION );
    wp_register_script( 'wcacp-admin-script', WCACP_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), WCACP_PLUGIN_VERSION, true );
    
    wp_enqueue_style( 'wcacp-admin-style' );
    wp_enqueue_script( 'wcacp-admin-script' );
}