<?php
/**
 * Taverne Clean Theme - footer.php
 * 
 * Global footer template.
 * Includes: Footer content, wp_footer(), closing body/html.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// =============================================================================
// GET FOOTER DATA
// =============================================================================

// Try to get data from options/meta
$studio_bio = get_option('taverne_studio_bio', 
    'Printmaker, researcher, educator. Working at the intersection of traditional intaglio techniques and contemporary practice. Based in the Netherlands.'
);

$contact_email = get_option('taverne_contact_email', 'info@poltaverne.nl');

// Social links (stored as option array)
$socials = get_option('taverne_social_links', [
    'instagram' => '#',
    'artsy'     => '#',
    'linkedin'  => '#',
]);

?>

</div><!-- #main-content -->

<footer class="site-footer">
    <div class="footer-main">
        
        <?php // BRAND & BIO ?>
        <div class="footer-brand">
            <h4>Pol Taverne.</h4>
            <p><?php echo esc_html($studio_bio); ?></p>
        </div>
        
        <?php // CONTACT & SOCIAL ?>
        <div class="footer-contact">
            <a href="mailto:<?php echo esc_attr($contact_email); ?>" class="footer-email hover-line">
                <?php echo esc_html($contact_email); ?>
            </a>
            
            <div class="footer-social">
                <?php if (!empty($socials['instagram'])) : ?>
                    <a href="<?php echo esc_url($socials['instagram']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       aria-label="Follow on Instagram">
                        Instagram
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($socials['artsy'])) : ?>
                    <a href="<?php echo esc_url($socials['artsy']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       aria-label="View on Artsy">
                        Artsy
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($socials['linkedin'])) : ?>
                    <a href="<?php echo esc_url($socials['linkedin']); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       aria-label="Connect on LinkedIn">
                        LinkedIn
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <?php // BOTTOM BAR ?>
    <div class="footer-bottom">
        <span>&copy; <?php echo date('Y'); ?> Pol Taverne. All Rights Reserved.</span>
        
        <nav class="footer-nav" aria-label="Footer navigation">
            <?php
            if (has_nav_menu('footer')) :
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'footer-nav-list',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ]);
            else :
                // Fallback footer links
            ?>
                <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" class="hover-line">Privacy Policy</a>
                <a href="<?php echo esc_url(home_url('/terms-conditions')); ?>" class="hover-line">Terms & Conditions</a>
                <a href="<?php echo esc_url(home_url('/impressum')); ?>" class="hover-line">Impressum</a>
            <?php endif; ?>
        </nav>
    </div>
    
    <?php // NEWSLETTER SIGNUP (Optional - uncomment if needed)
    /*
    <div class="footer-newsletter">
        <h5>Stay Updated</h5>
        <p>New works, exhibitions, and insights delivered to your inbox.</p>
        <form class="newsletter-form" action="#" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" class="btn btn-primary">Subscribe</button>
        </form>
    </div>
    */
    ?>
    
</footer>

<?php // LIGHTBOX MODAL (Global, loaded once) ?>
<div id="lightbox" class="lightbox" role="dialog" aria-label="Image lightbox" aria-hidden="true">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close lightbox">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    
    <div class="lightbox-content">
        <img id="lightbox-img" src="" alt="" class="lightbox-img">
        <div id="lightbox-thumbs" class="lightbox-thumbs"></div>
    </div>
</div>

<?php // BACK TO TOP BUTTON ?>
<button 
    id="back-to-top" 
    class="back-to-top" 
    onclick="scrollToTop()" 
    aria-label="Back to top"
    style="display: none;"
>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
</button>

<style>
/* =============================================================================
   FOOTER COMPONENT STYLES
   ============================================================================= */

/* Footer nav list (when using wp_nav_menu) */
.footer-nav-list {
    display: flex;
    gap: var(--space-4);
    list-style: none;
}

.footer-nav-list li a {
    color: rgba(255, 255, 255, 0.4);
    font-size: var(--text-xs);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.footer-nav-list li a:hover {
    color: rgba(255, 255, 255, 0.8);
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: var(--space-6);
    right: var(--space-6);
    width: 44px;
    height: 44px;
    background: var(--ink);
    color: var(--paper);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-fast);
    z-index: 100;
    box-shadow: var(--shadow-md);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--accent);
    transform: translateY(-2px);
}

/* Newsletter (optional) */
.footer-newsletter {
    padding-top: var(--space-8);
    margin-top: var(--space-8);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    max-width: 400px;
}

.footer-newsletter h5 {
    font-size: var(--text-lg);
    margin-bottom: var(--space-2);
}

.footer-newsletter p {
    font-size: var(--text-sm);
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: var(--space-4);
}

.newsletter-form {
    display: flex;
    gap: var(--space-2);
}

.newsletter-form input {
    flex: 1;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--paper);
    padding: var(--space-3);
}

.newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

/* Lightbox (base styles, enhanced in product.js) */
.lightbox {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
}

.lightbox.open {
    display: flex;
}

.lightbox-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    color: var(--paper);
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--space-2);
    transition: opacity var(--transition-fast);
    z-index: 10;
}

.lightbox-close:hover {
    opacity: 0.7;
}

.lightbox-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.lightbox-img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.lightbox-thumbs {
    display: flex;
    gap: var(--space-2);
    justify-content: center;
    margin-top: var(--space-4);
    overflow-x: auto;
    max-width: 100%;
    padding: var(--space-2);
}

.lightbox-thumbs img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--radius);
    cursor: pointer;
    opacity: 0.6;
    transition: opacity var(--transition-fast);
}

.lightbox-thumbs img:hover,
.lightbox-thumbs img.active {
    opacity: 1;
}
</style>

<script>
// =============================================================================
// FOOTER JAVASCRIPT
// =============================================================================

// Back to Top functionality
(function() {
    const backToTop = document.getElementById('back-to-top');
    if (!backToTop) return;
    
    // Show/hide based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 500) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    }, { passive: true });
})();

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Lightbox base functions (enhanced in product.js)
function openLightbox(imageSrc, imageAlt) {
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    
    if (!lightbox || !img) return;
    
    img.src = imageSrc;
    img.alt = imageAlt || '';
    
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    
    if (!lightbox) return;
    
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

// Close lightbox on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Close lightbox on background click
document.getElementById('lightbox')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>

<?php wp_footer(); ?>

</body>
</html>