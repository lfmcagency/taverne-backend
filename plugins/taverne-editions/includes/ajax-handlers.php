<?php
/**
 * AJAX Handlers for States & Impressions
 * All CRUD operations without page reloads
 */

if (!defined('ABSPATH')) exit;

// ============================================
// STATE OPERATIONS
// ============================================

/**
 * Add new state
 */
add_action('wp_ajax_taverne_add_state', 'taverne_ajax_add_state');
function taverne_ajax_add_state() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $plate_id = isset($_POST['plate_id']) ? intval($_POST['plate_id']) : 0;
    
    if (!$plate_id || get_post_type($plate_id) !== 'plate') {
        wp_send_json_error('Invalid plate ID');
    }
    
    if (!current_user_can('edit_post', $plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    // Create state
    $state_id = taverne_create_state($plate_id, array(
        'title' => 'New State',
        'excerpt' => '',
        'description' => ''
    ));
    
    if (is_wp_error($state_id)) {
        wp_send_json_error($state_id->get_error_message());
    }
    
    $state = taverne_get_state($state_id);
    
    wp_send_json_success(array(
        'state_id' => $state_id,
        'state_number' => $state->state_number,
        'message' => 'State created successfully'
    ));
}

/**
 * Update state
 */
add_action('wp_ajax_taverne_update_state', 'taverne_ajax_update_state');
function taverne_ajax_update_state() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $value = isset($_POST['value']) ? $_POST['value'] : '';
    
    if (!$state_id) {
        wp_send_json_error('Invalid state ID');
    }
    
    $state = taverne_get_state($state_id);
    if (!$state) {
        wp_send_json_error('State not found');
    }
    
    if (!current_user_can('edit_post', $state->plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    // Build update data based on field
    $data = array();
    switch ($field) {
        case 'title':
            $data['title'] = sanitize_text_field($value);
            break;
        case 'excerpt':
            $data['excerpt'] = sanitize_textarea_field($value);
            break;
        case 'description':
            $data['description'] = wp_kses_post($value);
            break;
        case 'featured_impression_id':
            $data['featured_impression_id'] = intval($value) ?: null;
            break;
        default:
            wp_send_json_error('Invalid field');
    }
    
    $result = taverne_update_state($state_id, $data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => 'State updated successfully'
    ));
}

/**
 * Delete state
 */
add_action('wp_ajax_taverne_delete_state', 'taverne_ajax_delete_state');
function taverne_ajax_delete_state() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
    
    if (!$state_id) {
        wp_send_json_error('Invalid state ID');
    }
    
    $state = taverne_get_state($state_id);
    if (!$state) {
        wp_send_json_error('State not found');
    }
    
    if (!current_user_can('edit_post', $state->plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    $result = taverne_delete_state($state_id);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => 'State deleted successfully'
    ));
}

// ============================================
// IMPRESSION OPERATIONS
// ============================================

/**
 * Add new impression
 */
add_action('wp_ajax_taverne_add_impression', 'taverne_ajax_add_impression');
function taverne_ajax_add_impression() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
    
    if (!$state_id) {
        wp_send_json_error('Invalid state ID');
    }
    
    $state = taverne_get_state($state_id);
    if (!$state) {
        wp_send_json_error('State not found');
    }
    
    if (!current_user_can('edit_post', $state->plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    // Get base price for default
    $base_price = get_post_meta($state->plate_id, '_plate_price', true);
    
    // Create impression
    $impression_id = taverne_create_impression($state->plate_id, $state_id, array(
        'color' => '',
        'price' => $base_price ?: 0,
        'availability' => 'available',
        'changes' => '',
        'notes' => ''
    ));
    
    if (is_wp_error($impression_id)) {
        wp_send_json_error($impression_id->get_error_message());
    }
    
    $impression = taverne_get_impression($impression_id);
    
    wp_send_json_success(array(
        'impression_id' => $impression_id,
        'impression_number' => $impression->impression_number,
        'message' => 'Impression created successfully'
    ));
}

/**
 * Update impression
 */
add_action('wp_ajax_taverne_update_impression', 'taverne_ajax_update_impression');
function taverne_ajax_update_impression() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $impression_id = isset($_POST['impression_id']) ? intval($_POST['impression_id']) : 0;
    $field = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $value = isset($_POST['value']) ? $_POST['value'] : '';
    
    if (!$impression_id) {
        wp_send_json_error('Invalid impression ID');
    }
    
    $impression = taverne_get_impression($impression_id);
    if (!$impression) {
        wp_send_json_error('Impression not found');
    }
    
    if (!current_user_can('edit_post', $impression->plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    // Build update data based on field
    $data = array();
    switch ($field) {
        case 'color':
            $data['color'] = sanitize_text_field($value);
            break;
        case 'price':
            $data['price'] = floatval($value);
            break;
        case 'availability':
            $data['availability'] = sanitize_text_field($value);
            break;
        case 'changes':
            $data['changes'] = sanitize_textarea_field($value);
            break;
        case 'notes':
            $data['notes'] = sanitize_textarea_field($value);
            break;
        case 'image_id':
            // NEW: Handle impression image uploads
            $data['image_id'] = intval($value) ?: null;
            break;
        default:
            wp_send_json_error('Invalid field');
    }
    
    $result = taverne_update_impression($impression_id, $data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => 'Impression updated successfully'
    ));
}

/**
 * Delete impression
 */
add_action('wp_ajax_taverne_delete_impression', 'taverne_ajax_delete_impression');
function taverne_ajax_delete_impression() {
    check_ajax_referer('taverne_editions_nonce', 'nonce');
    
    $impression_id = isset($_POST['impression_id']) ? intval($_POST['impression_id']) : 0;
    
    if (!$impression_id) {
        wp_send_json_error('Invalid impression ID');
    }
    
    $impression = taverne_get_impression($impression_id);
    if (!$impression) {
        wp_send_json_error('Impression not found');
    }
    
    if (!current_user_can('edit_post', $impression->plate_id)) {
        wp_send_json_error('Permission denied');
    }
    
    $result = taverne_delete_impression($impression_id);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
    
    wp_send_json_success(array(
        'message' => 'Impression deleted successfully'
    ));
}
