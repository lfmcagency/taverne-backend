<?php
/**
 * Taverne Clean Theme - template-parts/pagination.php
 * 
 * Custom pagination for archive grids.
 * Preserves query vars for filtered views.
 * 
 * @package Taverne_Clean
 * @version 2.0
 * 
 * @param array $args {
 *     @type WP_Query $query  Query object to paginate
 * }
 */

if (empty($args['query'])) {
    return;
}

$query = $args['query'];
$current_page = max(1, get_query_var('paged'));
$total_pages = $query->max_num_pages;

if ($total_pages <= 1) {
    return;
}

// Build base URL preserving filters
$base_url = remove_query_arg('paged');

?>

<nav class="pagination" aria-label="Archive navigation">
    
    <?php // Previous ?>
    <?php if ($current_page > 1) : 
        $prev_url = $current_page === 2 
            ? $base_url 
            : add_query_arg('paged', $current_page - 1, $base_url);
    ?>
        <a href="<?php echo esc_url($prev_url); ?>" class="pagination-btn pagination-prev" rel="prev">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Previous</span>
        </a>
    <?php else : ?>
        <span class="pagination-btn pagination-prev is-disabled">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Previous</span>
        </span>
    <?php endif; ?>
    
    <?php // Page indicator ?>
    <span class="pagination-info">
        Page <?php echo esc_html($current_page); ?> of <?php echo esc_html($total_pages); ?>
    </span>
    
    <?php // Next ?>
    <?php if ($current_page < $total_pages) : 
        $next_url = add_query_arg('paged', $current_page + 1, $base_url);
    ?>
        <a href="<?php echo esc_url($next_url); ?>" class="pagination-btn pagination-next" rel="next">
            <span>Next</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    <?php else : ?>
        <span class="pagination-btn pagination-next is-disabled">
            <span>Next</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </span>
    <?php endif; ?>
    
</nav>

<?php
// =============================================================================
// COMPONENT STYLES (output once)
// =============================================================================
static $pagination_styles = false;
if (!$pagination_styles) :
    $pagination_styles = true;
?>
<style>
/* Pagination Component */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-4);
    padding: var(--space-8) 0;
    margin-top: var(--space-8);
    border-top: 1px solid var(--stone);
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--ink);
    border: 1px solid var(--stone);
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

a.pagination-btn:hover {
    border-color: var(--ink);
    background: var(--ink);
    color: var(--paper);
}

.pagination-btn.is-disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    font-size: var(--text-sm);
    color: var(--ink-secondary);
}

@media (max-width: 480px) {
    .pagination-btn span {
        display: none;
    }
    
    .pagination-btn {
        padding: var(--space-3);
    }
}
</style>
<?php endif; ?>
