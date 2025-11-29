<?php
/**
 * Taverne Clean Theme - front-page.php
 * 
 * Homepage: Hero splash, A/R/Tography roles, archive index, recent works, series.
 * Set as static front page in Settings > Reading.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();
?>

<main class="front-page">
    
    <?php // ================================================================
          // HERO SECTION - Full viewport quote splash
          // ================================================================ ?>
    <?php get_template_part('template-parts/hero-home'); ?>
    
    <?php // ================================================================
          // ROLES SECTION - Artist / Researcher / Teacher trifecta
          // ================================================================ ?>
    <section id="roles" class="section roles-section">
        <div class="container">
            <div class="section-header text-center">
                <h2>A/R/Tography</h2>
                <p class="section-intro">Three roles, one practice—where making, thinking, and teaching interweave.</p>
            </div>
            <?php get_template_part('template-parts/roles-grid'); ?>
        </div>
    </section>
    
    <?php // ================================================================
          // ARCHIVE INDEX - Taxonomy navigation tree
          // ================================================================ ?>
    <section id="archive" class="section archive-section bg-stone">
        <div class="container">
            <div class="section-header text-center">
                <h2>Explore the Archive</h2>
                <p class="section-intro">
                    <?php 
                    $total = taverne_get_total_impression_count();
                    printf('%d impressions across techniques, motifs, and materials.', $total);
                    ?>
                </p>
            </div>
            <?php get_template_part('template-parts/archive-index'); ?>
            <div class="text-center mt-12">
                <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline">
                    View All Works
                </a>
            </div>
        </div>
    </section>
    
    <?php // ================================================================
          // RECENT WORKS - Horizontal slider of latest plates
          // ================================================================ ?>
    <section id="recent" class="section recent-section">
        <div class="container">
            <div class="section-header text-center">
                <h2>Recent Works</h2>
            </div>
            <?php 
            $recent_query = new WP_Query([
                'post_type'      => 'plate',
                'posts_per_page' => 6,
                'meta_key'       => '_plate_year',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
                'meta_query'     => [
                    [
                        'key'     => '_plate_available_impressions',
                        'value'   => 0,
                        'compare' => '>',
                        'type'    => 'NUMERIC',
                    ],
                ],
            ]);
            
            get_template_part('template-parts/works-slider', null, [
                'query' => $recent_query,
                'id'    => 'recent-slider',
            ]);
            
            wp_reset_postdata();
            ?>
        </div>
    </section>
    
    <?php // ================================================================
          // SERIES SECTION - Featured artist series carousels
          // ================================================================ ?>
    <?php 
    $featured_series = taverne_get_featured_series(3);
    if (!empty($featured_series)) : 
    ?>
    <section id="series" class="section series-section bg-stone">
        <div class="container">
            <div class="section-header text-center">
                <h2>Artist Series</h2>
                <p class="section-intro">Thematic explorations—works in conversation with each other.</p>
            </div>
            
            <div class="series-grid">
                <?php foreach ($featured_series as $series_term) : 
                    $series_plates = taverne_get_series_group($series_term->slug);
                    if (empty($series_plates)) continue;
                ?>
                    <?php get_template_part('template-parts/series-carousel', null, [
                        'term'   => $series_term,
                        'plates' => $series_plates,
                    ]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php // ================================================================
          // EXHIBITIONS TEASER (Optional - if exhibitions exist)
          // ================================================================ ?>
    <?php 
    $upcoming_exhib = new WP_Query([
        'post_type'      => 'exhibition',
        'posts_per_page' => 1,
        'meta_key'       => '_exhibition_date_range',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
    ]);
    
    if ($upcoming_exhib->have_posts()) : $upcoming_exhib->the_post();
    ?>
    <section id="exhibitions" class="section exhibitions-teaser">
        <div class="container">
            <div class="exhib-feature">
                <div class="exhib-content">
                    <span class="exhib-label">Latest Exhibition</span>
                    <h2><?php the_title(); ?></h2>
                    <p class="exhib-meta">
                        <?php echo esc_html(get_post_meta(get_the_ID(), '_exhibition_date_range', true)); ?>
                        <?php if ($venue = get_post_meta(get_the_ID(), '_exhibition_venue', true)) : ?>
                            — <?php echo esc_html($venue); ?>
                        <?php endif; ?>
                    </p>
                    <p class="exhib-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></p>
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Exhibition</a>
                </div>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="exhib-image">
                        <?php the_post_thumbnail('plate-medium'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php 
    endif; 
    wp_reset_postdata(); 
    ?>
    
</main>

<style>
/* =============================================================================
   FRONT PAGE SPECIFIC STYLES
   ============================================================================= */

/* Section headers */
.section-header {
    margin-bottom: var(--space-12);
}

.section-header h2 {
    font-size: var(--text-4xl);
    margin-bottom: var(--space-4);
}

.section-intro {
    font-size: var(--text-lg);
    color: var(--ink-secondary);
    max-width: 600px;
    margin: 0 auto;
}

/* Background variant */
.bg-stone {
    background-color: #f9f9f9;
}

/* Series grid */
.series-grid {
    display: grid;
    gap: var(--space-16);
}

/* Exhibition teaser */
.exhib-feature {
    display: grid;
    gap: var(--space-8);
    align-items: center;
}

@media (min-width: 768px) {
    .exhib-feature {
        grid-template-columns: 1fr 1fr;
    }
}

.exhib-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--accent);
    display: block;
    margin-bottom: var(--space-2);
}

.exhib-content h2 {
    font-size: var(--text-3xl);
    margin-bottom: var(--space-3);
}

.exhib-meta {
    font-size: var(--text-sm);
    color: var(--ink-secondary);
    margin-bottom: var(--space-4);
}

.exhib-excerpt {
    margin-bottom: var(--space-6);
    color: var(--charcoal);
}

.exhib-image {
    aspect-ratio: 4/3;
    overflow: hidden;
    border-radius: var(--radius-lg);
}

.exhib-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Utility */
.text-center {
    text-align: center;
}

.mt-12 {
    margin-top: var(--space-12);
}
</style>

<?php
get_footer();
?>
