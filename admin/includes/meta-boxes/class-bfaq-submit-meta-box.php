<?php
/**
 * Meta box class.
 *
 * @since 0.1.0
 */
class Bfaq_Submit_Meta_Box {

	public function __construct() {}

	/**
	 * Register meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register() {
		add_meta_box(
			'bfaqdiv-submit',
			__( 'Status', BFAQ_TEXT_DOMAIN ),
			array( $this, 'render' ),
			BFAQ_POST_TYPE,
			'side',
			'high',
			array()
		);
	}

	/**
	 * Render meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render( $post, $metabox ) {
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
?>
<div class="submitbox" id="submitpost">
	<div id="major-publishing-actions">
		<div id="delete-action">
			<?php if ( current_user_can( "delete_post", $post->ID ) ) : ?>
				<?php
					if ( ! EMPTY_TRASH_DAYS ) {
						$delete_text = __( 'Delete Permanently' );
					} else {
						$delete_text = __( 'Move to Trash' );
					}
				?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo esc_html( $delete_text ); ?></a>
			<?php endif; ?>
		</div>
		<div id="publishing-action">
			<span class="spinner"></span>
			<?php if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) : ?>
				<?php if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
					<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false ); ?>
				<?php else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
					<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false ); ?>
				<?php endif; ?>
			<?php else : ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
				<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ) ?>" />
			<?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}
}
