# Taverne Editions

## What It Does
Admin interface for managing plates, states, and impressions in WP admin. Provides mobile-first card-based UI with AJAX CRUD operations. Replaces default WordPress editor with custom meta boxes. Content editors use this to manage variable editions without touching code.

## Key Files

**taverne-editions.php** - Bootstrap, removes default editor, enqueues assets only on plate screens
**includes/meta-boxes.php** - Renders 4 meta boxes (Plate Details, Techniques, States/Impressions, SEO)
**includes/ajax-handlers.php** - 6 AJAX endpoints for real-time state/impression CRUD without page reloads
**includes/bulk-operations.php** - Stub for future bulk actions (currently empty)
**assets/css/admin.css** - Card-based UI styling for mobile-first admin interface
**assets/js/admin.js** - JavaScript for AJAX interactions, image uploads, status lights

## How It Connects

- **Depends on:** Taverne CPT (plate post type), Taverne Meta (CRUD functions, meta fields)
- **Consumed by:** WP Admin users (content editors)
- **Calls functions from:** Taverne Meta plugin (all `taverne_*()` CRUD functions)

```
WP Admin Editor
    ↓
Taverne Editions (UI layer)
    ↓
Taverne Meta (CRUD functions)
    ↓
Custom Tables + Post Meta
```

## Common Tasks

### Add a new field to Plate Details
1. Edit `includes/meta-boxes.php` in `taverne_render_plate_details_box()` function (line ~71)
2. Add HTML input field inside `.taverne-details-grid` div
3. Edit `taverne_save_plate_details()` function (line ~254) to handle POST data
4. Use `update_post_meta($post_id, '_your_field', $value)` to save

### Add a new meta box
1. Edit `includes/meta-boxes.php` in `taverne_register_meta_boxes()` function (line ~25)
2. Call `add_meta_box()` with unique ID, title, callback, post type
3. Create render function (e.g., `taverne_render_your_box($post)`)
4. Create save function and hook to `save_post` action

### Modify the States/Impressions card layout
1. Edit `includes/meta-boxes.php` function `taverne_render_states_impressions_box()` (line ~482)
2. Edit HTML structure of `.taverne-state-card` elements
3. Edit `assets/css/admin.css` to match new layout
4. Edit `assets/js/admin.js` if adding interactive elements

### Modify impression fields
1. Edit `includes/meta-boxes.php` function `taverne_render_impression_card()` (line ~605)
2. Add/remove input fields inside `.taverne-impression-fields` div
3. Update `includes/ajax-handlers.php` function `taverne_ajax_update_impression()` (line ~191)
4. Add new field case in switch statement to handle AJAX updates

### Add a new AJAX endpoint
1. Edit `includes/ajax-handlers.php`
2. Create function `taverne_ajax_your_action()`
3. Add security checks: `check_ajax_referer('taverne_editions_nonce', 'nonce')`
4. Process data and call Taverne Meta CRUD functions
5. Return `wp_send_json_success()` or `wp_send_json_error()`
6. Hook to action: `add_action('wp_ajax_taverne_your_action', 'taverne_ajax_your_action')`
7. Add JavaScript in `assets/js/admin.js` to call your endpoint

### Change which admin screens load assets
1. Edit `taverne-editions.php` function `taverne_editions_enqueue_assets()` (line ~22)
2. Modify `$hook` check to add more screens (e.g., `'edit.php'` for list view)
3. Currently restricted to: `post.php` and `post-new.php` for plate post type only

### Remove the default editor for another post type
1. Edit `taverne-editions.php` function `taverne_remove_plate_editor()` (line ~57)
2. Call `remove_post_type_support('your_post_type', 'editor')`

## Meta Boxes Rendered

### 1. Plate Details (Normal Priority, High)
- **Location:** Below title, above content
- **Fields:** Description (WYSIWYG), Excerpt, Dimensions, Price, Year, Matrix, Study, Size (readonly)
- **Renders:** Dropdowns populated from taxonomies, computed size radio buttons
- **Saves:** Line 254 in meta-boxes.php

### 2. Techniques & Classifications (Normal Priority, High)
- **Location:** Below Plate Details
- **Fields:** Technique, Medium, Motif, Traces (checkboxes), Palette (readonly aggregate)
- **Renders:** Checkbox grids from taxonomy terms
- **Saves:** Line 420 in meta-boxes.php with term validation to prevent duplicates

### 3. States & Impressions (Normal Priority, Default)
- **Location:** Below Techniques
- **Fields:** State cards with collapsible impression rows
- **State fields:** Title, Excerpt, Description
- **Impression fields:** Image, Number, Color, Price, Availability (status lights), Changes, Notes
- **Interactive:** Add/delete states, add/delete impressions, upload images, click status lights
- **No save handler:** Uses AJAX exclusively (ajax-handlers.php)

### 4. SEO (Sidebar, Default)
- **Location:** Right sidebar
- **Fields:** Meta title, Meta description (with char counter), Canonical URL, Noindex checkbox
- **Saves:** Line 791 in meta-boxes.php

## AJAX Endpoints

All endpoints require `taverne_editions_nonce` and `edit_post` capability.

### State Operations
- **`taverne_add_state`** - Creates new state, returns state_id and state_number
- **`taverne_update_state`** - Updates single field (title, excerpt, description, featured_impression_id)
- **`taverne_delete_state`** - Deletes state + all impressions in cascade

### Impression Operations
- **`taverne_add_impression`** - Creates new impression with base price, returns impression_id and number
- **`taverne_update_impression`** - Updates single field (color, price, availability, changes, notes, image_id)
- **`taverne_delete_impression`** - Deletes single impression

## JavaScript Hooks

Edit `assets/js/admin.js` to modify:
- **Image uploader:** `.taverne-upload-trigger` click handler uses `wp.media()`
- **Status lights:** `.taverne-status-light` click handler updates availability
- **Field updates:** `.taverne-impression-field` change/blur handlers debounce AJAX saves
- **State updates:** `.taverne-state-title`, `.taverne-state-excerpt`, `.taverne-state-description` blur handlers
- **Add/delete buttons:** Various click handlers trigger AJAX CRUD

## Styling Notes

Edit `assets/css/admin.css` for:
- **Card layout:** `.taverne-card-body`, `.taverne-state-card`
- **Form grids:** `.taverne-details-grid`, `.taverne-checkbox-grid`
- **Status lights:** `.taverne-status-light` (green/orange/red)
- **Mobile-first:** Uses flexbox and grid with responsive breakpoints
- **Color coding:** Green = available, Orange = artist collection, Red = sold

## Security

- **Nonces:** All meta boxes use unique nonces (`taverne_plate_details_nonce`, etc.)
- **AJAX:** All endpoints check `taverne_editions_nonce` via `check_ajax_referer()`
- **Capabilities:** Requires `edit_post` capability for all operations
- **Sanitization:** All inputs sanitized (`sanitize_text_field`, `sanitize_textarea_field`, `wp_kses_post`)
- **Validation:** Taxonomy term IDs validated before saving (line 420) to prevent duplicates
- **Escaping:** All outputs escaped (`esc_attr`, `esc_html`, `esc_textarea`, `esc_url`)

## File Quick Reference

| File | Lines | Purpose |
|------|-------|---------|
| taverne-editions.php | 71 | Bootstrap & asset enqueuing |
| meta-boxes.php | 830 | Renders 4 meta boxes + save handlers |
| ajax-handlers.php | 281 | 6 AJAX CRUD endpoints |
| bulk-operations.php | 15 | Stub for future features |
| assets/css/admin.css | ~500 | Card UI styling (not shown) |
| assets/js/admin.js | ~800 | AJAX interactions (not shown) |

**Total PHP:** ~1,197 lines
