<?php
/**
 * Taxonomy Archive Template
 * Shows plates filtered by taxonomy term
 */

get_header();

$term = get_queried_object();
?>

<div class="site-container">
    <?php if (have_posts()) : ?>
        
        <header class="archive-header">
            <h1 class="archive-title"><?php echo esc_html($term->name); ?></h1>
            
            <?php if ($term->description) : ?>
                <div class="archive-description">
                    <?php echo wp_kses_post($term->description); ?>
                </div>
            <?php endif; ?>
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
            <h2>No prints found in this category</h2>
            <p><a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>">View all prints</a></p>
        </div>
        
    <?php endif; ?>
</div>

<?php
get_footer();
