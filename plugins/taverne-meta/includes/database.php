<?php
/**
 * Custom Tables & CRUD Operations
 * States and Impressions for variable plate editions
 */

if (!defined('ABSPATH')) exit;

/**
 * Create wp_plate_states and wp_plate_impressions tables
 * States: Variable editions of a plate (state 1, 2, 3...)
 * Impressions: Individual prints within each state with pricing/availability
 */
function taverne_create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // States table
    $states_table = $wpdb->prefix . 'plate_states';
    $states_sql = "CREATE TABLE $states_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        plate_id bigint(20) NOT NULL,
        state_number int(11) NOT NULL,
        title varchar(255) DEFAULT '',
        excerpt text,
        description text,
        featured_image_id bigint(20) DEFAULT NULL,
        featured_impression_id bigint(20) DEFAULT NULL,
        sort_order int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY plate_id (plate_id),
        KEY state_number (state_number),
        KEY sort_order (sort_order)
    ) $charset_collate;";
    
    // Impressions table
    $impressions_table = $wpdb->prefix . 'plate_impressions';
    $impressions_sql = "CREATE TABLE $impressions_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        plate_id bigint(20) NOT NULL,
        state_id bigint(20) NOT NULL,
        impression_number int(11) NOT NULL,
        image_id bigint(20) DEFAULT NULL,
        color varchar(100) DEFAULT '',
        price decimal(10,2) DEFAULT 0,
        availability varchar(50) DEFAULT 'available',
        changes text,
        notes text,
        sort_order int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY plate_id (plate_id),
        KEY state_id (state_id),
        KEY impression_number (impression_number),
        KEY availability (availability),
        KEY sort_order (sort_order)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($states_sql);
    dbDelta($impressions_sql);
}

// ============================================
// STATE CRUD OPERATIONS
// ============================================

/**
 * Create new state for a plate (returns state_id or WP_Error)
 * Auto-assigns next state_number, default title "State N"
 * Updates computed fields on parent plate after creation
 */
function taverne_create_state($plate_id, $data = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    
    // Get next state number
    $next_number = taverne_get_next_state_number($plate_id);
    
    // Prepare data
    $insert_data = array(
        'plate_id' => absint($plate_id),
        'state_number' => $next_number,
        'title' => isset($data['title']) ? sanitize_text_field($data['title']) : 'State ' . $next_number,
        'excerpt' => isset($data['excerpt']) ? sanitize_textarea_field($data['excerpt']) : '',
        'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
        'featured_image_id' => isset($data['featured_image_id']) ? absint($data['featured_image_id']) : null,
        'featured_impression_id' => isset($data['featured_impression_id']) ? absint($data['featured_impression_id']) : null,
        'sort_order' => isset($data['sort_order']) ? absint($data['sort_order']) : $next_number
    );
    
    $result = $wpdb->insert($table, $insert_data, array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d'));
    
    if ($result === false) {
        return new WP_Error('state_create_failed', 'Failed to create state', $wpdb->last_error);
    }
    
    taverne_update_plate_computed_fields($plate_id);
    return $wpdb->insert_id;
}

/**
 * Get single state by ID (returns object or null)
 * Returns row from wp_plate_states table with all fields
 */
function taverne_get_state($state_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $state_id));
}

/**
 * Get all states for a plate (returns array of objects)
 * Default sort: sort_order ASC (manual ordering)
 * Options: id, state_number, sort_order, created_at
 */
function taverne_get_states($plate_id, $orderby = 'sort_order', $order = 'ASC') {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    
    $allowed_orderby = array('id', 'state_number', 'sort_order', 'created_at');
    $allowed_order = array('ASC', 'DESC');
    
    $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'sort_order';
    $order = in_array($order, $allowed_order) ? $order : 'ASC';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE plate_id = %d ORDER BY $orderby $order",
        $plate_id
    ));
}

/**
 * Update state fields (title, excerpt, description, featured_image_id, etc)
 * Only updates provided fields, sanitizes all inputs
 * Returns true or WP_Error, updates computed fields on parent plate
 */
function taverne_update_state($state_id, $data = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    
    // Get current state to get plate_id
    $state = taverne_get_state($state_id);
    if (!$state) {
        return new WP_Error('state_not_found', 'State not found');
    }
    
    $update_data = array();
    $format = array();
    
    if (isset($data['title'])) {
        $update_data['title'] = sanitize_text_field($data['title']);
        $format[] = '%s';
    }
    if (isset($data['excerpt'])) {
        $update_data['excerpt'] = sanitize_textarea_field($data['excerpt']);
        $format[] = '%s';
    }
    if (isset($data['description'])) {
        $update_data['description'] = wp_kses_post($data['description']);
        $format[] = '%s';
    }
    if (isset($data['featured_image_id'])) {
        $update_data['featured_image_id'] = absint($data['featured_image_id']) ?: null;
        $format[] = '%d';
    }
    if (isset($data['featured_impression_id'])) {
        $update_data['featured_impression_id'] = absint($data['featured_impression_id']) ?: null;
        $format[] = '%d';
    }
    if (isset($data['sort_order'])) {
        $update_data['sort_order'] = absint($data['sort_order']);
        $format[] = '%d';
    }
    
    if (empty($update_data)) {
        return new WP_Error('no_data', 'No data to update');
    }
    
    $result = $wpdb->update($table, $update_data, array('id' => $state_id), $format, array('%d'));
    
    if ($result === false) {
        return new WP_Error('state_update_failed', 'Failed to update state', $wpdb->last_error);
    }
    
    taverne_update_plate_computed_fields($state->plate_id);
    return true;
}

/**
 * Delete state AND cascade delete all impressions in that state
 * Returns true or WP_Error, updates computed fields on parent plate
 */
function taverne_delete_state($state_id) {
    global $wpdb;
    $states_table = $wpdb->prefix . 'plate_states';
    $impressions_table = $wpdb->prefix . 'plate_impressions';
    
    // Get state to get plate_id
    $state = taverne_get_state($state_id);
    if (!$state) {
        return new WP_Error('state_not_found', 'State not found');
    }
    
    // Delete all impressions in this state
    $wpdb->delete($impressions_table, array('state_id' => $state_id), array('%d'));
    
    // Delete the state
    $result = $wpdb->delete($states_table, array('id' => $state_id), array('%d'));
    
    if ($result === false) {
        return new WP_Error('state_delete_failed', 'Failed to delete state');
    }
    
    taverne_update_plate_computed_fields($state->plate_id);
    return true;
}

/**
 * Get next state number for a plate
 */
function taverne_get_next_state_number($plate_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    $max = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(state_number) FROM $table WHERE plate_id = %d",
        $plate_id
    ));
    return $max ? ($max + 1) : 1;
}

/**
 * Get state count for a plate
 */
function taverne_get_state_count($plate_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_states';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE plate_id = %d",
        $plate_id
    ));
}

// ============================================
// IMPRESSION CRUD OPERATIONS
// ============================================

/**
 * Create new impression within a state (returns impression_id or WP_Error)
 * Auto-assigns next impression_number for that state
 * Fields: image_id, color, price, availability, changes, notes
 */
function taverne_create_impression($plate_id, $state_id, $data = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    // Verify state exists and belongs to plate
    $state = taverne_get_state($state_id);
    if (!$state || $state->plate_id != $plate_id) {
        return new WP_Error('invalid_state', 'State does not belong to this plate');
    }
    
    // Get next impression number for this state
    $next_number = taverne_get_next_impression_number($state_id);
    
    // Prepare data
    $insert_data = array(
        'plate_id' => absint($plate_id),
        'state_id' => absint($state_id),
        'impression_number' => $next_number,
        'image_id' => isset($data['image_id']) ? absint($data['image_id']) : null,
        'color' => isset($data['color']) ? sanitize_text_field($data['color']) : '',
        'price' => isset($data['price']) ? floatval($data['price']) : 0,
        'availability' => isset($data['availability']) ? sanitize_text_field($data['availability']) : 'available',
        'changes' => isset($data['changes']) ? sanitize_textarea_field($data['changes']) : '',
        'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '',
        'sort_order' => isset($data['sort_order']) ? absint($data['sort_order']) : $next_number
    );
    
    $result = $wpdb->insert($table, $insert_data, array('%d', '%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%d'));
    
    if ($result === false) {
        return new WP_Error('impression_create_failed', 'Failed to create impression', $wpdb->last_error);
    }
    
    taverne_update_plate_computed_fields($plate_id);
    return $wpdb->insert_id;
}

/**
 * Get a single impression by ID
 */
function taverne_get_impression($impression_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $impression_id));
}

/**
 * Get all impressions for a specific state (returns array of objects)
 * Default sort: sort_order ASC (manual ordering)
 */
function taverne_get_impressions_by_state($state_id, $orderby = 'sort_order', $order = 'ASC') {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    $allowed_orderby = array('id', 'impression_number', 'price', 'sort_order', 'created_at');
    $allowed_order = array('ASC', 'DESC');
    
    $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'sort_order';
    $order = in_array($order, $allowed_order) ? $order : 'ASC';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE state_id = %d ORDER BY $orderby $order",
        $state_id
    ));
}

/**
 * Get all impressions for a plate (across all states)
 */
function taverne_get_all_impressions($plate_id, $orderby = 'sort_order', $order = 'ASC') {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    $allowed_orderby = array('id', 'impression_number', 'price', 'sort_order', 'created_at');
    $allowed_order = array('ASC', 'DESC');
    
    $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'sort_order';
    $order = in_array($order, $allowed_order) ? $order : 'ASC';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE plate_id = %d ORDER BY $orderby $order",
        $plate_id
    ));
}

/**
 * Update an impression
 */
function taverne_update_impression($impression_id, $data = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    // Get current impression to get plate_id
    $impression = taverne_get_impression($impression_id);
    if (!$impression) {
        return new WP_Error('impression_not_found', 'Impression not found');
    }
    
    $update_data = array();
    $format = array();
    
    if (isset($data['image_id'])) {
        $update_data['image_id'] = absint($data['image_id']) ?: null;
        $format[] = '%d';
    }
    if (isset($data['color'])) {
        $update_data['color'] = sanitize_text_field($data['color']);
        $format[] = '%s';
    }
    if (isset($data['price'])) {
        $update_data['price'] = floatval($data['price']);
        $format[] = '%f';
    }
    if (isset($data['availability'])) {
        $update_data['availability'] = sanitize_text_field($data['availability']);
        $format[] = '%s';
    }
    if (isset($data['changes'])) {
        $update_data['changes'] = sanitize_textarea_field($data['changes']);
        $format[] = '%s';
    }
    if (isset($data['notes'])) {
        $update_data['notes'] = sanitize_textarea_field($data['notes']);
        $format[] = '%s';
    }
    if (isset($data['sort_order'])) {
        $update_data['sort_order'] = absint($data['sort_order']);
        $format[] = '%d';
    }
    
    if (empty($update_data)) {
        return new WP_Error('no_data', 'No data to update');
    }
    
    $result = $wpdb->update($table, $update_data, array('id' => $impression_id), $format, array('%d'));
    
    if ($result === false) {
        return new WP_Error('impression_update_failed', 'Failed to update impression', $wpdb->last_error);
    }
    
    taverne_update_plate_computed_fields($impression->plate_id);
    return true;
}

/**
 * Delete an impression
 */
function taverne_delete_impression($impression_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    
    // Get impression to get plate_id
    $impression = taverne_get_impression($impression_id);
    if (!$impression) {
        return new WP_Error('impression_not_found', 'Impression not found');
    }
    
    $result = $wpdb->delete($table, array('id' => $impression_id), array('%d'));
    
    if ($result === false) {
        return new WP_Error('impression_delete_failed', 'Failed to delete impression');
    }
    
    taverne_update_plate_computed_fields($impression->plate_id);
    return true;
}

/**
 * Get next impression number for a state
 */
function taverne_get_next_impression_number($state_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    $max = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(impression_number) FROM $table WHERE state_id = %d",
        $state_id
    ));
    return $max ? ($max + 1) : 1;
}

/**
 * Get impression count for a state
 */
function taverne_get_impression_count($state_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE state_id = %d",
        $state_id
    ));
}

/**
 * Get total impression count for a plate
 */
function taverne_get_total_impression_count($plate_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE plate_id = %d",
        $plate_id
    ));
}

/**
 * Get available impression count for a plate
 */
function taverne_get_available_impression_count($plate_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE plate_id = %d AND availability = 'available'",
        $plate_id
    ));
}

/**
 * Get aggregate color palette from all impressions
 */
function taverne_get_palette_aggregate($plate_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'plate_impressions';
    $colors = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT color FROM $table WHERE plate_id = %d AND color != ''",
        $plate_id
    ));
    return !empty($colors) ? implode(', ', $colors) : '';
}
