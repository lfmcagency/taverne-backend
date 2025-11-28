<?php
/**
 * Register all GraphQL types and fields
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load type files
require_once TAVERNE_GRAPHQL_PATH . 'includes/types/plate-type.php';
require_once TAVERNE_GRAPHQL_PATH . 'includes/types/state-type.php';
require_once TAVERNE_GRAPHQL_PATH . 'includes/types/impression-type.php';
require_once TAVERNE_GRAPHQL_PATH . 'includes/types/research-type.php';
require_once TAVERNE_GRAPHQL_PATH . 'includes/types/teaching-type.php';

/**
 * Register all custom types on GraphQL schema initialization
 */
add_action( 'graphql_register_types', function() {
    
    // Register State type
    taverne_register_state_type();
    
    // Register Impression type
    taverne_register_impression_type();
    
    // Register Plate fields (extends existing Plate type from CPT)
    taverne_register_plate_fields();
    
    // Register Research fields
    taverne_register_research_fields();
    
    // Register Teaching fields
    taverne_register_teaching_fields();
    
});
