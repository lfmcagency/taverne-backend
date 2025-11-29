<?php
/**
 * Taverne Clean Theme - template-parts/filter-drawer.php
 * 
 * Offcanvas filter sidebar (mobile) / static sidebar (desktop).
 * Generates checkboxes for all plate taxonomies.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type string $current_taxonomy  Pre-selected taxonomy (from taxonomy.php)
 *     @type string $current_term      Pre-selected term slug
 * }
 */

$current_taxonomy = $args['current_taxonomy'] ?? '';
$current_term = $args['current_term'] ?? '';

// Get all plate taxonomies
$taxonomies = taverne_get_plate_taxonomies();

?>

<aside class="filter-drawer" id="filter-drawer">
    
    <?php // Mobile header ?>
    <div class="filter-header">
        <h3>Filter Works</h3>
        <button type="button" class="filter-close" id="filter-close" aria-label="Close filters">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <?php // Filter form ?>
    <form class="filter-form" method="get" action="">
        
        <?php foreach ($taxonomies as $tax_slug => $tax_label) :
            $tax_obj = get_taxonomy($tax_slug);
            if (!$tax_obj) continue;
            
            $terms = get_terms([
                'taxonomy'   => $tax_slug,
                'hide_empty' => true,
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => 15,
            ]);
            
            if (empty($terms) || is_wp_error($terms)) continue;
            
            // Check if any terms are selected
            $selected_terms = isset($_GET[$tax_slug]) ? (array) $_GET[$tax_slug] : [];
            
            // If this is the current taxonomy page, pre-select that term
            if ($tax_slug === $current_taxonomy && $current_term) {
                $selected_terms[] = $current_term;
                $selected_terms = array_unique($selected_terms);
            }
            
            $has_selection = !empty($selected_terms);
        ?>
            <fieldset class="filter-group <?php echo $has_selection ? 'has-selection' : ''; ?>">
                <legend class="filter-group-toggle" data-group="<?php echo esc_attr($tax_slug); ?>">
                    <span><?php echo esc_html($tax_label); ?></span>
                    <?php if ($has_selection) : ?>
                        <span class="filter-count"><?php echo count($selected_terms); ?></span>
                    <?php endif; ?>
                    <svg class="filter-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </legend>
                
                <div class="filter-group-content" id="group-<?php echo esc_attr($tax_slug); ?>">
                    <?php foreach ($terms as $term) : 
                        $is_checked = in_array($term->slug, $selected_terms);
                        $checkbox_id = $tax_slug . '-' . $term->slug;
                    ?>
                        <label class="filter-option <?php echo $is_checked ? 'is-checked' : ''; ?>">
                            <input 
                                type="checkbox" 
                                name="<?php echo esc_attr($tax_slug); ?>[]" 
                                value="<?php echo esc_attr($term->slug); ?>"
                                id="<?php echo esc_attr($checkbox_id); ?>"
                                <?php checked($is_checked); ?>
                            >
                            <span class="filter-checkbox"></span>
                            <span class="filter-label"><?php echo esc_html($term->name); ?></span>
                            <span class="filter-term-count"><?php echo esc_html($term->count); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        <?php endforeach; ?>
        
        <?php // Action buttons ?>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-full">
                Apply Filters
            </button>
            <a href="<?php echo esc_url(get_post_type_archive_link('plate')); ?>" class="btn btn-outline btn-full">
                Clear All
            </a>
        </div>
        
    </form>
    
</aside>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $filter_drawer_styles = false;
if (!$filter_drawer_styles) :
    $filter_drawer_styles = true;
?>
<style>
/* Filter Drawer Component */
.filter-drawer {
    background: var(--paper);
}

/* Mobile: Offcanvas */
@media (max-width: 899px) {
    .filter-drawer {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        max-width: 320px;
        height: 100vh;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform var(--transition-base);
        overflow-y: auto;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    }
    
    .filter-drawer.is-open {
        transform: translateX(0);
    }
    
    /* Overlay when open */
    body.filter-open::after {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 999;
    }
}

/* Desktop: Static sidebar */
@media (min-width: 900px) {
    .filter-drawer {
        position: sticky;
        top: calc(var(--header-height) + var(--space-6));
        max-height: calc(100vh - var(--header-height) - var(--space-12));
        overflow-y: auto;
    }
    
    .filter-header {
        display: none;
    }
}

/* Header (mobile only) */
.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4) var(--space-6);
    border-bottom: 1px solid var(--stone);
    position: sticky;
    top: 0;
    background: var(--paper);
    z-index: 10;
}

.filter-header h3 {
    font-size: var(--text-lg);
    margin: 0;
}

.filter-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--space-2);
    color: var(--ink);
}

/* Form */
.filter-form {
    padding: var(--space-4);
}

@media (min-width: 900px) {
    .filter-form {
        padding: 0;
    }
}

/* Filter groups */
.filter-group {
    border: none;
    padding: 0;
    margin: 0 0 var(--space-4);
}

.filter-group-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    width: 100%;
    padding: var(--space-3) 0;
    cursor: pointer;
    font-size: var(--text-sm);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--stone);
}

.filter-group-toggle span:first-child {
    flex: 1;
}

.filter-count {
    background: var(--accent);
    color: var(--paper);
    font-size: var(--text-xs);
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 500;
}

.filter-chevron {
    transition: transform var(--transition-fast);
}

.filter-group.is-collapsed .filter-chevron {
    transform: rotate(-90deg);
}

.filter-group-content {
    padding: var(--space-3) 0;
    max-height: 300px;
    overflow-y: auto;
}

.filter-group.is-collapsed .filter-group-content {
    display: none;
}

/* Individual options */
.filter-option {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) 0;
    cursor: pointer;
    font-size: var(--text-sm);
}

.filter-option input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.filter-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid var(--stone);
    border-radius: 3px;
    position: relative;
    transition: all var(--transition-fast);
    flex-shrink: 0;
}

.filter-option input:checked + .filter-checkbox {
    background: var(--accent);
    border-color: var(--accent);
}

.filter-option input:checked + .filter-checkbox::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid var(--paper);
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.filter-option input:focus + .filter-checkbox {
    box-shadow: 0 0 0 2px rgba(199, 68, 64, 0.3);
}

.filter-label {
    flex: 1;
    color: var(--ink);
}

.filter-term-count {
    font-size: var(--text-xs);
    color: var(--ink-secondary);
    background: var(--stone);
    padding: 2px 6px;
    border-radius: 8px;
}

/* Actions */
.filter-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    padding-top: var(--space-4);
    border-top: 1px solid var(--stone);
    margin-top: var(--space-4);
}

.btn-full {
    width: 100%;
    justify-content: center;
}
</style>

<script>
(function() {
    // Toggle filter groups
    document.querySelectorAll('.filter-group-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const group = this.closest('.filter-group');
            group.classList.toggle('is-collapsed');
        });
    });
    
    // Close drawer button (mobile)
    const closeBtn = document.getElementById('filter-close');
    const drawer = document.getElementById('filter-drawer');
    const toggleBtn = document.getElementById('filter-toggle');
    
    if (closeBtn && drawer) {
        closeBtn.addEventListener('click', function() {
            drawer.classList.remove('is-open');
            document.body.classList.remove('filter-open');
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Update checkbox visual state
    document.querySelectorAll('.filter-option input').forEach(input => {
        input.addEventListener('change', function() {
            this.closest('.filter-option').classList.toggle('is-checked', this.checked);
            
            // Update group count
            const group = this.closest('.filter-group');
            const checked = group.querySelectorAll('input:checked').length;
            const countBadge = group.querySelector('.filter-count');
            
            if (checked > 0) {
                if (countBadge) {
                    countBadge.textContent = checked;
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'filter-count';
                    badge.textContent = checked;
                    group.querySelector('.filter-group-toggle span:first-child').after(badge);
                }
                group.classList.add('has-selection');
            } else {
                if (countBadge) countBadge.remove();
                group.classList.remove('has-selection');
            }
        });
    });
})();
</script>
<?php endif; ?>
