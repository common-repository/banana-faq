<?php

require( BFAQ_PLUGIN_DIR . 'admin/includes/meta-boxes/class-bfaq-meta-box.php' );
require( BFAQ_PLUGIN_DIR . 'admin/includes/meta-boxes/class-bfaq-submit-meta-box.php' );

add_action( 'admin_menu', 'bfaq_register_meta_boxes' );
add_action( 'admin_init', 'bfaq_upgrade' );
add_action( 'admin_enqueue_scripts', 'bfaq_admin_enqueue_scripts' );
add_filter( 'default_hidden_meta_boxes', 'bfaq_default_hidden_meta_boxes', 10, 2 );
add_filter( 'post_row_actions','bfaq_remove_quick_edit', 10, 2 );

function bfaq_register_meta_boxes() {
	$faq_meta_box = new BFAQ_Meta_Box();
	$faq_meta_box->register();

	remove_meta_box( 'submitdiv', BFAQ_POST_TYPE, 'side' );
	$submit_meta_box = new BFAQ_Submit_Meta_Box();
	$submit_meta_box->register();
}

function bfaq_admin_enqueue_scripts( $hook_suffix ) {
	$is_post_page = in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) );

	if ( $is_post_page && get_post_type() === BFAQ_POST_TYPE ) {
		wp_register_script(
			'bfaq-touch-punch',
			plugins_url( 'js/jquery.ui.touch-punch.min.js', dirname( __FILE__ ) ),
			array( 'jquery', 'jquery-ui-sortable'),
			true,
			false
		);
		wp_enqueue_script( 'bfaq-touch-punch' );

		wp_register_script(
			'bfaq-admin',
			plugins_url( 'js/admin.js', dirname( __FILE__ ) ),
			array( 'jquery', 'jquery-ui-sortable'),
			true,
			false
		);
		wp_enqueue_script( 'bfaq-admin' );

		$faqs = get_post_meta( get_the_ID(), 'bfaq_faq', true );
		$data = array(
			'labels' => array(
				'question' => __( 'Question', BFAQ_TEXT_DOMAIN ),
				'answer' => __( 'Answer', BFAQ_TEXT_DOMAIN ),
				'remove' => __( 'Remove', BFAQ_TEXT_DOMAIN ),
				'up' => __( 'Up', BFAQ_TEXT_DOMAIN ),
				'down' => __( 'Down', BFAQ_TEXT_DOMAIN )
			),
			'faqs' => (array) $faqs
		);
		wp_localize_script( 'bfaq-admin', 'BFAQ', $data );

		// CSS
		wp_register_style(
			'bfaq-admin',
			plugins_url( 'css/admin.css', dirname( __FILE__ ) ),
			array(),
			false,
			'all'
		);
		wp_enqueue_style( 'bfaq-admin' );
	}
}

function bfaq_default_hidden_meta_boxes( $hidden, $screen ) {
	if ( $screen->post_type === BFAQ_POST_TYPE ) {
		return array();
	}
	return $hidden;
}

function bfaq_remove_quick_edit( $actions ) {
	if ( get_post_type() === BFAQ_POST_TYPE ) {
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
