<?php
/**
 * Plugin Name: Taverne Meta
 * Description: Data layer for plate metadata, custom tables (states/impressions), and computed fields. No UI.
 * Version: 1.0
 * Author: Pol Taverne
 */

if (!defined('ABSPATH')) exit;

// Constants
define('TAVERNE_META_VERSION', '1.0');
define('TAVERNE_META_PATH', plugin_dir_path(__FILE__));
define('TAVERNE_META_URL', plugin_dir_url(__FILE__));

// Includes
require_once TAVERNE_META_PATH . 'includes/database.php';
require_once TAVERNE_META_PATH . 'includes/meta-fields.php';

// Activation: Create tables
register_activation_hook(__FILE__, 'taverne_meta_activate');
function taverne_meta_activate() {
    taverne_create_custom_tables();
    flush_rewrite_rules();
}

// Deactivation: Just flush
register_deactivation_hook(__FILE__, 'taverne_meta_deactivate');
function taverne_meta_deactivate() {
    flush_rewrite_rules();
}
