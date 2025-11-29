# Taverne Clean Theme - template-parts/product-data.php Spec
# Purpose: Right col for singles—title/tags/specs, edition selector (updates via JS), qty form, buy btn. From product mock: Grid specs, dropdown impressions. ~60 lines: $args['plate_id'], taverne_display_taxonomy_terms.

# Structure
<aside class="data-grid space-y-6">
    <div>
        <h1 class="font-serif text-3xl mb-2"><?php the_title($args['plate_id']); ?></h1>
        <div class="tags flex gap-2 mb-4">
            <?php taverne_display_taxonomy_terms($args['plate_id'], 'plate_motif', ''); // Pills: Motif, Year, etc. ?>
        </div>
    </div>
    
    <div class="specs grid grid-cols-2 gap-4 text-sm">
        <div><strong>Technique:</strong> <?php echo get_the_terms($args['plate_id'], 'plate_technique')[0]->name ?? ''; ?></div>
        <div><strong>Matrix:</strong> <?php echo get_post_meta($args['plate_id'], '_plate_matrix', true); ?></div>
        <div><strong>Size:</strong> <?php echo get_post_meta($args['plate_id'], '_plate_size_computed', true); ?></div>
        <div><strong>Year:</strong> <?php echo get_post_meta($args['plate_id'], '_plate_year', true); ?></div>
        <div><strong>Total Edition:</strong> <?php echo get_post_meta($args['plate_id'], '_plate_total_impressions', true); ?></div>
        <div><strong>Available:</strong> <?php echo get_post_meta($args['plate_id'], '_plate_available_impressions', true); ?></div>
    </div>
    
    <div id="edition-selector" class="edition-selector">
        <label class="block mb-2">Select Impression</label>
        <select id="imp-select" class="w-full p-2 border border-stone rounded" onchange="updateSelectedImp(this.value)">
            <?php foreach (taverne_get_all_impressions($args['plate_id']) as $imp): ?>
                <option value="<?php echo $imp->id; ?>" data-price="<?php echo $imp->price; ?>" data-status="<?php echo $imp->availability; ?>"><?php echo $imp->impressionNumber . ' (' . $imp->color . ' - ' . $imp->availability . ')'; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div id="imp-details" class="imp-box border border-stone p-4 rounded">
        <!-- JS populates: Status, Price, Notes -->
    </div>
    
    <form id="buy-form" class="buy-form space-y-4">
        <label class="flex justify-between">Quantity <input type="number" name="qty" min="1" max="1" value="1" class="border border-stone p-2 w-20"></label>
        <button type="submit" class="add-to-cart w-full bg-accent text-paper py-3 uppercase tracking-wide disabled:opacity-50" id="cart-btn">Add to Cart - €<span id="dynamic-price">0.00</span></button>
        <?php wp_nonce_field('taverne_add_cart'); ?>
        <input type="hidden" name="imp_id" id="selected-imp" value="">
    </form>
</aside>

# Implementation Notes
- JS: onchange: let opt = this.options[this.selectedIndex]; document.getElementById('dynamic-price').textContent = opt.dataset.price; #selected-imp.value = this.value; Cart submit: AJAX to wp_ajax_taverne_add_cart.
- Gotchas: Max qty from availability. For sold: Disable btn.
- Perf: Impressions loop small ( <50/plate).