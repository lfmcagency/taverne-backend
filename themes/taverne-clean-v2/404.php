<?php
/**
 * Taverne Clean Theme - 404.php
 * 
 * Lost in the studio—elegant 404 page with search and rescue links.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// Get some recent works for rescue grid
$rescue_query = new WP_Query([
    'post_type'      => 'plate',
    'posts_per_page' => 6,
    'meta_key'       => '_plate_year',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
]);

?>

<main class="error-404">
    
    <section class="error-hero">
        <div class="container">
            <div class="error-content">
                <span class="error-code">404</span>
                <h1>Lost in the Studio</h1>
                <p>The page you're looking for has wandered off—perhaps pulled through the press one too many times.</p>
                
                <?php // Search form ?>
                <form class="error-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input 
                        type="search" 
                        name="s" 
                        placeholder="Search the archive..."
                        aria-label="Search"
                    >
                    <button type="submit" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
                
                <div class="error-links">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Back to Home</a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline">Browse All Works</a>
                </div>
            </div>
        </div>
    </section>
    
    <?php // Rescue grid ?>
    <?php if ($rescue_query->have_posts()) : ?>
        <section class="rescue-section">
            <div class="container">
                <h2>Perhaps you were looking for...</h2>
                <div class="rescue-grid">
                    <?php while ($rescue_query->have_posts()) : $rescue_query->the_post(); ?>
                        <?php get_template_part('template-parts/content-impression-card', null, [
                            'post_id' => get_the_ID(),
                        ]); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
    
</main>

<style>
/* =============================================================================
   404 PAGE STYLES
   ============================================================================= */

.error-404 {
    min-height: 100vh;
}

.error-hero {
    padding: var(--space-24) 0 var(--space-16);
    background: linear-gradient(135deg, #f9f9f9 0%, #fff 100%);
    text-align: center;
}

.error-content {
    max-width: 600px;
    margin: 0 auto;
}

.error-code {
    font-size: var(--text-7xl);
    font-weight: 700;
    color: var(--stone);
    display: block;
    margin-bottom: var(--space-4);
    line-height: 1;
}

.error-hero h1 {
    font-size: var(--text-4xl);
    margin-bottom: var(--space-4);
}

.error-hero p {
    font-size: var(--text-lg);
    color: var(--charcoal);
    margin-bottom: var(--space-8);
}

.error-search {
    display: flex;
    max-width: 400px;
    margin: 0 auto var(--space-8);
    border: 2px solid var(--stone);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: border-color var(--transition-fast);
}

.error-search:focus-within {
    border-color: var(--ink);
}

.error-search input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: none;
    font-size: var(--text-base);
    background: transparent;
}

.error-search input:focus {
    outline: none;
}

.error-search button {
    padding: var(--space-3) var(--space-4);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--ink-secondary);
    transition: color var(--transition-fast);
}

.error-search button:hover {
    color: var(--ink);
}

.error-links {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

/* Rescue section */
.rescue-section {
    padding: var(--space-16) 0;
}

.rescue-section h2 {
    text-align: center;
    font-size: var(--text-2xl);
    margin-bottom: var(--space-8);
}

.rescue-grid {
    display: grid;
    gap: var(--space-6);
    grid-template-columns: repeat(2, 1fr);
}

@media (min-width: 600px) {
    .rescue-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 900px) {
    .rescue-grid {
        grid-template-columns: repeat(6, 1fr);
    }
}
</style>

<?php
get_footer();
?>
