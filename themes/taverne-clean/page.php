<?php
/**
 * Page Template - Static Pages
 *
 * Simple centered layout for static content (Artist, Researcher, Teacher, About, Contact).
 * Structure: page-header (title) → optional featured image (plate-large) → page-content (the_content).
 * No sidebar or special formatting - clean canvas for longform content and custom layouts.
 * Perfect for A/R/T pages with blocks or classic editor content.
 */

get_header();
?>

<?php while (have_posts()) : the_post(); ?>

<div class="site-container">
    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
        
        <header class="page-header">
            <h1><?php the_title(); ?></h1>
        </header>
        
        <?php if (has_post_thumbnail()) : ?>
            <div class="page-featured-image">
                <?php the_post_thumbnail('plate-large'); ?>
            </div>
        <?php endif; ?>
        
        <div class="page-content">
            <?php the_content(); ?>
        </div>
        
    </article>
</div>

<?php endwhile; ?>

<?php
get_footer();
