# Taverne GraphQL

## What It Does
Exposes Taverne data via GraphQL API for headless Next.js frontend. Extends WPGraphQL with custom types for States, Impressions, and adds 25+ fields to Plate type. Requires WPGraphQL and Taverne Meta plugins. Frontend developers query this, not WordPress REST API.

## Key Files

**taverne-graphql.php** - Bootstrap, dependency checks (WPGraphQL + Taverne Meta), activation validation
**includes/register-types.php** - Central hub that loads all type files and registers them on `graphql_register_types` hook
**includes/types/plate-type.php** - Extends Plate with 25+ custom fields (dimensions, pricing, SEO, states)
**includes/types/state-type.php** - Registers State object type with 13 fields + impressions connection
**includes/types/impression-type.php** - Registers Impression object type with image, color, price, availability
**includes/types/research-type.php** - Extends Research post type with additional fields
**includes/types/teaching-type.php** - Extends Teaching post type with additional fields

## How It Connects

- **Depends on:** WPGraphQL (3rd party), Taverne CPT (post types), Taverne Meta (CRUD functions + meta fields)
- **Consumed by:** Next.js frontend (headless)
- **Data flow:** GraphQL query → WPGraphQL → Taverne GraphQL resolvers → Taverne Meta CRUD functions → Database

```
Next.js Frontend
    ↓ (GraphQL query)
WPGraphQL Plugin
    ↓
Taverne GraphQL (custom types)
    ↓
Taverne Meta (CRUD functions)
    ↓
Custom Tables + Post Meta
```

## Common Tasks

### Add a new field to Plate type
1. Edit `includes/types/plate-type.php` inside `taverne_register_plate_fields()` function
2. Call `register_graphql_field('Plate', 'yourFieldName', [...])`
3. Specify type (`String`, `Int`, `Float`, `Boolean`, or custom type)
4. Write resolver function that returns data from post meta or custom tables
5. Field immediately available in GraphQL schema

Example:
```php
register_graphql_field('Plate', 'edition', [
    'type' => 'String',
    'description' => 'Edition number',
    'resolve' => function($post) {
        return get_post_meta($post->ID, '_plate_edition', true);
    }
]);
```

### Add a new field to State type
1. Edit `includes/types/state-type.php` inside `register_graphql_object_type('State', [...])` fields array
2. Add field definition with type, description, and resolver
3. Resolver receives `$state` object from `wp_plate_states` table
4. Return data from `$state->field_name`

### Create a new custom GraphQL type
1. Create new file in `includes/types/` (e.g., `collection-type.php`)
2. Define registration function (e.g., `taverne_register_collection_type()`)
3. Call `register_graphql_object_type('Collection', [...])` with fields
4. Add root query with `register_graphql_field('RootQuery', 'collection', [...])`
5. Include file in `includes/register-types.php`
6. Call registration function on `graphql_register_types` hook

### Query states and impressions from frontend
GraphQL query example:
```graphql
query GetPlate($id: ID!) {
  plate(id: $id, idType: DATABASE_ID) {
    title
    width
    height
    basePrice
    states {
      id
      stateNumber
      title
      impressions {
        id
        impressionNumber
        color
        price
        availability
      }
    }
  }
}
```

### Expose a computed field that aggregates data
1. Add resolver that calls Taverne Meta functions
2. Example in `plate-type.php` line ~189 (states connection):
```php
register_graphql_field('Plate', 'states', [
    'type' => ['list_of' => 'State'],
    'resolve' => function($post) {
        return taverne_get_states($post->ID);
    }
]);
```

### Add SEO fields to Research or Teaching
1. Edit `includes/types/research-type.php` or `teaching-type.php`
2. Copy SEO field pattern from `plate-type.php` (lines ~156-186)
3. Adjust meta keys (e.g., `_research_meta_title` instead of `_taverne_meta_title`)
4. Register meta fields in Taverne Meta plugin first

### Test GraphQL queries in WordPress
1. Install GraphiQL plugin or use WPGraphQL's built-in IDE
2. Navigate to **GraphQL → GraphiQL IDE** in WP admin
3. Write query in left pane, click Execute
4. View JSON response in right pane
5. Use for debugging before implementing in Next.js

## GraphQL Types Registered

### Plate (Extended)
**Base:** Registered by Taverne CPT via WPGraphQL
**Extensions:** 25+ custom fields added by this plugin
- Core: `content`, `excerpt`
- Dimensions: `width`, `height`, `size`, `area`
- Pricing: `basePrice`
- Metadata: `year`, `matrixSlug`, `studySlug`, `sku`
- Computed: `totalStates`, `totalImpressions`, `availableImpressions`, `paletteAggregate`
- SEO: `seoTitle`, `seoDescription`, `canonicalUrl`, `noindex`
- Relations: `states` (list of State objects)

### State (Custom Object)
**Source:** `wp_plate_states` table
**Fields:** 13 + 2 relations
- IDs: `id`, `plateId`
- Data: `stateNumber`, `title`, `excerpt`, `description`
- Images: `featuredImageId`, `featuredImpressionId`
- Ordering: `sortOrder`
- Timestamps: `createdAt`, `updatedAt`
- Relations: `impressions` (list), `plate` (parent)
- Computed: `impressionCount`

**Root Query:** `state(id: Int)`

### Impression (Custom Object)
**Source:** `wp_plate_impressions` table
**Fields:** Print-specific data (image, color, price, availability, changes, notes)

**Root Query:** `impression(id: Int)` (if registered)

### Research (Extended)
**Base:** Registered by Taverne CPT via WPGraphQL
**Extensions:** Additional fields for research posts (implementation in research-type.php)

### Teaching (Extended)
**Base:** Registered by Taverne CPT via WPGraphQL
**Extensions:** Additional fields for teaching posts (implementation in teaching-type.php)

## Dependency Chain

Plugin won't activate without:
1. **WPGraphQL** - Checked on activation (hard requirement, dies if missing)
2. **Taverne Meta** - Checked at runtime (displays admin notice if missing)

All CRUD resolvers check if functions exist before calling:
```php
if (!function_exists('taverne_get_states')) {
    return null;
}
```

## Resolver Patterns

### Post Meta Field
```php
'resolve' => function($post) {
    $value = get_post_meta($post->ID, '_meta_key', true);
    return $value ? (float) $value : null;
}
```

### Custom Table Data (List)
```php
'resolve' => function($post) {
    if (!function_exists('taverne_get_states')) {
        return null;
    }
    return taverne_get_states($post->ID);
}
```

### Computed Aggregate
```php
'resolve' => function($post) {
    $total = get_post_meta($post->ID, '_plate_total_states', true);
    return $total ? (int) $total : 0;
}
```

### Parent Relationship
```php
'resolve' => function($state) {
    return get_post($state->plate_id);
}
```

## Type Casting

Always cast to correct GraphQL type:
- `String` → return as-is or `(string)`
- `Int` → `(int) $value`
- `Float` → `(float) $value`
- `Boolean` → `(bool) $value`
- `[list_of => 'Type']` → return array of objects

## Frontend Query Examples

### Get all plates with states
```graphql
query GetAllPlates {
  plates {
    nodes {
      id
      databaseId
      title
      slug
      width
      height
      basePrice
      availableImpressions
      states {
        stateNumber
        title
      }
    }
  }
}
```

### Get single plate with full details
```graphql
query GetPlateDetails($slug: ID!) {
  plate(id: $slug, idType: SLUG) {
    title
    content
    excerpt
    width
    height
    size
    basePrice
    year
    totalStates
    totalImpressions
    availableImpressions
    states {
      id
      stateNumber
      title
      excerpt
      description
      impressions {
        id
        impressionNumber
        color
        price
        availability
        changes
      }
    }
    plateTechniques {
      nodes {
        name
        slug
      }
    }
  }
}
```

### Get states for a plate
```graphql
query GetPlateStates($plateId: Int!) {
  plate(id: $plateId, idType: DATABASE_ID) {
    states {
      stateNumber
      title
      impressionCount
      impressions {
        color
        price
        availability
      }
    }
  }
}
```

## File Quick Reference

| File | Lines | Purpose |
|------|-------|---------|
| taverne-graphql.php | 83 | Bootstrap & dependency validation |
| register-types.php | 39 | Type registration hub |
| plate-type.php | 207 | 25+ Plate fields + states connection |
| state-type.php | 162 | State object + root query |
| impression-type.php | ~150 | Impression object + root query (estimated) |
| research-type.php | ~50 | Research extensions (estimated) |
| teaching-type.php | ~50 | Teaching extensions (estimated) |

**Total:** ~741 lines (estimated)
