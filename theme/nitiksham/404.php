<?php /** 404 template. @package Nitiksham */ get_header(); ?>
<main id="main-content" class="inner-page shell page-not-found"><p class="eyebrow">404 / PAGE NOT FOUND</p><h1><?php esc_html_e( 'This page took a different turn.', 'nitiksham' ); ?></h1><p><?php esc_html_e( 'The page you requested does not exist. Let’s get you back on track.', 'nitiksham' ); ?></p><a class="button button-dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'nitiksham' ); ?> ↗</a></main>
<?php get_footer(); ?>
