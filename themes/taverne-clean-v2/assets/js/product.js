/**
 * Taverne Clean Theme - product.js
 * 
 * Single plate/impression interactions: thumb gallery, lightbox zoom/pan,
 * impression selector, cart form handling
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

(function() {
    'use strict';

    // ==========================================================================
    // STATE
    // ==========================================================================

    let currentImpressionId = null;
    let lightboxOpen = false;
    let zoom = 1;
    let panX = 0;
    let panY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    // Data from wp_localize_script
    const plateData = window.plateData || { impressions: [], plateSlug: '' };

    // ==========================================================================
    // DOM ELEMENTS
    // ==========================================================================

    const heroImg = document.getElementById('hero-img');
    const impSelect = document.getElementById('imp-select');
    const dynamicPrice = document.getElementById('dynamic-price');
    const impDetails = document.getElementById('imp-details');
    const buyForm = document.getElementById('buy-form');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.querySelector('.lightbox-close');
    const thumbs = document.querySelectorAll('.thumb-item, .impression-thumb');

    // ==========================================================================
    // IMPRESSION UPDATES
    // ==========================================================================

    function updateImpression(impId) {
        const imp = findImpression(impId);
        if (!imp) return;

        currentImpressionId = impId;

        // Update hero image
        if (heroImg && imp.image_url) {
            heroImg.src = imp.image_url;
            heroImg.alt = `Impression ${imp.id}`;
        }

        // Update select dropdown
        if (impSelect) {
            impSelect.value = impId;
        }

        // Update price display
        if (dynamicPrice && imp.price !== undefined) {
            dynamicPrice.textContent = formatPrice(imp.price);
        }

        // Update details section
        if (impDetails) {
            impDetails.innerHTML = buildDetailsHTML(imp);
        }

        // Update hidden form field
        const impIdInput = buyForm?.querySelector('input[name="imp_id"]');
        if (impIdInput) {
            impIdInput.value = impId;
        }

        // Update active thumb styling
        thumbs.forEach(thumb => {
            const thumbId = thumb.dataset.impId || thumb.dataset.impressionId;
            thumb.classList.toggle('active', thumbId == impId);
        });

        // Update URL for deep linking (optional)
        if (plateData.plateSlug && history.replaceState) {
            const newUrl = `/plates/${plateData.plateSlug}/impression/${impId}`;
            history.replaceState({ impId }, '', newUrl);
        }
    }

    function findImpression(impId) {
        return plateData.impressions.find(i => i.id == impId);
    }

    function formatPrice(price) {
        const num = parseFloat(price);
        return isNaN(num) ? '—' : `€${num.toFixed(2)}`;
    }

    function buildDetailsHTML(imp) {
        const statusClass = imp.availability || 'available';
        const statusLabel = {
            'available': 'Available',
            'artist': 'Artist Proof',
            'sold': 'Sold'
        }[statusClass] || imp.availability;

        return `
            <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${statusLabel}</span></p>
            ${imp.notes ? `<p><strong>Notes:</strong> ${imp.notes}</p>` : ''}
            ${imp.width && imp.height ? `<p><strong>Dimensions:</strong> ${imp.width} × ${imp.height} cm</p>` : ''}
        `;
    }

    // ==========================================================================
    // LIGHTBOX
    // ==========================================================================

    function openLightbox(impId) {
        const imp = findImpression(impId || currentImpressionId);
        if (!imp || !lightbox || !lightboxImg) return;

        lightboxImg.src = imp.image_url;
        lightbox.classList.add('open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        lightboxOpen = true;

        // Reset zoom/pan
        zoom = 1;
        panX = 0;
        panY = 0;
        updateLightboxTransform();
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lightboxOpen = false;
    }

    function updateLightboxTransform() {
        if (!lightboxImg) return;
        lightboxImg.style.transform = `scale(${zoom}) translate(${panX}px, ${panY}px)`;
    }

    // Expose globally
    window.openLightbox = openLightbox;
    window.closeLightbox = closeLightbox;

    // ==========================================================================
    // LIGHTBOX CONTROLS: ZOOM & PAN
    // ==========================================================================

    function initLightboxControls() {
        if (!lightboxImg) return;

        // Mouse wheel zoom
        lightboxImg.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.2 : 0.2;
            zoom = Math.max(1, Math.min(4, zoom + delta));
            
            // Reset pan when zooming out to 1
            if (zoom === 1) {
                panX = 0;
                panY = 0;
            }
            updateLightboxTransform();
        });

        // Mouse drag pan
        lightboxImg.addEventListener('mousedown', (e) => {
            if (zoom <= 1) return;
            isDragging = true;
            startX = e.clientX - panX;
            startY = e.clientY - panY;
            lightboxImg.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            panX = e.clientX - startX;
            panY = e.clientY - startY;
            updateLightboxTransform();
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            if (lightboxImg) lightboxImg.style.cursor = 'grab';
        });

        // Touch support for mobile
        let touchStartX, touchStartY, initialPanX, initialPanY;
        let lastTouchDist = 0;

        lightboxImg.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1 && zoom > 1) {
                // Single touch - pan
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                initialPanX = panX;
                initialPanY = panY;
            } else if (e.touches.length === 2) {
                // Pinch zoom
                lastTouchDist = getTouchDistance(e.touches);
            }
        });

        lightboxImg.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (e.touches.length === 1 && zoom > 1) {
                panX = initialPanX + (e.touches[0].clientX - touchStartX);
                panY = initialPanY + (e.touches[0].clientY - touchStartY);
                updateLightboxTransform();
            } else if (e.touches.length === 2) {
                const dist = getTouchDistance(e.touches);
                const delta = (dist - lastTouchDist) * 0.01;
                zoom = Math.max(1, Math.min(4, zoom + delta));
                lastTouchDist = dist;
                updateLightboxTransform();
            }
        });

        function getTouchDistance(touches) {
            const dx = touches[0].clientX - touches[1].clientX;
            const dy = touches[0].clientY - touches[1].clientY;
            return Math.sqrt(dx * dx + dy * dy);
        }
    }

    // ==========================================================================
    // EVENT BINDINGS
    // ==========================================================================

    function initEventListeners() {
        // Impression selector dropdown
        if (impSelect) {
            impSelect.addEventListener('change', (e) => {
                updateImpression(e.target.value);
            });
        }

        // Thumbnail clicks
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const impId = thumb.dataset.impId || thumb.dataset.impressionId;
                if (impId) updateImpression(impId);
            });
        });

        // Hero image click opens lightbox
        if (heroImg) {
            heroImg.addEventListener('click', () => openLightbox());
            heroImg.style.cursor = 'zoom-in';
        }

        // Lightbox close button
        if (lightboxClose) {
            lightboxClose.addEventListener('click', closeLightbox);
        }

        // Close lightbox on backdrop click
        if (lightbox) {
            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) closeLightbox();
            });
        }

        // ESC closes lightbox
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightboxOpen) {
                closeLightbox();
            }
        });

        // Cart form submission
        if (buyForm) {
            buyForm.addEventListener('submit', handleCartSubmit);
        }
    }

    // ==========================================================================
    // CART HANDLING
    // ==========================================================================

    async function handleCartSubmit(e) {
        e.preventDefault();

        const formData = new FormData(buyForm);
        formData.append('action', 'taverne_add_to_cart');
        formData.append('nonce', window.taverneAjax?.nonce || '');

        const submitBtn = buyForm.querySelector('button[type="submit"]');
        const originalText = submitBtn?.textContent;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';
        }

        try {
            const response = await fetch(window.taverneAjax?.ajax_url || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                // Success feedback
                if (submitBtn) submitBtn.textContent = 'Added!';
                setTimeout(() => {
                    if (submitBtn) {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                }, 2000);
                
                // Update cart count if visible
                const cartCount = document.querySelector('.cart-count');
                if (cartCount && data.data?.cart_count) {
                    cartCount.textContent = data.data.cart_count;
                }
            } else {
                alert(data.data?.message || 'Unable to add to cart. Please try again.');
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            }
        } catch (err) {
            console.error('Cart error:', err);
            alert('Something went wrong. Please try again.');
            if (submitBtn) {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        }
    }

    // ==========================================================================
    // INIT
    // ==========================================================================

    document.addEventListener('DOMContentLoaded', () => {
        initEventListeners();
        initLightboxControls();

        // Set initial impression from URL or first available
        const urlImpId = new URLSearchParams(window.location.search).get('imp');
        const pathMatch = window.location.pathname.match(/\/impression\/(\d+)/);
        const initialId = urlImpId || pathMatch?.[1] || plateData.impressions[0]?.id;
        
        if (initialId) {
            updateImpression(initialId);
        }
    });

})();
