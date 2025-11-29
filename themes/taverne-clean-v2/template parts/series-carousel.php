<?php
/**
 * Taverne Clean Theme - template-parts/series-carousel.php
 * 
 * Individual series carousel with header and horizontal scroll.
 * Multiple instances on front page for different series.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type WP_Term $term    Series term object
 *     @type array   $plates  Array of plate post IDs
 * }
 */

if (empty($args['term']) || empty($args['plates'])) {
    return;
}

$term = $args['term'];
$plates = $args['plates'];
$carousel_id = 'series-' . $term->slug;

?>

<div class="series-carousel" id="<?php echo esc_attr($carousel_id); ?>">
    
    <?php // Series header ?>
    <div class="series-header">
        <div class="series-info">
            <h3 class="series-title"><?php echo esc_html($term->name); ?></h3>
            <?php if ($term->description) : ?>
                <p class="series-desc"><?php echo esc_html(wp_trim_words($term->description, 20)); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="series-nav">
            <button class="carousel-btn carousel-prev" aria-label="Previous" data-carousel="<?php echo esc_attr($carousel_id); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="carousel-btn carousel-next" aria-label="Next" data-carousel="<?php echo esc_attr($carousel_id); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
    </div>
    
    <?php // Carousel track ?>
    <div class="carousel-track">
        <?php foreach ($plates as $plate_id) : 
            $plate = get_post($plate_id);
            if (!$plate) continue;
            
            // Get first available impression
            $impressions = function_exists('taverne_get_impressions') 
                ? taverne_get_impressions($plate_id) 
                : [];
            
            $first_available = null;
            foreach ($impressions as $imp) {
                if ($imp->status === 'available') {
                    $first_available = $imp;
                    break;
                }
            }
            
            // Build URL
            $plate_url = get_permalink($plate_id);
            if ($first_available) {
                $plate_url = home_url('/plates/' . $plate->post_name . '/impression/' . $first_available->id);
            }
            
            // Image
            $img_id = $first_available && !empty($first_available->image_id) 
                ? $first_available->image_id 
                : get_post_thumbnail_id($plate_id);
                
            // Year
            $year = get_post_meta($plate_id, '_plate_year', true);
        ?>
            <a href="<?php echo esc_url($plate_url); ?>" class="carousel-item">
                <div class="carousel-item-image">
                    <?php if ($img_id) : ?>
                        <?php echo wp_get_attachment_image($img_id, 'plate-thumb', false, [
                            'loading' => 'lazy',
                        ]); ?>
                    <?php else : ?>
                        <div class="placeholder-thumb"></div>
                    <?php endif; ?>
                </div>
                <div class="carousel-item-info">
                    <span class="carousel-item-title"><?php echo esc_html($plate->post_title); ?></span>
                    <?php if ($year) : ?>
                        <span class="carousel-item-year"><?php echo esc_html($year); ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    
    <?php // View all link ?>
    <div class="series-footer">
        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="series-link hover-line">
            View all <?php echo esc_html($term->count); ?> works in this series →
        </a>
    </div>
    
</div>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $series_carousel_styles = false;
if (!$series_carousel_styles) :
    $series_carousel_styles = true;
?>
<style>
/* Series Carousel Component */
.series-carousel {
    padding: var(--space-8) 0;
    border-bottom: 1px solid var(--stone);
}

.series-carousel:last-child {
    border-bottom: none;
}

.series-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

.series-info {
    flex: 1;
}

.series-title {
    font-size: var(--text-xl);
    margin: 0 0 var(--space-2);
}

.series-desc {
    font-size: var(--text-sm);
    color: var(--charcoal);
    margin: 0;
    max-width: 400px;
}

.series-nav {
    display: flex;
    gap: var(--space-2);
}

.carousel-btn {
    width: 36px;
    height: 36px;
    border: 1px solid var(--stone);
    border-radius: 50%;
    background: var(--paper);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

.carousel-btn:hover {
    border-color: var(--ink);
    background: var(--ink);
    color: var(--paper);
}

.carousel-track {
    display: flex;
    gap: var(--space-4);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: var(--space-1) 0;
    margin: 0 calc(var(--gutter) * -1);
    padding-left: var(--gutter);
    padding-right: var(--gutter);
}

@media (min-width: 900px) {
    .carousel-track {
        margin: 0;
        padding-left: 0;
        padding-right: 0;
    }
}

.carousel-track::-webkit-scrollbar {
    display: none;
}

.carousel-item {
    flex: 0 0 140px;
    scroll-snap-align: start;
    text-decoration: none;
    color: inherit;
}

@media (min-width: 600px) {
    .carousel-item {
        flex: 0 0 180px;
    }
}

.carousel-item-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--stone);
    margin-bottom: var(--space-2);
}

.carousel-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-img);
}

.carousel-item:hover .carousel-item-image img {
    transform: scale(1.05);
}

.placeholder-thumb {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--stone) 0%, #ddd 100%);
}

.carousel-item-info {
    text-align: center;
}

.carousel-item-title {
    display: block;
    font-size: var(--text-sm);
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.carousel-item-year {
    display: block;
    font-size: var(--text-xs);
    color: var(--ink-secondary);
}

.series-footer {
    margin-top: var(--space-6);
}

.series-link {
    font-size: var(--text-sm);
    color: var(--accent);
    font-weight: 500;
}
</style>

<script>
(function() {
    // Carousel navigation (runs once, handles all carousels)
    if (window.carouselNavInitialized) return;
    window.carouselNavInitialized = true;
    
    document.querySelectorAll('.carousel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const carouselId = this.dataset.carousel;
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;
            
            const track = carousel.querySelector('.carousel-track');
            const item = track.querySelector('.carousel-item');
            const scrollAmount = item.offsetWidth + 16; // item width + gap
            
            if (this.classList.contains('carousel-prev')) {
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        });
    });
})();
</script>
<?php endif; ?>
