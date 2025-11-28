<?php
/**
 * Meta Boxes for Plate Editing
 * Renders card-based UI below the main content area
 */

if (!defined('ABSPATH')) exit;

// Add inline CSS for admin UI
add_action('admin_head', 'taverne_meta_boxes_inline_css');
function taverne_meta_boxes_inline_css() {
    global $post_type;
    if ($post_type === 'plate') {
        ?>
        <style>
            .taverne-full-width {
                grid-column: 1 / -1;
            }
        </style>
        <?php
    }
}

/**
 * Register 4 meta boxes for plate admin UI
 * Plate Details, Techniques & Classifications, States & Impressions, SEO
 */
add_action('add_meta_boxes', 'taverne_register_meta_boxes');
function taverne_register_meta_boxes() {
    // Plate Details card (below content)
    add_meta_box(
        'taverne_plate_details',
        'Plate Details',
        'taverne_render_plate_details_box',
        'plate',
        'normal',
        'high'
    );
    
    // Techniques & Classifications card
    add_meta_box(
        'taverne_techniques',
        'Techniques & Classifications',
        'taverne_render_techniques_box',
        'plate',
        'normal',
        'high'
    );
    
    // States & Impressions card
    add_meta_box(
        'taverne_states_impressions',
        'States & Impressions',
        'taverne_render_states_impressions_box',
        'plate',
        'normal',
        'default'
    );
    
    // SEO card (sidebar)
    add_meta_box(
        'taverne_seo',
        'SEO',
        'taverne_render_seo_box',
        'plate',
        'side',
        'default'
    );
}

/**
 * Render Plate Details meta box: description, dimensions, price, year, matrix, study
 * Includes WYSIWYG editor, taxonomy dropdowns, computed size display (readonly)
 */
function taverne_render_plate_details_box($post) {
    // Nonce for security
    wp_nonce_field('taverne_plate_details_nonce', 'taverne_plate_details_nonce');
    
    // Get current values
    $width = get_post_meta($post->ID, '_plate_width', true);
    $height = get_post_meta($post->ID, '_plate_height', true);
    $price = get_post_meta($post->ID, '_plate_price', true);
    $year_slug = get_post_meta($post->ID, '_plate_year', true);
    $matrix_slug = get_post_meta($post->ID, '_plate_matrix', true);
    $study_slug = get_post_meta($post->ID, '_plate_study', true);
    $size_computed = get_post_meta($post->ID, '_plate_size_computed', true);
    $area_computed = get_post_meta($post->ID, '_plate_area_computed', true);
    
    // Get taxonomy terms for dropdowns
    $years = get_terms(array('taxonomy' => 'plate_year', 'hide_empty' => false));
    $matrices = get_terms(array('taxonomy' => 'plate_matrix', 'hide_empty' => false));
    $studies = get_terms(array('taxonomy' => 'plate_study', 'hide_empty' => false));
    
    // Determine selected size based on WIDTH only
    $size_s = $size_m = $size_l = '';
    if ($width < 38) {
        $size_s = 'checked';
    } elseif ($width < 70) {
        $size_m = 'checked';
    } elseif ($width >= 70) {
        $size_l = 'checked';
    }
    ?>
    
    <div class="taverne-card-body">
        <div class="taverne-details-grid">
            
            <!-- Plate Description (Editor) -->
            <div class="taverne-form-row taverne-full-width">
                <label for="plate_description">Plate Description</label>
                <?php 
                wp_editor( 
                    get_post_field( 'post_content', $post->ID ), 
                    'plate_description',
                    array(
                        'textarea_name' => 'content',
                        'textarea_rows' => 8,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => true,
                    )
                );
                ?>
                <p class="taverne-helper">Main description for this plate (shows on detail page)</p>
            </div>
            
            <!-- Plate Excerpt -->
            <div class="taverne-form-row taverne-full-width">
                <label for="excerpt">Short Excerpt</label>
                <textarea 
                    id="excerpt" 
                    name="excerpt" 
                    rows="3"
                    placeholder="Brief summary for cards and previews..."
                ><?php echo esc_textarea( get_post_field( 'post_excerpt', $post->ID ) ); ?></textarea>
                <p class="taverne-helper">Short summary shown in grid/card views</p>
            </div>
            
            <!-- Dimensions -->
            <div class="taverne-form-row">
                <label for="plate_width">Dimensions (cm)</label>
                <div class="taverne-dimensions-inline">
                    <input 
                        type="number" 
                        id="plate_width" 
                        name="plate_width" 
                        value="<?php echo esc_attr($width); ?>" 
                        step="0.1" 
                        min="0"
                        placeholder="30"
                    >
                    <span class="taverne-separator">×</span>
                    <input 
                        type="number" 
                        id="plate_height" 
                        name="plate_height" 
                        value="<?php echo esc_attr($height); ?>" 
                        step="0.1" 
                        min="0"
                        placeholder="40"
                    >
                </div>
                <p class="taverne-helper">Width × Height in centimeters</p>
            </div>
            
            <!-- Base Price -->
            <div class="taverne-form-row">
                <label for="plate_price">Base Price (€)</label>
                <input 
                    type="number" 
                    id="plate_price" 
                    name="plate_price" 
                    value="<?php echo esc_attr($price); ?>" 
                    step="0.01" 
                    min="0"
                    placeholder="250"
                >
                <p class="taverne-helper">Starting price for impressions</p>
            </div>
            
            <!-- Year (Taxonomy) -->
            <div class="taverne-form-row">
                <label for="plate_year">Year</label>
                <select id="plate_year" name="plate_year">
                    <option value="">Select year...</option>
                    <?php 
                    if (!empty($years)) {
                        foreach ($years as $term) {
                            $selected = ($year_slug === $term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <!-- Matrix (Taxonomy) -->
            <div class="taverne-form-row">
                <label for="plate_matrix">Matrix (Plate Material)</label>
                <select id="plate_matrix" name="plate_matrix">
                    <option value="">Select material...</option>
                    <?php 
                    if (!empty($matrices)) {
                        foreach ($matrices as $term) {
                            $selected = ($matrix_slug === $term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <!-- Study/Series (Taxonomy) -->
            <div class="taverne-form-row">
                <label for="plate_study">Study/Series</label>
                <select id="plate_study" name="plate_study">
                    <option value="">Select study...</option>
                    <?php 
                    if (!empty($studies)) {
                        foreach ($studies as $term) {
                            $selected = ($study_slug === $term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <!-- Size (Auto-calculated, readonly) -->
            <div class="taverne-form-row">
                <label>Size (auto from dimensions)</label>
                <div class="taverne-size-radios">
                    <label class="taverne-radio">
                        <input type="radio" name="plate_size" value="S" <?php echo $size_s; ?> disabled>
                        <span>S (0-38cm)</span>
                    </label>
                    <label class="taverne-radio">
                        <input type="radio" name="plate_size" value="M" <?php echo $size_m; ?> disabled>
                        <span>M (38-70cm)</span>
                    </label>
                    <label class="taverne-radio">
                        <input type="radio" name="plate_size" value="L" <?php echo $size_l; ?> disabled>
                        <span>L (70cm+)</span>
                    </label>
                </div>
                <p class="taverne-helper">Auto-calculated based on width</p>
            </div>
            
        </div>
    </div>
    
    <?php
}

/**
 * Save Plate Details meta box
 */
add_action('save_post', 'taverne_save_plate_details');
function taverne_save_plate_details($post_id) {
    // Security checks
    if (!isset($_POST['taverne_plate_details_nonce']) || !wp_verify_nonce($_POST['taverne_plate_details_nonce'], 'taverne_plate_details_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (get_post_type($post_id) !== 'plate') {
        return;
    }
    
    // Save meta fields
    if (isset($_POST['plate_width'])) {
        update_post_meta($post_id, '_plate_width', sanitize_text_field($_POST['plate_width']));
    }
    
    if (isset($_POST['plate_height'])) {
        update_post_meta($post_id, '_plate_height', sanitize_text_field($_POST['plate_height']));
    }
    
    if (isset($_POST['plate_price'])) {
        update_post_meta($post_id, '_plate_price', sanitize_text_field($_POST['plate_price']));
    }
    
    if (isset($_POST['plate_year'])) {
        update_post_meta($post_id, '_plate_year', sanitize_text_field($_POST['plate_year']));
    }
    
    if (isset($_POST['plate_matrix'])) {
        update_post_meta($post_id, '_plate_matrix', sanitize_text_field($_POST['plate_matrix']));
    }
    
    if (isset($_POST['plate_study'])) {
        update_post_meta($post_id, '_plate_study', sanitize_text_field($_POST['plate_study']));
    }
    
    // Update computed fields
    taverne_update_plate_computed_fields($post_id);
}

/**
 * Render Techniques & Classifications meta box
 */
function taverne_render_techniques_box($post) {
    // Add nonce for taxonomy saving
    wp_nonce_field('taverne_techniques_nonce', 'taverne_techniques_nonce');
    
    // Get all plate taxonomies
    $technique_terms = get_terms(array('taxonomy' => 'plate_technique', 'hide_empty' => false));
    $medium_terms = get_terms(array('taxonomy' => 'plate_medium', 'hide_empty' => false));
    $motif_terms = get_terms(array('taxonomy' => 'plate_motif', 'hide_empty' => false));
    $traces_terms = get_terms(array('taxonomy' => 'plate_traces', 'hide_empty' => false));
    
    // Get currently assigned terms
    $current_techniques = wp_get_object_terms($post->ID, 'plate_technique', array('fields' => 'ids'));
    $current_mediums = wp_get_object_terms($post->ID, 'plate_medium', array('fields' => 'ids'));
    $current_motifs = wp_get_object_terms($post->ID, 'plate_motif', array('fields' => 'ids'));
    $current_traces = wp_get_object_terms($post->ID, 'plate_traces', array('fields' => 'ids'));
    
    // Get palette aggregate (readonly)
    $palette_aggregate = get_post_meta($post->ID, '_plate_palette_aggregate', true);
    ?>
    
    <div class="taverne-card-body">
        
        <!-- Techniques -->
        <div class="taverne-taxonomy-section">
            <label class="taverne-taxonomy-label">Techniques</label>
            <div class="taverne-checkbox-grid">
                <?php foreach ($technique_terms as $term) : ?>
                    <label class="taverne-checkbox-item">
                        <input 
                            type="checkbox" 
                            name="taverne_technique[]" 
                            value="<?php echo esc_attr($term->term_id); ?>"
                            <?php checked(in_array($term->term_id, $current_techniques)); ?>
                        >
                        <span><?php echo esc_html($term->name); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Mediums -->
        <div class="taverne-taxonomy-section">
            <label class="taverne-taxonomy-label">Mediums (Support)</label>
            <div class="taverne-checkbox-grid">
                <?php foreach ($medium_terms as $term) : ?>
                    <label class="taverne-checkbox-item">
                        <input 
                            type="checkbox" 
                            name="taverne_medium[]" 
                            value="<?php echo esc_attr($term->term_id); ?>"
                            <?php checked(in_array($term->term_id, $current_mediums)); ?>
                        >
                        <span><?php echo esc_html($term->name); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Motifs -->
        <div class="taverne-taxonomy-section">
            <label class="taverne-taxonomy-label">Motifs</label>
            <div class="taverne-checkbox-grid">
                <?php foreach ($motif_terms as $term) : ?>
                    <label class="taverne-checkbox-item">
                        <input 
                            type="checkbox" 
                            name="taverne_motif[]" 
                            value="<?php echo esc_attr($term->term_id); ?>"
                            <?php checked(in_array($term->term_id, $current_motifs)); ?>
                        >
                        <span><?php echo esc_html($term->name); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Traces -->
        <div class="taverne-taxonomy-section">
            <label class="taverne-taxonomy-label">Traces</label>
            <div class="taverne-checkbox-grid">
                <?php foreach ($traces_terms as $term) : ?>
                    <label class="taverne-checkbox-item">
                        <input 
                            type="checkbox" 
                            name="taverne_traces[]" 
                            value="<?php echo esc_attr($term->term_id); ?>"
                            <?php checked(in_array($term->term_id, $current_traces)); ?>
                        >
                        <span><?php echo esc_html($term->name); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Palette (Auto-aggregated, readonly) -->
        <div class="taverne-taxonomy-section">
            <label class="taverne-taxonomy-label">Palette (auto from impressions)</label>
            <input 
                type="text" 
                class="taverne-palette-display" 
                value="<?php echo esc_attr($palette_aggregate ?: 'No impressions yet'); ?>" 
                readonly
            >
            <p class="taverne-helper">Automatically aggregated from impression colors</p>
        </div>
        
    </div>
    
    <?php
}

/**
 * Save Techniques & Classifications meta box
 * CRITICAL FIX: Validates term IDs before saving to prevent duplicate term creation
 */
add_action('save_post', 'taverne_save_techniques_box', 10, 1);
function taverne_save_techniques_box($post_id) {
    // Security checks
    if (!isset($_POST['taverne_techniques_nonce']) || !wp_verify_nonce($_POST['taverne_techniques_nonce'], 'taverne_techniques_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (get_post_type($post_id) !== 'plate') {
        return;
    }
    
    // Save taxonomies with validation
    $taxonomy_mappings = array(
        'taverne_technique' => 'plate_technique',
        'taverne_medium' => 'plate_medium',
        'taverne_motif' => 'plate_motif',
        'taverne_traces' => 'plate_traces'
    );
    
    foreach ($taxonomy_mappings as $input_name => $taxonomy) {
        if (isset($_POST[$input_name]) && is_array($_POST[$input_name])) {
            // Convert to integers
            $term_ids = array_map('intval', $_POST[$input_name]);
            
            // CRITICAL: Validate that these term IDs actually exist in this taxonomy
            $valid_term_ids = array();
            foreach ($term_ids as $term_id) {
                $term = get_term($term_id, $taxonomy);
                if ($term && !is_wp_error($term)) {
                    // Double-check the term belongs to the correct taxonomy
                    if ($term->taxonomy === $taxonomy) {
                        $valid_term_ids[] = (int) $term->term_id;
                    }
                }
            }
            
            // Set terms using validated IDs only
            // The third parameter FALSE means replace all terms (don't append)
            if (!empty($valid_term_ids)) {
                wp_set_object_terms($post_id, $valid_term_ids, $taxonomy, false);
            } else {
                // Clear all terms if none are valid
                wp_set_object_terms($post_id, array(), $taxonomy, false);
            }
        } else {
            // Clear terms if checkbox group not set (none checked)
            wp_set_object_terms($post_id, array(), $taxonomy, false);
        }
    }
}

/**
 * Render States & Impressions meta box
 */
function taverne_render_states_impressions_box($post) {
    // Get all states for this plate
    $states = taverne_get_states($post->ID);
    $state_count = count($states);
    
    // Get total impression count
    $total_impressions = taverne_get_total_impression_count($post->ID);
    $available_impressions = taverne_get_available_impression_count($post->ID);
    
    // Get color terms for impressions
    $color_terms = get_terms(array('taxonomy' => 'plate_palette', 'hide_empty' => false));
    ?>
    
    <div class="taverne-card-body">
        
        <div class="taverne-states-header">
            <div class="taverne-states-summary">
                <strong><?php echo esc_html($state_count); ?></strong> States
                <span class="taverne-summary-meta">(<?php echo esc_html($total_impressions); ?> total impressions, <?php echo esc_html($available_impressions); ?> available)</span>
            </div>
        </div>
        
        <?php if (empty($states)) : ?>
            <p class="taverne-empty-state">No states yet. Add one to get started.</p>
        <?php else : ?>
            
            <?php foreach ($states as $index => $state) : ?>
                <?php 
                $impressions = taverne_get_impressions_by_state($state->id);
                $impression_count = count($impressions);
                ?>
                
                <div class="taverne-state-card" data-state-id="<?php echo esc_attr($state->id); ?>">
                    
                    <!-- State Header -->
                    <div class="taverne-state-header">
                        <div class="taverne-state-number-badge">
                            State <?php echo esc_html($state->state_number); ?>
                        </div>
                        <div class="taverne-state-impression-count">
                            <?php echo esc_html($impression_count); ?> impression<?php echo ($impression_count !== 1) ? 's' : ''; ?>
                        </div>
                        <div class="taverne-state-actions">
                            <button type="button" class="taverne-icon-btn taverne-add-impression" data-state-id="<?php echo esc_attr($state->id); ?>" title="Add impression">
                                <span class="dashicons dashicons-plus-alt2"></span>
                            </button>
                            <button type="button" class="taverne-icon-btn taverne-delete-state" data-state-id="<?php echo esc_attr($state->id); ?>" title="Delete state">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- State Details (collapsible) -->
                    <div class="taverne-state-body">
                        
                        <div class="taverne-state-fields">
                            <div class="taverne-field-group">
                                <label>State Title</label>
                                <input 
                                    type="text" 
                                    class="taverne-state-title" 
                                    data-state-id="<?php echo esc_attr($state->id); ?>"
                                    value="<?php echo esc_attr($state->title); ?>"
                                    placeholder="e.g., First State"
                                >
                            </div>
                            
                            <div class="taverne-field-group">
                                <label>Excerpt</label>
                                <input 
                                    type="text" 
                                    class="taverne-state-excerpt" 
                                    data-state-id="<?php echo esc_attr($state->id); ?>"
                                    value="<?php echo esc_attr($state->excerpt); ?>"
                                    placeholder="Brief description"
                                >
                            </div>
                            
                            <div class="taverne-field-group">
                                <label>Description</label>
                                <textarea 
                                    class="taverne-state-description" 
                                    data-state-id="<?php echo esc_attr($state->id); ?>"
                                    placeholder="Detailed notes about this state..."
                                    rows="3"
                                ><?php echo esc_textarea($state->description); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Impressions Table -->
                        <?php if (!empty($impressions)) : ?>
                            <div class="taverne-impressions-list">
                                <?php foreach ($impressions as $impression) : ?>
                                    <?php taverne_render_impression_card($impression, $color_terms); ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p class="taverne-empty-impressions">No impressions yet. Click + to add one.</p>
                        <?php endif; ?>
                        
                    </div>
                    
                </div>
                
            <?php endforeach; ?>
            
        <?php endif; ?>
        
        <!-- Add State Button -->
        <div class="taverne-add-state-wrapper">
            <button type="button" class="button button-primary button-large taverne-add-state" data-plate-id="<?php echo esc_attr($post->ID); ?>">
                <span class="dashicons dashicons-plus-alt2"></span> Add New State
            </button>
        </div>
        
    </div>
    
    <?php
}

/**
 * Render a single impression card
 */
function taverne_render_impression_card($impression, $color_terms) {
    ?>
    
    <div class="taverne-impression-row" data-impression-id="<?php echo esc_attr($impression->id); ?>">
        
        <div class="taverne-impression-handle">
            <span class="dashicons dashicons-menu"></span>
        </div>
        
        <!-- Clickable thumbnail for image upload (matches admin.js expectations) -->
        <div class="taverne-impression-thumb taverne-upload-trigger" 
             data-impression-id="<?php echo esc_attr($impression->id); ?>"
             title="Click to upload image">
            <?php if ($impression->image_id) {
                echo wp_get_attachment_image($impression->image_id, 'thumbnail');
            } else { ?>
                <div class="taverne-thumb-placeholder">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
            <?php } ?>
        </div>
        
        <div class="taverne-impression-number">
            <?php echo esc_html($impression->impression_number); ?>/<?php echo taverne_get_impression_count($impression->state_id); ?>
        </div>
        
        <div class="taverne-impression-fields">
            
            <div class="taverne-field-group">
                <label>Color</label>
                <select class="taverne-impression-field" data-field="color" data-impression-id="<?php echo esc_attr($impression->id); ?>">
                    <option value="">Select color...</option>
                    <?php 
                    foreach ($color_terms as $term) {
                        $selected = ($impression->color === $term->slug) ? 'selected' : '';
                        echo '<option value="' . esc_attr($term->slug) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="taverne-field-group">
                <label>Price</label>
                <input 
                    type="number" 
                    class="taverne-impression-field" 
                    data-field="price" 
                    data-impression-id="<?php echo esc_attr($impression->id); ?>"
                    value="<?php echo esc_attr($impression->price); ?>"
                    step="0.01"
                    min="0"
                    placeholder="€"
                >
            </div>
            
            <div class="taverne-field-group taverne-availability-group">
                <label>Availability</label>
                <div class="taverne-status-lights">
                    <div class="taverne-status-light taverne-status-available <?php echo ($impression->availability === 'available') ? 'active' : ''; ?>" 
                         data-status="available" 
                         data-impression-id="<?php echo esc_attr($impression->id); ?>"
                         title="Available for sale">
                    </div>
                    <div class="taverne-status-light taverne-status-artist <?php echo ($impression->availability === 'artist') ? 'active' : ''; ?>" 
                         data-status="artist" 
                         data-impression-id="<?php echo esc_attr($impression->id); ?>"
                         title="Artist collection">
                    </div>
                    <div class="taverne-status-light taverne-status-sold <?php echo ($impression->availability === 'sold') ? 'active' : ''; ?>" 
                         data-status="sold" 
                         data-impression-id="<?php echo esc_attr($impression->id); ?>"
                         title="Sold">
                    </div>
                </div>
            </div>
            
            <div class="taverne-field-group">
                <label>Changes</label>
                <input 
                    type="text" 
                    class="taverne-impression-field" 
                    data-field="changes" 
                    data-impression-id="<?php echo esc_attr($impression->id); ?>"
                    value="<?php echo esc_attr($impression->changes); ?>"
                    placeholder="e.g., Enhanced lines"
                >
            </div>
            
            <div class="taverne-field-group taverne-notes-group">
                <label>Notes</label>
                <input 
                    type="text" 
                    class="taverne-impression-field" 
                    data-field="notes" 
                    data-impression-id="<?php echo esc_attr($impression->id); ?>"
                    value="<?php echo esc_attr($impression->notes); ?>"
                    placeholder="Internal notes"
                >
            </div>
            
        </div>
        
        <div class="taverne-impression-actions">
            <button type="button" class="taverne-icon-btn taverne-delete-impression" data-impression-id="<?php echo esc_attr($impression->id); ?>" title="Delete impression">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        
    </div>
    
    <?php
}

/**
 * Render SEO meta box
 */
function taverne_render_seo_box($post) {
    // Nonce for security
    wp_nonce_field('taverne_seo_nonce', 'taverne_seo_nonce');
    
    // Get current values
    $meta_title = get_post_meta($post->ID, '_taverne_meta_title', true);
    $meta_description = get_post_meta($post->ID, '_taverne_meta_description', true);
    $canonical_url = get_post_meta($post->ID, '_taverne_canonical_url', true) ?: get_permalink($post->ID);
    $noindex = get_post_meta($post->ID, '_taverne_noindex', true);
    ?>
    
    <div class="taverne-seo-body">
        
        <div class="taverne-form-row">
            <label for="taverne_meta_title">Meta Title</label>
            <input 
                type="text" 
                id="taverne_meta_title" 
                name="taverne_meta_title" 
                value="<?php echo esc_attr($meta_title); ?>"
                placeholder="Leave blank to use post title"
            >
        </div>
        
        <div class="taverne-form-row">
            <label for="taverne_meta_description">Meta Description</label>
            <textarea 
                id="taverne_meta_description" 
                name="taverne_meta_description" 
                rows="3"
                placeholder="Brief description for search engines..."
            ><?php echo esc_textarea($meta_description); ?></textarea>
            <p class="taverne-helper taverne-char-count">
                <span id="taverne-char-counter">0</span> characters (aim for 120-160)
            </p>
        </div>
        
        <div class="taverne-form-row">
            <label for="taverne_canonical_url">Canonical URL</label>
            <input 
                type="url" 
                id="taverne_canonical_url" 
                name="taverne_canonical_url" 
                value="<?php echo esc_attr($canonical_url); ?>"
                placeholder="<?php echo esc_url(get_permalink($post->ID)); ?>"
            >
            <p class="taverne-helper">Usually the post's permalink</p>
        </div>
        
        <div class="taverne-form-row">
            <label class="taverne-checkbox-label">
                <input 
                    type="checkbox" 
                    id="taverne_noindex" 
                    name="taverne_noindex" 
                    value="1"
                    <?php checked($noindex, '1'); ?>
                >
                <span>Hide from search engines (noindex)</span>
            </label>
        </div>
        
    </div>
    
    <?php
}

/**
 * Save SEO meta box
 */
add_action('save_post', 'taverne_save_seo_box');
function taverne_save_seo_box($post_id) {
    // Security checks
    if (!isset($_POST['taverne_seo_nonce']) || !wp_verify_nonce($_POST['taverne_seo_nonce'], 'taverne_seo_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (get_post_type($post_id) !== 'plate') {
        return;
    }
    
    // Save meta fields
    if (isset($_POST['taverne_meta_title'])) {
        update_post_meta($post_id, '_taverne_meta_title', sanitize_text_field($_POST['taverne_meta_title']));
    }
    
    if (isset($_POST['taverne_meta_description'])) {
        update_post_meta($post_id, '_taverne_meta_description', sanitize_textarea_field($_POST['taverne_meta_description']));
    }
    
    if (isset($_POST['taverne_canonical_url'])) {
        update_post_meta($post_id, '_taverne_canonical_url', esc_url_raw($_POST['taverne_canonical_url']));
    }
    
    // Checkbox handling
    if (isset($_POST['taverne_noindex'])) {
        update_post_meta($post_id, '_taverne_noindex', '1');
    } else {
        delete_post_meta($post_id, '_taverne_noindex');
    }
}
