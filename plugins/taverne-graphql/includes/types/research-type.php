<?php
/**
 * GraphQL Research type fields
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register custom fields for Research type
 * 
 * Research posts are already exposed via WPGraphQL's default CPT integration.
 * This file is a placeholder for any future custom meta fields.
 */
function taverne_register_research_fields() {
    
    // Currently no custom meta fields defined for Research in taverne-meta
    // Research uses standard WP fields: title, content, excerpt, featured image
    // Plus research_category taxonomy (already exposed via WPGraphQL)
    
    // Example of adding custom meta field if needed in future:
    /*
    register_graphql_field( 'Research', 'customField', [
        'type' => 'String',
        'description' => 'Custom field description',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_research_custom_field', true );
        }
    ]);
    */
}
