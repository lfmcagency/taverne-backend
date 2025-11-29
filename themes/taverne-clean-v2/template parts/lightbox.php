<?php
/**
 * Taverne Clean Theme - template-parts/lightbox.php
 * 
 * Global lightbox modal for image zoom.
 * Loaded once in footer.php—controlled via JS.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// This is the structural markup - styles and JS are in footer.php
// This partial can be included for reference or customization
?>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-hidden="true">
    
    <?php // Close button ?>
    <button class="lightbox-close" id="lightbox-close" aria-label="Close">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
    
    <?php // Image container ?>
    <div class="lightbox-content">
        <div class="lightbox-stage" id="lightbox-stage">
            <img src="" alt="" id="lightbox-img" draggable="false">
        </div>
        
        <?php // Caption ?>
        <div class="lightbox-caption" id="lightbox-caption"></div>
    </div>
    
    <?php // Zoom controls ?>
    <div class="lightbox-controls">
        <button class="lightbox-ctrl" id="lightbox-zoom-out" aria-label="Zoom out">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <span class="lightbox-zoom-level" id="lightbox-zoom-level">100%</span>
        <button class="lightbox-ctrl" id="lightbox-zoom-in" aria-label="Zoom in">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button class="lightbox-ctrl" id="lightbox-reset" aria-label="Reset zoom">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"></polyline>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
        </button>
    </div>
    
</div>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $lightbox_styles = false;
if (!$lightbox_styles) :
    $lightbox_styles = true;
?>
<style>
/* Lightbox Component */
.lightbox {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity var(--transition-base), visibility var(--transition-base);
}

.lightbox.is-open {
    opacity: 1;
    visibility: visible;
}

.lightbox-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    z-index: 10;
    background: none;
    border: none;
    color: var(--paper);
    cursor: pointer;
    padding: var(--space-2);
    opacity: 0.7;
    transition: opacity var(--transition-fast);
}

.lightbox-close:hover {
    opacity: 1;
}

.lightbox-content {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-12) var(--space-4);
}

.lightbox-stage {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: grab;
    max-width: 100%;
    max-height: calc(100vh - 120px);
}

.lightbox-stage.is-dragging {
    cursor: grabbing;
}

.lightbox-stage img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transform-origin: center center;
    transition: transform 0.1s ease-out;
    user-select: none;
}

.lightbox-caption {
    color: var(--paper);
    font-size: var(--text-sm);
    text-align: center;
    padding: var(--space-4);
    opacity: 0.8;
}

.lightbox-controls {
    position: absolute;
    bottom: var(--space-4);
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    background: rgba(255, 255, 255, 0.1);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-lg);
    backdrop-filter: blur(10px);
}

.lightbox-ctrl {
    background: none;
    border: none;
    color: var(--paper);
    cursor: pointer;
    padding: var(--space-2);
    opacity: 0.7;
    transition: opacity var(--transition-fast);
}

.lightbox-ctrl:hover {
    opacity: 1;
}

.lightbox-zoom-level {
    color: var(--paper);
    font-size: var(--text-sm);
    min-width: 50px;
    text-align: center;
}

/* Prevent body scroll when lightbox open */
body.lightbox-open {
    overflow: hidden;
}
</style>

<script>
(function() {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const lightboxStage = document.getElementById('lightbox-stage');
    const zoomLevelDisplay = document.getElementById('lightbox-zoom-level');
    
    let currentZoom = 1;
    let isDragging = false;
    let startX, startY, translateX = 0, translateY = 0;
    
    // Global open function
    window.openLightbox = function(src, caption = '') {
        if (!lightbox || !lightboxImg) return;
        
        lightboxImg.src = src;
        lightboxCaption.textContent = caption;
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
        
        // Reset zoom
        currentZoom = 1;
        translateX = 0;
        translateY = 0;
        updateTransform();
    };
    
    // Global close function
    window.closeLightbox = function() {
        if (!lightbox) return;
        
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');
        
        // Clear after transition
        setTimeout(() => {
            if (lightboxImg) lightboxImg.src = '';
        }, 300);
    };
    
    function updateTransform() {
        if (!lightboxImg) return;
        lightboxImg.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`;
        if (zoomLevelDisplay) {
            zoomLevelDisplay.textContent = Math.round(currentZoom * 100) + '%';
        }
    }
    
    function zoom(delta) {
        currentZoom = Math.min(Math.max(currentZoom + delta, 0.5), 4);
        if (currentZoom === 1) {
            translateX = 0;
            translateY = 0;
        }
        updateTransform();
    }
    
    // Event listeners
    if (lightbox) {
        // Close button
        document.getElementById('lightbox-close')?.addEventListener('click', closeLightbox);
        
        // Background click
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target === lightboxStage) {
                closeLightbox();
            }
        });
        
        // ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
                closeLightbox();
            }
        });
        
        // Zoom controls
        document.getElementById('lightbox-zoom-in')?.addEventListener('click', () => zoom(0.25));
        document.getElementById('lightbox-zoom-out')?.addEventListener('click', () => zoom(-0.25));
        document.getElementById('lightbox-reset')?.addEventListener('click', () => {
            currentZoom = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        });
        
        // Mouse wheel zoom
        lightboxStage?.addEventListener('wheel', function(e) {
            e.preventDefault();
            zoom(e.deltaY > 0 ? -0.1 : 0.1);
        });
        
        // Drag to pan
        lightboxStage?.addEventListener('mousedown', function(e) {
            if (currentZoom > 1) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                lightboxStage.classList.add('is-dragging');
            }
        });
        
        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            updateTransform();
        });
        
        document.addEventListener('mouseup', function() {
            isDragging = false;
            lightboxStage?.classList.remove('is-dragging');
        });
    }
})();
</script>
<?php endif; ?>
