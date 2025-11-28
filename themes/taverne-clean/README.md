# Taverne Clean Theme

## What It Does

Minimal, responsive WordPress theme for displaying Pol Taverne's print collection. Features a filterable gallery grid with taxonomy-based filtering sidebar, individual plate detail views, and clean typography optimized for showcasing artwork. Works with the Plate custom post type and 9 custom taxonomies (technique, medium, study, motif, traces, palette, matrix, size, year).

## Key Files

### Core Templates
- **style.css** - Main stylesheet with typography, grid layout, responsive breakpoints
- **functions.php** - Theme setup, image sizes, helper functions (taxonomies, filters, pagination)
- **header.php** - Site header with logo and primary navigation menu
- **footer.php** - Site footer with footer navigation and copyright
- **index.php** - Fallback template for uncaught content types

### Plate Templates
- **archive-page.php** - Main gallery view with filter sidebar and grid layout
- **single-plate.php** - Individual plate detail page with image, specs, taxonomies
- **taxonomy.php** - Filtered gallery view for specific taxonomy terms (e.g., /drypoint)

### Static Pages
- **page.php** - Template for static pages (Artist, About, Contact, etc.)

### Assets
- **assets/js/main.js** - Image gallery interactions, smooth scroll, mobile menu placeholder

## Template Hierarchy

```
/prints → archive-page.php
/prints/example-plate → single-plate.php
/prints/drypoint → taxonomy.php
/artist → page.php
```

## Key Functions (functions.php)

### Setup
- `taverne_gallery_setup()` - Registers menus, image sizes, theme supports
- `taverne_gallery_scripts()` - Enqueues CSS and JS

### Helper Functions
- `taverne_get_plate_taxonomies()` - Returns array of all plate taxonomies
- `taverne_display_taxonomy_terms($post_id, $taxonomy, $label)` - Outputs taxonomy terms for a plate
- `taverne_filter_sidebar()` - Generates filterable sidebar with all taxonomies
- `taverne_pagination()` - Custom pagination with prev/next links
- `taverne_breadcrumbs()` - Breadcrumb navigation

## Image Sizes

- **plate-thumb** - 400×400px (cropped) - Gallery grid cards
- **plate-medium** - 800×800px - Mid-size display
- **plate-large** - 1400×1400px - Single plate view
- **plate-hero** - 1920×1920px - Full-size hero images

## Taxonomies Used

1. **plate_technique** - Printmaking technique (drypoint, etching, etc.)
2. **plate_medium** - Medium used (zinc, lead, wood, etc.)
3. **plate_study** - Study type
4. **plate_motif** - Subject matter/motif
5. **plate_palette** - Color palette used
6. **plate_traces** - Trace information
7. **plate_matrix** - Matrix type
8. **plate_size** - Plate dimensions
9. **plate_year** - Year created

## Menu Locations

- **primary** - Main navigation (header)
- **footer** - Footer links (privacy, terms)

Set up in: Appearance → Menus

## Responsive Breakpoints

```css
Mobile:  < 600px  (1 column)
Tablet:  600-900px (2 columns)
Desktop: 900-1200px (3 columns + sidebar)
Large:   1200px+ (4 columns + sidebar)
```

Filter sidebar appears at 900px+

## Color Scheme

- **Headers:** #000000 (black)
- **Body:** #2C2C2C (dark grey)
- **Links:** #C74440 (warm red)
- **Background:** #FFFFFF (white)
- **Borders:** #E8E8E8 (light grey)

Edit in style.css

## Typography

- **Headers:** Georgia (serif)
- **Body:** Georgia (serif)
- **Base size:** 16px
- **Line height:** 1.6

## Which File to Edit

| Task | File | Line Range |
|------|------|------------|
| Change colors | style.css | Throughout |
| Add/remove taxonomies from filter | functions.php | 162-174, 207-238 |
| Modify gallery grid layout | archive-page.php | 25-66 |
| Change single plate layout | single-plate.php | 12-112 |
| Edit header/logo | header.php | 11-42 |
| Edit footer content | footer.php | 3-23 |
| Add custom image sizes | functions.php | 18-22 |
| Modify pagination | functions.php | 135-157 |
| Change grid breakpoints | style.css | Media queries |
| Add ACF fields display | single-plate.php | 46-89 |

## Dependencies

- **WordPress:** 5.0+
- **Required Plugin:** taverne-core (Plate CPT + taxonomies)
- **Optional:** ACF Pro (for custom fields)

## Installation

1. Upload to wp-content/themes/
2. Activate in Appearance → Themes
3. Set permalinks to "Post name"
4. Configure menus (primary + footer)
5. Upload logo (Appearance → Customize)

## Notes

- No jQuery dependency (vanilla JS)
- No page builder required
- Designed for WPGraphQL compatibility
- ACF field sections commented out in single-plate.php (lines 50-64)
- Removes WP bloat (emojis, generator tags, etc.)
- Semantic HTML5 markup throughout
