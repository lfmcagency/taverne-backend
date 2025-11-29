<?php
/**
 * Taverne Clean Theme - archive-plate.php
 * 
 * Main gallery archive for plates.
 * This is the REFERENCE IMPLEMENTATION - all other templates follow this pattern.
 * 
 * URL: /plates/ or /plates/?plate_technique[]=drypoint&plate_palette[]=sepia
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();
?>

<main class="archive-plate">
    
    <?php // SUB-HEADER: Breadcrumbs ?>
    <nav class="sub-header">
        <a href="<?php echo esc_url(wp_get_referer() ?: home_url()); ?>" class="back-btn">
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
            <span class="current">Plates</span>
        </div>
    </nav>
    
    <?php // INTRO SECTION ?>
    <section class="intro-section container">
        <div class="intro-text">
            <h1>Plates Archive</h1>
            <p>
                <?php 
                // Get archive description from options or default
                $archive_desc = get_option('taverne_archive_description', 
                    'Explore the complete collection of printmaking works—drypoint, carborundum, etching, and photography. Each plate tells a story of landscape, texture, and the dialogue between mark and material.'
                );
                echo esc_html($archive_desc);
                ?>
            </p>
        </div>
        <div class="featured-cat-img">
            <?php 
            $archive_img = get_option('taverne_archive_image');
            if ($archive_img) {
                echo wp_get_attachment_image($archive_img, 'plate-medium');
            }
            ?>
        </div>
    </section>
    
    <?php // CONTROLS BAR ?>
    <div class="controls-bar">
        <div class="total-works">
            <?php 
            $total = taverne_get_total_impression_count();
            printf(
                '%d %s Available',
                $total,
                _n('Impression', 'Impressions', $total, 'taverne')
            );
            ?>
        </div>
        <button class="ctrl-btn mobile-only" onclick="toggleFilterDrawer()" aria-label="Toggle filters">
            Filter
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
        </button>
    </div>
    
    <?php // MAIN CONTENT: Sidebar + Grid ?>
    <div class="container">
        <div class="archive-layout">
            
            <?php // FILTER DRAWER (Desktop: sidebar, Mobile: offcanvas) ?>
            <aside id="filterDrawer" class="filter-drawer">
                <div class="drawer-header">
                    <h2>Filters</h2>
                    <button class="drawer-close mobile-only" onclick="toggleFilterDrawer()" aria-label="Close filters">
                        ×
                    </button>
                </div>
                
                <div class="drawer-content">
                    <form id="filterForm" method="get" action="<?php echo esc_url(get_post_type_archive_link('plate')); ?>">
                        <?php 
                        // Output filter checkboxes for all taxonomies
                        taverne_filter_sidebar(); 
                        ?>
                    </form>
                </div>
                
                <div class="drawer-footer">
                    <button type="submit" form="filterForm" class="btn btn-primary btn-full">
                        Apply Filters
                    </button>
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline btn-full mt-4">
                        Clear All
                    </a>
                </div>
            </aside>
            
            <?php // GRID CONTAINER ?>
            <div class="archive-content">
                <div id="grid-container" class="artwork-grid">
                    <?php
                    // Build query args
                    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
                    
                    $args = [
                        'post_type'      => 'plate',
                        'posts_per_page' => 20,
                        'paged'          => $paged,
                        'orderby'        => 'meta_value_num',
                        'meta_key'       => '_plate_year',
                        'order'          => 'DESC',
                        'meta_query'     => [
                            [
                                'key'     => '_plate_available_impressions',
                                'value'   => 0,
                                'compare' => '>',
                                'type'    => 'NUMERIC',
                            ],
                        ],
                    ];
                    
                    // Add taxonomy filters from URL params
                    $tax_query = taverne_build_tax_query_from_request();
                    if (!empty($tax_query)) {
                        $args['tax_query'] = $tax_query;
                    }
                    
                    // Execute query
                    $plates_query = new WP_Query($args);
                    
                    if ($plates_query->have_posts()) :
                        while ($plates_query->have_posts()) : $plates_query->the_post();
                            
                            // Get top impression for this plate
                            $top_impression = null;
                            if (function_exists('taverne_get_impressions')) {
                                $impressions = taverne_get_impressions(get_the_ID());
                                $top_impression = !empty($impressions) ? $impressions[0] : null;
                            }
                            
                            // Render card
                            get_template_part('template-parts/content-impression-card', null, [
                                'post_id'    => get_the_ID(),
                                'impression' => $top_impression,
                            ]);
                            
                        endwhile;
                    else :
                        ?>
                        <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: var(--space-12);">
                            <p>No works match your current filters.</p>
                            <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline mt-6">
                                View All Plates
                            </a>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>
                
                <?php // PAGINATION ?>
                <?php if ($plates_query->max_num_pages > 1) : ?>
                    <nav class="pagination">
                        <?php
                        // Previous link
                        if ($paged > 1) {
                            $prev_url = add_query_arg('paged', $paged - 1);
                            echo '<a href="' . esc_url($prev_url) . '" class="hover-line">← Previous</a>';
                        }
                        ?>
                        
                        <span class="page-info">
                            Page <?php echo $paged; ?> of <?php echo $plates_query->max_num_pages; ?>
                        </span>
                        
                        <?php
                        // Next link
                        if ($paged < $plates_query->max_num_pages) {
                            $next_url = add_query_arg('paged', $paged + 1);
                            echo '<a href="' . esc_url($next_url) . '" class="hover-line">Next →</a>';
                        }
                        ?>
                    </nav>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
            </div>
            
        </div>
    </div>
    
    <?php // ARCHIVE FOOTER: Process description ?>
    <section class="section bg-stone">
        <div class="container">
            <div class="intro-text" style="max-width: 700px;">
                <h2>The Printmaking Process</h2>
                <p>
                    Each plate begins as a zinc or copper surface, etched through acid baths and burnished by hand. 
                    States evolve as the artist works—adding burrs, softening lines, discovering new depths. 
                    Every impression pulled from the press carries the memory of this dialogue between artist and material.
                </p>
                <a href="<?php echo esc_url(home_url('/researcher')); ?>" class="btn btn-outline mt-6">
                    Learn About A/R/Tography
                </a>
            </div>
        </div>
    </section>
    
</main>

<?php
get_footer();
?>

<style>
/* Archive-specific layout styles */
.archive-layout {
    display: grid;
    gap: var(--space-8);
    padding: var(--space-8) 0;
}

@media (min-width: 900px) {
    .archive-layout {
        grid-template-columns: 280px 1fr;
    }
}

.archive-content {
    min-width: 0; /* Prevent grid blowout */
}

/* Override sticky for desktop sidebar */
@media (min-width: 900px) {
    .archive-layout .filter-drawer {
        position: sticky;
        top: calc(var(--header-height) + var(--controls-height) + var(--space-4));
        align-self: start;
        max-height: calc(100vh - var(--header-height) - var(--controls-height) - var(--space-8));
        overflow-y: auto;
    }
}

/* Mobile filter toggle */
@media (max-width: 899px) {
    .archive-layout {
        grid-template-columns: 1fr;
    }
    
    .archive-layout .filter-drawer {
        position: fixed;
    }
}
</style>

<script>
// Filter drawer toggle
function toggleFilterDrawer() {
    const drawer = document.getElementById('filterDrawer');
    drawer.classList.toggle('open');
    document.body.style.overflow = drawer.classList.contains('open') ? 'hidden' : '';
}

// Close drawer on outside click (mobile)
document.addEventListener('click', function(e) {
    const drawer = document.getElementById('filterDrawer');
    const ctrlBtn = document.querySelector('.ctrl-btn');
    
    if (window.innerWidth < 900 && 
        drawer.classList.contains('open') && 
        !drawer.contains(e.target) && 
        !ctrlBtn.contains(e.target)) {
        toggleFilterDrawer();
    }
});

// AJAX filter (optional enhancement - works without JS too)
document.getElementById('filterForm')?.addEventListener('submit', function(e) {
    // For now, let the form submit normally (URL-based filtering)
    // AJAX enhancement can be added later
    
    // Close mobile drawer on submit
    if (window.innerWidth < 900) {
        const drawer = document.getElementById('filterDrawer');
        if (drawer.classList.contains('open')) {
            toggleFilterDrawer();
        }
    }
});
</script>