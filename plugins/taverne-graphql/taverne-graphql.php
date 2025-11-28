<?php
/**
 * Plugin Name: Taverne GraphQL
 * Plugin URI: https://poltaverne.nl
 * Description: Custom GraphQL connector for Pol Taverne's printmaking catalog. Exposes plates, states, impressions, research, and teaching content for headless Next.js frontend.
 * Version: 1.0.0
 * Author: LFMC Agency / Louis
 * Author URI: https://lfmc.agency
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * Text Domain: taverne-graphql
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'TAVERNE_GRAPHQL_VERSION', '1.0.0' );
define( 'TAVERNE_GRAPHQL_PATH', plugin_dir_path( __FILE__ ) );
define( 'TAVERNE_GRAPHQL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check if WPGraphQL is active
 */
function taverne_graphql_check_dependencies() {
    if ( ! class_exists( 'WPGraphQL' ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><strong>Taverne GraphQL</strong> requires WPGraphQL to be installed and activated.</p>
            </div>
            <?php
        });
        return false;
    }
    
    // Check if required plugins are active
    if ( ! function_exists( 'taverne_get_states' ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><strong>Taverne GraphQL</strong> requires Taverne Meta plugin to be installed and activated.</p>
            </div>
            <?php
        });
        return false;
    }
    
    return true;
}

/**
 * Initialize the plugin
 */
function taverne_graphql_init() {
    // Check dependencies
    if ( ! taverne_graphql_check_dependencies() ) {
        return;
    }
    
    // Load includes
    require_once TAVERNE_GRAPHQL_PATH . 'includes/register-types.php';
}
add_action( 'plugins_loaded', 'taverne_graphql_init', 20 );

/**
 * Activation hook
 */
function taverne_graphql_activate() {
    // Check WPGraphQL
    if ( ! class_exists( 'WPGraphQL' ) ) {
        wp_die( 
            'Taverne GraphQL requires WPGraphQL to be installed and activated.',
            'Plugin Dependency Error',
            array( 'back_link' => true )
        );
    }
}
register_activation_hook( __FILE__, 'taverne_graphql_activate' );
