<?php
/**
 * Plugin Name: Banana FAQ
 * Version: 0.1.0
 * Description: Simple FAQ page management tool.
 * Author: LafCreate
 * Author URI: http://www.lafcreate.com
 * Plugin URI: http://www.lafcreate.com
 * Text Domain: banana-faq
 * Domain Path: /languages
 * @package Banana FAQ
 */
/*
Copyright (C) 2016 LafCreate (email: info at lafcreate.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Prevent directly access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bfaq_header = get_file_data(
	__FILE__,
 	array(
		'version' => 'Version',
		'text_domain' => 'Text Domain',
		'domain_path' => 'Domain Path'
	)
);

define( 'BFAQ_VERSION', $bfaq_header[ 'version' ] );
define( 'BFAQ_TEXT_DOMAIN', $bfaq_header[ 'text_domain' ] );
define( 'BFAQ_LANG_DIR', dirname( plugin_basename( __FILE__ ) ) . $bfaq_header[ 'domain_path' ] );
define( 'BFAQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BFAQ_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'BFAQ_POST_TYPE', 'bfaq_faq' );

unset( $bfaq_header );

require( BFAQ_PLUGIN_DIR . 'includes/class-bfaq-option.php' );

global $bfaq_option;
$bfaq_option = new Bfaq_Option();
$bfaq_option->load_option();

add_action( 'plugins_loaded', 'bfaq_load_textdomain' );
add_action( 'init', 'bfaq_register_post_types' );
add_filter( 'map_meta_cap', 'bfaq_map_meta_cap', 10, 4 );
add_action( 'switch_blog', 'bfaq_switch_option', 10, 2 );
add_action( 'wp_enqueue_scripts', 'bfaq_enqueue_scripts', 10, 2 );
add_shortcode( 'bfaq_faq_tabs', 'bfaq_faq_tabs_shortcode' );

if ( is_admin() ) {
	require( BFAQ_PLUGIN_DIR . 'admin/admin.php' );
}

register_activation_hook( __FILE__, 'bfaq_install' );
register_deactivation_hook( __FILE__, 'bfaq_deactivate' );

function bfaq_load_textdomain() {
	return load_plugin_textdomain( BFAQ_TEXT_DOMAIN, false, BFAQ_LANG_DIR );
}

function bfaq_register_post_types() {
	$labels = array(
		'name' => _x( 'FAQ', 'post type general name', BFAQ_TEXT_DOMAIN ),
		'singular_name' => _x( 'FAQ', 'post type singular name', BFAQ_TEXT_DOMAIN ),
		'add_new' => _x( 'Add New', 'post', BFAQ_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New FAQ', BFAQ_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit FAQ', BFAQ_TEXT_DOMAIN ),
		'new_item' => __( 'New FAQ', BFAQ_TEXT_DOMAIN ),
		'view_item' => __( 'View FAQ', BFAQ_TEXT_DOMAIN ),
		'search_items' => __( 'Search FAQ', BFAQ_TEXT_DOMAIN ),
		'not_found' => __( 'No FAQ found.', BFAQ_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No FAQ found in Trash.', BFAQ_TEXT_DOMAIN ),
		'parent_item_colon' => null,
		'all_items' => __( 'All FAQ', BFAQ_TEXT_DOMAIN ),
		'featured_image' => __( 'Featured Image', BFAQ_TEXT_DOMAIN ),
		'set_featured_image' => __( 'Set featured image', BFAQ_TEXT_DOMAIN ),
		'remove_featured_image' => __( 'Remove featured image', BFAQ_TEXT_DOMAIN ),
		'use_featured_image' => __( 'Use as featured image', BFAQ_TEXT_DOMAIN )
	);
	$args = array(
		'label' => apply_filters( 'bfaq_post_type_label', __( 'FAQ', BFAQ_TEXT_DOMAIN )  ),
		'labels' => $labels,
		'description' => '',
		'public' => false,
		'hierarchical' => false,
		//'exclude_from_search' => null,
		//'publicly_queryable' => null,
		'show_ui' => true,
		//'show_in_nav_menus' => null,
		//'show_in_menu' => false,
		//'show_in_admin_bar' => false,
		//'menu_position' => null,
		'menu_icon' => null,
		'capability_type' => BFAQ_POST_TYPE,
		//'capabilities' => array(),
		'map_meta_cap' => true,
		'supports' => array( 'title' ),
		'register_meta_box_cb' => null,
		'taxonomies' => array(),
		'has_archive' => false,
		'rewrite' => false,
		'query_var' => false,
		'can_export' => false,
		'show_in_rest' => false
	);
	register_post_type( BFAQ_POST_TYPE, $args );
}

function bfaq_switch_option() {
	global $bfaq_option;
	$bfaq_option->load_option();
}

function bfaq_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'bfaq_manage_options' => 'manage_options'
	);
	$meta_caps = apply_filters( 'bfaq_meta_caps', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );
	if ( isset( $meta_caps[ $cap ] ) ) {
		$caps[] = $meta_caps[ $cap ];
	}

	return $caps;
}

function bfaq_install( $network_wide = false ) {

	bfaq_load_textdomain();

	if ( is_multisite() && $network_wide ) {
		$blogs = wp_get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			bfaq_process_install();
			restore_current_blog();
		}
	} else {
		bfaq_process_install();
	}
}

function bfaq_process_install() {
	bfaq_register_post_types();
	flush_rewrite_rules( false );
	bfaq_upgrade();
	bfaq_add_caps();
}

function bfaq_upgrade() {
	global $bfaq_option;

	$current_version = $bfaq_option->get_option( 'version' );
	if ( $current_version === BFAQ_VERSION ) {
		return;
	}

	$bfaq_option->update_option( 'version', BFAQ_VERSION );
}

function bfaq_add_caps() {
	$post_type = get_post_type_object( BFAQ_POST_TYPE );
	if ( $post_type ) {
		$caps = (array) $post_type->cap;
		$roles = array( 'administrator', 'editor' );
		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}
}

function bfaq_deactivate() {
	$caps = array();
	$post_type = get_post_type_object( BFAQ_POST_TYPE );
	if ( $post_type ) {
		$caps = (array) $post_type->cap;
	}
	update_option( 'bfaq_caps', $caps, '', 'no' );
}

function bfaq_faq_tabs_shortcode( $atts, $content = null ) {
	global $post;

	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC'
	);
	$atts = shortcode_atts( $defaults, $atts, 'bfaq_faq_tabs' );

	$html = '';

	$args = array(
		'post_type' => BFAQ_POST_TYPE,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);

	if ( $atts[ 'orderby' ] ) {
		$args[ 'orderby' ] = $atts[ 'orderby' ];
	}
	if ( $atts[ 'order' ] ) {
		$args[ 'order' ] = $atts[ 'order' ];
	}
	$faq_posts = get_posts( $args );

	if ( ! $faq_posts ) {
		return $html;
	}

	/**
	 * Build tabs
	 */
	$html .= '<div class="bfaq-faq-tabs">';

	$html .= '<ul class="bfaq-tabs">';

	foreach ( $faq_posts as $post ) {
		setup_postdata( $post );

		$html .= '<li class="bfaq-tab"><a href="#">' . get_the_title() . '</a></li>';
	}
	wp_reset_postdata();

	$html .= '</ul>';

	/**
	 * Build Q and A
	 */
	foreach ( $faq_posts as $post ) {
		setup_postdata( $post );

		$faqs = get_post_meta( get_the_ID(), 'bfaq_faq', true );
		if ( $faqs ) {

			$html .= '<dl class="bfaq-faqs">';
			foreach ( $faqs as $faq ) {
				$html .= '<dt class="bfaq-q">' . esc_html( $faq[ 'q' ] ) . '</dt>';
				$html .= '<dd class="bfaq-a">' . esc_html( $faq[ 'a' ] ) . '</dd>';
			}
			$html .= '</dl>';

		} else {

			$html .= '<div class="bfaq-faqs bfaq-faqs-not-found">';
			$html .= __( 'No FAQ found.', BFAQ_TEXT_DOMAIN );
			$html .= '</div>';

		}
	}
	wp_reset_postdata();

	$html .= '</div>';

	return $html;
}

function bfaq_enqueue_scripts() {
	wp_register_script(
		'bfaq-script',
		plugins_url( 'js/script.js', __FILE__ ),
		array( 'jquery' ),
		true,
		false
	);
	wp_enqueue_script( 'bfaq-script' );

	wp_register_style(
		'bfaq-style',
		plugins_url( 'css/style.css', __FILE__ ),
		array(),
		false,
		'all'
	);
	wp_enqueue_style( 'bfaq-style' );

	wp_register_style(
		'bfaq-default-theme',
		plugins_url( 'css/default-theme.css', __FILE__ ),
		array(),
		false,
		'all'
	);
	wp_enqueue_style( 'bfaq-default-theme' );
}
