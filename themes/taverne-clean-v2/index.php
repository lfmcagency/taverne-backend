<?php
/**
 * Taverne Clean Theme - index.php
 * 
 * Ultimate fallback template.
 * Handles search results and any uncaught queries.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// If this is the blog home and we have a front page, redirect there
if (is_home() && get_option('page_on_front')) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();
?>

<main class="index-template">
    
    <?php if (is_search()) : ?>
        <?php // ============================================================
              // SEARCH RESULTS
              // ============================================================ ?>
        <section class="search-header">
            <div class="container">
                <span class="search-label">Search Results</span>
                <h1>
                    <?php 
                    global $wp_query;
                    printf('"%s"', esc_html(get_search_query()));
                    ?>
                </h1>
                <p class="search-count">
                    <?php printf('%d %s found', $wp_query->found_posts, $wp_query->found_posts === 1 ? 'result' : 'results'); ?>
                </p>
                
                <?php // Search again form ?>
                <form class="search-form-inline" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Search...">
                    <button type="submit">Search</button>
                </form>
            </div>
        </section>
        
        <?php if (have_posts()) : ?>
            <section class="search-results">
                <div class="container">
                    <div class="archive-grid">
                        <?php while (have_posts()) : the_post(); 
                            $post_type = get_post_type();
                        ?>
                            <?php if ($post_type === 'plate') : ?>
                                <?php get_template_part('template-parts/content-impression-card', null, [
                                    'post_id' => get_the_ID(),
                                ]); ?>
                            <?php else : ?>
                                <?php // Generic search result ?>
                                <article class="search-result">
                                    <a href="<?php the_permalink(); ?>">
                                        <span class="result-type"><?php echo esc_html(ucfirst($post_type)); ?></span>
                                        <h2 class="result-title"><?php the_title(); ?></h2>
                                        <?php if (has_excerpt()) : ?>
                                            <p class="result-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                        <?php endif; ?>
                                    </a>
                                </article>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php // Pagination ?>
                    <?php if ($wp_query->max_num_pages > 1) : ?>
                        <?php get_template_part('template-parts/pagination', null, [
                            'query' => $wp_query,
                        ]); ?>
                    <?php endif; ?>
                </div>
            </section>
        <?php else : ?>
            <section class="search-empty">
                <div class="container">
                    <p>No results found. Try a different search term.</p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline">
                        Browse All Works
                    </a>
                </div>
            </section>
        <?php endif; ?>
        
    <?php else : ?>
        <?php // ============================================================
              // DEFAULT FALLBACK
              // ============================================================ ?>
        <?php 
        // Redirect to 404 for unknown requests
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part(404);
        exit;
        ?>
    <?php endif; ?>
    
</main>

<style>
/* =============================================================================
   INDEX TEMPLATE STYLES
   ============================================================================= */

/* Search header */
.search-header {
    padding: var(--space-16) 0 var(--space-8);
    text-align: center;
    border-bottom: 1px solid var(--stone);
}

.search-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--ink-secondary);
    display: block;
    margin-bottom: var(--space-2);
}

.search-header h1 {
    font-size: var(--text-3xl);
    margin-bottom: var(--space-2);
}

.search-count {
    font-size: var(--text-lg);
    color: var(--charcoal);
    margin-bottom: var(--space-6);
}

.search-form-inline {
    display: flex;
    max-width: 400px;
    margin: 0 auto;
    border: 2px solid var(--stone);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.search-form-inline:focus-within {
    border-color: var(--ink);
}

.search-form-inline input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: none;
    font-size: var(--text-base);
}

.search-form-inline input:focus {
    outline: none;
}

.search-form-inline button {
    padding: var(--space-3) var(--space-6);
    background: var(--ink);
    color: var(--paper);
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: background var(--transition-fast);
}

.search-form-inline button:hover {
    background: var(--accent);
}

/* Search results */
.search-results {
    padding: var(--space-12) 0;
}

.search-result {
    padding: var(--space-6);
    border: 1px solid var(--stone);
    border-radius: var(--radius-md);
    transition: border-color var(--transition-fast);
}

.search-result:hover {
    border-color: var(--ink);
}

.search-result a {
    color: inherit;
    text-decoration: none;
}

.result-type {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--accent);
    display: block;
    margin-bottom: var(--space-2);
}

.result-title {
    font-size: var(--text-lg);
    margin: 0 0 var(--space-2);
}

.result-excerpt {
    font-size: var(--text-sm);
    color: var(--charcoal);
    margin: 0;
}

/* Empty state */
.search-empty {
    padding: var(--space-16) 0;
    text-align: center;
}

.search-empty p {
    margin-bottom: var(--space-6);
    color: var(--charcoal);
}
</style>

<?php
get_footer();
?>
