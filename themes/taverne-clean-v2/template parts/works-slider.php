<?php
/**
 * Taverne Clean Theme - template-parts/works-slider.php
 * 
 * Horizontal snap-scroll carousel for plates.
 * Pure CSS scroll-snap, no JS dependencies.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type WP_Query $query  Query object with plates
 *     @type string   $id     Unique ID for this slider instance
 * }
 */

if (empty($args['query']) || !$args['query']->have_posts()) {
    return;
}

$query = $args['query'];
$slider_id = $args['id'] ?? 'works-slider-' . wp_rand();

?>

<div class="works-slider" id="<?php echo esc_attr($slider_id); ?>">
    
    <?php // Navigation arrows ?>
    <button class="slider-nav slider-prev" aria-label="Previous" data-slider="<?php echo esc_attr($slider_id); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
    </button>
    
    <button class="slider-nav slider-next" aria-label="Next" data-slider="<?php echo esc_attr($slider_id); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>
    
    <?php // Slider track ?>
    <div class="slider-track">
        <?php while ($query->have_posts()) : $query->the_post(); 
            $plate_id = get_the_ID();
            
            // Get first available impression for link
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
            $plate_url = get_permalink();
            if ($first_available) {
                $plate_url = home_url('/plates/' . get_post_field('post_name', $plate_id) . '/impression/' . $first_available->id);
            }
            
            // Get data
            $year = get_post_meta($plate_id, '_plate_year', true);
            $technique_terms = get_the_terms($plate_id, 'plate_technique');
            $technique = $technique_terms && !is_wp_error($technique_terms) ? $technique_terms[0]->name : '';
            
            // Image
            $img_id = $first_available && !empty($first_available->image_id) 
                ? $first_available->image_id 
                : get_post_thumbnail_id();
        ?>
            <article class="slider-card">
                <a href="<?php echo esc_url($plate_url); ?>" class="slider-card-link">
                    
                    <?php // Image ?>
                    <div class="slider-card-image">
                        <?php if ($img_id) : ?>
                            <?php echo wp_get_attachment_image($img_id, 'plate-medium', false, [
                                'loading' => 'lazy',
                            ]); ?>
                        <?php else : ?>
                            <div class="placeholder-image"></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php // Content ?>
                    <div class="slider-card-content">
                        <?php if ($year || $technique) : ?>
                            <span class="slider-card-tag">
                                <?php echo esc_html($year); ?>
                                <?php if ($year && $technique) echo ' · '; ?>
                                <?php echo esc_html($technique); ?>
                            </span>
                        <?php endif; ?>
                        
                        <h3 class="slider-card-title"><?php the_title(); ?></h3>
                        
                        <?php if (has_excerpt()) : ?>
                            <p class="slider-card-desc"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                        <?php endif; ?>
                        
                        <span class="slider-card-cta hover-line">View Work →</span>
                    </div>
                    
                </a>
            </article>
        <?php endwhile; ?>
    </div>
    
</div>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $works_slider_styles = false;
if (!$works_slider_styles) :
    $works_slider_styles = true;
?>
<style>
/* Works Slider Component */
.works-slider {
    position: relative;
    margin: 0 calc(var(--gutter) * -1);
    padding: 0 var(--gutter);
}

@media (min-width: 900px) {
    .works-slider {
        margin: 0;
        padding: 0;
    }
}

.slider-track {
    display: flex;
    gap: var(--space-6);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: var(--space-2) 0;
}

.slider-track::-webkit-scrollbar {
    display: none;
}

.slider-card {
    flex: 0 0 85vw;
    max-width: 400px;
    scroll-snap-align: start;
}

@media (min-width: 600px) {
    .slider-card {
        flex: 0 0 350px;
    }
}

@media (min-width: 900px) {
    .slider-card {
        flex: 0 0 320px;
    }
}

.slider-card-link {
    display: block;
    color: inherit;
    text-decoration: none;
}

.slider-card-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--stone);
    margin-bottom: var(--space-4);
}

.slider-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-img);
}

.slider-card-link:hover .slider-card-image img {
    transform: scale(1.03);
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--stone) 0%, #ddd 100%);
}

.slider-card-tag {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ink-secondary);
    display: block;
    margin-bottom: var(--space-2);
}

.slider-card-title {
    font-size: var(--text-lg);
    font-weight: 500;
    margin: 0 0 var(--space-2);
    line-height: 1.3;
}

.slider-card-desc {
    font-size: var(--text-sm);
    color: var(--charcoal);
    margin: 0 0 var(--space-3);
    line-height: 1.5;
}

.slider-card-cta {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--accent);
}

/* Navigation arrows */
.slider-nav {
    position: absolute;
    top: 35%;
    transform: translateY(-50%);
    z-index: 10;
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 50%;
    background: var(--paper);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

@media (min-width: 900px) {
    .slider-nav {
        display: flex;
    }
}

.slider-nav:hover {
    background: var(--ink);
    color: var(--paper);
}

.slider-prev {
    left: -22px;
}

.slider-next {
    right: -22px;
}

.slider-nav:disabled {
    opacity: 0.3;
    cursor: default;
}
</style>

<script>
(function() {
    // Slider navigation
    document.querySelectorAll('.slider-nav').forEach(btn => {
        btn.addEventListener('click', function() {
            const sliderId = this.dataset.slider;
            const slider = document.getElementById(sliderId);
            if (!slider) return;
            
            const track = slider.querySelector('.slider-track');
            const card = track.querySelector('.slider-card');
            const scrollAmount = card.offsetWidth + 24; // card width + gap
            
            if (this.classList.contains('slider-prev')) {
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        });
    });
})();
</script>
<?php endif; ?>
