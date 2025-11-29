<?php
/**
 * Taverne Clean Theme - single-plate.php
 * 
 * Single plate (product) template.
 * The money maker - where impressions get sold.
 * 
 * Features:
 * - States as tabs
 * - Impression selector with live updates
 * - Lightbox zoom
 * - Add to cart
 * - Related works
 * 
 * URL: /plates/{plate-slug}/
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// GET PLATE DATA
// =============================================================================

$plate_id = get_the_ID();
$plate_title = get_the_title();
$plate_slug = get_post_field('post_name', $plate_id);

// Get states and impressions from taverne-meta
$states = function_exists('taverne_get_states') ? taverne_get_states($plate_id) : [];
$all_impressions = function_exists('taverne_get_all_impressions') ? taverne_get_all_impressions($plate_id) : [];

// Get the first available impression as default
$current_impression = null;
$current_state = null;

foreach ($all_impressions as $imp) {
    if ($imp->availability === 'available') {
        $current_impression = $imp;
        break;
    }
}

// Fallback to first impression if none available
if (!$current_impression && !empty($all_impressions)) {
    $current_impression = $all_impressions[0];
}

// Find current state
if ($current_impression && !empty($states)) {
    foreach ($states as $state) {
        if ($state->id === $current_impression->state_id) {
            $current_state = $state;
            break;
        }
    }
}

// Fallback to first state
if (!$current_state && !empty($states)) {
    $current_state = $states[0];
}

// =============================================================================
// GET META DATA
// =============================================================================

$plate_year = get_post_meta($plate_id, '_plate_year', true);
$plate_width = get_post_meta($plate_id, '_plate_width', true);
$plate_height = get_post_meta($plate_id, '_plate_height', true);
$plate_size = get_post_meta($plate_id, '_plate_size_computed', true);
$plate_description = get_post_meta($plate_id, '_plate_description', true);
$total_impressions = get_post_meta($plate_id, '_plate_total_impressions', true);
$available_impressions = get_post_meta($plate_id, '_plate_available_impressions', true);

// Get taxonomies
$technique_terms = get_the_terms($plate_id, 'plate_technique');
$motif_terms = get_the_terms($plate_id, 'plate_motif');
$palette_terms = get_the_terms($plate_id, 'plate_palette');
$matrix_terms = get_the_terms($plate_id, 'plate_matrix');
$series_terms = get_the_terms($plate_id, 'plate_series');

// =============================================================================
// PREPARE IMAGE DATA FOR JAVASCRIPT
// =============================================================================

$impressions_data = [];
foreach ($all_impressions as $imp) {
    $img_src = $imp->image_id ? wp_get_attachment_image_src($imp->image_id, 'plate-large') : null;
    $thumb_src = $imp->image_id ? wp_get_attachment_image_src($imp->image_id, 'plate-thumb') : null;
    
    $impressions_data[] = [
        'id'               => $imp->id,
        'state_id'         => $imp->state_id,
        'impressionNumber' => $imp->impressionNumber ?? '',
        'color'            => $imp->color ?? '',
        'price'            => floatval($imp->price ?? 0),
        'availability'     => $imp->availability ?? 'available',
        'notes'            => $imp->notes ?? '',
        'image_url'        => $img_src ? $img_src[0] : '',
        'thumb_url'        => $thumb_src ? $thumb_src[0] : '',
    ];
}

$states_data = [];
foreach ($states as $state) {
    $states_data[] = [
        'id'          => $state->id,
        'stateNumber' => $state->stateNumber ?? '',
        'title'       => $state->title ?? '',
        'description' => $state->description ?? '',
    ];
}

?>

<main class="single-plate">
    
    <?php // SUB-HEADER: Breadcrumbs ?>
    <nav class="sub-header">
        <a href="<?php echo esc_url(wp_get_referer() ?: get_post_type_archive_link('plate')); ?>" class="back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <div class="breadcrumbs">
            <a href="<?php echo esc_url(home_url()); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
            </a>
            <span class="separator">/</span>
            <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>">Plates</a>
            <?php if ($technique_terms && !is_wp_error($technique_terms)) : ?>
                <span class="separator">/</span>
                <a href="<?php echo esc_url(get_term_link($technique_terms[0])); ?>"><?php echo esc_html($technique_terms[0]->name); ?></a>
            <?php endif; ?>
            <span class="separator">/</span>
            <span class="current"><?php echo esc_html($plate_title); ?></span>
        </div>
    </nav>
    
    <?php // PRODUCT LAYOUT: Stage + Data ?>
    <div class="container">
        <div class="product-layout">
            
            <?php // ================================================================
                  // LEFT COLUMN: PRODUCT STAGE (Images)
                  // ================================================================ ?>
            <section class="product-stage">
                
                <?php // States Tabs (if multiple states) ?>
                <?php if (count($states) > 1) : ?>
                    <div class="states-tabs">
                        <?php foreach ($states as $i => $state) : ?>
                            <button 
                                class="tab-btn <?php echo ($current_state && $state->id === $current_state->id) ? 'active' : ''; ?>"
                                data-state-id="<?php echo esc_attr($state->id); ?>"
                                onclick="switchState(<?php echo esc_attr($state->id); ?>)"
                            >
                                State <?php echo esc_html($state->stateNumber ?? ($i + 1)); ?>
                                <?php if ($state->title) : ?>
                                    <span class="state-title">– <?php echo esc_html($state->title); ?></span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php // Main Canvas Area ?>
                <div class="canvas-area" onclick="openProductLightbox()">
                    <?php 
                    $hero_image_url = '';
                    if ($current_impression && $current_impression->image_id) {
                        $hero_src = wp_get_attachment_image_src($current_impression->image_id, 'plate-large');
                        $hero_image_url = $hero_src ? $hero_src[0] : '';
                    } elseif (has_post_thumbnail()) {
                        $hero_image_url = get_the_post_thumbnail_url($plate_id, 'plate-large');
                    }
                    ?>
                    <img 
                        id="hero-img" 
                        src="<?php echo esc_url($hero_image_url); ?>" 
                        alt="<?php echo esc_attr($plate_title); ?>"
                    >
                    
                    <?php // Zoom hint ?>
                    <div class="zoom-hint">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <line x1="11" y1="8" x2="11" y2="14"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                        Click to zoom
                    </div>
                </div>
                
                <?php // Thumbnail Strip ?>
                <?php if (!empty($all_impressions)) : ?>
                    <div class="thumbs no-scrollbar">
                        <?php foreach ($all_impressions as $imp) : 
                            $thumb_src = $imp->image_id ? wp_get_attachment_image_src($imp->image_id, 'plate-thumb') : null;
                            if (!$thumb_src) continue;
                            $is_active = ($current_impression && $imp->id === $current_impression->id);
                        ?>
                            <img 
                                src="<?php echo esc_url($thumb_src[0]); ?>" 
                                alt="<?php echo esc_attr($plate_title . ' #' . ($imp->impressionNumber ?? $imp->id)); ?>"
                                class="thumb <?php echo $is_active ? 'active' : ''; ?>"
                                data-imp-id="<?php echo esc_attr($imp->id); ?>"
                                onclick="selectImpression(<?php echo esc_attr($imp->id); ?>)"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </section>
            
            <?php // ================================================================
                  // RIGHT COLUMN: PRODUCT DATA (Specs, Selector, Buy)
                  // ================================================================ ?>
            <aside class="product-data">
                
                <?php // Title & Tags ?>
                <div class="product-header">
                    <h1><?php echo esc_html($plate_title); ?></h1>
                    
                    <?php // Taxonomy Pills ?>
                    <div class="taxonomy-pills">
                        <?php if ($technique_terms && !is_wp_error($technique_terms)) : ?>
                            <?php foreach ($technique_terms as $term) : ?>
                                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="taxonomy-pill">
                                    <?php echo esc_html($term->name); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($palette_terms && !is_wp_error($palette_terms)) : ?>
                            <?php foreach ($palette_terms as $term) : ?>
                                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="taxonomy-pill">
                                    <?php echo esc_html($term->name); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($plate_year) : ?>
                            <span class="taxonomy-pill"><?php echo esc_html($plate_year); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php // Description ?>
                <?php if ($plate_description) : ?>
                    <div class="product-description">
                        <p><?php echo esc_html($plate_description); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php // Specifications Grid ?>
                <div class="specs-grid">
                    <?php if ($technique_terms && !is_wp_error($technique_terms)) : ?>
                        <div class="spec-item">
                            <strong>Technique</strong>
                            <?php echo esc_html($technique_terms[0]->name); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($matrix_terms && !is_wp_error($matrix_terms)) : ?>
                        <div class="spec-item">
                            <strong>Matrix</strong>
                            <?php echo esc_html($matrix_terms[0]->name); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($plate_width && $plate_height) : ?>
                        <div class="spec-item">
                            <strong>Dimensions</strong>
                            <?php echo esc_html($plate_width . ' × ' . $plate_height . ' cm'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($plate_size) : ?>
                        <div class="spec-item">
                            <strong>Size</strong>
                            <?php echo esc_html(ucfirst($plate_size)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="spec-item">
                        <strong>Edition</strong>
                        <?php echo esc_html($total_impressions ?: count($all_impressions)); ?> impressions
                    </div>
                    
                    <div class="spec-item">
                        <strong>Available</strong>
                        <span id="available-count"><?php echo esc_html($available_impressions ?: '0'); ?></span> available
                    </div>
                </div>
                
                <?php // Edition Selector ?>
                <?php if (!empty($all_impressions)) : ?>
                    <div class="edition-selector">
                        <label for="imp-select">Select Impression</label>
                        <select id="imp-select" onchange="selectImpression(this.value)">
                            <?php foreach ($all_impressions as $imp) : 
                                $is_selected = ($current_impression && $imp->id === $current_impression->id);
                                $is_available = ($imp->availability === 'available');
                                $status_text = $is_available ? '' : ' (' . ucfirst($imp->availability) . ')';
                            ?>
                                <option 
                                    value="<?php echo esc_attr($imp->id); ?>"
                                    <?php echo $is_selected ? 'selected' : ''; ?>
                                    <?php echo !$is_available ? 'disabled' : ''; ?>
                                >
                                    #<?php echo esc_html($imp->impressionNumber ?? $imp->id); ?>
                                    <?php if ($imp->color) : ?>
                                        – <?php echo esc_html($imp->color); ?>
                                    <?php endif; ?>
                                    <?php echo $status_text; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php // Selected Impression Details ?>
                <div id="imp-details" class="imp-box">
                    <div class="imp-status">
                        <span class="status-light <?php echo esc_attr($current_impression->availability ?? 'available'); ?>"></span>
                        <span id="status-text"><?php echo esc_html(ucfirst($current_impression->availability ?? 'Available')); ?></span>
                    </div>
                    
                    <div class="imp-price">
                        <span class="price-label">Price</span>
                        <span id="dynamic-price" class="price-value">€<?php echo number_format($current_impression->price ?? 0, 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($current_impression && $current_impression->notes) : ?>
                        <div class="imp-notes">
                            <span class="notes-label">Notes</span>
                            <span id="imp-notes-text"><?php echo esc_html($current_impression->notes); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php // Buy Form ?>
                <form id="buy-form" class="buy-form" method="post" action="">
                    <?php wp_nonce_field('taverne_add_cart', 'taverne_cart_nonce'); ?>
                    <input type="hidden" name="action" value="taverne_add_to_cart">
                    <input type="hidden" name="plate_id" value="<?php echo esc_attr($plate_id); ?>">
                    <input type="hidden" name="imp_id" id="selected-imp-id" value="<?php echo esc_attr($current_impression->id ?? ''); ?>">
                    
                    <div class="qty-row">
                        <label for="qty">Quantity</label>
                        <input type="number" name="qty" id="qty" min="1" max="1" value="1">
                    </div>
                    
                    <button 
                        type="submit" 
                        id="add-to-cart-btn"
                        class="btn btn-primary btn-full"
                        <?php echo ($current_impression && $current_impression->availability !== 'available') ? 'disabled' : ''; ?>
                    >
                        <?php if ($current_impression && $current_impression->availability === 'available') : ?>
                            Add to Cart – €<span id="cart-price"><?php echo number_format($current_impression->price ?? 0, 2, ',', '.'); ?></span>
                        <?php elseif ($current_impression && $current_impression->availability === 'sold') : ?>
                            Sold
                        <?php else : ?>
                            Not Available
                        <?php endif; ?>
                    </button>
                </form>
                
                <?php // Shipping/Contact Info ?>
                <div class="product-meta-info">
                    <p><strong>Shipping:</strong> Worldwide, insured. Contact for quote.</p>
                    <p><strong>Questions?</strong> <a href="mailto:info@poltaverne.nl" class="hover-line">Get in touch</a></p>
                </div>
                
                <?php // Series Link (if part of series) ?>
                <?php if ($series_terms && !is_wp_error($series_terms)) : ?>
                    <div class="product-series">
                        <span>Part of:</span>
                        <?php foreach ($series_terms as $term) : ?>
                            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="series-link hover-line">
                                <?php echo esc_html($term->name); ?> →
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            </aside>
            
        </div>
    </div>
    
    <?php // ================================================================
          // RELATED WORKS SECTION
          // ================================================================ ?>
    <section class="related-section section">
        <div class="container">
            <h2>Related Works</h2>
            
            <div class="artwork-grid">
                <?php
                // Query related plates (same series or technique)
                $related_args = [
                    'post_type'      => 'plate',
                    'posts_per_page' => 4,
                    'post__not_in'   => [$plate_id],
                    'meta_query'     => [
                        [
                            'key'     => '_plate_available_impressions',
                            'value'   => 0,
                            'compare' => '>',
                            'type'    => 'NUMERIC',
                        ],
                    ],
                ];
                
                // Prefer same series
                if ($series_terms && !is_wp_error($series_terms)) {
                    $related_args['tax_query'] = [
                        [
                            'taxonomy' => 'plate_series',
                            'field'    => 'term_id',
                            'terms'    => wp_list_pluck($series_terms, 'term_id'),
                        ],
                    ];
                } 
                // Fallback to same technique
                elseif ($technique_terms && !is_wp_error($technique_terms)) {
                    $related_args['tax_query'] = [
                        [
                            'taxonomy' => 'plate_technique',
                            'field'    => 'term_id',
                            'terms'    => wp_list_pluck($technique_terms, 'term_id'),
                        ],
                    ];
                }
                
                $related_query = new WP_Query($related_args);
                
                if ($related_query->have_posts()) :
                    while ($related_query->have_posts()) : $related_query->the_post();
                        $rel_impression = null;
                        if (function_exists('taverne_get_impressions')) {
                            $rel_impressions = taverne_get_impressions(get_the_ID());
                            $rel_impression = !empty($rel_impressions) ? $rel_impressions[0] : null;
                        }
                        
                        get_template_part('template-parts/content-impression-card', null, [
                            'post_id'    => get_the_ID(),
                            'impression' => $rel_impression,
                        ]);
                    endwhile;
                    wp_reset_postdata();
                else :
                    // Fallback: just get latest plates
                    $fallback_args = [
                        'post_type'      => 'plate',
                        'posts_per_page' => 4,
                        'post__not_in'   => [$plate_id],
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    ];
                    
                    $fallback_query = new WP_Query($fallback_args);
                    
                    if ($fallback_query->have_posts()) :
                        while ($fallback_query->have_posts()) : $fallback_query->the_post();
                            $rel_impression = null;
                            if (function_exists('taverne_get_impressions')) {
                                $rel_impressions = taverne_get_impressions(get_the_ID());
                                $rel_impression = !empty($rel_impressions) ? $rel_impressions[0] : null;
                            }
                            
                            get_template_part('template-parts/content-impression-card', null, [
                                'post_id'    => get_the_ID(),
                                'impression' => $rel_impression,
                            ]);
                        endwhile;
                        wp_reset_postdata();
                    endif;
                endif;
                ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline">
                    View All Works
                </a>
            </div>
        </div>
    </section>
    
</main>

<?php // Canonical URL (self-referencing for SEO) ?>
<link rel="canonical" href="<?php echo esc_url(get_permalink($plate_id)); ?>">

<style>
/* =============================================================================
   SINGLE PLATE COMPONENT STYLES
   ============================================================================= */

/* Product Header */
.product-header {
    margin-bottom: var(--space-6);
}

.product-header h1 {
    margin-bottom: var(--space-3);
}

/* Product Description */
.product-description {
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: var(--border);
}

.product-description p {
    color: var(--charcoal);
    line-height: 1.8;
}

/* Zoom Hint */
.canvas-area {
    position: relative;
}

.zoom-hint {
    position: absolute;
    bottom: var(--space-4);
    right: var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
    color: var(--ink-secondary);
    background: rgba(255, 255, 255, 0.9);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius);
    opacity: 0;
    transition: opacity var(--transition-fast);
}

.canvas-area:hover .zoom-hint {
    opacity: 1;
}

/* States Tabs */
.states-tabs {
    display: flex;
    gap: 0;
    border-bottom: var(--border);
    margin-bottom: var(--space-4);
    overflow-x: auto;
}

.tab-btn .state-title {
    font-weight: 400;
    color: var(--ink-secondary);
    margin-left: var(--space-1);
}

/* Impression Box */
.imp-box {
    display: grid;
    gap: var(--space-4);
}

.imp-status {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.imp-price {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}

.price-label,
.notes-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--ink-secondary);
}

.price-value {
    font-family: var(--serif);
    font-size: var(--text-2xl);
    font-weight: 500;
}

.imp-notes {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    font-size: var(--text-sm);
    color: var(--charcoal);
}

/* Buy Form */
.qty-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.qty-row input {
    width: 80px;
    text-align: center;
}

/* Product Meta Info */
.product-meta-info {
    margin-top: var(--space-6);
    padding-top: var(--space-6);
    border-top: var(--border);
    font-size: var(--text-sm);
    color: var(--charcoal);
}

.product-meta-info p {
    margin-bottom: var(--space-2);
}

/* Series Link */
.product-series {
    margin-top: var(--space-6);
    padding: var(--space-4);
    background: var(--stone);
    border-radius: var(--radius);
    font-size: var(--text-sm);
}

.product-series span {
    color: var(--ink-secondary);
}

.series-link {
    display: inline-block;
    margin-left: var(--space-2);
    font-weight: 500;
}

/* Related Section */
.related-section {
    background: #fafafa;
}

.related-section h2 {
    margin-bottom: var(--space-8);
}

/* Status light colors */
.status-light.available { background: var(--status-available); }
.status-light.artist { background: var(--status-artist); }
.status-light.sold { background: var(--status-sold); }
</style>

<script>
// =============================================================================
// SINGLE PLATE JAVASCRIPT
// =============================================================================

// Plate data from PHP
const plateData = {
    plateId: <?php echo json_encode($plate_id); ?>,
    plateSlug: <?php echo json_encode($plate_slug); ?>,
    impressions: <?php echo json_encode($impressions_data); ?>,
    states: <?php echo json_encode($states_data); ?>,
    currentImpId: <?php echo json_encode($current_impression->id ?? null); ?>,
    currentStateId: <?php echo json_encode($current_state->id ?? null); ?>
};

// Current selected impression
let currentImpId = plateData.currentImpId;

/**
 * Select an impression by ID
 */
function selectImpression(impId) {
    impId = parseInt(impId);
    const imp = plateData.impressions.find(i => i.id === impId);
    
    if (!imp) return;
    
    currentImpId = impId;
    
    // Update hero image
    const heroImg = document.getElementById('hero-img');
    if (heroImg && imp.image_url) {
        heroImg.src = imp.image_url;
    }
    
    // Update thumbnail active state
    document.querySelectorAll('.thumb').forEach(thumb => {
        thumb.classList.toggle('active', parseInt(thumb.dataset.impId) === impId);
    });
    
    // Update dropdown selection
    const select = document.getElementById('imp-select');
    if (select) {
        select.value = impId;
    }
    
    // Update price display
    const priceEl = document.getElementById('dynamic-price');
    const cartPriceEl = document.getElementById('cart-price');
    const formattedPrice = formatPrice(imp.price);
    
    if (priceEl) priceEl.textContent = formattedPrice;
    if (cartPriceEl) cartPriceEl.textContent = formattedPrice;
    
    // Update status
    const statusText = document.getElementById('status-text');
    const statusLight = document.querySelector('.status-light');
    
    if (statusText) {
        statusText.textContent = capitalizeFirst(imp.availability);
    }
    
    if (statusLight) {
        statusLight.className = 'status-light ' + imp.availability;
    }
    
    // Update notes
    const notesEl = document.getElementById('imp-notes-text');
    if (notesEl) {
        notesEl.textContent = imp.notes || '';
        notesEl.closest('.imp-notes').style.display = imp.notes ? 'flex' : 'none';
    }
    
    // Update hidden form field
    const hiddenInput = document.getElementById('selected-imp-id');
    if (hiddenInput) {
        hiddenInput.value = impId;
    }
    
    // Update button state
    const btn = document.getElementById('add-to-cart-btn');
    if (btn) {
        if (imp.availability === 'available') {
            btn.disabled = false;
            btn.innerHTML = 'Add to Cart – €<span id="cart-price">' + formattedPrice + '</span>';
        } else if (imp.availability === 'sold') {
            btn.disabled = true;
            btn.textContent = 'Sold';
        } else {
            btn.disabled = true;
            btn.textContent = 'Not Available';
        }
    }
}

/**
 * Switch to a different state (filter impressions)
 */
function switchState(stateId) {
    stateId = parseInt(stateId);
    
    // Update tab active state
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.classList.toggle('active', parseInt(tab.dataset.stateId) === stateId);
    });
    
    // Filter impressions by state
    const stateImpressions = plateData.impressions.filter(i => i.state_id === stateId);
    
    // Update thumbnails to show only this state's impressions
    document.querySelectorAll('.thumb').forEach(thumb => {
        const impId = parseInt(thumb.dataset.impId);
        const imp = plateData.impressions.find(i => i.id === impId);
        
        if (imp) {
            thumb.style.display = (imp.state_id === stateId) ? 'block' : 'none';
        }
    });
    
    // Select first available impression in this state
    const firstAvailable = stateImpressions.find(i => i.availability === 'available') || stateImpressions[0];
    
    if (firstAvailable) {
        selectImpression(firstAvailable.id);
    }
}

/**
 * Open lightbox with current impression
 */
function openProductLightbox() {
    const imp = plateData.impressions.find(i => i.id === currentImpId);
    
    if (imp && imp.image_url) {
        openLightbox(imp.image_url, '<?php echo esc_js($plate_title); ?>');
        
        // Populate lightbox thumbs
        const thumbsContainer = document.getElementById('lightbox-thumbs');
        if (thumbsContainer) {
            thumbsContainer.innerHTML = plateData.impressions
                .filter(i => i.thumb_url)
                .map(i => `
                    <img 
                        src="${i.thumb_url}" 
                        alt="Thumbnail"
                        class="${i.id === currentImpId ? 'active' : ''}"
                        onclick="selectImpressionFromLightbox(${i.id})"
                    >
                `).join('');
        }
    }
}

/**
 * Select impression from lightbox thumbnail
 */
function selectImpressionFromLightbox(impId) {
    selectImpression(impId);
    
    // Update lightbox image
    const imp = plateData.impressions.find(i => i.id === impId);
    if (imp) {
        document.getElementById('lightbox-img').src = imp.image_url;
        
        // Update lightbox thumb active states
        document.querySelectorAll('.lightbox-thumbs img').forEach(thumb => {
            // Compare by checking if the onclick contains this ID
            const thumbImpId = parseInt(thumb.getAttribute('onclick').match(/\d+/)[0]);
            thumb.classList.toggle('active', thumbImpId === impId);
        });
    }
}

/**
 * Format price with European formatting
 */
function formatPrice(price) {
    return new Intl.NumberFormat('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

/**
 * Capitalize first letter
 */
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Form submission (placeholder - wire up to actual cart)
document.getElementById('buy-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // TODO: Implement actual cart functionality
    // For now, just show confirmation
    const impId = document.getElementById('selected-imp-id').value;
    const imp = plateData.impressions.find(i => i.id === parseInt(impId));
    
    if (imp && imp.availability === 'available') {
        alert('Added to cart: <?php echo esc_js($plate_title); ?> #' + imp.impressionNumber + '\nPrice: €' + formatPrice(imp.price));
    }
});

// Initialize: ensure correct state is shown
document.addEventListener('DOMContentLoaded', function() {
    if (plateData.currentStateId) {
        // Show only current state's impressions
        switchState(plateData.currentStateId);
    }
});
</script>

<?php
get_footer();
?>