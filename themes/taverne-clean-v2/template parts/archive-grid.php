# Taverne Clean Theme - template-parts/archive-grid.php Spec
# Purpose: Core grid wrapper for archives—masonry display of impression-cards, with pagination. From category mock: .artwork-grid, row mixes. ~30 lines: $args['query'] passed, loop with the_post().

# Structure
<div class="artwork-grid <?php echo isset($args['masonry']) ? 'masonry' : ''; ?>">
    <?php $query = $args['query'] ?? $wp_query; if ($query->have_posts()): while ($query->have_posts()): $query->the_post(); 
        $top_imp = taverne_get_impressions(get_the_ID())[0] ?? null;
    ?>
        <?php get_template_part('template-parts/content-impression-card', null, ['post_id' => get_the_ID(), 'impression' => $top_imp]); ?>
    <?php endwhile; else: ?>
        <p class="col-span-full text-center text-gray-500">No works yet—check back for new impressions.</p>
    <?php endif; wp_reset_postdata(); ?>
</div>
<?php if ($query->max_num_pages > 1): get_template_part('template-parts/pagination', null, ['query' => $query]); endif; ?>

# Implementation Notes
- Masonry: CSS grid auto-fit or enqueue masonry.js if needed.
- Gotchas: Empty state for filters. For aggregates: Pass query with joined impressions.
- Perf: 20 per page—offset for paged.