# Taverne Meta

## What It Does
Manages plate metadata and custom database tables for states/impressions. Registers post meta fields, auto-calculates computed values (size, totals), and provides CRUD functions for variable editions. Pure data layer with no UI.

## Key Files

**taverne-meta.php** - Plugin bootstrap, creates tables on activation
**includes/database.php** - Custom tables schema + 20+ CRUD functions for states/impressions
**includes/meta-fields.php** - Post meta registration (dimensions, pricing, computed fields, SEO)

## How It Connects

- **Depends on:** Taverne CPT (needs plate post type to exist)
- **Consumed by:** Taverne Editions (uses CRUD functions), Taverne GraphQL (exposes meta fields)
- **Database:** Creates 2 custom tables: `wp_plate_states` and `wp_plate_impressions`

```
Taverne CPT → Taverne Meta (data layer) → Taverne Editions (UI)
                    ↓
            Taverne GraphQL (API)
```

## Common Tasks

### Add a new post meta field
1. Edit `includes/meta-fields.php`
2. Add `register_post_meta()` call inside `taverne_register_plate_meta_fields()` function (line ~13)
3. Specify type, description, REST visibility, sanitization callback
4. Field is immediately available via REST API and to other plugins

### Change size computation logic
1. Edit `includes/meta-fields.php` line ~178
2. Modify thresholds in `taverne_update_plate_computed_fields()` function
3. Currently: S < 38cm, M 38-70cm, L > 70cm (width-based, not area)
4. Computed on every save or when dimensions change

### Create a state programmatically
```php
$state_id = taverne_create_state($plate_id, [
    'title' => 'State 1',
    'description' => 'Initial proof',
    'featured_image_id' => 123
]);
```

### Create an impression programmatically
```php
$impression_id = taverne_create_impression($plate_id, $state_id, [
    'image_id' => 456,
    'color' => 'sepia',
    'price' => 250.00,
    'availability' => 'available'
]);
```

### Get all states for a plate
```php
$states = taverne_get_states($plate_id); // Returns array of objects
```

### Get all impressions for a plate
```php
$impressions = taverne_get_all_impressions($plate_id); // Across all states
```

### Update computed fields manually
```php
taverne_update_plate_computed_fields($plate_id);
// Auto-updates: size, area, totals, palette aggregate
```

### Add a new column to states table
1. Edit `includes/database.php` line ~18 (states table schema)
2. Add column definition to CREATE TABLE statement
3. Deactivate and reactivate plugin (or run dbDelta manually)
4. Update CRUD functions to handle new column

### Add a new column to impressions table
1. Edit `includes/database.php` line ~38 (impressions table schema)
2. Add column definition to CREATE TABLE statement
3. Deactivate and reactivate plugin (or run dbDelta manually)
4. Update CRUD functions (create, update) to handle new column

## Database Schema

### `wp_plate_states` Table
```
id                      bigint      Primary key
plate_id                bigint      FK to posts table
state_number            int         Sequential state number (1, 2, 3...)
title                   varchar     Display name
excerpt                 text        Short description
description             text        Full HTML content
featured_image_id       bigint      FK to media library
featured_impression_id  bigint      FK to impressions table
sort_order              int         Manual ordering
created_at              datetime    Auto timestamp
updated_at              datetime    Auto timestamp on update
```

### `wp_plate_impressions` Table
```
id                  bigint      Primary key
plate_id            bigint      FK to posts table
state_id            bigint      FK to states table
impression_number   int         Sequential number within state
image_id            bigint      FK to media library
color               varchar     Color name/code
price               decimal     Price in euros (10,2)
availability        varchar     Status: available/sold/reserved
changes             text        What changed from previous impression
notes               text        Internal notes
sort_order          int         Manual ordering
created_at          datetime    Auto timestamp
updated_at          datetime    Auto timestamp on update
```

## CRUD Functions Reference

### States
- `taverne_create_state($plate_id, $data)` - Returns state_id or WP_Error
- `taverne_get_state($state_id)` - Returns state object
- `taverne_get_states($plate_id, $orderby, $order)` - Returns array of states
- `taverne_update_state($state_id, $data)` - Returns true or WP_Error
- `taverne_delete_state($state_id)` - Deletes state + all impressions, returns true or WP_Error
- `taverne_get_next_state_number($plate_id)` - Returns next sequential number
- `taverne_get_state_count($plate_id)` - Returns integer

### Impressions
- `taverne_create_impression($plate_id, $state_id, $data)` - Returns impression_id or WP_Error
- `taverne_get_impression($impression_id)` - Returns impression object
- `taverne_get_impressions_by_state($state_id, $orderby, $order)` - Returns array
- `taverne_get_all_impressions($plate_id, $orderby, $order)` - Returns array (all states)
- `taverne_update_impression($impression_id, $data)` - Returns true or WP_Error
- `taverne_delete_impression($impression_id)` - Returns true or WP_Error
- `taverne_get_next_impression_number($state_id)` - Returns next sequential number
- `taverne_get_impression_count($state_id)` - Returns integer
- `taverne_get_total_impression_count($plate_id)` - Returns integer (all states)
- `taverne_get_available_impression_count($plate_id)` - Returns integer (available only)
- `taverne_get_palette_aggregate($plate_id)` - Returns comma-separated color list

### Computed Fields
- `taverne_update_plate_computed_fields($plate_id)` - Updates all auto-calculated meta

## Post Meta Fields

### Direct Input
- `_plate_width` - Width in cm (float)
- `_plate_height` - Height in cm (float)
- `_plate_price` - Base price in euros (float)
- `_plate_year` - Year created (int)
- `_plate_matrix` - Material slug (string)
- `_plate_study` - Study/series slug (string)
- `_plate_sku` - Internal catalog number (string)

### Auto-Computed (read-only)
- `_plate_size_computed` - S/M/L label (string)
- `_plate_area_computed` - Area in cm² (float)
- `_plate_total_states` - State count (int)
- `_plate_total_impressions` - Total impression count (int)
- `_plate_available_impressions` - Available impression count (int)
- `_plate_palette_aggregate` - Comma-separated colors (string)

### SEO
- `_taverne_meta_title` - SEO title override (string)
- `_taverne_meta_description` - Meta description 160 chars (string)
- `_taverne_canonical_url` - Canonical URL (auto-set to permalink if empty)
- `_taverne_noindex` - Hide from search engines (boolean)

## File Quick Reference

| File | Lines | Purpose |
|------|-------|---------|
| taverne-meta.php | 32 | Bootstrap & activation hooks |
| database.php | 471 | Custom tables + CRUD operations |
| meta-fields.php | 243 | Post meta registration + computed fields |

**Total:** ~746 lines
