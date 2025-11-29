# Taverne Clean Theme - template-parts/nav-mobile.php Spec
# Purpose: Offcanvas mobile menu—hamburger toggle to full-screen links (Archive, Profile, tax dropdowns like Techniques > Drypoint). From home mock: Hidden md+, slide-in from right. ~30 lines: WP menu or hardcode for speed, close on link click.

# Structure
<div id="mobile-nav" class="fixed inset-0 bg-paper z-40 hidden translate-x-full transition-transform md:hidden">
    <div class="flex justify-end p-6">
        <button onclick="toggleMobileMenu()" class="text-2xl">&times;</button>
    </div>
    <nav class="p-6 h-full overflow-y-auto">
        <ul class="space-y-4 text-lg">
            <li><a href="<?php echo home_url('/plates'); ?>" onclick="toggleMobileMenu()" class="block py-2 hover-line">Archive</a></li>
            <li><a href="<?php echo home_url('/artist'); ?>" onclick="toggleMobileMenu()" class="block py-2 hover-line">Profile</a></li>
            <li><a href="#exhibitions" onclick="toggleMobileMenu()" class="block py-2 hover-line">Exhibitions</a></li>
            <li class="dropdown">
                <a href="#" class="block py-2 hover-line">Techniques <span class="arrow">▼</span></a>
                <ul class="ml-4 space-y-1 hidden">
                    <?php $terms = get_terms(['taxonomy' => 'plate_technique', 'hide_empty' => true, 'number' => 5]); foreach ($terms as $term): ?>
                        <li><a href="<?php echo get_term_link($term); ?>" class="text-sm block py-1"><?php echo $term->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <!-- Add more tax dropdowns or series -->
        </ul>
    </nav>
</div>

# Implementation Notes
- JS: main.js: const menu = document.getElementById('mobile-nav'); function toggleMobileMenu() { menu.classList.toggle('translate-x-full'); menu.classList.toggle('hidden'); } document.querySelectorAll('.dropdown a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); a.nextElementSibling.classList.toggle('hidden'); })); Close on outside click.
- Gotchas: For 300+ terms, limit to 5 + "View All". Ties to primary menu if dynamic.
- Perf: No query in partial—preload terms in functions.php if needed.