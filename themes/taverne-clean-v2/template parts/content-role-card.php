<?php
/**
 * Taverne Clean Theme - template-parts/content-role-card.php
 * 
 * Single role card for A/R/Tography grid.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type array $role {
 *         @type string $title    Role title (Artist, Researcher, Teacher)
 *         @type string $slug     URL-safe slug
 *         @type string $icon     Icon name (brush, book, users)
 *         @type string $bio      Role description
 *         @type string $quote    Pull quote
 *         @type string $cta_text Button text
 *         @type string $cta_url  Button URL
 *     }
 * }
 */

if (empty($args['role'])) {
    return;
}

$role = $args['role'];

// SVG icons inline for performance
$icons = [
    'brush' => '<path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"></path><polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon>',
    'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
];

$icon_svg = $icons[$role['icon']] ?? $icons['brush'];

?>

<article class="role-card">
    
    <?php // Icon ?>
    <div class="role-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <?php echo $icon_svg; ?>
        </svg>
    </div>
    
    <?php // Title ?>
    <h3 class="role-title"><?php echo esc_html($role['title']); ?></h3>
    
    <?php // Bio ?>
    <p class="role-bio"><?php echo esc_html($role['bio']); ?></p>
    
    <?php // Quote ?>
    <?php if (!empty($role['quote'])) : ?>
        <blockquote class="role-quote">
            "<?php echo esc_html($role['quote']); ?>"
        </blockquote>
    <?php endif; ?>
    
    <?php // CTA ?>
    <a href="<?php echo esc_url($role['cta_url']); ?>" class="role-cta hover-line">
        <?php echo esc_html($role['cta_text']); ?> →
    </a>
    
</article>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $role_card_styles_output = false;
if (!$role_card_styles_output) :
    $role_card_styles_output = true;
?>
<style>
/* Role Card Component */
.role-card {
    text-align: center;
    padding: var(--space-6) 0;
}

.role-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    margin-bottom: var(--space-6);
    color: var(--accent);
}

.role-title {
    font-size: var(--text-2xl);
    margin-bottom: var(--space-4);
}

.role-bio {
    font-size: var(--text-base);
    line-height: 1.7;
    color: var(--charcoal);
    margin-bottom: var(--space-6);
    max-width: 320px;
    margin-left: auto;
    margin-right: auto;
}

.role-quote {
    font-style: italic;
    font-size: var(--text-lg);
    color: var(--ink);
    padding-left: var(--space-4);
    border-left: 3px solid var(--accent);
    margin: 0 auto var(--space-6);
    max-width: 280px;
    text-align: left;
}

.role-cta {
    font-size: var(--text-sm);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--ink);
}

/* Hover line effect */
.hover-line {
    position: relative;
    display: inline-block;
}

.hover-line::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: currentColor;
    transition: width var(--transition-base);
}

.hover-line:hover::after {
    width: 100%;
}

.hover-line:hover {
    opacity: 1;
}
</style>
<?php endif; ?>
