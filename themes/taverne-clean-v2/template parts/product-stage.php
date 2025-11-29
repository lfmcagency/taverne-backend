# Taverne Clean Theme - template-parts/product-stage.php Spec
# Purpose: Visual wheel for single-plate—states as tabs, impressions as horizontal swipe carousel, hero img + thumbs. From product mock: Sticky left col, onclick lightbox. ~50 lines: $args['states']/$args['impressions'], localize for JS.

# Structure
<section class="stage sticky top-32 self-start h-screen overflow-y-auto">
    <div class="states-tabs mb-4 flex border-b border-stone">
        <?php foreach ($args['states'] as $i => $state): ?>
            <button class="tab-btn px-4 py-2 <?php echo $i == 0 ? 'active border-b-2 border-accent' : ''; ?>" onclick="switchState(<?php echo $state->id; ?>)"><?php echo $state->stateNumber; ?> - <?php echo $state->title; ?></button>
        <?php endforeach; ?>
    </div>
    <div class="canvas-area mb-4 cursor-zoom relative h-[75vh]" onclick="openLightbox(currentImpId)">
        <img id="hero-img" src="" alt="" class="absolute inset-0 w-full h-full object-contain">
    </div>
    <div class="thumbs flex overflow-x-auto gap-2 no-scrollbar">
        <?php foreach ($args['impressions'] as $imp): ?>
            <img src="<?php echo wp_get_attachment_image_src($imp->image_id, 'plate-thumb')[0]; ?>" alt="Thumb" class="thumb w-20 h-20 object-cover rounded cursor-pointer <?php echo $imp->id == $args['current_imp'] ? 'active border-2 border-accent' : ''; ?>" onclick="updateImpression(<?php echo $imp->id; ?>)">
        <?php endforeach; ?>
    </div>
</section>

# Implementation Notes
- JS: product.js: let currentImpId = <?php echo json_encode($args['impressions'][0]->id ?? ''); ?>; function updateImpression(id) { fetch(`/wp-json/wp/v2/plate-imp/${id}`) // Or localize full array .then(res => res.json()).then(data => { document.getElementById('hero-img').src = data.image; // Update price etc in data partial }); } SwitchState: Filter impressions by state_id.
- Gotchas: Current imp from query var if single-impression. For photog: If video, <video> tag.
- Perf: Localize $args['impressions'] array in single-plate.php—no fetch on load.