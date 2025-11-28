<?php
/**
 * GraphQL Plate type fields
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Extend Plate GraphQL type with 25+ custom fields
 * Dimensions, pricing, SEO, computed totals, states connection
 * Resolvers pull from post meta and Taverne Meta CRUD functions
 */
function taverne_register_plate_fields() {
    
    // Core WordPress fields (explicitly expose for headless frontend)
    register_graphql_field( 'Plate', 'content', [
        'type' => 'String',
        'description' => 'Full plate description/content (HTML)',
        'resolve' => function( $post ) {
            $content = get_post_field( 'post_content', $post->ID );
            return apply_filters( 'the_content', $content );
        }
    ]);
    
    register_graphql_field( 'Plate', 'excerpt', [
        'type' => 'String',
        'description' => 'Short excerpt/summary of the plate',
        'resolve' => function( $post ) {
            $excerpt = get_post_field( 'post_excerpt', $post->ID );
            if ( empty( $excerpt ) ) {
                // Auto-generate from content if no manual excerpt
                $excerpt = wp_trim_words( get_post_field( 'post_content', $post->ID ), 55, '...' );
            }
            return $excerpt;
        }
    ]);
    
    // Plate dimensions and computed fields
    register_graphql_field( 'Plate', 'width', [
        'type' => 'Float',
        'description' => 'Plate width in centimeters',
        'resolve' => function( $post ) {
            $width = get_post_meta( $post->ID, '_plate_width', true );
            return $width ? (float) $width : null;
        }
    ]);
    
    register_graphql_field( 'Plate', 'height', [
        'type' => 'Float',
        'description' => 'Plate height in centimeters',
        'resolve' => function( $post ) {
            $height = get_post_meta( $post->ID, '_plate_height', true );
            return $height ? (float) $height : null;
        }
    ]);
    
    register_graphql_field( 'Plate', 'size', [
        'type' => 'String',
        'description' => 'Computed size category: S (0-38cm), M (38-70cm), L (70cm+)',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_plate_size_computed', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'area', [
        'type' => 'Float',
        'description' => 'Computed plate area in cm²',
        'resolve' => function( $post ) {
            $area = get_post_meta( $post->ID, '_plate_area_computed', true );
            return $area ? (float) $area : null;
        }
    ]);
    
    // Pricing
    register_graphql_field( 'Plate', 'basePrice', [
        'type' => 'Float',
        'description' => 'Base price in euros for new impressions',
        'resolve' => function( $post ) {
            $price = get_post_meta( $post->ID, '_plate_price', true );
            return $price ? (float) $price : null;
        }
    ]);
    
    // Catalog metadata
    register_graphql_field( 'Plate', 'year', [
        'type' => 'Int',
        'description' => 'Year of creation',
        'resolve' => function( $post ) {
            $year = get_post_meta( $post->ID, '_plate_year', true );
            return $year ? (int) $year : null;
        }
    ]);
    
    register_graphql_field( 'Plate', 'matrixSlug', [
        'type' => 'String',
        'description' => 'Matrix material slug (e.g., copper, zinc)',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_plate_matrix', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'studySlug', [
        'type' => 'String',
        'description' => 'Study type slug (e.g., landscape, portrait)',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_plate_study', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'sku', [
        'type' => 'String',
        'description' => 'Internal catalog SKU',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_plate_sku', true );
        }
    ]);
    
    // Computed aggregates
    register_graphql_field( 'Plate', 'totalStates', [
        'type' => 'Int',
        'description' => 'Total number of states for this plate',
        'resolve' => function( $post ) {
            $total = get_post_meta( $post->ID, '_plate_total_states', true );
            return $total ? (int) $total : 0;
        }
    ]);
    
    register_graphql_field( 'Plate', 'totalImpressions', [
        'type' => 'Int',
        'description' => 'Total number of impressions across all states',
        'resolve' => function( $post ) {
            $total = get_post_meta( $post->ID, '_plate_total_impressions', true );
            return $total ? (int) $total : 0;
        }
    ]);
    
    register_graphql_field( 'Plate', 'availableImpressions', [
        'type' => 'Int',
        'description' => 'Number of available impressions for sale',
        'resolve' => function( $post ) {
            $available = get_post_meta( $post->ID, '_plate_available_impressions', true );
            return $available ? (int) $available : 0;
        }
    ]);
    
    register_graphql_field( 'Plate', 'paletteAggregate', [
        'type' => 'String',
        'description' => 'Comma-separated list of unique colors across all impressions',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_plate_palette_aggregate', true );
        }
    ]);
    
    // SEO fields
    register_graphql_field( 'Plate', 'seoTitle', [
        'type' => 'String',
        'description' => 'Custom SEO title',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_taverne_meta_title', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'seoDescription', [
        'type' => 'String',
        'description' => 'Custom SEO meta description',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_taverne_meta_description', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'canonicalUrl', [
        'type' => 'String',
        'description' => 'Canonical URL for this plate',
        'resolve' => function( $post ) {
            return get_post_meta( $post->ID, '_taverne_canonical_url', true );
        }
    ]);
    
    register_graphql_field( 'Plate', 'noindex', [
        'type' => 'Boolean',
        'description' => 'Whether to hide from search engines',
        'resolve' => function( $post ) {
            return (bool) get_post_meta( $post->ID, '_taverne_noindex', true );
        }
    ]);
    
    // States connection (one-to-many)
    register_graphql_field( 'Plate', 'states', [
        'type' => [ 'list_of' => 'State' ],
        'description' => 'All states for this plate, ordered by state number',
        'resolve' => function( $post ) {
            if ( ! function_exists( 'taverne_get_states' ) ) {
                return null;
            }
            
            $states = taverne_get_states( $post->ID );
            
            if ( empty( $states ) ) {
                return [];
            }
            
            return $states;
        }
    ]);
}
