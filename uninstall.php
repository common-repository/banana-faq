<?php
/**
 * Uninstall.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function bfaq_uninstall() {
	global $wpdb;

	if ( is_multisite() ) {
		$blogs = wp_get_sites();
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			bfaq_process_uninstall();
			restore_current_blog();
		}
	} else {
		bfaq_process_uninstall();
	}
}

function bfaq_process_uninstall() {
	global $wpdb, $wp_roles;

	/**
	 * Delete caps
	 */
	if ( class_exists( 'WP_Roles' ) ) {
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
	}

	$caps = get_option( 'bfaq_caps', array() );
	if ( $caps && is_object( $wp_roles ) ) {
		foreach ( $wp_roles->roles as $role_name => $role ) {
			foreach ( $caps as $cap ) {
				$wp_roles->remove_cap( $role_name, $cap );
			}
		}
	}
	delete_option( 'bfaq_caps' );

	/**
	 * Delete Custom Posts
	 */
	$args = array(
		'post_type' => 'bfaq_faq',
		'posts_per_page' => -1,
		'post_status' => 'any'
	);
	$posts = get_posts( $args );
	if ( $posts ) {
		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Delete Posts Meta
	 */
	$where = array( 'meta_key' => 'bfaq_faq' );
	$format = array( '%s' );
	$wpdb->delete( $wpdb->postmeta, $where, $format );
}

bfaq_uninstall();
