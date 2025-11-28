<?php
/**
 * Header Template
 *
 * Site header with logo/site-title and primary navigation menu. Outputs HTML5 doctype, head tags,
 * and opening body tag. Custom logo set via Customize → Site Identity. Primary menu location.
 * Includes wp_head() hook for enqueueing styles/scripts. Falls back to site name if no logo set.
 * Opens <main> tag - closed in footer.php.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="site-container">
        <div class="header-inner">
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>
                <?php endif; ?>
            </div>
            
            <?php if (has_nav_menu('primary')) : ?>
                <nav class="main-navigation" role="navigation" aria-label="Primary Navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'primary-menu',
                        'fallback_cb'    => false,
                    ));
                    ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="site-main" id="main" role="main">
