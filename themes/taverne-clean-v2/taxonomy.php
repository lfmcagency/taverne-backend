<?php
/**
 * Taverne Clean Theme - taxonomy.php
 * 
 * Faceted archive for taxonomy terms.
 * URLs like: /plates/technique/drypoint, /plates/matrix/zinc
 * Shows filtered grid with pre-selected current term.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// GET TAXONOMY DATA
// =============================================================================

$term = get_queried_object();
$taxonomy = get_queried_object()->taxonomy;
$tax_obj = get_taxonomy($taxonomy);

// Term meta for customization
$term_description = $term->description ?: '';
$term_process = get_term_meta($term->term_id, '_taverne_term_process', true);
$term_image_id = get_term_meta($term->term_id, '_taverne_term_image', true);

// Human-readable taxonomy label
$tax_labels = [
    'plate_technique' => 'Technique',
    'plate_medium'    => 'Medium',
    'plate_study'     => 'Study',
    'plate_motif'     => 'Motif',
    'plate_palette'   => 'Palette',
    'plate_traces'    => 'Traces',
    'plate_matrix'    => 'Matrix',
    'plate_size'      => 'Size',
    'plate_year'      => 'Year',
    'plate_series'    => 'Series',
];
$tax_label = $tax_labels[$taxonomy] ?? $tax_obj->labels->singular_name;

// =============================================================================
// BUILD QUERY
// =============================================================================

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Start with current term
$tax_query = [
    [
        'taxonomy' => $taxonomy,
        'field'    => 'slug',
        'terms'    => $term->slug,
    ],
];

// Add any additional filters from request
$additional_tax_query = taverne_build_tax_query_from_request();
if (!empty($additional_tax_query)) {
    $tax_query = array_merge($tax_query, $additional_tax_query);
    $tax_query['relation'] = 'AND';
}

$args = [
    'post_type'      => 'plate',
    'posts_per_page' => 20,
    'paged'          => $paged,
    'meta_key'       => '_plate_year',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'tax_query'      => $tax_query,
];

$plates_query = new WP_Query($args);
$total_plates = $plates_query->found_posts;

?>

<main class="taxonomy-archive">
    
    <?php // ================================================================
          // SUB-HEADER
          // ================================================================ ?>
    <?php get_template_part('template-parts/sub-header'); ?>
    
    <?php // ================================================================
          // INTRO SECTION
          // ================================================================ ?>
    <section class="tax-intro section">
        <div class="container">
            <div class="tax-intro-grid">
                <div class="tax-intro-content">
                    <span class="tax-intro-label"><?php echo esc_html($tax_label); ?></span>
                    <h1 class="tax-intro-title"><?php echo esc_html($term->name); ?></h1>
                    <?php if ($term_description) : ?>
                        <div class="tax-intro-desc">
                            <?php echo wp_kses_post(wpautop($term_description)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($term_image_id) : ?>
                    <div class="tax-intro-image">
                        <?php echo wp_get_attachment_image($term_image_id, 'plate-medium'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <?php // ================================================================
          // CONTROLS BAR
          // ================================================================ ?>
    <div class="controls-bar">
        <div class="container">
            <div class="controls-inner">
                <span class="controls-count">
                    <?php printf('%d %s', $total_plates, $total_plates === 1 ? 'work' : 'works'); ?>
                </span>
                
                <button class="controls-filter-btn" id="filter-toggle" aria-expanded="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>Refine</span>
                </button>
            </div>
        </div>
    </div>
    
    <?php // ================================================================
          // ARCHIVE LAYOUT (Sidebar + Grid)
          // ================================================================ ?>
    <section class="archive-layout">
        <div class="container">
            <div class="archive-wrapper">
                
                <?php // Filter drawer - pre-check current term ?>
                <?php get_template_part('template-parts/filter-drawer', null, [
                    'current_taxonomy' => $taxonomy,
                    'current_term'     => $term->slug,
                ]); ?>
                
                <?php // Grid ?>
                <div class="archive-grid-container">
                    <?php if ($plates_query->have_posts()) : ?>
                        <div class="archive-grid">
                            <?php while ($plates_query->have_posts()) : $plates_query->the_post(); ?>
                                <?php get_template_part('template-parts/content-impression-card', null, [
                                    'post_id' => get_the_ID(),
                                ]); ?>
                            <?php endwhile; ?>
                        </div>
                        
                        <?php // Pagination ?>
                        <?php if ($plates_query->max_num_pages > 1) : ?>
                            <?php get_template_part('template-parts/pagination', null, [
                                'query' => $plates_query,
                            ]); ?>
                        <?php endif; ?>
                        
                    <?php else : ?>
                        <div class="archive-empty">
                            <p>No works found matching these filters.</p>
                            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="btn btn-outline">
                                Clear Filters
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </section>
    
    <?php wp_reset_postdata(); ?>
    
    <?php // ================================================================
          // ARCHIVE FOOTER (Process description)
          // ================================================================ ?>
    <?php if ($term_process) : ?>
        <?php get_template_part('template-parts/archive-footer', null, [
            'process_text' => $term_process,
            'term'         => $term,
        ]); ?>
    <?php endif; ?>
    
</main>

<style>
/* =============================================================================
   TAXONOMY ARCHIVE SPECIFIC STYLES
   ============================================================================= */

/* Intro section */
.tax-intro {
    background: var(--paper);
    padding: var(--space-12) 0;
}

.tax-intro-grid {
    display: grid;
    gap: var(--space-8);
    align-items: center;
}

@media (min-width: 768px) {
    .tax-intro-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.tax-intro-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--accent);
    display: block;
    margin-bottom: var(--space-2);
}

.tax-intro-title {
    font-size: var(--text-4xl);
    margin: 0 0 var(--space-4);
}

.tax-intro-desc {
    font-size: var(--text-lg);
    color: var(--charcoal);
    line-height: 1.7;
}

.tax-intro-desc p:last-child {
    margin-bottom: 0;
}

.tax-intro-image {
    aspect-ratio: 4/3;
    overflow: hidden;
    border-radius: var(--radius-lg);
}

.tax-intro-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Archive wrapper */
.archive-wrapper {
    display: grid;
    gap: var(--space-8);
}

@media (min-width: 900px) {
    .archive-wrapper {
        grid-template-columns: 280px 1fr;
        gap: var(--space-12);
    }
}

/* Empty state */
.archive-empty {
    text-align: center;
    padding: var(--space-16) var(--space-8);
    background: #f9f9f9;
    border-radius: var(--radius-lg);
}

.archive-empty p {
    margin-bottom: var(--space-6);
    color: var(--charcoal);
}
</style>

<script>
(function() {
    // Filter toggle
    const filterToggle = document.getElementById('filter-toggle');
    const filterDrawer = document.getElementById('filter-drawer');
    
    if (filterToggle && filterDrawer) {
        filterToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            filterDrawer.classList.toggle('is-open');
            document.body.classList.toggle('filter-open');
        });
        
        // Close on outside click (mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 900 && 
                filterDrawer.classList.contains('is-open') &&
                !filterDrawer.contains(e.target) &&
                !filterToggle.contains(e.target)) {
                filterToggle.setAttribute('aria-expanded', 'false');
                filterDrawer.classList.remove('is-open');
                document.body.classList.remove('filter-open');
            }
        });
    }
})();
</script>

<?php
get_footer();
?>
