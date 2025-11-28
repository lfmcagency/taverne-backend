# Taverne CPT

## What It Does
Registers the data schema foundation for Taverne: 3 post types (Plate, Research, Teaching) and 9 plate taxonomies. Also provides CSV import/export for bulk term management with metadata (thumbnails, descriptions). Zero UI—pure data model.

## Key Files

**taverne-cpt.php** - Plugin bootstrap, disables Gutenberg for plates, flushes rewrites on activation
**includes/post-types.php** - Registers 3 CPTs (plate, research, teaching) with REST & GraphQL support
**includes/taxonomies.php** - Registers 9 plate taxonomies (technique, medium, study, motif, palette, traces, matrix, size, year)
**includes/csv-importer.php** - Admin page for bulk import/export of taxonomy terms with images

## How It Connects

- **Consumed by:** Taverne Meta (stores data for these CPTs), Taverne Editions (admin UI for plates), Taverne GraphQL (exposes via API)
- **Depends on:** Nothing—this is the foundation layer
- **Data flow:** CPT schema → other plugins build on top

```
Taverne CPT (schema)
    ↓
    ├─→ Taverne Meta (data layer)
    ├─→ Taverne Editions (admin UI)
    └─→ Taverne GraphQL (API exposure)
```

## Common Tasks

### Add a new taxonomy to Plate CPT
1. Edit `includes/taxonomies.php`
2. Copy one of the 9 existing `register_taxonomy()` blocks
3. Change taxonomy name (e.g., `plate_your_taxonomy`) and labels
4. Add taxonomy to `includes/csv-importer.php` in `$plate_taxonomies` array (lines ~111, ~296)
5. Add to plate's `taxonomies` array in `includes/post-types.php` (line ~45)

### Add a new post type
1. Edit `includes/post-types.php`
2. Copy one of the 3 existing `register_post_type()` blocks
3. Change post type slug, labels, and menu icon
4. Optionally register taxonomies for it in `includes/taxonomies.php`

### Change Plate CPT capabilities
1. Edit `includes/post-types.php` line 43
2. Modify the `supports` array (currently: title, editor, thumbnail, excerpt, custom-fields)
3. Add/remove features like 'revisions', 'author', 'comments'

### Import taxonomy terms from CSV
1. Navigate to **Plates → Import Terms** in WP admin
2. Upload CSV with format: `taxonomy,slug,name,description,image_url`
3. Terms are created or updated if they already exist
4. Images are downloaded to media library and attached as term thumbnails

### Export taxonomy terms to CSV
1. Navigate to **Plates → Import Terms** in WP admin
2. Click "Download Terms CSV"
3. All 9 plate_* taxonomies exported with metadata

### Disable GraphQL for a post type
1. Edit `includes/post-types.php`
2. Remove `show_in_graphql` line from the post type config
3. Remove `graphql_single_name` and `graphql_plural_name` lines

## Architecture Notes

- **No rewrites:** All taxonomies use `rewrite => false` because headless frontend doesn't care about WP URLs
- **Gutenberg disabled:** Plate CPT uses custom UI from Taverne Editions plugin, not block editor
- **REST + GraphQL:** All types expose both APIs for maximum flexibility
- **Activation hook:** Flushes rewrite rules to register permalink structure
- **CSV security:** Import/export requires `manage_options` capability (admin only)

## File Quick Reference

| File | Lines | Purpose |
|------|-------|---------|
| taverne-cpt.php | 52 | Bootstrap & configuration |
| post-types.php | 160 | 3 CPTs + 2 category taxonomies |
| taxonomies.php | 216 | 9 plate taxonomies |
| csv-importer.php | 354 | Term bulk import/export UI |

**Total:** ~782 lines
