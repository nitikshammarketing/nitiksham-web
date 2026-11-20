<?php
/** Original Nitiksham WordPress theme routing; the markup comes from the supplied source. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function nko_routes() { return array( 'about-us', 'contact-us', 'copyright', 'digital-marketing', 'expertise', 'expertise-seo', 'expertise-web-development', 'gallery', 'home', 'login', 'print', 'privacy', 'services', 'social-media-marketing', 'software', 'studio', 'team' ); }
function nko_add_rewrites() {
  foreach ( nko_routes() as $slug ) {
    add_rewrite_rule( '^' . preg_quote($slug, '/') . '/?$', 'index.php?nitiksham_page=' . $slug, 'top' );
  }
  add_rewrite_rule( '^llms\.txt$', 'index.php?nitiksham_llms=1', 'top' );
}
add_action( 'init', 'nko_add_rewrites' );
add_filter( 'query_vars', function($vars) { $vars[] = 'nitiksham_page'; $vars[] = 'nitiksham_llms'; return $vars; } );
add_filter( 'pre_handle_404', function($preempt,$query) {
  $slug=$query->get('nitiksham_page');
  if ($slug && in_array($slug, nko_routes(),true)) {
    $query->is_404=false;
    status_header(200);
    return true;
  }
  if ($query->get('nitiksham_llms')) {
    $query->is_404=false;
    status_header(200);
    return true;
  }
  return $preempt;
},10,2 );
add_filter( 'template_include', function($template) {
  $slug=get_query_var('nitiksham_page');
  if (!$slug || !in_array($slug, nko_routes(), true)) return $template;
  if ($slug==='studio') {
    nocache_headers();
    if (!is_user_logged_in()) { wp_safe_redirect(wp_login_url(home_url('/studio/'))); exit; }
    if (!current_user_can('manage_options')) { status_header(403); wp_die(esc_html__('Administrator access required.', 'nitiksham-original'), '', array('response'=>403)); }
  }
  return get_theme_file_path('pages/' . $slug . '/index.php');
}, 20 );
add_action( 'after_switch_theme', function() { nko_add_rewrites(); flush_rewrite_rules(); } );
add_action( 'after_setup_theme', function() { add_theme_support('post-thumbnails'); } );

/* --- llms.txt (GEO): factual agency summary for AI assistants --- */
function nko_llms_txt() {
  return <<<'LLMS'
# Nitiksham

> Nitiksham is a strategy-led brand, design and digital marketing agency working across India and worldwide.

## About

- Name: Nitiksham. "Niti" means strategy and "Ksham" means capability.
- Location: India — working worldwide.
- Address: Karnal, Haryana – 132001, India.
- Website: https://nitiksham.com/
- Email: contact@nitiksham.com
- Service area: India and worldwide.

## Services

- Brand & Graphic Design: https://nitiksham.com/expertise/
- Website Development: https://nitiksham.com/expertise-web-development/
- SEO (Search Engine Optimisation): https://nitiksham.com/expertise-seo/
- Digital Marketing: https://nitiksham.com/digital-marketing/
- Social Media Marketing: https://nitiksham.com/social-media-marketing/
- Copywriting & Content Writing: https://nitiksham.com/copyright/
- Print Design & Outdoor Advertising: https://nitiksham.com/print/

## Process

1. Discover
2. Plan
3. Build
4. Launch
5. Grow

## Industries served

- Real estate
- E-commerce
- Healthcare
- Education
- B2B & industrial
- FMCG & retail

## Pages

- Home: https://nitiksham.com/
- About Us: https://nitiksham.com/about-us/
- Services: https://nitiksham.com/services/
- Brand Identity & Graphic Design: https://nitiksham.com/expertise/
- Website Development: https://nitiksham.com/expertise-web-development/
- SEO Services: https://nitiksham.com/expertise-seo/
- Digital Marketing: https://nitiksham.com/digital-marketing/
- Social Media Marketing: https://nitiksham.com/social-media-marketing/
- Copywriting & Content Writing: https://nitiksham.com/copyright/
- Print Design & Outdoor Advertising: https://nitiksham.com/print/
- Custom Software Development: https://nitiksham.com/software/
- Team: https://nitiksham.com/team/
- Gallery: https://nitiksham.com/gallery/
- Contact Us: https://nitiksham.com/contact-us/
- Privacy Policy: https://nitiksham.com/privacy/
LLMS;
}
add_action( 'template_redirect', function() {
  if ( ! get_query_var( 'nitiksham_llms' ) ) { return; }
  status_header( 200 );
  header( 'Content-Type: text/plain; charset=utf-8' );
  echo nko_llms_txt();
  exit;
} );

/* --- Sitemap provider for the theme routes (not WP pages, so core sitemaps miss them) --- */
function nko_public_sitemap_paths() {
  return array( '/', '/about-us/', '/services/', '/expertise/', '/expertise-web-development/', '/expertise-seo/', '/digital-marketing/', '/social-media-marketing/', '/copyright/', '/print/', '/software/', '/team/', '/gallery/', '/contact-us/', '/privacy/' );
}
if ( class_exists( 'WP_Sitemaps_Provider' ) ) {
  class Nitiksham_Sitemap_Provider extends WP_Sitemaps_Provider {
    public function __construct() {
      $this->name = 'nitiksham';
      $this->object_type = 'nitiksham_page';
    }
    public function get_object_subtypes() {
      return array( 'nitiksham_page' => (object) array( 'name' => 'nitiksham_page' ) );
    }
    public function get_url_list( $page_num, $object_subtype = '' ) {
      if ( 1 !== (int) $page_num ) { return array(); }
      $urls = array();
      $lastmod = gmdate( 'Y-m-d' );
      foreach ( nko_public_sitemap_paths() as $path ) {
        $urls[] = array( 'loc' => home_url( $path ), 'lastmod' => $lastmod );
      }
      return $urls;
    }
    public function get_max_num_pages( $object_subtype = '' ) {
      return 1;
    }
  }
}
add_action( 'init', function() {
  if ( class_exists( 'Nitiksham_Sitemap_Provider' ) && function_exists( 'wp_sitemaps_add_provider' ) ) {
    wp_sitemaps_add_provider( 'nitiksham', new Nitiksham_Sitemap_Provider() );
  }
} );

/* --- Keep the default Sample Page / Hello World post out of sitemaps and search results --- */
add_filter( 'wp_sitemaps_posts_query_args', function( $args, $post_type ) {
  $exclude = array();
  if ( 'page' === $post_type ) {
    $page = get_page_by_path( 'sample-page', OBJECT, 'page' );
    if ( $page && isset( $page->ID ) ) { $exclude[] = (int) $page->ID; }
  }
  if ( 'post' === $post_type ) {
    $post = get_page_by_path( 'hello-world', OBJECT, 'post' );
    if ( $post && isset( $post->ID ) ) { $exclude[] = (int) $post->ID; }
  }
  if ( ! empty( $exclude ) ) {
    $existing = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
    $args['post__not_in'] = array_merge( $existing, $exclude );
  }
  return $args;
}, 10, 2 );
add_filter( 'wp_robots', function( $robots ) {
  $noindex = false;
  if ( is_page( 'sample-page' ) ) {
    $noindex = true;
  } elseif ( is_singular( 'post' ) ) {
    $post = get_queried_object();
    if ( $post && isset( $post->post_name ) && 'hello-world' === $post->post_name ) {
      $noindex = true;
    }
  }
  if ( $noindex ) {
    unset( $robots['index'], $robots['follow'] );
    $robots['noindex'] = true;
    $robots['nofollow'] = true;
  }
  return $robots;
} );
add_filter( 'wp_sitemaps_providers', function( $providers ) {
  unset( $providers['users'] );
  return $providers;
} );

/* --- Canonical cleanup on theme routes: the per-template canonical tags win --- */
add_action( 'wp', function() {
  if ( get_query_var( 'nitiksham_page' ) ) {
    remove_action( 'wp_head', 'rel_canonical' );
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
    remove_action( 'wp_head', 'adjacent_posts_rel_link', 10 );
  }
} );

/* --- One-time rewrite flush for the new llms.txt rule (theme stays active) --- */
add_action( 'init', function() {
  if ( get_option( 'nitiksham_rewrite_version' ) !== '2' ) {
    nko_add_rewrites();
    flush_rewrite_rules();
    update_option( 'nitiksham_rewrite_version', '2' );
  }
} );
