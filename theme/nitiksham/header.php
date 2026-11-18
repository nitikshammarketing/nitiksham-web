<?php /** @package Nitiksham */ ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'nitiksham' ); ?></a>
<header class="site-header" id="site-header">
    <div class="shell header-inner">
        <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <?php if ( has_custom_logo() ) : ?>
                <?php $logo_id = get_theme_mod( 'custom_logo' ); $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : false; ?>
                <?php if ( $logo_url ) : ?><span class="brand-custom-logo"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></span><?php endif; ?>
            <?php else : ?>
                <span class="brand-symbol" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="brand-wordmark">NITIKSHAM<span class="brand-dot">.</span></span>
            <?php endif; ?>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="<?php esc_attr_e( 'Toggle navigation', 'nitiksham' ); ?>">
            <span></span><span></span>
        </button>
        <nav id="primary-nav" class="main-navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'nitiksham' ); ?>">
            <?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'menu-items', 'menu_id' => 'primary-menu', 'container' => false, 'fallback_cb' => 'nitiksham_menu_fallback' ) ); ?>
            <a class="nav-cta" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"><?php esc_html_e( 'Let’s talk', 'nitiksham' ); ?> <span aria-hidden="true">↗</span></a>
        </nav>
    </div>
</header>
