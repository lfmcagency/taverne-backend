<?php
/**
 * GraphQL State type
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register State custom object type with 13 fields + impressions connection
 * Data from wp_plate_states table, includes root query state(id: Int)
 */
function taverne_register_state_type() {
    
    register_graphql_object_type( 'State', [
        'description' => 'A state (état) of a printmaking plate - represents a checkpoint in the artistic evolution',
        'fields' => [
            'id' => [
                'type' => 'Int',
                'description' => 'Unique state ID from wp_plate_states table',
                'resolve' => function( $state ) {
                    return isset( $state->id ) ? (int) $state->id : null;
                }
            ],
            'plateId' => [
                'type' => 'Int',
                'description' => 'Parent plate post ID',
                'resolve' => function( $state ) {
                    return isset( $state->plate_id ) ? (int) $state->plate_id : null;
                }
            ],
            'stateNumber' => [
                'type' => 'Int',
                'description' => 'Sequential state number for this plate (e.g., 1, 2, 3)',
                'resolve' => function( $state ) {
                    return isset( $state->state_number ) ? (int) $state->state_number : null;
                }
            ],
            'title' => [
                'type' => 'String',
                'description' => 'State title',
                'resolve' => function( $state ) {
                    return isset( $state->title ) ? $state->title : null;
                }
            ],
            'excerpt' => [
                'type' => 'String',
                'description' => 'Short description of this state',
                'resolve' => function( $state ) {
                    return isset( $state->excerpt ) ? $state->excerpt : null;
                }
            ],
            'description' => [
                'type' => 'String',
                'description' => 'Full description of changes and artistic decisions',
                'resolve' => function( $state ) {
                    return isset( $state->description ) ? $state->description : null;
                }
            ],
            'featuredImageId' => [
                'type' => 'Int',
                'description' => 'WordPress attachment ID for featured image',
                'resolve' => function( $state ) {
                    return isset( $state->featured_image_id ) ? (int) $state->featured_image_id : null;
                }
            ],
            'featuredImpressionId' => [
                'type' => 'Int',
                'description' => 'ID of featured impression from this state',
                'resolve' => function( $state ) {
                    return isset( $state->featured_impression_id ) ? (int) $state->featured_impression_id : null;
                }
            ],
            'sortOrder' => [
                'type' => 'Int',
                'description' => 'Custom sort order for manual arrangement',
                'resolve' => function( $state ) {
                    return isset( $state->sort_order ) ? (int) $state->sort_order : null;
                }
            ],
            'createdAt' => [
                'type' => 'String',
                'description' => 'Creation timestamp',
                'resolve' => function( $state ) {
                    return isset( $state->created_at ) ? $state->created_at : null;
                }
            ],
            'updatedAt' => [
                'type' => 'String',
                'description' => 'Last update timestamp',
                'resolve' => function( $state ) {
                    return isset( $state->updated_at ) ? $state->updated_at : null;
                }
            ],
            'impressions' => [
                'type' => [ 'list_of' => 'Impression' ],
                'description' => 'All impressions for this state, ordered by impression number',
                'resolve' => function( $state ) {
                    if ( ! function_exists( 'taverne_get_impressions_by_state' ) ) {
                        return null;
                    }
                    
                    $impressions = taverne_get_impressions_by_state( $state->id );
                    
                    if ( empty( $impressions ) ) {
                        return [];
                    }
                    
                    return $impressions;
                }
            ],
            'impressionCount' => [
                'type' => 'Int',
                'description' => 'Total number of impressions in this state',
                'resolve' => function( $state ) {
                    if ( ! function_exists( 'taverne_get_impressions_by_state' ) ) {
                        return 0;
                    }
                    
                    $impressions = taverne_get_impressions_by_state( $state->id );
                    return count( $impressions );
                }
            ],
            'plate' => [
                'type' => 'Plate',
                'description' => 'Parent plate for this state',
                'resolve' => function( $state ) {
                    if ( empty( $state->plate_id ) ) {
                        return null;
                    }
                    
                    return get_post( $state->plate_id );
                }
            ]
        ]
    ]);
    
    // Add root query for single state
    register_graphql_field( 'RootQuery', 'state', [
        'type' => 'State',
        'description' => 'Get a single state by ID',
        'args' => [
            'id' => [
                'type' => 'Int',
                'description' => 'State ID from wp_plate_states table',
            ]
        ],
        'resolve' => function( $root, $args ) {
            if ( ! function_exists( 'taverne_get_state' ) ) {
                return null;
            }
            
            if ( empty( $args['id'] ) ) {
                return null;
            }
            
            return taverne_get_state( $args['id'] );
        }
    ]);
}
