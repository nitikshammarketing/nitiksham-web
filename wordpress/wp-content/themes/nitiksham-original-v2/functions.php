<?php
/** Original Nitiksham WordPress theme routing; the markup comes from the supplied source. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function nko_routes() { return array( 'about-us', 'contact-us', 'copyright', 'digital-marketing', 'expertise', 'expertise-seo', 'expertise-web-development', 'gallery', 'home', 'login', 'print', 'privacy', 'services', 'social-media-marketing', 'software', 'studio', 'team' ); }
function nko_add_rewrites() {
  foreach ( nko_routes() as $slug ) {
    add_rewrite_rule( '^' . preg_quote($slug, '/') . '/?$', 'index.php?nitiksham_page=' . $slug, 'top' );
  }
}
add_action( 'init', 'nko_add_rewrites' );
add_filter( 'query_vars', function($vars) { $vars[] = 'nitiksham_page'; return $vars; } );
add_filter( 'pre_handle_404', function($preempt,$query) {
  $slug=$query->get('nitiksham_page');
  if ($slug && in_array($slug, nko_routes(),true)) {
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
