<?php
/**
 * Archive Template - Plate CPT
 *
 * Main gallery view at /prints displaying all plates in responsive grid (1-4 columns based on screen size).
 * Includes filter sidebar (900px+) via taverne_filter_sidebar() showing all 9 taxonomies with term counts.
 * Displays year, size, technique below each thumbnail. Uses plate-thumb image size (400×400px).
 * Template structure: archive-header → gallery-wrapper (sidebar + grid) → pagination.
 */

get_header();
?>

<div class="site-container">
    <?php if (have_posts()) : ?>
        
        <header class="archive-header">
            <h1 class="archive-title">Prints Collection</h1>
            <p class="archive-description">
                At the press's core, plates birth my worlds: Zinc's forgiving scratch for drypoint's velvet drag, lead's malleable depths yielding shadows. Wood grains pulse in relief's rhythmic gouge, linoleum layers color from absence—while mediums weave intaglio's intimate voids with photography's silver ghosts, carborundum's gritty rebellion. Palettes temper the pull: variable states across earth, sky, and fruit. 300+ impressions invite you: Wander the matrix, filter the motif, feel the plate's persistent pulse.
   Matrixes: 7 | Techniques: 12
            </p>
        </header>
        
        <div class="gallery-wrapper">
            <?php taverne_filter_sidebar(); ?>
            
            <div class="gallery-content">
                <div class="plate-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        
                        <article id="plate-<?php the_ID(); ?>" <?php post_class('plate-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="plate-card-image">
                                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                        <?php the_post_thumbnail('plate-thumb'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <div class="plate-meta">
                                <?php
                                // Show year and size
                                $year_terms = get_the_terms(get_the_ID(), 'plate_year');
                                $size_terms = get_the_terms(get_the_ID(), 'plate_size');
                                
                                if ($year_terms && !is_wp_error($year_terms)) {
                                    echo '<span class="taxonomy-term">' . esc_html($year_terms[0]->name) . '</span>';
                                }
                                
                                if ($size_terms && !is_wp_error($size_terms)) {
                                    echo '<span class="taxonomy-term">' . esc_html($size_terms[0]->name) . '</span>';
                                }
                                
                                // Show primary technique
                                $tech_terms = get_the_terms(get_the_ID(), 'plate_technique');
                                if ($tech_terms && !is_wp_error($tech_terms)) {
                                    echo '<span class="taxonomy-term">' . esc_html($tech_terms[0]->name) . '</span>';
                                }
                                ?>
                            </div>
                        </article>
                        
                    <?php endwhile; ?>
                </div>
                
                <?php taverne_pagination(); ?>
            </div>
        </div>
        
    <?php else : ?>
        
        <div class="no-results">
            <h2>No prints found</h2>
            <p>Check back soon for new work.</p>
        </div>
        
    <?php endif; ?>
</div>

<?php
get_footer();
