<?php
/**
 * Taverne Clean Theme - functions.php
 * 
 * Theme bootstrap: setup, enqueues, helpers for taverne-meta/cpt integration.
 * This file establishes THE PATTERN for the entire theme.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// =============================================================================
// CONSTANTS
// =============================================================================

define('TAVERNE_THEME_VERSION', '2.0.0');
define('TAVERNE_THEME_DIR', get_template_directory());
define('TAVERNE_THEME_URI', get_template_directory_uri());

// =============================================================================
// PLUGIN DEPENDENCY CHECK
// =============================================================================

add_action('after_setup_theme', 'taverne_check_dependencies');

function taverne_check_dependencies() {
    // Only check in admin to avoid frontend errors
    if (!is_admin()) return;
    
    $missing = [];
    
    if (!function_exists('taverne_get_states')) {
        $missing[] = 'Taverne Meta';
    }
    if (!post_type_exists('plate')) {
        $missing[] = 'Taverne CPT';
    }
    
    if (!empty($missing)) {
        add_action('admin_notices', function() use ($missing) {
            echo '<div class="notice notice-error"><p><strong>Taverne Clean Theme:</strong> Missing required plugins: ' . implode(', ', $missing) . '</p></div>';
        });
    }
}

// =============================================================================
// THEME SETUP
// =============================================================================

add_action('after_setup_theme', 'taverne_setup');

function taverne_setup() {
    // Theme supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails', ['plate', 'exhibition', 'page']);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    add_theme_support('custom-logo');
    
    // Navigation menus
    register_nav_menus([
        'primary' => __('Primary Navigation', 'taverne'),
        'footer'  => __('Footer Links', 'taverne'),
    ]);
    
    // Image sizes for plates
    add_image_size('plate-thumb', 400, 400, true);      // Grid thumbnails (cropped square)
    add_image_size('plate-medium', 800, 800, false);    // Card images (contained)
    add_image_size('plate-large', 1400, 1400, false);   // Product hero (contained)
    add_image_size('plate-hero', 1920, 1080, true);     // Homepage hero (cropped)
}

// =============================================================================
// ASSET ENQUEUES
// =============================================================================

add_action('wp_enqueue_scripts', 'taverne_enqueue_assets');

function taverne_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'taverne-style',
        get_stylesheet_uri(),
        [],
        TAVERNE_THEME_VERSION
    );
    
    // Main JavaScript (deferred)
    wp_enqueue_script(
        'taverne-main',
        TAVERNE_THEME_URI . '/assets/js/main.js',
        [],
        TAVERNE_THEME_VERSION,
        true
    );
    
    // Localize for AJAX
    wp_localize_script('taverne-main', 'taverneAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('taverne_filter_nonce'),
        'home_url' => home_url(),
    ]);
    
    // Product JavaScript (only on single plates/impressions)
    if (is_singular('plate') || get_query_var('impression_id')) {
        wp_enqueue_script(
            'taverne-product',
            TAVERNE_THEME_URI . '/assets/js/product.js',
            [],
            TAVERNE_THEME_VERSION,
            true
        );
    }
}

// Admin assets (only on plate screens)
add_action('admin_enqueue_scripts', 'taverne_admin_enqueue');

function taverne_admin_enqueue($hook) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'plate') {
        wp_enqueue_style(
            'taverne-admin',
            TAVERNE_THEME_URI . '/assets/css/admin.css',
            [],
            TAVERNE_THEME_VERSION
        );
    }
}

// =============================================================================
// IMPRESSION URL REWRITE RULES
// =============================================================================

add_action('init', 'taverne_add_rewrite_rules');

function taverne_add_rewrite_rules() {
    // /plates/{plate-slug}/impression/{impression-id}
    add_rewrite_rule(
        '^plates/([^/]+)/impression/([0-9]+)/?$',
        'index.php?plate_slug=$matches[1]&impression_id=$matches[2]',
        'top'
    );
}

add_filter('query_vars', 'taverne_register_query_vars');

function taverne_register_query_vars($vars) {
    $vars[] = 'plate_slug';
    $vars[] = 'impression_id';
    return $vars;
}

// Flush rewrite rules on theme activation
add_action('after_switch_theme', 'taverne_flush_rewrites');

function taverne_flush_rewrites() {
    taverne_add_rewrite_rules();
    flush_rewrite_rules();
}

// =============================================================================
// TEMPLATE ROUTING FOR IMPRESSIONS
// =============================================================================

add_filter('template_include', 'taverne_impression_template');

function taverne_impression_template($template) {
    $impression_id = get_query_var('impression_id');
    $plate_slug = get_query_var('plate_slug');
    
    if ($impression_id && $plate_slug) {
        $impression_template = locate_template('single-impression.php');
        if ($impression_template) {
            return $impression_template;
        }
    }
    
    return $template;
}

// =============================================================================
// HELPER FUNCTIONS: TAXONOMIES
// =============================================================================

/**
 * Get all plate taxonomy slugs
 * Used for filter generation and queries
 */
function taverne_get_plate_taxonomies() {
    return [
        'plate_technique',
        'plate_medium',
        'plate_study',
        'plate_motif',
        'plate_traces',
        'plate_palette',
        'plate_matrix',
        'plate_size',
        'plate_year',
        'plate_series', // Added: series as taxonomy
    ];
}

/**
 * Display taxonomy terms as pills/tags
 * 
 * @param int    $post_id  The plate post ID
 * @param string $taxonomy The taxonomy slug
 * @param string $label    Optional label prefix
 */
function taverne_display_taxonomy_terms($post_id, $taxonomy, $label = '') {
    $terms = get_the_terms($post_id, $taxonomy);
    
    if (!$terms || is_wp_error($terms)) {
        return;
    }
    
    echo '<span class="taxonomy-pills">';
    if ($label) {
        echo '<strong class="taxonomy-label">' . esc_html($label) . ':</strong> ';
    }
    
    $term_links = [];
    foreach ($terms as $term) {
        $term_links[] = sprintf(
            '<a href="%s" class="taxonomy-pill">%s</a>',
            esc_url(get_term_link($term)),
            esc_html($term->name)
        );
    }
    echo implode(' ', $term_links);
    echo '</span>';
}

// =============================================================================
// HELPER FUNCTIONS: FILTERS
// =============================================================================

/**
 * Generate filter sidebar HTML
 * Outputs checkboxes for all plate taxonomies
 */
function taverne_filter_sidebar() {
    $taxonomies = taverne_get_plate_taxonomies();
    
    foreach ($taxonomies as $tax_slug) {
        $taxonomy = get_taxonomy($tax_slug);
        if (!$taxonomy) continue;
        
        $terms = get_terms([
            'taxonomy'   => $tax_slug,
            'hide_empty' => true,
            'number'     => 10, // Limit for performance
            'orderby'    => 'count',
            'order'      => 'DESC',
        ]);
        
        if (empty($terms) || is_wp_error($terms)) continue;
        
        // Clean label from slug
        $label = ucfirst(str_replace('plate_', '', $tax_slug));
        
        echo '<div class="filter-group">';
        echo '<h3 class="filter-title">' . esc_html($label) . '</h3>';
        echo '<ul class="filter-list">';
        
        foreach ($terms as $term) {
            // Check if this term is currently active
            $is_checked = isset($_GET[$tax_slug]) && in_array($term->slug, (array)$_GET[$tax_slug]);
            $checked_attr = $is_checked ? 'checked' : '';
            
            printf(
                '<li><label><input type="checkbox" name="%s[]" value="%s" %s> %s <span class="term-count">(%d)</span></label></li>',
                esc_attr($tax_slug),
                esc_attr($term->slug),
                $checked_attr,
                esc_html($term->name),
                intval($term->count)
            );
        }
        
        echo '</ul>';
        echo '</div>';
    }
}

/**
 * Build tax_query array from $_GET parameters
 * 
 * @return array Tax query for WP_Query
 */
function taverne_build_tax_query_from_request() {
    $tax_query = [];
    $taxonomies = taverne_get_plate_taxonomies();
    
    foreach ($taxonomies as $tax_slug) {
        if (!empty($_GET[$tax_slug])) {
            $terms = array_map('sanitize_text_field', (array)$_GET[$tax_slug]);
            $tax_query[] = [
                'taxonomy' => $tax_slug,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }
    
    // If multiple taxonomies, use AND relation
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    
    return $tax_query;
}

// =============================================================================
// HELPER FUNCTIONS: BREADCRUMBS
// =============================================================================

/**
 * Output breadcrumb trail
 * Context-aware for plates, taxonomies, impressions
 */
function taverne_breadcrumbs() {
    $crumbs = [];
    
    // Always start with Plates for plate-related pages
    if (is_singular('plate') || is_post_type_archive('plate') || is_tax() || get_query_var('impression_id')) {
        $crumbs[] = '<a href="' . get_post_type_archive_link('plate') . '">Plates</a>';
    }
    
    // Taxonomy archive
    if (is_tax()) {
        $term = get_queried_object();
        $crumbs[] = '<span class="current">' . esc_html($term->name) . '</span>';
    }
    
    // Single plate
    if (is_singular('plate')) {
        global $post;
        
        // Add primary taxonomy term if exists
        $technique = get_the_terms($post->ID, 'plate_technique');
        if ($technique && !is_wp_error($technique)) {
            $term = $technique[0];
            $crumbs[] = '<a href="' . get_term_link($term) . '">' . esc_html($term->name) . '</a>';
        }
        
        $crumbs[] = '<span class="current">' . get_the_title() . '</span>';
    }
    
    // Single impression
    $impression_id = get_query_var('impression_id');
    $plate_slug = get_query_var('plate_slug');
    
    if ($impression_id && $plate_slug) {
        $plate = get_page_by_path($plate_slug, OBJECT, 'plate');
        if ($plate) {
            $crumbs[] = '<a href="' . get_permalink($plate->ID) . '">' . get_the_title($plate->ID) . '</a>';
            $crumbs[] = '<span class="current">Impression #' . intval($impression_id) . '</span>';
        }
    }
    
    // Exhibition archive/single
    if (is_singular('exhibition')) {
        $crumbs[] = '<a href="' . get_post_type_archive_link('exhibition') . '">Exhibitions</a>';
        $crumbs[] = '<span class="current">' . get_the_title() . '</span>';
    }
    
    if (is_post_type_archive('exhibition')) {
        $crumbs[] = '<span class="current">Exhibitions</span>';
    }
    
    // Output
    if (!empty($crumbs)) {
        echo implode(' <span class="separator">/</span> ', $crumbs);
    }
}

// =============================================================================
// HELPER FUNCTIONS: COUNTS (CACHED)
// =============================================================================

/**
 * Get total impression count across all plates
 * Uses cached option, updated on impression CRUD
 */
function taverne_get_total_impression_count() {
    $count = get_option('taverne_total_impression_count', false);
    
    // If cache doesn't exist, calculate and store
    if ($count === false) {
        $count = taverne_calculate_total_impression_count();
        update_option('taverne_total_impression_count', $count, false);
    }
    
    return intval($count);
}

/**
 * Calculate total impressions (expensive query, only run when needed)
 */
function taverne_calculate_total_impression_count() {
    if (!function_exists('taverne_get_all_impressions')) {
        return 0;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        return 0;
    }
    
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

/**
 * Get available impression count for a specific plate
 */
function taverne_get_available_impression_count($plate_id) {
    $count = get_post_meta($plate_id, '_plate_available_impressions', true);
    return intval($count) ?: 0;
}

// Hook to update cached counts when impressions change
add_action('taverne_impression_created', 'taverne_update_cached_counts');
add_action('taverne_impression_deleted', 'taverne_update_cached_counts');
add_action('taverne_impression_updated', 'taverne_update_cached_counts');

function taverne_update_cached_counts($plate_id = null) {
    // Update global count
    $total = taverne_calculate_total_impression_count();
    update_option('taverne_total_impression_count', $total, false);
    
    // Update plate-specific count if plate_id provided
    if ($plate_id && function_exists('taverne_get_all_impressions')) {
        $impressions = taverne_get_all_impressions($plate_id);
        $available = 0;
        $total_plate = count($impressions);
        
        foreach ($impressions as $imp) {
            if ($imp->availability === 'available') {
                $available++;
            }
        }
        
        update_post_meta($plate_id, '_plate_total_impressions', $total_plate);
        update_post_meta($plate_id, '_plate_available_impressions', $available);
    }
}

// =============================================================================
// HELPER FUNCTIONS: SERIES
// =============================================================================

/**
 * Get plates in a series
 * Now uses taxonomy query since series is a taxonomy
 * 
 * @param string $series_slug The series term slug
 * @return array Array of plate post IDs
 */
function taverne_get_series_group($series_slug) {
    $args = [
        'post_type'      => 'plate',
        'posts_per_page' => 10,
        'fields'         => 'ids',
        'tax_query'      => [
            [
                'taxonomy' => 'plate_series',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($series_slug),
            ],
        ],
        'meta_query'     => [
            [
                'key'     => '_plate_available_impressions',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
        ],
    ];
    
    $query = new WP_Query($args);
    return $query->posts;
}

/**
 * Get featured series for homepage
 * Returns series with most plates
 */
function taverne_get_featured_series($limit = 3) {
    $terms = get_terms([
        'taxonomy'   => 'plate_series',
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);
    
    if (is_wp_error($terms)) {
        return [];
    }
    
    return $terms;
}

// =============================================================================
// AJAX HANDLERS
// =============================================================================

add_action('wp_ajax_taverne_filter', 'taverne_ajax_filter_handler');
add_action('wp_ajax_nopriv_taverne_filter', 'taverne_ajax_filter_handler');

function taverne_ajax_filter_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'taverne_filter_nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Build query args
    $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
    
    $args = [
        'post_type'      => 'plate',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'meta_query'     => [
            [
                'key'     => '_plate_available_impressions',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
        ],
    ];
    
    // Build tax query from POST data
    $tax_query = [];
    $taxonomies = taverne_get_plate_taxonomies();
    
    foreach ($taxonomies as $tax_slug) {
        if (!empty($_POST[$tax_slug])) {
            $terms = array_map('sanitize_text_field', (array)$_POST[$tax_slug]);
            $tax_query[] = [
                'taxonomy' => $tax_slug,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }
    
    if (!empty($tax_query)) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }
    
    // Run query
    $query = new WP_Query($args);
    
    // Buffer output
    ob_start();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $top_imp = function_exists('taverne_get_impressions') 
                ? (taverne_get_impressions(get_the_ID())[0] ?? null) 
                : null;
            
            get_template_part('template-parts/content-impression-card', null, [
                'post_id'    => get_the_ID(),
                'impression' => $top_imp,
            ]);
        }
    } else {
        echo '<p class="no-results">No works match your filters. <a href="' . get_post_type_archive_link('plate') . '">View all plates</a></p>';
    }
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success([
        'html'      => $html,
        'found'     => $query->found_posts,
        'max_pages' => $query->max_num_pages,
    ]);
}

// =============================================================================
// CLEANUP & PERFORMANCE
// =============================================================================

// Remove unnecessary WP bloat
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

// Disable emojis
add_action('init', 'taverne_disable_emojis');

function taverne_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
}

// =============================================================================
// EXCERPT CUSTOMIZATION
// =============================================================================

// Custom excerpt length for cards
add_filter('excerpt_length', function($length) {
    return 20;
}, 999);

// Custom excerpt more
add_filter('excerpt_more', function($more) {
    return '...';
});

// =============================================================================
// BODY CLASSES
// =============================================================================

add_filter('body_class', 'taverne_body_classes');

function taverne_body_classes($classes) {
    // Add class for dark hero on front page
    if (is_front_page()) {
        $classes[] = 'has-hero';
    }
    
    // Add class for impression deep view
    if (get_query_var('impression_id')) {
        $classes[] = 'single-impression-view';
    }
    
    return $classes;
}

// =============================================================================
// FALLBACK FUNCTIONS
// =============================================================================

// Ensure taverne-meta functions exist (graceful degradation)
if (!function_exists('taverne_get_states')) {
    function taverne_get_states($plate_id) {
        return [];
    }
}

if (!function_exists('taverne_get_impressions')) {
    function taverne_get_impressions($plate_id) {
        return [];
    }
}

if (!function_exists('taverne_get_all_impressions')) {
    function taverne_get_all_impressions($plate_id) {
        return [];
    }
}

if (!function_exists('taverne_get_impression')) {
    function taverne_get_impression($impression_id) {
        return null;
    }
}

if (!function_exists('taverne_get_impressions_by_state')) {
    function taverne_get_impressions_by_state($state_id) {
        return [];
    }
}