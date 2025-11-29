<?php
/**
 * Taverne Clean Theme - template-parts/content-impression-card.php
 * 
 * Reusable card component for artwork display.
 * Used in: archive grids, taxonomy pages, sliders, related works.
 * 
 * This is the REFERENCE COMPONENT - all other template parts follow this pattern.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     Required arguments passed via get_template_part().
 *     
 *     @type int    $post_id    The plate post ID.
 *     @type object $impression Optional. Impression object from taverne_get_impressions().
 *                              If null, links to plate; if set, links to impression.
 * }
 */

// Bail if no post ID
if (empty($args['post_id'])) {
    return;
}

$post_id = absint($args['post_id']);
$impression = $args['impression'] ?? null;

// =============================================================================
// BUILD DATA
// =============================================================================

// Get plate data
$plate_title = get_the_title($post_id);
$plate_slug = get_post_field('post_name', $post_id);

// Get orientation for grid spanning
$orientation = get_post_meta($post_id, '_plate_orientation', true);
$orientation_class = ($orientation === 'landscape') ? 'landscape' : 'portrait';

// Get year
$year = get_post_meta($post_id, '_plate_year', true);

// Get primary technique term
$technique_terms = get_the_terms($post_id, 'plate_technique');
$technique_name = (!empty($technique_terms) && !is_wp_error($technique_terms)) 
    ? $technique_terms[0]->name 
    : '';

// Get description/excerpt
$description = get_post_meta($post_id, '_plate_description', true);
if (empty($description)) {
    $description = get_the_excerpt($post_id);
}

// =============================================================================
// BUILD IMAGE
// =============================================================================

$image_id = null;
$image_alt = $plate_title;

// Prefer impression image if available
if ($impression && !empty($impression->image_id)) {
    $image_id = $impression->image_id;
    $image_alt = sprintf('%s - Impression #%s', $plate_title, $impression->impressionNumber ?? '');
} else {
    // Fall back to plate featured image
    $image_id = get_post_thumbnail_id($post_id);
}

// Get image URL (use plate-thumb size for grids)
$image_url = '';
if ($image_id) {
    $image_src = wp_get_attachment_image_src($image_id, 'plate-thumb');
    $image_url = $image_src ? $image_src[0] : '';
}

// Placeholder if no image
if (empty($image_url)) {
    $image_url = TAVERNE_THEME_URI . '/assets/images/placeholder.jpg';
}

// =============================================================================
// BUILD LINK URL
// =============================================================================

if ($impression && !empty($impression->id)) {
    // Link to specific impression
    $card_url = home_url(sprintf('/plates/%s/impression/%d', $plate_slug, $impression->id));
    $title_suffix = ' #' . ($impression->impressionNumber ?? $impression->id);
} else {
    // Link to plate
    $card_url = get_permalink($post_id);
    $title_suffix = '';
}

// =============================================================================
// BUILD TAG LINE
// =============================================================================

$tag_parts = [];
if ($year) {
    $tag_parts[] = $year;
}
if ($technique_name) {
    $tag_parts[] = $technique_name;
}
$tag_line = implode(' | ', $tag_parts);

?>

<article class="artwork-card <?php echo esc_attr($orientation_class); ?>">
    <a href="<?php echo esc_url($card_url); ?>" class="card-link">
        
        <?php // IMAGE BOX ?>
        <div class="card-image-box">
            <img 
                src="<?php echo esc_url($image_url); ?>" 
                alt="<?php echo esc_attr($image_alt); ?>"
                loading="lazy"
                decoding="async"
            >
            
            <?php // Optional: Availability indicator ?>
            <?php if ($impression && !empty($impression->availability)) : ?>
                <?php if ($impression->availability === 'sold') : ?>
                    <span class="availability-badge sold">Sold</span>
                <?php elseif ($impression->availability === 'artist') : ?>
                    <span class="availability-badge artist">Artist Collection</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php // CARD INFO ?>
        <div class="card-info">
            
            <?php // Tag line (year | technique) ?>
            <?php if ($tag_line) : ?>
                <span class="tag"><?php echo esc_html($tag_line); ?></span>
            <?php endif; ?>
            
            <?php // Title ?>
            <h3><?php echo esc_html($plate_title . $title_suffix); ?></h3>
            
            <?php // Description (clamped to 2 lines) ?>
            <?php if ($description) : ?>
                <p class="line-clamp-2"><?php echo esc_html(wp_trim_words($description, 15, '...')); ?></p>
            <?php endif; ?>
            
            <?php // CTA ?>
            <span class="cta hover-line">View Details</span>
            
        </div>
        
    </a>
</article>

<?php
// =============================================================================
// COMPONENT-SPECIFIC STYLES (only output once)
// =============================================================================

// Use a static flag to prevent duplicate style output
static $card_styles_output = false;
if (!$card_styles_output) :
    $card_styles_output = true;
?>
<style>
/* Artwork Card - Component Styles */
.artwork-card {
    display: block;
}

.artwork-card .card-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

/* Availability badges */
.availability-badge {
    position: absolute;
    top: var(--space-3);
    left: var(--space-3);
    padding: var(--space-1) var(--space-2);
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    border-radius: var(--radius);
}

.availability-badge.sold {
    background: var(--status-sold);
    color: white;
}

.availability-badge.artist {
    background: var(--status-artist);
    color: white;
}

/* Card image box needs position relative for badge */
.card-image-box {
    position: relative;
}

/* Focus state for accessibility */
.artwork-card .card-link:focus {
    outline: 2px solid var(--accent);
    outline-offset: 4px;
}

.artwork-card .card-link:focus:not(:focus-visible) {
    outline: none;
}

/* Tag styling */
.card-info .tag {
    display: block;
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ink-secondary);
    margin-bottom: var(--space-2);
}

/* Title hover effect */
.artwork-card:hover h3 {
    color: var(--accent);
}

/* Transition for title */
.card-info h3 {
    transition: color var(--transition-fast);
}
</style>
<?php endif; ?>