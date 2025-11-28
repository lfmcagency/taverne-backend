<?php
/**
 * GraphQL Impression type
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Impression type and its fields
 */
function taverne_register_impression_type() {
    
    register_graphql_object_type( 'Impression', [
        'description' => 'An individual print/proof from a state - the actual sellable artwork',
        'fields' => [
            'id' => [
                'type' => 'Int',
                'description' => 'Unique impression ID from wp_plate_impressions table',
                'resolve' => function( $impression ) {
                    return isset( $impression->id ) ? (int) $impression->id : null;
                }
            ],
            'plateId' => [
                'type' => 'Int',
                'description' => 'Parent plate post ID (denormalized)',
                'resolve' => function( $impression ) {
                    return isset( $impression->plate_id ) ? (int) $impression->plate_id : null;
                }
            ],
            'stateId' => [
                'type' => 'Int',
                'description' => 'Parent state ID',
                'resolve' => function( $impression ) {
                    return isset( $impression->state_id ) ? (int) $impression->state_id : null;
                }
            ],
            'impressionNumber' => [
                'type' => 'Int',
                'description' => 'Sequential impression number within state (e.g., 1, 2, 3)',
                'resolve' => function( $impression ) {
                    return isset( $impression->impression_number ) ? (int) $impression->impression_number : null;
                }
            ],
            'imageId' => [
                'type' => 'Int',
                'description' => 'WordPress attachment ID for impression image',
                'resolve' => function( $impression ) {
                    return isset( $impression->image_id ) ? (int) $impression->image_id : null;
                }
            ],
            'color' => [
                'type' => 'String',
                'description' => 'Color slug from plate_palette taxonomy (e.g., black, sepia, blue)',
                'resolve' => function( $impression ) {
                    return isset( $impression->color ) ? $impression->color : null;
                }
            ],
            'price' => [
                'type' => 'Float',
                'description' => 'Price in euros (can override plate base price)',
                'resolve' => function( $impression ) {
                    return isset( $impression->price ) ? (float) $impression->price : null;
                }
            ],
            'availability' => [
                'type' => 'String',
                'description' => 'Availability status: available, artist, sold',
                'resolve' => function( $impression ) {
                    return isset( $impression->availability ) ? $impression->availability : null;
                }
            ],
            'changes' => [
                'type' => 'String',
                'description' => 'Description of changes or enhancements for this impression',
                'resolve' => function( $impression ) {
                    return isset( $impression->changes ) ? $impression->changes : null;
                }
            ],
            'notes' => [
                'type' => 'String',
                'description' => 'Internal notes about this impression',
                'resolve' => function( $impression ) {
                    return isset( $impression->notes ) ? $impression->notes : null;
                }
            ],
            'sortOrder' => [
                'type' => 'Int',
                'description' => 'Custom sort order for manual arrangement',
                'resolve' => function( $impression ) {
                    return isset( $impression->sort_order ) ? (int) $impression->sort_order : null;
                }
            ],
            'createdAt' => [
                'type' => 'String',
                'description' => 'Creation timestamp',
                'resolve' => function( $impression ) {
                    return isset( $impression->created_at ) ? $impression->created_at : null;
                }
            ],
            'updatedAt' => [
                'type' => 'String',
                'description' => 'Last update timestamp',
                'resolve' => function( $impression ) {
                    return isset( $impression->updated_at ) ? $impression->updated_at : null;
                }
            ],
            'state' => [
                'type' => 'State',
                'description' => 'Parent state for this impression',
                'resolve' => function( $impression ) {
                    if ( ! function_exists( 'taverne_get_state' ) ) {
                        return null;
                    }
                    
                    if ( empty( $impression->state_id ) ) {
                        return null;
                    }
                    
                    return taverne_get_state( $impression->state_id );
                }
            ],
            'plate' => [
                'type' => 'Plate',
                'description' => 'Parent plate for this impression',
                'resolve' => function( $impression, $args, $context, $info ) {
                    if ( empty( $impression->plate_id ) ) {
                        return null;
                    }
                    
                    // Use WPGraphQL DataSource for proper Plate resolution
                    return \WPGraphQL\Data\DataSource::resolve_post_object( $impression->plate_id, $context );
                }
            ],
            'image' => [
                'type' => 'MediaItem',
                'description' => 'Full image object from WordPress media library',
                'resolve' => function( $impression, $args, $context, $info ) {
                    if ( empty( $impression->image_id ) ) {
                        return null;
                    }
                    
                    // FIXED: Use WPGraphQL's DataSource to properly resolve MediaItem
                    // This goes through WPGraphQL's data layer instead of raw get_post()
                    return \WPGraphQL\Data\DataSource::resolve_post_object( $impression->image_id, $context );
                }
            ],
            'colorTerm' => [
                'type' => 'PlatePalette',
                'description' => 'Color as a taxonomy term object',
                'resolve' => function( $impression ) {
                    if ( empty( $impression->color ) ) {
                        return null;
                    }
                    
                    $term = get_term_by( 'slug', $impression->color, 'plate_palette' );
                    
                    return $term ? $term : null;
                }
            ]
        ]
    ]);
    
    // Add root query for single impression
    register_graphql_field( 'RootQuery', 'impression', [
        'type' => 'Impression',
        'description' => 'Get a single impression by ID',
        'args' => [
            'id' => [
                'type' => 'Int',
                'description' => 'Impression ID from wp_plate_impressions table',
            ]
        ],
        'resolve' => function( $root, $args ) {
            if ( ! function_exists( 'taverne_get_impression' ) ) {
                return null;
            }
            
            if ( empty( $args['id'] ) ) {
                return null;
            }
            
            return taverne_get_impression( $args['id'] );
        }
    ]);
}
