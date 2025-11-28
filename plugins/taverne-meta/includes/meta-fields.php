<?php
/**
 * Post Meta Registration & Computed Fields
 * All plate-level metadata and auto-calculated values
 */

if (!defined('ABSPATH')) exit;

/**
 * Register 15+ plate meta fields for REST/GraphQL exposure
 * Dimensions, pricing, computed fields (size, totals), SEO fields
 * All have sanitization callbacks and REST visibility
 */
add_action('init', 'taverne_register_plate_meta_fields');
function taverne_register_plate_meta_fields() {
    
    // Dimensions
    register_post_meta('plate', '_plate_width', array(
        'type' => 'number',
        'description' => 'Width in centimeters',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return floatval($value); },
    ));
    
    register_post_meta('plate', '_plate_height', array(
        'type' => 'number',
        'description' => 'Height in centimeters',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return floatval($value); },
    ));
    
    // Computed size (auto-calculated from dimensions)
    register_post_meta('plate', '_plate_size_computed', array(
        'type' => 'string',
        'description' => 'Auto-computed size label (S/M/L)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    register_post_meta('plate', '_plate_area_computed', array(
        'type' => 'number',
        'description' => 'Auto-computed area in cm²',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return floatval($value); },
    ));
    
    // Pricing
    register_post_meta('plate', '_plate_price', array(
        'type' => 'number',
        'description' => 'Base price in euros',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return floatval($value); },
    ));
    
    // Year
    register_post_meta('plate', '_plate_year', array(
        'type' => 'number',
        'description' => 'Year created',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return absint($value); },
    ));
    
    // Matrix (stored as taxonomy slug for quick reference)
    register_post_meta('plate', '_plate_matrix', array(
        'type' => 'string',
        'description' => 'Plate material (zinc, copper, etc)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // Study/Series (stored as taxonomy slug)
    register_post_meta('plate', '_plate_study', array(
        'type' => 'string',
        'description' => 'Study or series name',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // SKU / Internal ID
    register_post_meta('plate', '_plate_sku', array(
        'type' => 'string',
        'description' => 'Internal SKU or catalog number',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // Computed totals (auto-calculated from tables)
    register_post_meta('plate', '_plate_total_states', array(
        'type' => 'number',
        'description' => 'Total number of states',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return absint($value); },
    ));
    
    register_post_meta('plate', '_plate_total_impressions', array(
        'type' => 'number',
        'description' => 'Total number of impressions',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return absint($value); },
    ));
    
    register_post_meta('plate', '_plate_available_impressions', array(
        'type' => 'number',
        'description' => 'Number of available impressions',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return absint($value); },
    ));
    
    register_post_meta('plate', '_plate_palette_aggregate', array(
        'type' => 'string',
        'description' => 'Aggregate of all impression colors',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    // SEO Fields
    register_post_meta('plate', '_taverne_meta_title', array(
        'type' => 'string',
        'description' => 'SEO title tag (optional override)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    register_post_meta('plate', '_taverne_meta_description', array(
        'type' => 'string',
        'description' => 'SEO meta description (160 chars)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    register_post_meta('plate', '_taverne_canonical_url', array(
        'type' => 'string',
        'description' => 'Canonical URL (usually self-referencing)',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    register_post_meta('plate', '_taverne_noindex', array(
        'type' => 'boolean',
        'description' => 'Hide from search engines',
        'single' => true,
        'show_in_rest' => true,
        'sanitize_callback' => function($value) { return rest_sanitize_boolean($value); },
    ));
}

/**
 * Recalculate all computed fields: size, area, totals, palette
 * Called automatically after CRUD operations on states/impressions
 * Size based on WIDTH only (S<38cm, M 38-70cm, L>70cm)
 */
function taverne_update_plate_computed_fields($plate_id) {
    if (get_post_type($plate_id) !== 'plate') {
        return;
    }
    
    // Update size from dimensions (width-based, not area)
    $width = floatval(get_post_meta($plate_id, '_plate_width', true));
    $height = floatval(get_post_meta($plate_id, '_plate_height', true));
    
    if ($width > 0 && $height > 0) {
        $area = $width * $height;
        
        // Size based on WIDTH only (matching taxonomy thresholds)
        if ($width < 38) {
            $size_label = 'S (0-38cm)';
        } elseif ($width < 70) {
            $size_label = 'M (38-70cm)';
        } else {
            $size_label = 'L (70cm+)';
        }
        
        update_post_meta($plate_id, '_plate_area_computed', $area);
        update_post_meta($plate_id, '_plate_size_computed', $size_label);
    } else {
        update_post_meta($plate_id, '_plate_area_computed', 0);
        update_post_meta($plate_id, '_plate_size_computed', '');
    }
    
    // Update totals from custom tables
    $total_states = taverne_get_state_count($plate_id);
    $total_impressions = taverne_get_total_impression_count($plate_id);
    $available_impressions = taverne_get_available_impression_count($plate_id);
    $palette_aggregate = taverne_get_palette_aggregate($plate_id);
    
    update_post_meta($plate_id, '_plate_total_states', $total_states);
    update_post_meta($plate_id, '_plate_total_impressions', $total_impressions);
    update_post_meta($plate_id, '_plate_available_impressions', $available_impressions);
    update_post_meta($plate_id, '_plate_palette_aggregate', $palette_aggregate);
    
    // Auto-set canonical URL if empty
    $canonical = get_post_meta($plate_id, '_taverne_canonical_url', true);
    if (empty($canonical)) {
        $permalink = get_permalink($plate_id);
        if ($permalink) {
            update_post_meta($plate_id, '_taverne_canonical_url', $permalink);
        }
    }
}

/**
 * Auto-update computed fields when width/height meta changes
 * Triggers on any meta update, but only acts on _plate_width/_plate_height
 */
add_action('updated_post_meta', 'taverne_maybe_update_computed_on_meta_change', 10, 4);
function taverne_maybe_update_computed_on_meta_change($meta_id, $object_id, $meta_key, $meta_value) {
    // Only trigger on width/height changes for plate posts
    if (!in_array($meta_key, array('_plate_width', '_plate_height'))) {
        return;
    }
    
    if (get_post_type($object_id) !== 'plate') {
        return;
    }
    
    taverne_update_plate_computed_fields($object_id);
}

/**
 * Hook to update computed fields when post is published/saved
 */
add_action('save_post_plate', 'taverne_update_computed_on_save', 20, 2);
function taverne_update_computed_on_save($post_id, $post) {
    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    
    taverne_update_plate_computed_fields($post_id);
}
