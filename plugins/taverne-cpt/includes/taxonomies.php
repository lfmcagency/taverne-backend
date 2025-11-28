<?php
/**
 * Taxonomies Registration
 * Registers all 9 plate taxonomies (technique, medium, study, motif, palette, traces, matrix, size, year)
 * Clean, simple, no custom URL rewrites - frontend doesn't care about WP URLs anyway
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register 9 plate taxonomies (non-hierarchical tags)
 * technique, medium, study, motif, palette, traces, matrix, size, year
 * All expose REST + GraphQL, rewrite disabled (headless frontend doesn't care)
 */
add_action('init', 'taverne_register_taxonomies');

function taverne_register_taxonomies() {
    
    // PLATE TECHNIQUE TAXONOMY
    register_taxonomy('plate_technique', 'plate', [
        'labels' => [
            'name' => 'Techniques',
            'singular_name' => 'Technique',
            'search_items' => 'Search Techniques',
            'all_items' => 'All Techniques',
            'edit_item' => 'Edit Technique',
            'update_item' => 'Update Technique',
            'add_new_item' => 'Add New Technique',
            'new_item_name' => 'New Technique Name',
            'menu_name' => 'Techniques',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-techniques',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateTechnique',
        'graphql_plural_name' => 'PlateTechniques',
        'rewrite' => false,
    ]);

    // PLATE MEDIUM TAXONOMY
    register_taxonomy('plate_medium', 'plate', [
        'labels' => [
            'name' => 'Mediums',
            'singular_name' => 'Medium',
            'search_items' => 'Search Mediums',
            'all_items' => 'All Mediums',
            'edit_item' => 'Edit Medium',
            'update_item' => 'Update Medium',
            'add_new_item' => 'Add New Medium',
            'new_item_name' => 'New Medium Name',
            'menu_name' => 'Mediums',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-mediums',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateMedium',
        'graphql_plural_name' => 'PlateMediums',
        'rewrite' => false,
    ]);

    // PLATE STUDY TAXONOMY
    register_taxonomy('plate_study', 'plate', [
        'labels' => [
            'name' => 'Studies',
            'singular_name' => 'Study',
            'search_items' => 'Search Studies',
            'all_items' => 'All Studies',
            'edit_item' => 'Edit Study',
            'update_item' => 'Update Study',
            'add_new_item' => 'Add New Study',
            'new_item_name' => 'New Study Name',
            'menu_name' => 'Studies',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-studies',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateStudy',
        'graphql_plural_name' => 'PlateStudies',
        'rewrite' => false,
    ]);

    // PLATE MOTIF TAXONOMY
    register_taxonomy('plate_motif', 'plate', [
        'labels' => [
            'name' => 'Motifs',
            'singular_name' => 'Motif',
            'search_items' => 'Search Motifs',
            'all_items' => 'All Motifs',
            'edit_item' => 'Edit Motif',
            'update_item' => 'Update Motif',
            'add_new_item' => 'Add New Motif',
            'new_item_name' => 'New Motif Name',
            'menu_name' => 'Motifs',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-motifs',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateMotif',
        'graphql_plural_name' => 'PlateMotifs',
        'rewrite' => false,
    ]);

    // PLATE PALETTE TAXONOMY
    register_taxonomy('plate_palette', 'plate', [
        'labels' => [
            'name' => 'Palettes',
            'singular_name' => 'Palette',
            'search_items' => 'Search Palettes',
            'all_items' => 'All Palettes',
            'edit_item' => 'Edit Palette',
            'update_item' => 'Update Palette',
            'add_new_item' => 'Add New Palette',
            'new_item_name' => 'New Palette Name',
            'menu_name' => 'Palettes',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-palettes',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlatePalette',
        'graphql_plural_name' => 'PlatePalettes',
        'rewrite' => false,
    ]);

    // PLATE TRACES TAXONOMY
    register_taxonomy('plate_traces', 'plate', [
        'labels' => [
            'name' => 'Traces',
            'singular_name' => 'Trace',
            'search_items' => 'Search Traces',
            'all_items' => 'All Traces',
            'edit_item' => 'Edit Trace',
            'update_item' => 'Update Trace',
            'add_new_item' => 'Add New Trace',
            'new_item_name' => 'New Trace Name',
            'menu_name' => 'Traces',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-traces',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateTrace',
        'graphql_plural_name' => 'PlateTraces',
        'rewrite' => false,
    ]);

    // PLATE MATRIX TAXONOMY
    register_taxonomy('plate_matrix', 'plate', [
        'labels' => [
            'name' => 'Matrixes',
            'singular_name' => 'Matrix',
            'search_items' => 'Search Matrixes',
            'all_items' => 'All Matrixes',
            'edit_item' => 'Edit Matrix',
            'update_item' => 'Update Matrix',
            'add_new_item' => 'Add New Matrix',
            'new_item_name' => 'New Matrix Name',
            'menu_name' => 'Matrixes',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-matrixes',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateMatrix',
        'graphql_plural_name' => 'PlateMatrixes',
        'rewrite' => false,
    ]);

    // PLATE SIZE TAXONOMY
    register_taxonomy('plate_size', 'plate', [
        'labels' => [
            'name' => 'Sizes',
            'singular_name' => 'Size',
            'search_items' => 'Search Sizes',
            'all_items' => 'All Sizes',
            'edit_item' => 'Edit Size',
            'update_item' => 'Update Size',
            'add_new_item' => 'Add New Size',
            'new_item_name' => 'New Size Name',
            'menu_name' => 'Sizes',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-sizes',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateSize',
        'graphql_plural_name' => 'PlateSizes',
        'rewrite' => false,
    ]);

    // PLATE YEAR TAXONOMY
    register_taxonomy('plate_year', 'plate', [
        'labels' => [
            'name' => 'Years',
            'singular_name' => 'Year',
            'search_items' => 'Search Years',
            'all_items' => 'All Years',
            'edit_item' => 'Edit Year',
            'update_item' => 'Update Year',
            'add_new_item' => 'Add New Year',
            'new_item_name' => 'New Year Name',
            'menu_name' => 'Years',
        ],
        'hierarchical' => false,
        'show_in_rest' => true,
        'rest_base' => 'plate-years',
        'show_in_graphql' => true,
        'graphql_single_name' => 'PlateYear',
        'graphql_plural_name' => 'PlateYears',
        'rewrite' => false,
    ]);
}
