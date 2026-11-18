<?php
/**
 * Nitiksham Studio theme setup.
 *
 * @package Nitiksham
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nitiksham_theme_setup() {
    load_theme_textdomain( 'nitiksham', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array( 'height' => 96, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'comment-form', 'comment-list' ) );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    register_nav_menus( array( 'primary' => __( 'Primary navigation', 'nitiksham' ), 'footer' => __( 'Footer navigation', 'nitiksham' ) ) );
}
add_action( 'after_setup_theme', 'nitiksham_theme_setup' );

function nitiksham_theme_assets() {
    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'nitiksham-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap', array(), null );
    wp_enqueue_style( 'nitiksham-main', get_template_directory_uri() . '/assets/css/main.css', array( 'nitiksham-fonts' ), $version );
    wp_enqueue_script( 'nitiksham-main', get_template_directory_uri() . '/assets/js/main.js', array(), $version, true );
}
add_action( 'wp_enqueue_scripts', 'nitiksham_theme_assets' );

function nitiksham_menu_fallback() {
    echo '<ul class="menu-items" id="primary-menu">';
    echo '<li><a href="' . esc_url( home_url( '/#services' ) ) . '">' . esc_html__( 'Services', 'nitiksham' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#about' ) ) . '">' . esc_html__( 'About', 'nitiksham' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#approach' ) ) . '">' . esc_html__( 'Our Approach', 'nitiksham' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'nitiksham' ) . '</a></li>';
    echo '</ul>';
}

function nitiksham_customize_register( $customizer ) {
    $customizer->add_section( 'nitiksham_contact', array(
        'title'       => __( 'Nitiksham contact details', 'nitiksham' ),
        'priority'    => 35,
        'description' => __( 'Optional public contact email and phone. Leave empty until verified.', 'nitiksham' ),
    ) );
    $customizer->add_setting( 'nitiksham_email', array( 'default' => '', 'sanitize_callback' => 'sanitize_email' ) );
    $customizer->add_control( 'nitiksham_email', array( 'label' => __( 'Public contact email', 'nitiksham' ), 'section' => 'nitiksham_contact', 'type' => 'email' ) );
    $customizer->add_setting( 'nitiksham_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
    $customizer->add_control( 'nitiksham_phone', array( 'label' => __( 'Public contact phone', 'nitiksham' ), 'section' => 'nitiksham_contact', 'type' => 'text' ) );
}
add_action( 'customize_register', 'nitiksham_customize_register' );

function nitiksham_email_href() {
    $email = get_theme_mod( 'nitiksham_email', '' );
    return is_email( $email ) ? 'mailto:' . sanitize_email( $email ) : home_url( '/#contact' );
}
