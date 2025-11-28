<?php
/**
 * GraphQL Teaching type fields
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register custom fields for Teaching type
 * 
 * Teaching posts are already exposed via WPGraphQL's default CPT integration.
 * This file is a placeholder for any future custom meta fields.
 */
function taverne_register_teaching_fields() {
    
    // Currently no custom meta fields defined for Teaching in taverne-meta
    // Teaching uses standard WP fields: title, content, excerpt, featured image
    // Plus teaching_category taxonomy (already exposed via WPGraphQL)
    
    // Example of adding custom meta field if needed in future:
    /*
    register_graphql_field( 'Teaching', 'customField', [
        'type' => 'String',
        'description' => 'Custom field description',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_teaching_custom_field', true );
        }
    ]);
    */
}
