<?php
/**
 * Post Types Registration
 * Registers Plate, Research, and Teaching CPTs
 * Clean, simple, no custom URL rewrites
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register all custom post types
add_action('init', 'taverne_register_post_types');

function taverne_register_post_types() {
    
    // PLATE POST TYPE - Core for prints/photography/ceramics
    register_post_type('plate', [
        'labels' => [
            'name' => 'Plates',
            'singular_name' => 'Plate',
            'add_new' => 'Add New Plate',
            'add_new_item' => 'Add New Plate',
            'edit_item' => 'Edit Plate',
            'new_item' => 'New Plate',
            'view_item' => 'View Plate',
            'search_items' => 'Search Plates',
            'not_found' => 'No plates found',
            'not_found_in_trash' => 'No plates found in trash',
            'all_items' => 'All Plates',
            'menu_name' => 'Plates',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'plates',
        'show_in_graphql' => true,
        'graphql_single_name' => 'Plate',
        'graphql_plural_name' => 'Plates',
        'menu_icon' => 'dashicons-art',
        'menu_position' => 5,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'rewrite' => true,
        'taxonomies' => [
            'plate_technique',
            'plate_medium',
            'plate_study',
            'plate_motif',
            'plate_palette',
            'plate_traces',
            'plate_matrix',
            'plate_size',
            'plate_year'
        ],
    ]);

    // RESEARCH POST TYPE
    register_post_type('research', [
        'labels' => [
            'name' => 'Research',
            'singular_name' => 'Research',
            'add_new' => 'Add New Research',
            'add_new_item' => 'Add New Research',
            'edit_item' => 'Edit Research',
            'new_item' => 'New Research',
            'view_item' => 'View Research',
            'search_items' => 'Search Research',
            'not_found' => 'No research found',
            'not_found_in_trash' => 'No research found in trash',
            'all_items' => 'All Research',
            'menu_name' => 'Research',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'research',
        'show_in_graphql' => true,
        'graphql_single_name' => 'Research',
        'graphql_plural_name' => 'Researches',
        'menu_icon' => 'dashicons-book-alt',
        'menu_position' => 6,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'rewrite' => true,
    ]);

    // TEACHING POST TYPE
    register_post_type('teaching', [
        'labels' => [
            'name' => 'Teaching',
            'singular_name' => 'Teaching',
            'add_new' => 'Add New Teaching',
            'add_new_item' => 'Add New Teaching',
            'edit_item' => 'Edit Teaching',
            'new_item' => 'New Teaching',
            'view_item' => 'View Teaching',
            'search_items' => 'Search Teaching',
            'not_found' => 'No teaching found',
            'not_found_in_trash' => 'No teaching found in trash',
            'all_items' => 'All Teaching',
            'menu_name' => 'Teaching',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'rest_base' => 'teaching',
        'show_in_graphql' => true,
        'graphql_single_name' => 'Teaching',
        'graphql_plural_name' => 'Teachings',
        'menu_icon' => 'dashicons-welcome-learn-more',
        'menu_position' => 7,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'rewrite' => true,
    ]);

    // Research Categories (hierarchical)
    register_taxonomy('research_category', 'research', [
        'labels' => [
            'name' => 'Research Categories',
            'singular_name' => 'Category',
            'search_items' => 'Search Categories',
            'all_items' => 'All Categories',
            'edit_item' => 'Edit Category',
            'update_item' => 'Update Category',
            'add_new_item' => 'Add New Category',
            'new_item_name' => 'New Category Name',
            'menu_name' => 'Categories',
        ],
        'hierarchical' => true,
        'show_in_rest' => true,
        'rest_base' => 'research-categories',
        'show_in_graphql' => true,
        'graphql_single_name' => 'ResearchCategory',
        'graphql_plural_name' => 'ResearchCategories',
        'rewrite' => true,
    ]);

    // Teaching Categories (hierarchical)
    register_taxonomy('teaching_category', 'teaching', [
        'labels' => [
            'name' => 'Teaching Categories',
            'singular_name' => 'Category',
            'search_items' => 'Search Categories',
            'all_items' => 'All Categories',
            'edit_item' => 'Edit Category',
            'update_item' => 'Update Category',
            'add_new_item' => 'Add New Category',
            'new_item_name' => 'New Category Name',
            'menu_name' => 'Categories',
        ],
        'hierarchical' => true,
        'show_in_rest' => true,
        'rest_base' => 'teaching-categories',
        'show_in_graphql' => true,
        'graphql_single_name' => 'TeachingCategory',
        'graphql_plural_name' => 'TeachingCategories',
        'rewrite' => true,
    ]);
}
