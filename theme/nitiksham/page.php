<?php /** Generic page. @package Nitiksham */ get_header(); ?>
<main id="main-content" class="inner-page shell"><?php while ( have_posts() ) : the_post(); ?><article <?php post_class(); ?>><header class="inner-page-title"><div class="eyebrow"><span class="eyebrow-line"></span>NITIKSHAM / PAGE</div><h1><?php the_title(); ?></h1></header><div class="entry-content"><?php the_content(); ?></div></article><?php endwhile; ?></main>
<?php get_footer(); ?>
