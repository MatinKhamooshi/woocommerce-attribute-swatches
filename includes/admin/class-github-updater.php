<?php
/**
 * GitHub Updater Class
 * 
 * Handles plugin updates from GitHub repository
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCACP_GitHub_Updater {
    
    private $slug;
    private $plugin_data;
    private $username;
    private $repository;
    private $plugin_file;
    private $github_api_result;
    private $access_token;
    
    /**
     * Constructor
     */
    public function __construct( $plugin_file, $github_username, $github_repository, $access_token = '' ) {
        $this->plugin_file = $plugin_file;
        $this->username = $github_username;
        $this->repository = $github_repository;
        $this->access_token = $access_token;
        
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'set_transient' ) );
        add_filter( 'plugins_api', array( $this, 'set_plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
    }
    
    /**
     * Get plugin data
     */
    private function init_plugin_data() {
        $this->slug = plugin_basename( $this->plugin_file );
        $this->plugin_data = get_plugin_data( $this->plugin_file );
    }
    
    /**
     * Get repository info from GitHub
     */
    private function get_repository_info() {
        if ( is_null( $this->github_api_result ) ) {
            $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );
            
            // If we have an access token, use it
            if ( ! empty( $this->access_token ) ) {
                $request_uri = add_query_arg( array( 'access_token' => $this->access_token ), $request_uri );
            }
            
            $response = wp_remote_get( $request_uri );
            
            if ( is_wp_error( $response ) ) {
                return false;
            }
            
            $response_code = wp_remote_retrieve_response_code( $response );
            if ( 200 !== $response_code ) {
                return false;
            }
            
            $result = json_decode( wp_remote_retrieve_body( $response ) );
            if ( ! empty( $result ) ) {
                $this->github_api_result = $result;
            }
        }
        
        return $this->github_api_result;
    }
    
    /**
     * Check if update is available
     */
    public function set_transient( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        $this->init_plugin_data();
        
        $repository_info = $this->get_repository_info();
        if ( false === $repository_info ) {
            return $transient;
        }
        
        if ( empty( $repository_info->tag_name ) ) {
            return $transient;
        }
        
        // Strip 'v' prefix from version if it exists
        $version = preg_replace( '/^v/', '', $repository_info->tag_name );
        
        // Check if new version is higher than current
        if ( version_compare( $version, $this->plugin_data['Version'], '>' ) ) {
            $package = $repository_info->zipball_url;
            
            // Add access token if present
            if ( ! empty( $this->access_token ) ) {
                $package = add_query_arg( array( 'access_token' => $this->access_token ), $package );
            }
            
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $version;
            $obj->url = $this->plugin_data['PluginURI'];
            $obj->package = $package;
            $obj->tested = isset( $repository_info->tested ) ? $repository_info->tested : WP_CORE_STABLE_BRANCH;
            $obj->requires = isset( $repository_info->requires ) ? $repository_info->requires : null;
            $obj->requires_php = isset( $repository_info->requires_php ) ? $repository_info->requires_php : null;
            
            $transient->response[ $this->slug ] = $obj;
        }
        
        return $transient;
    }
    
    /**
     * Get plugin info for update screen
     */
    public function set_plugin_info( $result, $action, $args ) {
        if ( ! isset( $args->slug ) || $args->slug !== $this->slug ) {
            return $result;
        }
        
        $this->init_plugin_data();
        
        $repository_info = $this->get_repository_info();
        if ( false === $repository_info ) {
            return $result;
        }
        
        $result = new stdClass();
        $result->slug = $this->slug;
        $result->plugin_name = $this->plugin_data['Name'];
        $result->name = $this->plugin_data['Name'];
        $result->version = $repository_info->tag_name;
        $result->author = $this->plugin_data['AuthorName'];
        $result->homepage = $this->plugin_data['PluginURI'];
        
        // This is the main description for the update info
        $result->sections = array(
            'description' => $this->plugin_data['Description'],
            'changelog' => nl2br( $repository_info->body )
        );
        
        // Download URL (zip file)
        $download_url = $repository_info->zipball_url;
        if ( ! empty( $this->access_token ) ) {
            $download_url = add_query_arg( array( 'access_token' => $this->access_token ), $download_url );
        }
        
        $result->download_link = $download_url;
        
        return $result;
    }
    
    /**
     * Fix plugin folder after update
     */
    public function post_install( $true, $hook_extra, $result ) {
        $this->init_plugin_data();
        
        global $wp_filesystem;
        
        // Move original directory back in place - GitHub archives have an extra directory
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname( $this->slug );
        $wp_filesystem->move( $result['destination'], $plugin_folder );
        $result['destination'] = $plugin_folder;
        
        return $result;
    }
} 