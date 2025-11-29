<?php
/**
 * Taverne Clean Theme - template-parts/sub-header.php
 * 
 * Breadcrumb navigation bar with back button.
 * Used on taxonomy pages, single plates, etc.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// Determine back URL
$back_url = get_post_type_archive_link('plate');
$back_text = 'All Works';

// Refine based on context
if (is_tax()) {
    $term = get_queried_object();
    $taxonomy = $term->taxonomy;
    // Back goes to main archive with filter hint
    $back_url = get_post_type_archive_link('plate');
} elseif (is_singular('plate')) {
    // Check if coming from a filtered view (via referer or session)
    $referer = wp_get_referer();
    if ($referer && strpos($referer, '/plates/') !== false && strpos($referer, get_the_ID()) === false) {
        $back_url = $referer;
        $back_text = 'Back';
    }
}

?>

<div class="sub-header">
    <div class="container">
        <div class="sub-header-inner">
            
            <?php // Back button ?>
            <a href="<?php echo esc_url($back_url); ?>" class="sub-header-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span><?php echo esc_html($back_text); ?></span>
            </a>
            
            <?php // Breadcrumbs ?>
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <?php taverne_breadcrumbs(); ?>
            </nav>
            
        </div>
    </div>
</div>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $sub_header_styles = false;
if (!$sub_header_styles) :
    $sub_header_styles = true;
?>
<style>
/* Sub-header Component */
.sub-header {
    background: var(--paper);
    border-bottom: 1px solid var(--stone);
    padding: var(--space-3) 0;
}

.sub-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
}

.sub-header-back {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--ink);
    transition: color var(--transition-fast);
}

.sub-header-back:hover {
    color: var(--accent);
}

.sub-header-back svg {
    flex-shrink: 0;
}

/* Breadcrumbs */
.breadcrumbs {
    display: none;
    font-size: var(--text-sm);
    color: var(--ink-secondary);
}

@media (min-width: 600px) {
    .breadcrumbs {
        display: block;
    }
}

.breadcrumbs ol {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    list-style: none;
    margin: 0;
    padding: 0;
}

.breadcrumbs li {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.breadcrumbs li:not(:last-child)::after {
    content: '/';
    color: var(--stone);
}

.breadcrumbs a {
    color: var(--ink-secondary);
    transition: color var(--transition-fast);
}

.breadcrumbs a:hover {
    color: var(--ink);
}

.breadcrumbs .current {
    color: var(--ink);
    font-weight: 500;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Home icon */
.breadcrumb-home svg {
    display: block;
}
</style>
<?php endif; ?>
