<?php
/**
 * Taverne Clean Theme - header.php
 * 
 * Global header template.
 * Includes: DOCTYPE, head, opening body, fixed nav, mobile menu.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php // Preconnect to external resources if needed ?>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <?php // Preload hero image on front page for LCP ?>
    <?php if (is_front_page()) : 
        $hero_img = get_post_meta(get_option('page_on_front') ?: 1, '_taverne_hero_img', true);
        if ($hero_img) :
            $hero_url = wp_get_attachment_image_url($hero_img, 'plate-hero');
    ?>
        <link rel="preload" as="image" href="<?php echo esc_url($hero_url); ?>">
    <?php endif; endif; ?>
    
    <?php // Theme color for mobile browsers ?>
    <meta name="theme-color" content="#1a1a1a">
    
    <?php // Favicon (set in Customizer or add manually) ?>
    <link rel="icon" href="<?php echo esc_url(get_site_icon_url(32, TAVERNE_THEME_URI . '/assets/images/favicon.png')); ?>" sizes="32x32">
    <link rel="icon" href="<?php echo esc_url(get_site_icon_url(192, TAVERNE_THEME_URI . '/assets/images/favicon.png')); ?>" sizes="192x192">
    <link rel="apple-touch-icon" href="<?php echo esc_url(get_site_icon_url(180, TAVERNE_THEME_URI . '/assets/images/favicon.png')); ?>">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php // MOBILE NAV (Offcanvas - rendered first for z-index stacking) ?>
<div id="mobile-nav" class="mobile-nav" aria-hidden="true" role="dialog" aria-label="Mobile navigation">
    <div class="mobile-nav-header">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="brand" onclick="closeMobileMenu()">
            Pol Taverne.
        </a>
        <button 
            class="mobile-nav-close" 
            onclick="closeMobileMenu()" 
            aria-label="Close menu"
        >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <nav class="mobile-nav-content">
        <ul class="mobile-nav-list">
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" onclick="closeMobileMenu()">
                    Archive
                </a>
            </li>
            <li>
                <a href="<?php echo esc_url(home_url('/artist')); ?>" onclick="closeMobileMenu()">
                    Artist
                </a>
            </li>
            <li>
                <a href="<?php echo esc_url(home_url('/researcher')); ?>" onclick="closeMobileMenu()">
                    Researcher
                </a>
            </li>
            <li>
                <a href="<?php echo esc_url(home_url('/teacher')); ?>" onclick="closeMobileMenu()">
                    Teacher
                </a>
            </li>
            <li>
                <a href="<?php echo esc_url(get_post_type_archive_link('exhibition')); ?>" onclick="closeMobileMenu()">
                    Exhibitions
                </a>
            </li>
            
            <?php // Taxonomy quick links ?>
            <li class="mobile-nav-section">
                <span class="mobile-nav-label">Explore by</span>
            </li>
            
            <?php 
            // Show top 3 taxonomies with their top terms
            $featured_taxes = ['plate_technique', 'plate_motif', 'plate_palette'];
            foreach ($featured_taxes as $tax_slug) :
                $taxonomy = get_taxonomy($tax_slug);
                if (!$taxonomy) continue;
                
                $terms = get_terms([
                    'taxonomy'   => $tax_slug,
                    'hide_empty' => true,
                    'number'     => 4,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ]);
                
                if (empty($terms) || is_wp_error($terms)) continue;
                $label = ucfirst(str_replace('plate_', '', $tax_slug));
            ?>
                <li class="mobile-nav-dropdown">
                    <button class="mobile-nav-dropdown-toggle" onclick="toggleMobileDropdown(this)">
                        <?php echo esc_html($label); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <ul class="mobile-nav-dropdown-menu">
                        <?php foreach ($terms as $term) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($term)); ?>" onclick="closeMobileMenu()">
                                    <?php echo esc_html($term->name); ?>
                                    <span class="term-count">(<?php echo $term->count; ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>?view=<?php echo $tax_slug; ?>" onclick="closeMobileMenu()" class="view-all">
                                View All <?php echo esc_html($label); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <?php // Contact CTA at bottom ?>
        <div class="mobile-nav-footer">
            <a href="mailto:info@poltaverne.nl" class="mobile-nav-email">
                info@poltaverne.nl
            </a>
            <div class="mobile-nav-social">
                <a href="#" aria-label="Instagram">IG</a>
                <a href="#" aria-label="LinkedIn">LI</a>
            </div>
        </div>
    </nav>
</div>

<?php // Overlay for mobile nav ?>
<div id="mobile-nav-overlay" class="mobile-nav-overlay" onclick="closeMobileMenu()"></div>

<?php // FIXED HEADER ?>
<header class="fixed-nav <?php echo is_front_page() ? 'hero-nav' : ''; ?>">
    
    <?php // Brand/Logo ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="brand hover-line">
        Pol Taverne.
    </a>
    
    <?php // Desktop Navigation ?>
    <nav class="primary-nav" role="navigation" aria-label="Primary navigation">
        <?php
        // Use wp_nav_menu if menu exists, otherwise fallback
        if (has_nav_menu('primary')) :
            wp_nav_menu([
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'nav-list',
                'fallback_cb'    => false,
                'depth'          => 1,
            ]);
        else :
            // Fallback menu
        ?>
            <ul class="nav-list">
                <li>
                    <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="hover-line">
                        Archive
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(home_url('/artist')); ?>" class="hover-line">
                        Profile
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(get_post_type_archive_link('exhibition')); ?>" class="hover-line">
                        Exhibitions
                    </a>
                </li>
                <li class="nav-dropdown">
                    <button class="nav-dropdown-toggle hover-line">
                        Explore
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <ul class="nav-dropdown-menu">
                        <?php
                        $explore_taxes = ['plate_technique', 'plate_motif', 'plate_series'];
                        foreach ($explore_taxes as $tax_slug) :
                            $taxonomy = get_taxonomy($tax_slug);
                            if (!$taxonomy) continue;
                            $label = ucfirst(str_replace('plate_', '', $tax_slug));
                        ?>
                            <li>
                                <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>?view=<?php echo $tax_slug; ?>">
                                    <?php echo esc_html($label); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        <?php endif; ?>
    </nav>
    
    <?php // Hamburger Button (Mobile) ?>
    <button 
        class="hamburger" 
        onclick="openMobileMenu()" 
        aria-label="Open menu"
        aria-expanded="false"
        aria-controls="mobile-nav"
    >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
    
</header>

<?php // Spacer to prevent content jump (header is fixed) ?>
<div class="header-spacer" style="height: var(--header-height);"></div>

<?php // Skip to main content link for accessibility ?>
<a href="#main-content" class="skip-link sr-only">Skip to main content</a>

<div id="main-content">

<style>
/* =============================================================================
   HEADER COMPONENT STYLES
   ============================================================================= */

/* Mobile Nav Styles */
.mobile-nav {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 400px;
    background: var(--paper);
    z-index: 1100;
    transform: translateX(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}

.mobile-nav.open {
    transform: translateX(0);
}

.mobile-nav-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1099;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}

.mobile-nav-overlay.open {
    opacity: 1;
    visibility: visible;
}

.mobile-nav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) var(--space-6);
    border-bottom: var(--border);
}

.mobile-nav-header .brand {
    font-family: var(--serif);
    font-size: var(--text-xl);
    font-weight: 500;
}

.mobile-nav-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--space-2);
}

.mobile-nav-content {
    flex: 1;
    overflow-y: auto;
    padding: var(--space-6);
}

.mobile-nav-list {
    list-style: none;
}

.mobile-nav-list > li {
    border-bottom: var(--border);
}

.mobile-nav-list > li > a,
.mobile-nav-dropdown-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: var(--space-4) 0;
    font-size: var(--text-lg);
    font-weight: 500;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
}

.mobile-nav-section {
    padding-top: var(--space-6);
    border-bottom: none !important;
}

.mobile-nav-label {
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--ink-secondary);
}

.mobile-nav-dropdown-menu {
    list-style: none;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.mobile-nav-dropdown.open .mobile-nav-dropdown-menu {
    max-height: 300px;
}

.mobile-nav-dropdown-menu li a {
    display: flex;
    justify-content: space-between;
    padding: var(--space-2) 0 var(--space-2) var(--space-4);
    font-size: var(--text-base);
    color: var(--ink-secondary);
}

.mobile-nav-dropdown-menu .view-all {
    font-weight: 500;
    color: var(--accent);
}

.mobile-nav-footer {
    padding: var(--space-6);
    border-top: var(--border);
    margin-top: auto;
}

.mobile-nav-email {
    display: block;
    font-family: var(--serif);
    font-size: var(--text-lg);
    margin-bottom: var(--space-4);
}

.mobile-nav-social {
    display: flex;
    gap: var(--space-4);
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.mobile-nav-social a {
    color: var(--ink-secondary);
}

/* Desktop Nav Dropdown */
.nav-dropdown {
    position: relative;
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    background: none;
    border: none;
    cursor: pointer;
    font-size: inherit;
    font-weight: inherit;
}

.nav-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--paper);
    border: var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    min-width: 180px;
    padding: var(--space-2) 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    list-style: none;
}

.nav-dropdown:hover .nav-dropdown-menu,
.nav-dropdown-toggle:focus + .nav-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.nav-dropdown-menu li a {
    display: block;
    padding: var(--space-2) var(--space-4);
    font-size: var(--text-sm);
}

.nav-dropdown-menu li a:hover {
    background: var(--stone);
    opacity: 1;
}

/* Hero nav variant (white text on dark hero) */
.hero-nav {
    background: transparent;
    border-bottom: none;
}

.hero-nav .brand,
.hero-nav .nav-list a,
.hero-nav .nav-dropdown-toggle,
.hero-nav .hamburger {
    color: var(--paper);
}

.hero-nav .hover-line::after {
    background: var(--paper);
}

/* Transition when scrolling on hero page */
.hero-nav.scrolled {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(8px);
    border-bottom: var(--border);
}

.hero-nav.scrolled .brand,
.hero-nav.scrolled .nav-list a,
.hero-nav.scrolled .nav-dropdown-toggle,
.hero-nav.scrolled .hamburger {
    color: var(--ink);
}

.hero-nav.scrolled .hover-line::after {
    background: var(--ink);
}

/* Nav list */
.nav-list {
    display: flex;
    align-items: center;
    gap: var(--space-8);
    list-style: none;
}

.nav-list a,
.nav-dropdown-toggle {
    font-size: var(--text-sm);
    font-weight: 500;
    letter-spacing: 0.02em;
}

/* Hamburger visibility */
@media (min-width: 768px) {
    .hamburger {
        display: none;
    }
}

@media (max-width: 767px) {
    .primary-nav {
        display: none;
    }
}

/* Body scroll lock when menu open */
body.menu-open {
    overflow: hidden;
}
</style>

<script>
// =============================================================================
// HEADER JAVASCRIPT
// =============================================================================

// Mobile Menu Functions
function openMobileMenu() {
    const nav = document.getElementById('mobile-nav');
    const overlay = document.getElementById('mobile-nav-overlay');
    const hamburger = document.querySelector('.hamburger');
    
    nav.classList.add('open');
    nav.setAttribute('aria-hidden', 'false');
    overlay.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
    
    // Focus first menu item
    nav.querySelector('a')?.focus();
}

function closeMobileMenu() {
    const nav = document.getElementById('mobile-nav');
    const overlay = document.getElementById('mobile-nav-overlay');
    const hamburger = document.querySelector('.hamburger');
    
    nav.classList.remove('open');
    nav.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
}

function toggleMobileDropdown(button) {
    const dropdown = button.closest('.mobile-nav-dropdown');
    dropdown.classList.toggle('open');
    
    // Rotate arrow icon
    const arrow = button.querySelector('svg');
    arrow.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : '';
}

// Close menu on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMobileMenu();
    }
});

// Hero nav scroll effect
document.addEventListener('DOMContentLoaded', function() {
    const heroNav = document.querySelector('.hero-nav');
    if (!heroNav) return;
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            heroNav.classList.add('scrolled');
        } else {
            heroNav.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    }, { passive: true });
});
</script>