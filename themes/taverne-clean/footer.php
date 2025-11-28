</main>

<footer class="site-footer">
    <div class="site-container">
        <?php if (has_nav_menu('footer')) : ?>
            <nav class="footer-nav" role="navigation" aria-label="Footer Navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => '',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>
        <?php endif; ?>
        
        <div class="footer-info">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
