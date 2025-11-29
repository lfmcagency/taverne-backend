<?php
/**
 * Taverne Clean Theme - template-parts/roles-grid.php
 * 
 * Three-column grid for Artist/Researcher/Teacher cards.
 * The A/R/Tography trifecta—Pol's integrated practice.
 * 
 * @package Taverne_Clean
 * @version 2.0
 */

// =============================================================================
// ROLE DATA
// =============================================================================

// Get role content from options or use defaults
// In production, these would come from user meta or a settings page

$roles = [
    [
        'title'    => 'Artist',
        'slug'     => 'artist',
        'icon'     => 'brush',
        'bio'      => get_option('taverne_role_artist_bio', 
            'The hand that pulls the print, that knows the copper\'s grain and the ink\'s resistance. Making is thinking made visible—each plate a meditation on landscape, memory, and mark.'
        ),
        'quote'    => get_option('taverne_role_artist_quote',
            'I dive into the landscapes that shape us.'
        ),
        'cta_text' => 'Explore Works',
        'cta_url'  => get_post_type_archive_link('plate'),
    ],
    [
        'title'    => 'Researcher',
        'slug'     => 'researcher',
        'icon'     => 'book',
        'bio'      => get_option('taverne_role_researcher_bio',
            'Practice as inquiry, studio as laboratory. A/R/Tography weaves artistic knowing with scholarly rigor—not research about art, but research through making.'
        ),
        'quote'    => get_option('taverne_role_researcher_quote',
            'Research isn\'t desk-bound; it lives in the burr.'
        ),
        'cta_text' => 'Read Insights',
        'cta_url'  => home_url('/researcher'),
    ],
    [
        'title'    => 'Teacher',
        'slug'     => 'teacher',
        'icon'     => 'users',
        'bio'      => get_option('taverne_role_teacher_bio',
            'Teaching as transmission—not just technique, but the patience to listen to materials. The workshop as shared ground where mastery meets curiosity.'
        ),
        'quote'    => get_option('taverne_role_teacher_quote',
            'Every student brings new questions to the press.'
        ),
        'cta_text' => 'View Programs',
        'cta_url'  => home_url('/teacher'),
    ],
];

?>

<div class="roles-grid">
    <?php foreach ($roles as $role) : ?>
        <?php get_template_part('template-parts/content-role-card', null, ['role' => $role]); ?>
    <?php endforeach; ?>
</div>

<style>
/* =============================================================================
   ROLES GRID COMPONENT STYLES
   ============================================================================= */

.roles-grid {
    display: grid;
    gap: var(--space-8);
}

@media (min-width: 768px) {
    .roles-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-12);
    }
}

/* Add decorative borders between columns on desktop */
@media (min-width: 768px) {
    .roles-grid .role-card:not(:last-child) {
        position: relative;
    }
    
    .roles-grid .role-card:not(:last-child)::after {
        content: '';
        position: absolute;
        right: calc(var(--space-6) * -1);
        top: 20%;
        height: 60%;
        width: 1px;
        background: var(--stone);
    }
}
</style>
