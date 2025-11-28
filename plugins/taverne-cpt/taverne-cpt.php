<?php
/**
 * Plugin Name: Taverne CPT
 * Plugin URI: https://poltaverne.com
 * Description: Lightweight schema foundation. Registers Plate CPT + 9 taxonomies + CSV term importer with metadata. Pure data model, zero UI bloat.
 * Version: 1.0.0
 * Author: Louis Faucher
 * Author URI: https://poltaverne.com
 * Requires PHP: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TAVERNE_CPT_VERSION', '1.0.0');
define('TAVERNE_CPT_PATH', plugin_dir_path(__FILE__));
define('TAVERNE_CPT_URL', plugin_dir_url(__FILE__));

// Include core files
require_once TAVERNE_CPT_PATH . 'includes/post-types.php';
require_once TAVERNE_CPT_PATH . 'includes/taxonomies.php';
require_once TAVERNE_CPT_PATH . 'includes/csv-importer.php';

// Disable block editor for plate CPT
add_filter('use_block_editor_for_post_type', 'taverne_disable_gutenberg_for_plate', 10, 2);
function taverne_disable_gutenberg_for_plate($use_block_editor, $post_type) {
    if ($post_type === 'plate') {
        return false;
    }
    return $use_block_editor;
}

// Activation hook - flush rewrite rules
register_activation_hook(__FILE__, 'taverne_cpt_activate');
function taverne_cpt_activate() {
    // Trigger post type registration
    taverne_register_post_types();
    taverne_register_taxonomies();
    
    // Flush permalinks
    flush_rewrite_rules();
}

// Deactivation hook - flush rewrite rules
register_deactivation_hook(__FILE__, 'taverne_cpt_deactivate');
function taverne_cpt_deactivate() {
    flush_rewrite_rules();
}
