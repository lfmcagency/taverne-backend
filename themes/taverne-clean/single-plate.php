<?php
/**
 * Single Plate Template
 *
 * Individual plate detail page at /prints/{slug}. Two-column layout: featured image (plate-large) left,
 * details right (title, excerpt, content, specs, taxonomies). Stacks vertically on mobile.
 * ACF field sections for dimensions/price commented out at lines 50-64 - uncomment when ACF is configured.
 * Uses taverne_get_plate_taxonomies() and taverne_display_taxonomy_terms() for taxonomy display.
 */

get_header();
?>

<?php while (have_posts()) : the_post(); ?>

<div class="site-container">
    <div class="single-plate-content">
        
        <div class="plate-layout">
            <!-- Images Column -->
            <div class="plate-images">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="plate-featured-image">
                        <?php the_post_thumbnail('plate-large'); ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // If you have a gallery of states/impressions, display thumbs here
                // This would typically come from ACF or custom meta
                // For now, showing attachment gallery if exists
                ?>
            </div>
            
            <!-- Details Column -->
            <div class="plate-details">
                <h1><?php the_title(); ?></h1>
                
                <?php if (has_excerpt()) : ?>
                    <div class="plate-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="plate-content">
                    <?php the_content(); ?>
                </div>
                
                <!-- Specifications -->
                <div class="plate-specs">
                    <h3>Specifications</h3>
                    <div class="spec-grid">
                        <?php
                        // These would come from ACF fields
                        // Example structure for when ACF is set up:
                        
                        // Dimensions
                        // $width = get_field('plate_width');
                        // $height = get_field('plate_height');
                        // if ($width && $height) {
                        //     echo '<div class="spec-item">';
                        //     echo '<span class="spec-label">Dimensions</span>';
                        //     echo '<span class="spec-value">' . esc_html($width) . ' × ' . esc_html($height) . ' cm</span>';
                        //     echo '</div>';
                        // }
                        
                        // For now, showing placeholder
                        ?>
                        <div class="spec-item">
                            <span class="spec-label">Medium</span>
                            <span class="spec-value">
                                <?php
                                $medium_terms = get_the_terms(get_the_ID(), 'plate_medium');
                                if ($medium_terms && !is_wp_error($medium_terms)) {
                                    echo esc_html($medium_terms[0]->name);
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="spec-item">
                            <span class="spec-label">Year</span>
                            <span class="spec-value">
                                <?php
                                $year_terms = get_the_terms(get_the_ID(), 'plate_year');
                                if ($year_terms && !is_wp_error($year_terms)) {
                                    echo esc_html($year_terms[0]->name);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Taxonomies -->
                <div class="plate-taxonomies">
                    <?php
                    $taxonomies = taverne_get_plate_taxonomies();
                    
                    foreach ($taxonomies as $tax_slug => $tax_label) {
                        taverne_display_taxonomy_terms(get_the_ID(), $tax_slug, $tax_label);
                    }
                    ?>
                </div>
                
                <!-- Actions -->
                <div class="plate-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-secondary">
                        ← Back to Gallery
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php endwhile; ?>

<?php
get_footer();
