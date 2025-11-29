# Phase 1: Plugin Foundation (do this first, non-negotiable)

1.1 Add series taxonomy to taverne-cpt
1.2 Add exhibitions CPT to taverne-cpt
1.3 Add missing meta fields to taverne-meta
1.4 Add impression rewrite rule + query vars
1.5 Add helper functions to taverne-meta (the taverne_get_* stuff)

# Phase 2: Theme Skeleton + ONE Working Page

2.1 Theme basics: style.css header, functions.php with setup/enqueues, index.php
2.2 Get archive-plate.php + content-impression-card.php actually rendering real data

This validates EVERYTHING before you build more

# Phase 3: Global Frame

3.1 header.php, footer.php, nav-mobile.php
3.2 Base CSS (tokens, typography, grid system)
3.3 main.js (menu toggle, basic interactions)

# Phase 4: Remaining Templates

4.1 front-page.php + its partials (hero, roles, archive-index, sliders)
4.2 taxonomy.php + filter-drawer.php
4.3 single-plate.php + product partials + product.js
4.4 single-impression.php
4.5 Exhibition templates
4.6 page.php

Why this order? Phase 2 is the validation step. If archive-plate renders correctly, you've proven the whole stack works. Then Phase 3-4 is just filling in the pattern.