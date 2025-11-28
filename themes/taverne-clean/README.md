# Taverne Gallery WordPress Theme

Clean, minimalist art gallery theme for Pol Taverne's print collection. Built lightweight and responsive with that crispy gallery aesthetic you wanted.

## What's Inside

A fully functional WordPress theme with:
- **Responsive grid system** - Stacks on mobile, multi-column on larger screens
- **Filter sidebar** - Shows on tablet/desktop, hides on mobile
- **Clean typography** - Black serif headers, dark grey body text
- **Minimal design** - White background, sporadic lines for structure
- **Gallery-first layout** - Images are the star, everything else is supporting cast
- **Custom Plate CPT support** - Works with your existing post types and taxonomies

## File Structure

```
taverne-gallery/
├── style.css              # Main stylesheet + theme header
├── functions.php          # Theme setup, menus, helpers
├── header.php            # Site header with logo & nav
├── footer.php            # Site footer
├── index.php             # Fallback template
├── archive-plate.php     # Main gallery with filters
├── single-plate.php      # Individual product/plate page
├── taxonomy.php          # Taxonomy archives
├── page.php              # Static pages (Artist, About, etc.)
└── assets/
    └── js/
        └── main.js       # Minimal JS for interactions
```

## Installation

1. **Zip it up:**
   ```bash
   # From C:\Users\Louis\poltaverne-codebase\imports\
   zip -r taverne-gallery.zip theme/
   ```

2. **Upload to WordPress:**
   - Go to Appearance → Themes → Add New → Upload Theme
   - Choose your `taverne-gallery.zip`
   - Activate it

3. **Configure menus:**
   - Appearance → Menus
   - Create a "Primary Navigation" menu (Prints, A/R/T, About, Contact)
   - Create a "Footer" menu (Privacy, Terms)
   - Assign them to locations

4. **Add logo:**
   - Appearance → Customize → Site Identity → Logo
   - Upload Pol's logo (works best around 400x100px)

5. **Test permalinks:**
   - Settings → Permalinks → "Post name"
   - Save (flushes rewrites)

## What It Does

### Archive Pages (`/prints`)
- Full gallery grid with all plates
- Filter sidebar on larger screens (technique, motif, year, etc.)
- Responsive: 1 column (mobile) → 2 (tablet) → 3-4 (desktop)
- Shows year, size, technique below each thumbnail

### Taxonomy Pages (`/prints/drypoint`)
- Same grid layout with filters
- Shows term name + description at top
- Filtered to that specific taxonomy

### Single Plates (`/prints/rembrandt-variaties-1`)
- Two-column layout (image left, details right on desktop)
- Stacks on mobile
- Shows all taxonomies grouped nicely
- Ready for ACF fields (dimensions, price, editions - just uncomment in template)

### Static Pages (`/artist`, `/researcher`)
- Simple centered layout
- Full-width content area
- Perfect for A/R/T pages with Pol's writing

## Customization

### Colors
In `style.css`, look for:
- Headers: `#000000`
- Body text: `#2C2C2C`
- Active links: `#C74440` (warm red)
- Background: `#FFFFFF`
- Borders: `#E8E8E8`

### Typography
Currently using Georgia (web-safe serif). To use a custom font:
1. Add `@import` at top of `style.css`
2. Update `font-family` in body and heading styles

### Breakpoints
- Mobile: < 600px
- Tablet: 600-900px
- Desktop: 900-1200px
- Large: 1200px+

Filter sidebar kicks in at 900px.

## Next Steps

1. **ACF Integration:**
   - Uncomment the ACF field sections in `single-plate.php` (lines ~60-70)
   - Set up field groups for dimensions, price, editions, states, impressions
   - Theme will automatically display them

2. **Image Gallery:**
   - Add data attributes to thumbs: `data-full-image="url"`
   - JS in `main.js` will handle the switching

3. **Import Products:**
   - Use your existing `import_products.csv` and `taxonomies_import.xlsx`
   - WP All Import or custom script
   - Theme will display them automatically

4. **Tweak Filters:**
   - Edit `taverne_filter_sidebar()` in `functions.php`
   - Add/remove taxonomies from the array
   - Reorder as needed

## What Works Right Now

✅ Responsive grid (tested mobile → desktop)
✅ Filter sidebar (shows/hides based on screen size)
✅ All taxonomies displaying properly
✅ Clean, minimal aesthetic
✅ Fast loading (no bloat)
✅ Semantic HTML5
✅ Accessible navigation
✅ WP menu system integrated

## What Needs Your Custom Plugin

- Custom meta fields (dimensions, price, editions)
- States/impressions management
- Cart/checkout functionality
- Any e-commerce features

Theme just displays what you give it - your `taverne-core` plugin handles the data structure.

## Support

Built specifically for Pol Taverne's gallery. Pairs with:
- `taverne-core` plugin (CPT/taxonomies)
- Your custom meta plugin
- WPGraphQL for Next.js frontend



---

**Version:** 1.0.0  
**Author:** Louis Faucher  
**Built:** November 2025
