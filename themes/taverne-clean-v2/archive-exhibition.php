<?php
/**
 * Taverne Clean Theme - archive-exhibition.php
 * 
 * Chronological exhibition timeline—newest first.
 * Groups exhibitions by year with card layout.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// GET ALL EXHIBITIONS GROUPED BY YEAR
// =============================================================================

$exhibitions = new WP_Query([
    'post_type'      => 'exhibition',
    'posts_per_page' => -1,
    'meta_key'       => '_exhibition_date_range',
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
]);

// Group by year
$by_year = [];
if ($exhibitions->have_posts()) {
    while ($exhibitions->have_posts()) {
        $exhibitions->the_post();
        $date_range = get_post_meta(get_the_ID(), '_exhibition_date_range', true);
        // Extract year from date range (format: "2024" or "2024-01" etc.)
        preg_match('/\d{4}/', $date_range, $matches);
        $year = $matches[0] ?? date('Y');
        
        if (!isset($by_year[$year])) {
            $by_year[$year] = [];
        }
        $by_year[$year][] = get_the_ID();
    }
    wp_reset_postdata();
}

// Sort years descending
krsort($by_year);

$total_count = $exhibitions->found_posts;

?>

<main class="archive-exhibition">
    
    <?php // Header ?>
    <section class="exhib-header">
        <div class="container">
            <h1>Exhibitions</h1>
            <p class="exhib-count"><?php echo esc_html($total_count); ?> exhibitions since <?php echo end(array_keys($by_year)) ?: date('Y'); ?></p>
        </div>
    </section>
    
    <?php // Timeline ?>
    <section class="exhib-timeline">
        <div class="container">
            
            <?php if (!empty($by_year)) : ?>
                <?php foreach ($by_year as $year => $exhib_ids) : ?>
                    <div class="timeline-year">
                        <h2 class="year-marker"><?php echo esc_html($year); ?></h2>
                        
                        <div class="year-exhibitions">
                            <?php foreach ($exhib_ids as $exhib_id) : 
                                $venue = get_post_meta($exhib_id, '_exhibition_venue', true);
                                $type = get_post_meta($exhib_id, '_exhibition_type', true);
                                $date_range = get_post_meta($exhib_id, '_exhibition_date_range', true);
                            ?>
                                <article class="exhib-card">
                                    <?php if (has_post_thumbnail($exhib_id)) : ?>
                                        <div class="exhib-card-image">
                                            <?php echo get_the_post_thumbnail($exhib_id, 'plate-thumb'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="exhib-card-content">
                                        <?php if ($type) : ?>
                                            <span class="exhib-type"><?php echo esc_html(ucfirst($type)); ?></span>
                                        <?php endif; ?>
                                        
                                        <h3 class="exhib-title">
                                            <a href="<?php echo esc_url(get_permalink($exhib_id)); ?>">
                                                <?php echo esc_html(get_the_title($exhib_id)); ?>
                                            </a>
                                        </h3>
                                        
                                        <?php if ($venue) : ?>
                                            <p class="exhib-venue"><?php echo esc_html($venue); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($date_range) : ?>
                                            <p class="exhib-date"><?php echo esc_html($date_range); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (has_excerpt($exhib_id)) : ?>
                                            <p class="exhib-blurb"><?php echo wp_trim_words(get_the_excerpt($exhib_id), 20); ?></p>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo esc_url(get_permalink($exhib_id)); ?>" class="exhib-cta hover-line">
                                            View Details →
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="exhib-empty">
                    <p>No exhibitions found.</p>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
    
    <?php // CV link ?>
    <section class="exhib-cv">
        <div class="container">
            <a href="<?php echo esc_url(home_url('/artist')); ?>" class="btn btn-outline">
                View Full CV
            </a>
        </div>
    </section>
    
</main>

<style>
/* =============================================================================
   ARCHIVE EXHIBITION STYLES
   ============================================================================= */

.archive-exhibition {
    min-height: 100vh;
}

.exhib-header {
    padding: var(--space-16) 0 var(--space-8);
    border-bottom: 1px solid var(--stone);
}

.exhib-header h1 {
    font-size: var(--text-4xl);
    margin: 0 0 var(--space-2);
}

.exhib-count {
    font-size: var(--text-lg);
    color: var(--charcoal);
    margin: 0;
}

/* Timeline */
.exhib-timeline {
    padding: var(--space-12) 0;
}

.timeline-year {
    margin-bottom: var(--space-12);
    padding-bottom: var(--space-12);
    border-bottom: 1px solid var(--stone);
}

.timeline-year:last-child {
    border-bottom: none;
}

.year-marker {
    font-size: var(--text-2xl);
    color: var(--accent);
    margin-bottom: var(--space-6);
    position: relative;
    display: inline-block;
}

.year-marker::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--accent);
}

.year-exhibitions {
    display: grid;
    gap: var(--space-6);
}

@media (min-width: 600px) {
    .year-exhibitions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 900px) {
    .year-exhibitions {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Exhibition card */
.exhib-card {
    border: 1px solid var(--stone);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: border-color var(--transition-fast);
}

.exhib-card:hover {
    border-color: var(--ink);
}

.exhib-card-image {
    aspect-ratio: 16/10;
    overflow: hidden;
}

.exhib-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--transition-img);
}

.exhib-card:hover .exhib-card-image img {
    transform: scale(1.03);
}

.exhib-card-content {
    padding: var(--space-4);
}

.exhib-type {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--accent);
    display: block;
    margin-bottom: var(--space-2);
}

.exhib-title {
    font-size: var(--text-lg);
    margin: 0 0 var(--space-2);
    line-height: 1.3;
}

.exhib-title a {
    color: inherit;
    text-decoration: none;
}

.exhib-title a:hover {
    color: var(--accent);
}

.exhib-venue {
    font-size: var(--text-sm);
    font-weight: 500;
    margin: 0 0 var(--space-1);
}

.exhib-date {
    font-size: var(--text-sm);
    color: var(--ink-secondary);
    margin: 0 0 var(--space-3);
}

.exhib-blurb {
    font-size: var(--text-sm);
    color: var(--charcoal);
    margin: 0 0 var(--space-3);
    line-height: 1.5;
}

.exhib-cta {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--ink);
}

/* CV link */
.exhib-cv {
    padding: var(--space-12) 0;
    text-align: center;
    border-top: 1px solid var(--stone);
}

/* Empty state */
.exhib-empty {
    text-align: center;
    padding: var(--space-16) 0;
    color: var(--charcoal);
}
</style>

<?php
get_footer();
?>
