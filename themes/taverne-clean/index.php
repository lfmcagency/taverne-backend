<?php
/**
 * Fallback template
 */

get_header();
?>

<div class="site-container">
    <?php if (have_posts()) : ?>
        
        <?php if (is_home() || is_archive()) : ?>
            <header class="archive-header">
                <h1 class="archive-title">
                    <?php
                    if (is_home()) {
                        echo 'Latest';
                    } else {
                        the_archive_title();
                    }
                    ?>
                </h1>
                <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
            </header>
        <?php endif; ?>
        
        <div class="plate-grid">
            <?php while (have_posts()) : the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('plate-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="plate-card-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('plate-thumb'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    
                    <?php if (has_excerpt()) : ?>
                        <div class="plate-excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                        </div>
                    <?php endif; ?>
                </article>
                
            <?php endwhile; ?>
        </div>
        
        <?php taverne_pagination(); ?>
        
    <?php else : ?>
        
        <div class="no-results">
            <h2>Nothing found</h2>
            <p>No content available at this time.</p>
        </div>
        
    <?php endif; ?>
</div>

<?php
get_footer();
