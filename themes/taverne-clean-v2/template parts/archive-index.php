# Taverne Clean Theme - template-parts/archive-index.php Spec
# Purpose: Mega 3-col taxonomy tree for home #archive—h3 for Technique/Motif/etc., ul of terms with counts/links. From home mock: Borders between cols, space-y links. ~40 lines: get_terms for each plate_tax, hide_empty=true for 300+ scale.

# Structure
<div class="grid md:grid-cols-3 gap-8">
    <?php $tax_groups = [['title' => 'Techniques', 'tax' => 'plate_technique'], ['title' => 'Motifs', 'tax' => 'plate_motif'], ['title' => 'Matrices', 'tax' => 'plate_matrix'] /* Add more from taverne_get_plate_taxonomies() */]; 
    foreach ($tax_groups as $i => $group): $terms = get_terms(['taxonomy' => $group['tax'], 'hide_empty' => true, 'number' => 6, 'orderby' => 'count', 'order' => 'DESC']); ?>
        <div class="md:border-r md:<?php echo $i < 2 ? 'pr-4' : 'pr-0'; ?> md:border-stone last:md:border-r-0">
            <h3 class="font-serif text-2xl mb-6 capitalize"><?php echo $group['title']; ?></h3>
            <ul class="space-y-2">
                <?php foreach ($terms as $term): ?>
                    <li><a href="<?php echo get_term_link($term); ?>" class="text-sm hover-line block py-1"><?php echo esc_html($term->name); ?> (<?php echo $term->count; ?>)</a></li>
                <?php endforeach; ?>
                <li class="mt-4"><a href="/plates?taxonomy=<?php echo $group['tax']; ?>" class="text-sm italic hover-line">View All</a></li>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

# Implementation Notes
- Groups: Hardcode 3-4 cols to match mock; full list in functions.php return for dynamic.
- Gotchas: Counts from term->count (posts), but for impressions: Custom counter via Meta's taverne_get_impression_count if term meta.
- Perf: get_terms caches; limit 6 for speed.