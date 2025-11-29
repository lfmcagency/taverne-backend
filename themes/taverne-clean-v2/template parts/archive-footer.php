<?php
/**
 * Taverne Clean Theme - template-parts/archive-footer.php
 * 
 * Process description section for archive pages.
 * Shows term-specific or global fallback content.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type string  $process_text  The process/technique description
 *     @type WP_Term $term          Optional term object for context
 * }
 */

$process_text = $args['process_text'] ?? '';
$term = $args['term'] ?? null;

// Fallback to global process description
if (empty($process_text)) {
    $process_text = get_option('taverne_global_process', '');
}

if (empty($process_text)) {
    return;
}

// Build title
$section_title = 'About the Process';
if ($term) {
    $section_title = 'About ' . esc_html($term->name);
}

?>

<section class="archive-footer">
    <div class="container">
        <div class="archive-footer-content">
            
            <div class="archive-footer-text">
                <h2><?php echo esc_html($section_title); ?></h2>
                <div class="archive-footer-body">
                    <?php echo wp_kses_post(wpautop($process_text)); ?>
                </div>
            </div>
            
            <?php // CTA ?>
            <div class="archive-footer-cta">
                <a href="<?php echo esc_url(home_url('/artist')); ?>" class="btn btn-outline">
                    Learn About the Artist
                </a>
            </div>
            
        </div>
    </div>
</section>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $archive_footer_styles = false;
if (!$archive_footer_styles) :
    $archive_footer_styles = true;
?>
<style>
/* Archive Footer Component */
.archive-footer {
    background: var(--ink);
    color: var(--paper);
    padding: var(--space-16) 0;
    margin-top: var(--space-16);
}

.archive-footer-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.archive-footer h2 {
    font-size: var(--text-3xl);
    margin-bottom: var(--space-6);
}

.archive-footer-body {
    font-size: var(--text-lg);
    line-height: 1.8;
    opacity: 0.9;
    margin-bottom: var(--space-8);
}

.archive-footer-body p:last-child {
    margin-bottom: 0;
}

.archive-footer .btn-outline {
    border-color: var(--paper);
    color: var(--paper);
}

.archive-footer .btn-outline:hover {
    background: var(--paper);
    color: var(--ink);
}
</style>
<?php endif; ?>
