<?php
/**
 * Taverne Clean Theme - page.php
 * 
 * Static pages: /artist, /researcher, /teacher, etc.
 * Supports hero images, sidebars, and flexible content.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

get_header();

// =============================================================================
// PAGE DATA
// =============================================================================

$page_id = get_the_ID();

// Custom meta
$hero_img_id = get_post_meta($page_id, '_taverne_page_hero_img', true);
$teaser = get_post_meta($page_id, '_taverne_page_teaser', true);
$sidebar_resources = get_post_meta($page_id, '_taverne_page_resources', true);
$case_studies = get_post_meta($page_id, '_taverne_page_case_studies', true);

// Determine page type from slug for conditional sections
$page_slug = get_post_field('post_name', $page_id);
$is_role_page = in_array($page_slug, ['artist', 'researcher', 'teacher']);

?>

<main class="page-template">
    
    <?php // ================================================================
          // HERO SECTION (if hero image set)
          // ================================================================ ?>
    <?php if ($hero_img_id) : 
        $hero_url = wp_get_attachment_image_url($hero_img_id, 'plate-hero');
    ?>
        <section class="page-hero">
            <div class="page-hero-bg">
                <img src="<?php echo esc_url($hero_url); ?>" alt="" loading="eager">
                <div class="page-hero-overlay"></div>
            </div>
            <div class="page-hero-content">
                <div class="container">
                    <h1><?php the_title(); ?></h1>
                    <?php if ($teaser) : ?>
                        <p class="page-teaser"><?php echo esc_html($teaser); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php else : ?>
        <?php // Simple header without hero ?>
        <section class="page-header">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <?php if ($teaser) : ?>
                    <p class="page-teaser"><?php echo esc_html($teaser); ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <?php // ================================================================
          // MAIN CONTENT AREA
          // ================================================================ ?>
    <article class="page-content">
        <div class="container">
            <div class="page-grid <?php echo ($sidebar_resources || $case_studies) ? 'has-sidebar' : ''; ?>">
                
                <?php // Main content column ?>
                <div class="page-main">
                    <?php while (have_posts()) : the_post(); ?>
                        <div class="page-body prose">
                            <?php the_content(); ?>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php // Case studies accordion (for researcher page) ?>
                    <?php if ($case_studies && is_array($case_studies)) : ?>
                        <div class="case-studies">
                            <h2>Case Studies</h2>
                            <div class="accordion">
                                <?php foreach ($case_studies as $index => $study) : ?>
                                    <div class="accordion-item">
                                        <button class="accordion-trigger" aria-expanded="false" data-accordion="<?php echo $index; ?>">
                                            <span><?php echo esc_html($study['title'] ?? 'Case Study'); ?></span>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                        <div class="accordion-content" id="accordion-<?php echo $index; ?>">
                                            <?php echo wp_kses_post(wpautop($study['content'] ?? '')); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php // Sidebar ?>
                <?php if ($sidebar_resources) : ?>
                    <aside class="page-sidebar">
                        <div class="sidebar-card">
                            <h3>Resources</h3>
                            <?php if (is_array($sidebar_resources)) : ?>
                                <ul class="resources-list">
                                    <?php foreach ($sidebar_resources as $resource) : ?>
                                        <li>
                                            <a href="<?php echo esc_url($resource['url'] ?? '#'); ?>" 
                                               <?php echo !empty($resource['external']) ? 'target="_blank" rel="noopener"' : ''; ?>>
                                                <?php echo esc_html($resource['title'] ?? 'Resource'); ?>
                                                <?php if (!empty($resource['external'])) : ?>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                        <polyline points="15 3 21 3 21 9"></polyline>
                                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                                    </svg>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <?php echo wp_kses_post(wpautop($sidebar_resources)); ?>
                            <?php endif; ?>
                        </div>
                    </aside>
                <?php endif; ?>
                
            </div>
        </div>
    </article>
    
    <?php // ================================================================
          // RELATED WORKS CTA (for role pages)
          // ================================================================ ?>
    <?php if ($is_role_page) : ?>
        <section class="page-cta">
            <div class="container">
                <div class="cta-box">
                    <h2>Explore the Work</h2>
                    <p>Discover the prints, the process, and the philosophy behind the practice.</p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-primary">
                        View All Works
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
</main>

<style>
/* =============================================================================
   PAGE TEMPLATE STYLES
   ============================================================================= */

/* Hero */
.page-hero {
    position: relative;
    height: 50vh;
    min-height: 300px;
    max-height: 500px;
    display: flex;
    align-items: flex-end;
    background: var(--ink);
}

.page-hero-bg {
    position: absolute;
    inset: 0;
}

.page-hero-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.page-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
}

.page-hero-content {
    position: relative;
    z-index: 10;
    color: var(--paper);
    padding: var(--space-12) 0;
    width: 100%;
}

.page-hero h1 {
    font-size: var(--text-5xl);
    margin: 0 0 var(--space-4);
}

.page-teaser {
    font-size: var(--text-xl);
    opacity: 0.9;
    max-width: 600px;
    margin: 0;
}

/* Simple header (no hero) */
.page-header {
    padding: var(--space-16) 0 var(--space-8);
    border-bottom: 1px solid var(--stone);
}

.page-header h1 {
    font-size: var(--text-4xl);
    margin: 0 0 var(--space-4);
}

.page-header .page-teaser {
    color: var(--charcoal);
}

/* Content grid */
.page-content {
    padding: var(--space-12) 0 var(--space-16);
}

.page-grid {
    display: grid;
    gap: var(--space-12);
}

.page-grid.has-sidebar {
    grid-template-columns: 1fr;
}

@media (min-width: 900px) {
    .page-grid.has-sidebar {
        grid-template-columns: 1fr 300px;
    }
}

/* Prose styling */
.prose {
    font-size: var(--text-lg);
    line-height: 1.8;
    color: var(--charcoal);
}

.prose h2 {
    font-size: var(--text-2xl);
    color: var(--ink);
    margin: var(--space-12) 0 var(--space-4);
}

.prose h2:first-child {
    margin-top: 0;
}

.prose h3 {
    font-size: var(--text-xl);
    color: var(--ink);
    margin: var(--space-8) 0 var(--space-3);
}

.prose p {
    margin-bottom: var(--space-6);
}

.prose blockquote {
    border-left: 4px solid var(--accent);
    padding-left: var(--space-6);
    margin: var(--space-8) 0;
    font-style: italic;
    color: var(--ink);
}

.prose img {
    max-width: 100%;
    height: auto;
    margin: var(--space-8) 0;
}

/* Sidebar */
.page-sidebar {
    position: sticky;
    top: calc(var(--header-height) + var(--space-6));
}

.sidebar-card {
    background: #f9f9f9;
    padding: var(--space-6);
    border-radius: var(--radius-md);
}

.sidebar-card h3 {
    font-size: var(--text-lg);
    margin: 0 0 var(--space-4);
}

.resources-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.resources-list li {
    border-bottom: 1px solid var(--stone);
}

.resources-list li:last-child {
    border-bottom: none;
}

.resources-list a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2);
    padding: var(--space-3) 0;
    font-size: var(--text-sm);
    color: var(--ink);
}

.resources-list a:hover {
    color: var(--accent);
}

/* Accordion */
.case-studies {
    margin-top: var(--space-12);
    padding-top: var(--space-12);
    border-top: 1px solid var(--stone);
}

.case-studies h2 {
    margin-bottom: var(--space-6);
}

.accordion-item {
    border-bottom: 1px solid var(--stone);
}

.accordion-trigger {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) 0;
    background: none;
    border: none;
    cursor: pointer;
    font-size: var(--text-lg);
    font-weight: 500;
    text-align: left;
}

.accordion-trigger svg {
    transition: transform var(--transition-fast);
}

.accordion-trigger[aria-expanded="true"] svg {
    transform: rotate(180deg);
}

.accordion-content {
    display: none;
    padding: 0 0 var(--space-6);
    color: var(--charcoal);
}

.accordion-content.is-open {
    display: block;
}

/* CTA section */
.page-cta {
    background: var(--ink);
    color: var(--paper);
    padding: var(--space-16) 0;
}

.cta-box {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.cta-box h2 {
    font-size: var(--text-3xl);
    margin-bottom: var(--space-4);
}

.cta-box p {
    opacity: 0.9;
    margin-bottom: var(--space-8);
}
</style>

<script>
(function() {
    // Accordion functionality
    document.querySelectorAll('.accordion-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const contentId = 'accordion-' + this.dataset.accordion;
            const content = document.getElementById(contentId);
            
            // Toggle
            this.setAttribute('aria-expanded', !isExpanded);
            content?.classList.toggle('is-open');
        });
    });
})();
</script>

<?php
get_footer();
?>
