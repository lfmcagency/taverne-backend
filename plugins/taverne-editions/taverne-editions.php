<?php
/**
 * Plugin Name: Taverne Editions
 * Description: Admin UI for managing plates, states, and impressions. Mobile-first card interface.
 * Version: 1.0
 * Author: Pol Taverne
 */

if (!defined('ABSPATH')) exit;

// Constants
define('TAVERNE_EDITIONS_VERSION', '1.0');
define('TAVERNE_EDITIONS_PATH', plugin_dir_path(__FILE__));
define('TAVERNE_EDITIONS_URL', plugin_dir_url(__FILE__));

// Includes
require_once TAVERNE_EDITIONS_PATH . 'includes/meta-boxes.php';
require_once TAVERNE_EDITIONS_PATH . 'includes/ajax-handlers.php';

/**
 * Enqueue CSS/JS assets only on plate edit/new screens
 * Includes media uploader, AJAX nonce, and plate_id for JavaScript
 */
add_action('admin_enqueue_scripts', 'taverne_editions_enqueue_assets');
function taverne_editions_enqueue_assets($hook) {
    // Restrict to plate edit/new screens
    if (!in_array($hook, array('post.php', 'post-new.php'))) return;
    if (get_post_type() !== 'plate') return;

    // Enqueue CSS
    wp_enqueue_style(
        'taverne-editions-admin',
        TAVERNE_EDITIONS_URL . 'assets/css/admin.css',
        array(),
        TAVERNE_EDITIONS_VERSION
    );

    // Enqueue media uploader (required for wp.media)
    wp_enqueue_media();

    // Enqueue JS with dependencies
    wp_enqueue_script(
        'taverne-editions-admin',
        TAVERNE_EDITIONS_URL . 'assets/js/admin.js',
        array('jquery', 'wp-editor'),
        TAVERNE_EDITIONS_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('taverne-editions-admin', 'taverneEditions', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('taverne_editions_nonce'),
        'plate_id' => isset($_GET['post']) ? intval($_GET['post']) : 0,
    ));
}

/**
 * Remove default WordPress editor for plates
 * Plates use custom meta box UI instead of standard content editor
 */
add_action('init', 'taverne_remove_plate_editor');
function taverne_remove_plate_editor() {
    remove_post_type_support('plate', 'editor');
}

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'taverne_editions_activate');
function taverne_editions_activate() {
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'taverne_editions_deactivate');
function taverne_editions_deactivate() {
    flush_rewrite_rules();
}
