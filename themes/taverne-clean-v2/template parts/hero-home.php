<?php
/**
 * Taverne Clean Theme - template-parts/hero-home.php
 * 
 * Full-viewport hero with background image and centered quote.
 * Data from options or post meta on front page.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// =============================================================================
// GET HERO DATA
// =============================================================================

// Try front page meta first, then options, then defaults
$front_page_id = get_option('page_on_front');

$hero_quote = get_post_meta($front_page_id, '_taverne_hero_quote', true);
if (empty($hero_quote)) {
    $hero_quote = get_option('taverne_hero_quote', 
        '"The hand of the master that fascinates me—each mark a dialogue between intention and accident."'
    );
}

$hero_img_id = get_post_meta($front_page_id, '_taverne_hero_img', true);
if (empty($hero_img_id)) {
    $hero_img_id = get_option('taverne_hero_img');
}

// Get image URL
$hero_img_url = '';
if ($hero_img_id) {
    $hero_src = wp_get_attachment_image_src($hero_img_id, 'plate-hero');
    $hero_img_url = $hero_src ? $hero_src[0] : '';
}

// Fallback to placeholder or featured image
if (empty($hero_img_url)) {
    if (has_post_thumbnail($front_page_id)) {
        $hero_img_url = get_the_post_thumbnail_url($front_page_id, 'plate-hero');
    } else {
        $hero_img_url = TAVERNE_THEME_URI . '/assets/images/hero-default.jpg';
    }
}

?>

<section class="hero-home" id="hero">
    
    <?php // Background Image ?>
    <div class="hero-bg">
        <img 
            src="<?php echo esc_url($hero_img_url); ?>" 
            alt="Pol Taverne Studio"
            loading="eager"
            fetchpriority="high"
        >
        <div class="hero-overlay"></div>
    </div>
    
    <?php // Content ?>
    <div class="hero-content">
        <blockquote class="hero-quote">
            <?php echo wp_kses_post($hero_quote); ?>
        </blockquote>
        
        <div class="hero-attribution">
            <span class="hero-author">— Pol Taverne</span>
        </div>
        
        <?php // Scroll indicator ?>
        <a href="#roles" class="hero-cta" aria-label="Scroll to content">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </a>
    </div>
    
</section>

<style>
/* =============================================================================
   HERO HOME COMPONENT STYLES
   ============================================================================= */

.hero-home {
    position: relative;
    height: 100vh;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--ink);
}

/* Compensate for fixed header on hero page */
.has-hero .header-spacer {
    display: none;
}

.hero-bg {
    position: absolute;
    inset: 0;
}

.hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.3) 0%,
        rgba(0, 0, 0, 0.5) 100%
    );
}

.hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    color: var(--paper);
    padding: var(--gutter);
    max-width: 900px;
}

.hero-quote {
    font-family: var(--serif);
    font-size: var(--text-3xl);
    font-style: italic;
    font-weight: 400;
    line-height: 1.3;
    margin: 0 0 var(--space-6);
    text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
}

@media (min-width: 600px) {
    .hero-quote {
        font-size: var(--text-4xl);
    }
}

@media (min-width: 900px) {
    .hero-quote {
        font-size: var(--text-5xl);
        line-height: 1.2;
    }
}

@media (min-width: 1200px) {
    .hero-quote {
        font-size: var(--text-7xl);
    }
}

.hero-attribution {
    margin-bottom: var(--space-12);
}

.hero-author {
    font-size: var(--text-lg);
    font-weight: 500;
    letter-spacing: 0.05em;
    opacity: 0.9;
}

.hero-cta {
    display: inline-flex;
    color: var(--paper);
    opacity: 0.8;
    transition: all var(--transition-base);
    animation: float 3s ease-in-out infinite;
}

.hero-cta:hover {
    opacity: 1;
    transform: translateY(4px);
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(8px);
    }
}

/* Hide hero CTA on short viewports */
@media (max-height: 500px) {
    .hero-cta {
        display: none;
    }
}
</style>
