<?php
/**
 * Taverne Gallery Theme Functions
 * Lightweight, minimal setup for art gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

/* =========================================
   THEME SETUP
========================================= */
function taverne_gallery_setup() {
    // Featured images
    add_theme_support('post-thumbnails');
    
    // Image sizes for gallery
    add_image_size('plate-thumb', 400, 400, true);
    add_image_size('plate-medium', 800, 800, false);
    add_image_size('plate-large', 1400, 1400, false);
    add_image_size('plate-hero', 1920, 1920, false);
    
    // Title tag support
    add_theme_support('title-tag');
    
    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style'
    ));
    
    // Register nav menus
    register_nav_menus(array(
        'primary' => 'Primary Navigation',
        'footer'  => 'Footer Navigation'
    ));
    
    // Custom logo support
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'taverne_gallery_setup');

/* =========================================
   ENQUEUE STYLES & SCRIPTS
========================================= */
function taverne_gallery_scripts() {
    // Main stylesheet
    wp_enqueue_style(
        'taverne-gallery-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );
    
    // Main JS (for image gallery interaction)
    wp_enqueue_script(
        'taverne-gallery-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'taverne_gallery_scripts');

/* =========================================
   EXCERPT LENGTH
========================================= */
function taverne_gallery_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'taverne_gallery_excerpt_length');

function taverne_gallery_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'taverne_gallery_excerpt_more');

/* =========================================
   CLEAN UP WP BLOAT
========================================= */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

// Disable emojis
function taverne_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('init', 'taverne_disable_emojis');

/* =========================================
   HIDE TAXONOMY SIDEBARS FOR PLATE CPT
   (Keeps main content clean, no frontend impact)
========================================= */
function taverne_hide_plate_taxonomy_sidebars() {
    $post_type = 'plate';
    $taxonomies = array(
        'plate_technique',
        'plate_medium',
        'plate_study',
        'plate_motif',
        'plate_palette',
        'plate_traces',
        'plate_matrix',
        'plate_size',
        'plate_year'
    );

    foreach ($taxonomies as $tax) {
        remove_meta_box('tagsdiv-' . $tax, $post_type, 'side');
    }
}
add_action('add_meta_boxes_' . 'plate', 'taverne_hide_plate_taxonomy_sidebars');

/* =========================================
   PAGINATION
========================================= */
function taverne_pagination() {
    global $wp_query;
    
    $big = 999999999;
    
    $paginate = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'prev_text' => '← Previous',
        'next_text' => 'Next →',
        'type'      => 'list',
        'end_size'  => 3,
        'mid_size'  => 3
    ));
    
    if ($paginate) {
        echo '<nav class="pagination" role="navigation">';
        echo $paginate;
        echo '</nav>';
    }
}

/* =========================================
   GET PLATE TAXONOMIES (Helper Function)
========================================= */
function taverne_get_plate_taxonomies() {
    return array(
        'plate_technique' => 'Technique',
        'plate_medium'    => 'Medium',
        'plate_study'     => 'Study',
        'plate_motif'     => 'Motif',
        'plate_traces'    => 'Traces',
        'plate_palette'   => 'Palette',
        'plate_matrix'    => 'Matrix',
        'plate_size'      => 'Size',
        'plate_year'      => 'Year'
    );
}

/* =========================================
   DISPLAY TAXONOMY TERMS (Helper Function)
========================================= */
function taverne_display_taxonomy_terms($post_id, $taxonomy, $label = '') {
    $terms = get_the_terms($post_id, $taxonomy);
    
    if (!$terms || is_wp_error($terms)) {
        return;
    }
    
    if (empty($label)) {
        $tax = get_taxonomy($taxonomy);
        $label = $tax->labels->name;
    }
    
    echo '<div class="taxonomy-group">';
    echo '<strong>' . esc_html($label) . ':</strong>';
    echo '<div class="taxonomy-terms">';
    
    foreach ($terms as $term) {
        echo '<a href="' . esc_url(get_term_link($term)) . '">';
        echo esc_html($term->name);
        echo '</a>';
    }
    
    echo '</div></div>';
}

/* =========================================
   FILTER SIDEBAR (Helper Function)
========================================= */
function taverne_filter_sidebar() {
    $taxonomies = taverne_get_plate_taxonomies();
    
    echo '<aside class="filter-sidebar">';
    
    foreach ($taxonomies as $tax_slug => $tax_label) {
        $terms = get_terms(array(
            'taxonomy'   => $tax_slug,
            'hide_empty' => true,
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            continue;
        }
        
        echo '<div class="filter-group">';
        echo '<h3>' . esc_html($tax_label) . '</h3>';
        echo '<ul>';
        
        foreach ($terms as $term) {
            $current_class = (is_tax($tax_slug, $term->slug)) ? ' class="current"' : '';
            echo '<li' . $current_class . '>';
            echo '<a href="' . esc_url(get_term_link($term)) . '">';
            echo esc_html($term->name) . ' <span>(' . $term->count . ')</span>';
            echo '</a></li>';
        }
        
        echo '</ul></div>';
    }
    
    echo '</aside>';
}

/* =========================================
   BREADCRUMBS (Helper Function)
========================================= */
function taverne_breadcrumbs() {
    if (is_front_page()) {
        return;
    }
    
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . esc_url(home_url('/')) . '">Home</a>';
    echo ' / ';
    
    if (is_post_type_archive('plate')) {
        echo 'Prints';
    } elseif (is_tax()) {
        $term = get_queried_object();
        echo '<a href="' . esc_url(get_post_type_archive_link('plate')) . '">Prints</a>';
        echo ' / ';
        echo esc_html($term->name);
    } elseif (is_singular('plate')) {
        echo '<a href="' . esc_url(get_post_type_archive_link('plate')) . '">Prints</a>';
        echo ' / ';
        the_title();
    } elseif (is_page()) {
        the_title();
    }
    
    echo '</nav>';
}
