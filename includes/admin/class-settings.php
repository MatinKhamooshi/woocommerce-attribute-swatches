<?php
/**
 * Settings Class
 * 
 * Handles the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCACP_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add settings page
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        
        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    
    /**
     * Add settings page to menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            __( 'Attribute Swatches Settings', 'wc-attribute-swatches' ),
            __( 'Attribute Swatches', 'wc-attribute-swatches' ),
            'manage_options',
            'wc-attribute-swatches',
            array( $this, 'settings_page' )
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'wcacp_settings', 'wcacp_github_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ) );
    }
    
    /**
     * Settings page HTML
     */
    public function settings_page() {
        // Check capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Show success message when settings are updated
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'wcacp_messages',
                'wcacp_message',
                __( 'Settings Saved', 'wc-attribute-swatches' ),
                'updated'
            );
        }
        
        // Get GitHub token
        $github_token = get_option( 'wcacp_github_token', '' );
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php settings_errors( 'wcacp_messages' ); ?>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'wcacp_settings' ); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="wcacp_github_token"><?php esc_html_e( 'GitHub Access Token', 'wc-attribute-swatches' ); ?></label>
                        </th>
                        <td>
                            <input type="password" id="wcacp_github_token" name="wcacp_github_token" value="<?php echo esc_attr( $github_token ); ?>" class="regular-text" />
                            <p class="description">
                                <?php esc_html_e( 'Optional: Enter your GitHub personal access token to enable automatic updates from private repositories. Leave empty for public repositories.', 'wc-attribute-swatches' ); ?>
                                <br>
                                <a href="https://github.com/settings/tokens" target="_blank"><?php esc_html_e( 'Get a GitHub token', 'wc-attribute-swatches' ); ?></a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e( 'Plugin Information', 'wc-attribute-swatches' ); ?></h2>
            <p>
                <strong><?php esc_html_e( 'Version:', 'wc-attribute-swatches' ); ?></strong> <?php echo esc_html( WCACP_PLUGIN_VERSION ); ?><br>
                <strong><?php esc_html_e( 'GitHub Repository:', 'wc-attribute-swatches' ); ?></strong> 
                <a href="https://github.com/<?php echo esc_attr( WCACP_GITHUB_USERNAME ); ?>/<?php echo esc_attr( WCACP_GITHUB_REPOSITORY ); ?>" target="_blank">
                    <?php echo esc_html( WCACP_GITHUB_USERNAME . '/' . WCACP_GITHUB_REPOSITORY ); ?>
                </a>
            </p>
            
            <p>
                <?php esc_html_e( 'This plugin checks for updates from GitHub. If you have a GitHub personal access token, enter it above to enable updates from private repositories.', 'wc-attribute-swatches' ); ?>
            </p>
        </div>
        <?php
    }
} 