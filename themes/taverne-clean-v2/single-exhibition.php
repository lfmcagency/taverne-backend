<?php
/**
 * Taverne Clean Theme - single-exhibition.php
 * 
 * Individual exhibition detail page.
 * Shows poster, specs, description, and related works.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// EXHIBITION DATA
// =============================================================================

$exhib_id = get_the_ID();

// Meta fields
$venue = get_post_meta($exhib_id, '_exhibition_venue', true);
$curator = get_post_meta($exhib_id, '_exhibition_curator', true);
$date_range = get_post_meta($exhib_id, '_exhibition_date_range', true);
$type = get_post_meta($exhib_id, '_exhibition_type', true);
$cv_link = get_post_meta($exhib_id, '_exhibition_cv_link', true);

// Extract year for related works
preg_match('/\d{4}/', $date_range, $matches);
$year = $matches[0] ?? '';

// Type labels
$type_labels = [
    'solo'   => 'Solo Exhibition',
    'group'  => 'Group Exhibition',
    'fair'   => 'Art Fair',
    'online' => 'Online Exhibition',
];
$type_label = $type_labels[$type] ?? ucfirst($type);

?>

<main class="single-exhibition">
    
    <?php // Sub-header ?>
    <?php get_template_part('template-parts/sub-header'); ?>
    
    <?php // ================================================================
          // EXHIBITION CONTENT
          // ================================================================ ?>
    <article class="exhib-detail">
        <div class="container">
            <div class="exhib-grid">
                
                <?php // Left: Content ?>
                <div class="exhib-content">
                    
                    <?php // Header ?>
                    <header class="exhib-header">
                        <?php if ($type) : ?>
                            <span class="exhib-type-label"><?php echo esc_html($type_label); ?></span>
                        <?php endif; ?>
                        <h1><?php the_title(); ?></h1>
                    </header>
                    
                    <?php // Specs grid ?>
                    <dl class="exhib-specs">
                        <?php if ($venue) : ?>
                            <div class="spec-item">
                                <dt>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Venue
                                </dt>
                                <dd><?php echo esc_html($venue); ?></dd>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($date_range) : ?>
                            <div class="spec-item">
                                <dt>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    Date
                                </dt>
                                <dd><?php echo esc_html($date_range); ?></dd>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($curator) : ?>
                            <div class="spec-item">
                                <dt>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Curator
                                </dt>
                                <dd><?php echo esc_html($curator); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    
                    <?php // Description ?>
                    <div class="exhib-body prose">
                        <?php the_content(); ?>
                    </div>
                    
                    <?php // CV link ?>
                    <?php if ($cv_link) : ?>
                        <div class="exhib-cv-link">
                            <a href="<?php echo esc_url($cv_link); ?>" class="btn btn-outline" target="_blank" rel="noopener">
                                View Full CV
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <?php // Right: Poster ?>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="exhib-poster">
                        <div class="poster-frame">
                            <?php the_post_thumbnail('plate-large'); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </article>
    
    <?php // ================================================================
          // RELATED WORKS (from same year)
          // ================================================================ ?>
    <?php if ($year) : 
        $related_works = new WP_Query([
            'post_type'      => 'plate',
            'posts_per_page' => 4,
            'meta_query'     => [
                [
                    'key'     => '_plate_year',
                    'value'   => $year,
                    'compare' => '=',
                ],
            ],
        ]);
        
        if ($related_works->have_posts()) :
    ?>
        <section class="exhib-related">
            <div class="container">
                <h2>Works from <?php echo esc_html($year); ?></h2>
                <div class="related-grid">
                    <?php while ($related_works->have_posts()) : $related_works->the_post(); ?>
                        <?php get_template_part('template-parts/content-impression-card', null, [
                            'post_id' => get_the_ID(),
                        ]); ?>
                    <?php endwhile; ?>
                </div>
                <div class="related-cta">
                    <a href="<?php echo esc_url(add_query_arg('plate_year[]', $year, get_post_type_archive_link('plate'))); ?>" class="btn btn-outline">
                        View All Works from <?php echo esc_html($year); ?>
                    </a>
                </div>
            </div>
        </section>
        <?php 
        wp_reset_postdata();
        endif; 
    endif; 
    ?>
    
    <?php // More exhibitions link ?>
    <section class="exhib-more">
        <div class="container">
            <a href="<?php echo esc_url(get_post_type_archive_link('exhibition')); ?>" class="hover-line">
                ← View All Exhibitions
            </a>
        </div>
    </section>
    
</main>

<style>
/* =============================================================================
   SINGLE EXHIBITION STYLES
   ============================================================================= */

.exhib-detail {
    padding: var(--space-8) 0 var(--space-16);
}

.exhib-grid {
    display: grid;
    gap: var(--space-8);
}

@media (min-width: 768px) {
    .exhib-grid {
        grid-template-columns: 1fr 1fr;
        gap: var(--space-12);
        align-items: start;
    }
}

/* Header */
.exhib-header {
    margin-bottom: var(--space-6);
}

.exhib-type-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--accent);
    display: block;
    margin-bottom: var(--space-2);
}

.exhib-header h1 {
    font-size: var(--text-4xl);
    margin: 0;
    line-height: 1.2;
}

/* Specs */
.exhib-specs {
    display: grid;
    gap: var(--space-4);
    padding: var(--space-6) 0;
    border-top: 1px solid var(--stone);
    border-bottom: 1px solid var(--stone);
    margin-bottom: var(--space-6);
}

.spec-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-4);
}

.spec-item dt {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    color: var(--ink-secondary);
    min-width: 100px;
}

.spec-item dd {
    font-size: var(--text-base);
    font-weight: 500;
    margin: 0;
}

/* Body */
.exhib-body {
    margin-bottom: var(--space-8);
}

/* CV link */
.exhib-cv-link .btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
}

/* Poster */
.exhib-poster {
    position: sticky;
    top: calc(var(--header-height) + var(--space-6));
}

.poster-frame {
    aspect-ratio: 3/4;
    overflow: hidden;
    background: #f5f5f5;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.poster-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Related works */
.exhib-related {
    padding: var(--space-16) 0;
    background: #f9f9f9;
}

.exhib-related h2 {
    font-size: var(--text-2xl);
    margin-bottom: var(--space-8);
}

.related-grid {
    display: grid;
    gap: var(--space-6);
    grid-template-columns: repeat(2, 1fr);
}

@media (min-width: 768px) {
    .related-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.related-cta {
    text-align: center;
    margin-top: var(--space-8);
}

/* More exhibitions */
.exhib-more {
    padding: var(--space-8) 0;
    border-top: 1px solid var(--stone);
}

.exhib-more a {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--ink);
}
</style>

<?php
get_footer();
?>
