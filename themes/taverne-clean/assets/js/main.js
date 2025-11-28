/**
 * Taverne Gallery Theme JavaScript
 * Minimal interactions for gallery functionality
 */

(function() {
    'use strict';
    
    /**
     * Mobile menu toggle (if needed later)
     */
    function initMobileMenu() {
        // Placeholder for mobile menu functionality
        // Can add hamburger menu logic here if needed
    }
    
    /**
     * Image gallery interaction for single plates
     * Allows clicking thumbs to change main image
     */
    function initImageGallery() {
        const thumbs = document.querySelectorAll('.plate-thumb');
        const featuredImage = document.querySelector('.plate-featured-image img');
        
        if (!thumbs.length || !featuredImage) return;
        
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                // Remove active class from all thumbs
                thumbs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked thumb
                this.classList.add('active');
                
                // Get the full-size image URL from data attribute
                const newImageSrc = this.dataset.fullImage || this.querySelector('img').src;
                
                // Update featured image
                featuredImage.src = newImageSrc;
                featuredImage.alt = this.querySelector('img').alt;
            });
        });
    }
    
    /**
     * Smooth scroll to top
     */
    function initSmoothScroll() {
        const scrollLinks = document.querySelectorAll('a[href^="#"]');
        
        scrollLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if (href === '#') {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    /**
     * Initialize all functions on DOM ready
     */
    function init() {
        initMobileMenu();
        initImageGallery();
        initSmoothScroll();
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
