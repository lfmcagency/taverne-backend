# Taverne CPT - Exhibitions Post Type Addition
# Purpose: Dedicated CPT for Pol's exhibitions—solo/group shows, curators, venues, years. Hierarchical? No (flat list). Supports: title, editor (desc), thumbnail (venue/poster img), excerpt (blurb), custom-fields (date_range, type: solo/group). REST/GraphQL: Yes for headless. ~50 lines added to post-types.php.

# Registration Code (Add to includes/post-types.php, after plate/research/teaching)
function taverne_register_exhibitions_cpt() {
    $labels = [
        'name' => 'Exhibitions',
        'singular_name' => 'Exhibition',
        'menu_name' => 'Exhibitions',
        'add_new' => 'Add New Exhibition',
        'add_new_item' => 'Add New Exhibition',
        'edit_item' => 'Edit Exhibition',
        'view_item' => 'View Exhibition',
        'all_items' => 'All Exhibitions',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'exhibitions'],
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest' => true,
        'show_in_graphql' => true,
        'graphql_single_name' => 'Exhibition',
        'graphql_plural_name' => 'Exhibitions',
        'menu_icon' => 'dashicons-calendar-alt', // Or custom SVG for gallery icon
    ];
    register_post_type('exhibition', $args);
}
add_action('init', 'taverne_register_exhibitions_cpt');

// Meta Fields (Add to Taverne Meta's meta-fields.php)
function taverne_register_exhibition_meta() {
    register_post_meta('exhibition', '_exhibition_date_range', [
        'type' => 'string',
        'description' => 'Date range (e.g., March-April 2023)',
        'single' => true,
        'show_in_rest' => true,
    ]);
    register_post_meta('exhibition', '_exhibition_type', [
        'type' => 'string',
        'description' => 'Solo or Group',
        'single' => true,
        'show_in_rest' => true,
    ]);
    register_post_meta('exhibition', '_exhibition_venue', [
        'type' => 'string',
        'description' => 'Venue name',
        'single' => true,
        'show_in_rest' => true,
    ]);
    register_post_meta('exhibition', '_exhibition_curator', [
        'type' => 'string',
        'description' => 'Curator (if group)',
        'single' => true,
        'show_in_rest' => true,
    ]);
    register_post_meta('exhibition', '_exhibition_cv_link', [
        'type' => 'string',
        'description' => 'Link to full CV or details',
        'single' => true,
        'show_in_rest' => true,
    ]);
}
add_action('init', 'taverne_register_exhibition_meta');


# taverne-meta plugin

// Register new meta fields in 
- _taverne_hero_quote, _taverne_hero_img (home hero)
- _taverne_studio_bio, _taverne_socials (footer)
- User meta for roles (_taverne_role_artist_bio, etc.)

// cached impression helper functions in taverne-meta plugin

In taverne-meta, hook into impression CRUD
add_action('taverne_impression_created', 'taverne_update_cached_counts');
add_action('taverne_impression_deleted', 'taverne_update_cached_counts');

function taverne_update_cached_counts($plate_id) {
    $count = taverne_count_impressions($plate_id); // Live query once
    update_post_meta($plate_id, '_plate_total_impressions', $count);
    update_option('taverne_global_impression_count', taverne_count_all_impressions());
}