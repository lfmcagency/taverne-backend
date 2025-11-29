<?php
/**
 * Taverne Clean Theme - single-impression.php
 * 
 * Deep variant view: /plates/{plate-slug}/impression/{imp-id}
 * Shows single impression with sibling navigation.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// GET DATA FROM URL
// =============================================================================

$plate_slug = get_query_var('plate_slug');
$impression_id = absint(get_query_var('impression_id'));

// Get plate
$plate = get_page_by_path($plate_slug, OBJECT, 'plate');

if (!$plate) {
    // Redirect to 404
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit;
}

$plate_id = $plate->ID;

// Get impression
$impression = null;
if (function_exists('taverne_get_impression')) {
    $impression = taverne_get_impression($impression_id);
}

// Validate impression belongs to this plate
if (!$impression || $impression->plate_id != $plate_id) {
    // Redirect to plate page
    wp_redirect(get_permalink($plate_id));
    exit;
}

// Get all impressions for navigation
$all_impressions = function_exists('taverne_get_impressions') 
    ? taverne_get_impressions($plate_id) 
    : [];

// Get state info if impression has one
$current_state = null;
$state_impressions = $all_impressions;
if (!empty($impression->state_id) && function_exists('taverne_get_state')) {
    $current_state = taverne_get_state($impression->state_id);
    // Filter to same state for sibling nav
    $state_impressions = array_filter($all_impressions, function($imp) use ($impression) {
        return $imp->state_id === $impression->state_id;
    });
}

// Find prev/next in siblings
$sibling_keys = array_keys($state_impressions);
$current_key = array_search($impression_id, array_column($state_impressions, 'id'));
$prev_impression = $current_key > 0 ? $state_impressions[$sibling_keys[$current_key - 1]] : null;
$next_impression = $current_key < count($state_impressions) - 1 ? $state_impressions[$sibling_keys[$current_key + 1]] : null;

// =============================================================================
// PREPARE DISPLAY DATA
// =============================================================================

// Image
$img_id = !empty($impression->image_id) ? $impression->image_id : get_post_thumbnail_id($plate_id);
$img_url = $img_id ? wp_get_attachment_image_url($img_id, 'plate-hero') : '';

// Status
$status_labels = [
    'available' => 'Available',
    'reserved'  => 'Reserved',
    'sold'      => 'Sold',
    'artist'    => 'Artist Collection',
];
$status_label = $status_labels[$impression->status] ?? 'Unknown';

// Price
$price = $impression->price ? '€' . number_format($impression->price, 0, ',', '.') : 'Price on request';

// Edition number
$edition_display = $impression->edition_number;
if (!empty($impression->edition_total)) {
    $edition_display .= '/' . $impression->edition_total;
}

// Plate meta
$year = get_post_meta($plate_id, '_plate_year', true);
$technique_terms = get_the_terms($plate_id, 'plate_technique');
$technique = $technique_terms && !is_wp_error($technique_terms) ? $technique_terms[0]->name : '';
$matrix_terms = get_the_terms($plate_id, 'plate_matrix');
$matrix = $matrix_terms && !is_wp_error($matrix_terms) ? $matrix_terms[0]->name : '';
$dimensions = get_post_meta($plate_id, '_plate_dimensions', true);

// Canonical URL
$canonical_url = get_permalink($plate_id);

?>

<main class="single-impression">
    
    <?php // Sub-header ?>
    <?php get_template_part('template-parts/sub-header'); ?>
    
    <?php // ================================================================
          // IMPRESSION LAYOUT
          // ================================================================ ?>
    <article class="impression-layout">
        <div class="container">
            <div class="impression-grid">
                
                <?php // ========== LEFT: Image Stage ========== ?>
                <div class="impression-stage">
                    
                    <?php // Main image ?>
                    <div class="impression-canvas" id="impression-canvas">
                        <?php if ($img_url) : ?>
                            <img 
                                src="<?php echo esc_url($img_url); ?>" 
                                alt="<?php echo esc_attr($plate->post_title . ' - Edition ' . $edition_display); ?>"
                                id="impression-hero"
                                loading="eager"
                            >
                        <?php endif; ?>
                        
                        <?php // Zoom button ?>
                        <button class="canvas-zoom" id="open-lightbox" aria-label="Zoom image">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <polyline points="9 21 3 21 3 15"></polyline>
                                <line x1="21" y1="3" x2="14" y2="10"></line>
                                <line x1="3" y1="21" x2="10" y2="14"></line>
                            </svg>
                        </button>
                    </div>
                    
                    <?php // Sibling navigation ?>
                    <?php if (count($state_impressions) > 1) : ?>
                        <div class="impression-nav">
                            <?php if ($prev_impression) : 
                                $prev_url = home_url('/plates/' . $plate_slug . '/impression/' . $prev_impression->id);
                            ?>
                                <a href="<?php echo esc_url($prev_url); ?>" class="imp-nav-btn" rel="prev">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </a>
                            <?php else : ?>
                                <span class="imp-nav-btn is-disabled">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </span>
                            <?php endif; ?>
                            
                            <span class="imp-nav-info">
                                <?php echo esc_html($current_key + 1); ?> / <?php echo count($state_impressions); ?>
                                <?php if ($current_state) : ?>
                                    <small>(<?php echo esc_html($current_state->name); ?>)</small>
                                <?php endif; ?>
                            </span>
                            
                            <?php if ($next_impression) : 
                                $next_url = home_url('/plates/' . $plate_slug . '/impression/' . $next_impression->id);
                            ?>
                                <a href="<?php echo esc_url($next_url); ?>" class="imp-nav-btn" rel="next">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </a>
                            <?php else : ?>
                                <span class="imp-nav-btn is-disabled">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <?php // ========== RIGHT: Data Panel ========== ?>
                <div class="impression-data">
                    
                    <?php // Title & edition ?>
                    <header class="impression-header">
                        <h1><?php echo esc_html($plate->post_title); ?></h1>
                        <span class="impression-edition">Edition <?php echo esc_html($edition_display); ?></span>
                    </header>
                    
                    <?php // Status & price ?>
                    <div class="impression-purchase">
                        <div class="impression-status status-<?php echo esc_attr($impression->status); ?>">
                            <span class="status-light"></span>
                            <span class="status-text"><?php echo esc_html($status_label); ?></span>
                        </div>
                        
                        <?php if ($impression->status === 'available') : ?>
                            <div class="impression-price"><?php echo esc_html($price); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php // Specs grid ?>
                    <dl class="impression-specs">
                        <?php if ($technique) : ?>
                            <div class="spec-row">
                                <dt>Technique</dt>
                                <dd><?php echo esc_html($technique); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($matrix) : ?>
                            <div class="spec-row">
                                <dt>Matrix</dt>
                                <dd><?php echo esc_html($matrix); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($dimensions) : ?>
                            <div class="spec-row">
                                <dt>Dimensions</dt>
                                <dd><?php echo esc_html($dimensions); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($year) : ?>
                            <div class="spec-row">
                                <dt>Year</dt>
                                <dd><?php echo esc_html($year); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($impression->notes)) : ?>
                            <div class="spec-row spec-notes">
                                <dt>Notes</dt>
                                <dd><?php echo esc_html($impression->notes); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    
                    <?php // Buy form ?>
                    <?php if ($impression->status === 'available') : ?>
                        <form class="impression-buy-form" method="post" action="">
                            <input type="hidden" name="impression_id" value="<?php echo esc_attr($impression_id); ?>">
                            <input type="hidden" name="action" value="add_to_cart">
                            <?php wp_nonce_field('taverne_cart', 'cart_nonce'); ?>
                            
                            <button type="submit" class="btn btn-primary btn-lg btn-full">
                                Add to Cart — <?php echo esc_html($price); ?>
                            </button>
                        </form>
                        
                        <p class="impression-shipping">
                            Free shipping within the Netherlands. International shipping quoted at checkout.
                        </p>
                    <?php else : ?>
                        <div class="impression-unavailable">
                            <p>This impression is no longer available.</p>
                            <a href="<?php echo esc_url($canonical_url); ?>" class="btn btn-outline">
                                View Other Editions
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php // View all link ?>
                    <div class="impression-plate-link">
                        <a href="<?php echo esc_url($canonical_url); ?>" class="hover-line">
                            ← View all editions of "<?php echo esc_html($plate->post_title); ?>"
                        </a>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </article>
    
    <?php // ================================================================
          // SIBLING STRIP (All impressions horizontal scroll)
          // ================================================================ ?>
    <?php if (count($all_impressions) > 1) : ?>
        <section class="siblings-section">
            <div class="container">
                <h2>All Editions</h2>
                <div class="siblings-strip">
                    <?php foreach ($all_impressions as $sibling) : 
                        $sib_url = home_url('/plates/' . $plate_slug . '/impression/' . $sibling->id);
                        $sib_img_id = !empty($sibling->image_id) ? $sibling->image_id : get_post_thumbnail_id($plate_id);
                        $is_current = $sibling->id === $impression_id;
                    ?>
                        <a href="<?php echo esc_url($sib_url); ?>" 
                           class="sibling-thumb <?php echo $is_current ? 'is-active' : ''; ?> status-<?php echo esc_attr($sibling->status); ?>">
                            <?php if ($sib_img_id) : ?>
                                <?php echo wp_get_attachment_image($sib_img_id, 'plate-thumb'); ?>
                            <?php endif; ?>
                            <span class="sibling-edition"><?php echo esc_html($sibling->edition_number); ?></span>
                            <span class="sibling-status"><?php echo esc_html($status_labels[$sibling->status] ?? ''); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
    <?php // Canonical link to plate ?>
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">
    
</main>

<style>
/* =============================================================================
   SINGLE IMPRESSION STYLES
   ============================================================================= */

.impression-layout {
    padding: var(--space-8) 0 var(--space-16);
}

.impression-grid {
    display: grid;
    gap: var(--space-8);
}

@media (min-width: 900px) {
    .impression-grid {
        grid-template-columns: 1fr 400px;
        gap: var(--space-12);
        align-items: start;
    }
}

/* Stage */
.impression-stage {
    position: relative;
}

@media (min-width: 900px) {
    .impression-stage {
        position: sticky;
        top: calc(var(--header-height) + var(--space-6));
    }
}

.impression-canvas {
    position: relative;
    background: #f5f5f5;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.impression-canvas img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.canvas-zoom {
    position: absolute;
    bottom: var(--space-4);
    right: var(--space-4);
    width: 44px;
    height: 44px;
    background: var(--paper);
    border: none;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

.canvas-zoom:hover {
    background: var(--ink);
    color: var(--paper);
}

/* Sibling nav */
.impression-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-4);
    padding: var(--space-4) 0;
}

.imp-nav-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--stone);
    border-radius: 50%;
    color: var(--ink);
    transition: all var(--transition-fast);
}

a.imp-nav-btn:hover {
    border-color: var(--ink);
    background: var(--ink);
    color: var(--paper);
}

.imp-nav-btn.is-disabled {
    opacity: 0.3;
    cursor: default;
}

.imp-nav-info {
    font-size: var(--text-sm);
    color: var(--ink-secondary);
}

.imp-nav-info small {
    display: block;
    font-size: var(--text-xs);
}

/* Data panel */
.impression-header h1 {
    font-size: var(--text-3xl);
    margin: 0 0 var(--space-2);
}

.impression-edition {
    font-size: var(--text-lg);
    color: var(--ink-secondary);
}

.impression-purchase {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-6) 0;
    border-bottom: 1px solid var(--stone);
    margin-bottom: var(--space-6);
}

.impression-status {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.status-light {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--status-available);
}

.status-sold .status-light { background: var(--status-sold); }
.status-reserved .status-light { background: var(--status-sold); }
.status-artist .status-light { background: var(--status-artist); }

.impression-price {
    font-size: var(--text-2xl);
    font-weight: 600;
}

/* Specs */
.impression-specs {
    margin: 0 0 var(--space-6);
}

.spec-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--stone);
}

.spec-row dt {
    font-size: var(--text-sm);
    color: var(--ink-secondary);
}

.spec-row dd {
    font-size: var(--text-sm);
    font-weight: 500;
    margin: 0;
}

.spec-notes {
    flex-direction: column;
    gap: var(--space-2);
}

.spec-notes dd {
    font-weight: 400;
    color: var(--charcoal);
}

/* Buy form */
.impression-buy-form {
    margin-bottom: var(--space-4);
}

.impression-shipping {
    font-size: var(--text-xs);
    color: var(--ink-secondary);
    text-align: center;
    margin-bottom: var(--space-6);
}

.impression-unavailable {
    text-align: center;
    padding: var(--space-6);
    background: #f9f9f9;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-6);
}

.impression-plate-link {
    text-align: center;
    padding-top: var(--space-6);
    border-top: 1px solid var(--stone);
}

/* Siblings section */
.siblings-section {
    background: #f9f9f9;
    padding: var(--space-12) 0;
}

.siblings-section h2 {
    font-size: var(--text-xl);
    margin-bottom: var(--space-6);
}

.siblings-strip {
    display: flex;
    gap: var(--space-4);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: var(--space-2) 0;
}

.siblings-strip::-webkit-scrollbar { display: none; }

.sibling-thumb {
    flex: 0 0 100px;
    scroll-snap-align: start;
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border: 2px solid transparent;
    transition: border-color var(--transition-fast);
}

.sibling-thumb.is-active {
    border-color: var(--accent);
}

.sibling-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sibling-edition {
    position: absolute;
    top: var(--space-1);
    left: var(--space-1);
    background: var(--paper);
    font-size: var(--text-xs);
    padding: 2px 6px;
    border-radius: 2px;
}

.sibling-status {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: var(--paper);
    font-size: 9px;
    text-transform: uppercase;
    text-align: center;
    padding: 2px;
}

.sibling-thumb.status-sold .sibling-status { background: var(--status-sold); }
.sibling-thumb.status-artist .sibling-status { background: var(--status-artist); }
</style>

<script>
(function() {
    // Lightbox open
    const openBtn = document.getElementById('open-lightbox');
    const heroImg = document.getElementById('impression-hero');
    
    if (openBtn && heroImg && typeof openLightbox === 'function') {
        openBtn.addEventListener('click', function() {
            openLightbox(heroImg.src, '<?php echo esc_js($plate->post_title); ?>');
        });
        
        // Also open on image click
        heroImg.style.cursor = 'zoom-in';
        heroImg.addEventListener('click', function() {
            openLightbox(this.src, '<?php echo esc_js($plate->post_title); ?>');
        });
    }
})();
</script>

<?php
get_footer();
?>
