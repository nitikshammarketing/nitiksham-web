<?php /** Main posts template. @package Nitiksham */ get_header(); ?>
<main id="main-content" class="inner-page shell">
    <header class="inner-page-title"><div class="eyebrow"><span class="eyebrow-line"></span><?php esc_html_e( 'Insights', 'nitiksham' ); ?></div><h1><?php echo esc_html( get_the_archive_title() ?: __( 'Stories & perspectives', 'nitiksham' ) ); ?></h1></header>
    <?php if ( have_posts() ) : ?><div class="posts-grid"><?php while ( have_posts() ) : the_post(); ?><article <?php post_class( 'post-card' ); ?>><a href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); endif; ?><span><?php echo esc_html( get_the_date() ); ?></span><h2><?php the_title(); ?></h2><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p></a></article><?php endwhile; ?></div><?php the_posts_pagination(); ?><?php else : ?><p><?php esc_html_e( 'No posts yet.', 'nitiksham' ); ?></p><?php endif; ?>
</main>
<?php get_footer(); ?>
