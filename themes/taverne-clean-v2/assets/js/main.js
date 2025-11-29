/**
 * Taverne Clean Theme - main.js
 * 
 * Global vanilla JS: mobile menu, slider scrolls, AJAX filters, drawer toggle
 * No jQuery dependency - pure vanilla for performance
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

(function() {
    'use strict';

    // ==========================================================================
    // MOBILE MENU
    // ==========================================================================

    const mobileNav = document.getElementById('mobile-nav');
    const hamburger = document.querySelector('.hamburger');
    const mobileClose = document.querySelector('.mobile-nav-close');

    function openMobileMenu() {
        if (!mobileNav) return;
        mobileNav.classList.add('open');
        mobileNav.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        if (!mobileNav) return;
        mobileNav.classList.remove('open');
        mobileNav.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    // Expose globally for onclick handlers in PHP
    window.openMobileMenu = openMobileMenu;
    window.closeMobileMenu = closeMobileMenu;

    if (hamburger) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            openMobileMenu();
        });
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', closeMobileMenu);
    }

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (mobileNav && mobileNav.classList.contains('open')) {
            if (!mobileNav.contains(e.target) && !hamburger?.contains(e.target)) {
                closeMobileMenu();
            }
        }
    });

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
            closeFilterDrawer();
        }
    });

    // ==========================================================================
    // SLIDER / CAROUSEL SCROLLS
    // ==========================================================================

    function initSlider(sliderId, prevBtnId, nextBtnId) {
        const slider = document.getElementById(sliderId);
        const prev = document.getElementById(prevBtnId);
        const next = document.getElementById(nextBtnId);
        
        if (!slider) return;

        const scrollAmount = () => {
            // Scroll by card width + gap
            const card = slider.querySelector('.slider-card, .carousel-card, [class*="-card"]');
            return card ? card.offsetWidth + 24 : 400;
        };

        if (prev) {
            prev.addEventListener('click', () => {
                slider.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
            });
        }

        if (next) {
            next.addEventListener('click', () => {
                slider.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
            });
        }

        // Update button states on scroll
        slider.addEventListener('scroll', () => {
            if (prev) prev.disabled = slider.scrollLeft <= 0;
            if (next) next.disabled = slider.scrollLeft >= slider.scrollWidth - slider.clientWidth - 10;
        });
    }

    // ==========================================================================
    // FILTER DRAWER (Taxonomy Archive Pages)
    // ==========================================================================

    const filterDrawer = document.getElementById('filterDrawer');
    const filterToggle = document.querySelector('.filter-toggle');
    const filterClose = document.querySelector('.filter-close');

    function openFilterDrawer() {
        if (!filterDrawer) return;
        filterDrawer.classList.add('open');
        filterDrawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeFilterDrawer() {
        if (!filterDrawer) return;
        filterDrawer.classList.remove('open');
        filterDrawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    window.openFilterDrawer = openFilterDrawer;
    window.closeFilterDrawer = closeFilterDrawer;
    window.toggleFilterDrawer = () => {
        filterDrawer?.classList.contains('open') ? closeFilterDrawer() : openFilterDrawer();
    };

    if (filterToggle) {
        filterToggle.addEventListener('click', openFilterDrawer);
    }

    if (filterClose) {
        filterClose.addEventListener('click', closeFilterDrawer);
    }

    // ==========================================================================
    // AJAX FILTER FORM
    // ==========================================================================

    const filterForm = document.getElementById('filterForm');
    const gridContainer = document.getElementById('grid-container');
    const resultsCount = document.querySelector('.results-count');

    if (filterForm && gridContainer) {
        filterForm.addEventListener('submit', handleFilterSubmit);
        
        // Also handle checkbox changes for live filtering
        filterForm.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', debounce(handleFilterSubmit, 300));
        });
    }

    async function handleFilterSubmit(e) {
        if (e) e.preventDefault();
        
        const formData = new FormData(filterForm);
        formData.append('action', 'taverne_filter');
        formData.append('nonce', window.taverneAjax?.nonce || '');

        gridContainer.classList.add('loading');

        try {
            const response = await fetch(window.taverneAjax?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                gridContainer.innerHTML = data.data.html;
                if (resultsCount) {
                    resultsCount.textContent = `${data.data.found} work${data.data.found !== 1 ? 's' : ''}`;
                }
                // Update URL without reload
                const params = new URLSearchParams(formData);
                params.delete('action');
                params.delete('nonce');
                const queryString = params.toString();
                const newUrl = queryString 
                    ? `${window.location.pathname}?${queryString}`
                    : window.location.pathname;
                history.replaceState({}, '', newUrl);
            }
        } catch (err) {
            console.error('Filter error:', err);
        } finally {
            gridContainer.classList.remove('loading');
        }
    }

    // Reset filters
    const resetBtn = document.querySelector('.filter-reset');
    if (resetBtn && filterForm) {
        resetBtn.addEventListener('click', () => {
            filterForm.reset();
            handleFilterSubmit();
        });
    }

    // ==========================================================================
    // DROPDOWN TOGGLES (Desktop Nav)
    // ==========================================================================

    document.querySelectorAll('.nav-dropdown > a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu = link.nextElementSibling;
            const isOpen = !submenu.classList.contains('hidden');
            
            // Close all other dropdowns
            document.querySelectorAll('.nav-dropdown ul').forEach(ul => {
                ul.classList.add('hidden');
            });

            // Toggle this one
            if (!isOpen) {
                submenu.classList.remove('hidden');
            }
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-dropdown')) {
            document.querySelectorAll('.nav-dropdown ul').forEach(ul => {
                ul.classList.add('hidden');
            });
        }
    });

    // ==========================================================================
    // UTILITY: DEBOUNCE
    // ==========================================================================

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ==========================================================================
    // INIT ON DOM READY
    // ==========================================================================

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize sliders if they exist
        initSlider('recent-slider', 'recentPrev', 'recentNext');
        initSlider('series-slider', 'seriesPrev', 'seriesNext');
        initSlider('works-slider', 'worksPrev', 'worksNext');
    });

})();
