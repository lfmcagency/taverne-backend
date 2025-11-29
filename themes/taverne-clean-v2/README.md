# Taverne Clean V2 Theme

## Overview
Minimal, responsive WordPress theme for Pol Taverne's gallery—showcasing 300+ works in printmaking (drypoint/carborundum on zinc/lead), photography (remixed old masters), and ceramics (glazed motifs from earth tones). Headless-ready (WPGraphQL exposed via Taverne GraphQL plugin), but shines native: Filterable grids, swipeable impression variants, artist-series carousels. Built for Hetzner CX23 (Ubuntu/Apache)—sub-1s loads on 300-catalog queries. Modular partials for easy tweaks, vanilla JS/CSS for lightness.

## Features
- **Gallery Grids**: Masonry-ish archives (/plates, /plates/matrix/zinc) with AJAX facets (9 taxonomies + series meta-groups).
- **Product Bundles**: Single plates (/plates/rb0501) with states tabs, impression wheels (flick like proofs), lightbox zoom/pan.
- **Deep Variants**: /plates/rb0501/impression/3-sepia—sellable units with qty/cart, canonical to plate.
- **Home Flow**: Hero quote (thesis pulls), roles trifecta (artist/researcher/teacher), recent impressions slider, series carousels ("Fragments of Silence" clusters), archive index, exhibitions
- **Admin Bliss**: Card UI via Taverne Editions (drag-sort impressions, status lights).
- **Responsive**: Mobile drawers, touch swipes (hammer.js?); breakpoints: <600px (1-col), 900px+ (4-col + sidebar).
- **SEO**: Term metas from CSV (Excel descs), canonicals, noindex sold variants.

## Dependencies
- **Plugins** (Required):
  - Taverne CPT: Registers 'plate'/'research'/'teaching' + 9 taxonomies (technique, motif, palette, etc.).
  - Taverne Meta: Custom tables (wp_plate_states/impressions), CRUD funcs (taverne_get_impressions()), computed fields (_plate_available_impressions).
  - Taverne Editions: Admin meta boxes, AJAX handlers.
  - Taverne GraphQL: Exposes Plate → states:[State] → impressions:[Impression] (optional for headless).
  - WPGraphQL + WPGraphQL for WP (for API).
- **WordPress**: 6.0+ (no Gutenberg on plates).
- **Optional**: WooCommerce (for /cart—hook add-to-cart in product-data.php).

## Installation
1. **Upload**: Zip theme to `/wp-content/themes/taverne-clean/` on Hetzner (/var/www/html/wp-content/themes/).
2. **Activate**: WP Admin > Appearance > Themes > Activate Taverne Clean.
3. **Menus**: Appearance > Menus > Create Primary (Archive/Profile/...) & Footer (Privacy/Impressum); assign locations.
4. **Permalinks**: Settings > Permalinks > Save (flushes for /plates/{slug}/impression/{id}).
5. **Options**: Create "Studio" page (ID=1), add meta (_taverne_studio_bio, hero_quote) via custom fields or Editions extension.
6. **Plugins**: Install/activate Taverne stack; import terms via Plates > Import Terms (CSV from Excel).
7. **Test**: Add test plate in Plates > Add New (via Editions UI), drag impressions, view /plates.

## Template Hierarchy
- **front-page.php**: Home (hero + roles + index + recent slider + series).
- **page.php**: /artist, /researcher, /teacher (expanded bios/accordions).
- **archive-plate.php**: /plates (main gallery + filters).
- **taxonomy.php**: /plates/{taxonomy}/{term} (faceted, e.g., /plates/palette/ochre).
- **single-plate.php**: /plates/{slug} (bundle wheel + relateds).
- **single-impression.php**: /plates/{slug}/impression/{id} (variant deep-dive).
- **Fallback**: index.php redirects to front-page.

## Key Functions (in functions.php)
- `taverne_setup()`: Theme supports, menus, image sizes (plate-thumb 400x400 cropped, etc.).
- `taverne_scripts()`: Enqueues style.css, main.js (sliders/filters), product.js (on singles).
- `taverne_get_plate_taxonomies()`: Returns ['plate_technique', ...] for filters.
- `taverne_display_taxonomy_terms($post_id, $tax)`: Outputs tag pills.
- `taverne_filter_sidebar()`: Generates checkbox form.
- `taverne_ajax_filter()`: WP_AJAX handler for live grids.
- `taverne_breadcrumbs()`: Trail with term/plate awareness.
- `taverne_update_computed_on_save($post_id)`: Calls Meta func on plate save.

## Assets
- **css/admin.css**: Card styling for Editions (status lights, uploads; mobile grids).
- **js/main.js**: Menu toggle, slider scrolls, AJAX filters.
- **js/product.js**: Impression updates, lightbox zoom/drag.
- **js/admin.js**: Sortable drags, field debounces, wp.media uploads.

## Customization
| Task | File/Line | Notes |
|------|-----------|-------|
| Colors (ink/accent) | style.css :root | Update vars, re-minify. |
| Add taxonomy filter | functions.php 100-120 (filter_sidebar) | Append to array, flush permalinks. |
| Grid columns | style.css .artwork-grid | Media query tweaks for 300+ loads. |
| Hero quote rotation | front-page.php hero-home | JS random from Meta array. |
| Cart integration | product-data.php form | Add woocommerce_template_single_add_to_cart() hook. |
| Series query | functions.php taverne_get_series_group | SQL tweak for meta LIKE. |
| Image sizes | functions.php add_image_size | Add 'plate-3d' for ceramics GLTF thumbs. |

## Responsive Breakpoints
- Mobile (<600px): 1-col grids, drawer menus, touch swipes.
- Tablet (600-900px): 2-col, stacked cards.
- Desktop (900-1200px): 3-col + sidebar, hover scales.
- Large (1200px+): 4-col masonry, sticky stages.

## Color Scheme & Typography
- **Colors**: Ink #1a1a1a (body), Accent #C74440 (CTAs), Paper #ffffff (bg), Stone #e5e5e5 (borders).
- **Typography**: Georgia serif (headers/body, 16px base, 1.6 lh). Italics for quotes.

## Performance & Hetzner Notes
- **Queries**: Paginate 20/plate, hide_empty terms, meta_cache in Meta CRUD.
- **Assets**: Minify CSS/JS (wp-cli or Gulp), lazy imgs, no jQuery.
- **Cache**: Apache mod_expires, Hetzner firewall (80/443 open), optional Redis for queries.
- **Scales to 300+**: Facets aggregate via term counts/Meta totals; test with WP_Query EXPLAIN.

## Troubleshooting
- **404 on /impression**: Flush permalinks; check rewrite rule in functions.php.
- **Filters Empty**: Ensure hide_empty=false if testing zero-count terms.
- **Admin Drag Fails**: Enqueue jquery-ui-core/sortable; check console for nonce errors.
- **No Impressions**: Verify Taverne Meta tables created on activation.
- **Headless Test**: Query /graphql? query={ plates { nodes { title states { impressions { price } } } } }.

## Credits & License
- Built for Pol Taverne's a/r/tography inquiry—art as living research.
- GPL v2; fork away, just credit the burrs.

Last Updated: November 29, 2025 | Version 1.0