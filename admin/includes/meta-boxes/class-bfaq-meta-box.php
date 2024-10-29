<?php
/**
 * Meta box class.
 *
 * @since 0.1.0
 */
class Bfaq_Meta_Box {

	private $field;
	private $action;
	private $nonce_field;

	public function __construct() {
		$this->field = 'bfaq_faq';
		$this->action = 'bfaq_update_faq';
		$this->nonce_field = 'bfaq_nonce_update_faq';
		$this->meta_key = 'bfaq_faq';

		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	/**
	 * Register meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register() {
		add_meta_box(
			'bfaqdiv',
			__( 'Questions', BFAQ_TEXT_DOMAIN ),
			array( $this, 'render' ),
			BFAQ_POST_TYPE,
			'advanced',
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
?>
<div class="wrap">

	<table class="faq-table sortable wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th class="th-order"><?php esc_html_e( 'Order', BFAQ_TEXT_DOMAIN ); ?></th>
				<th class="th-content"><?php esc_html_e( 'Content', BFAQ_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Operation', BFAQ_TEXT_DOMAIN ); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
	<div class="add-row-wrapper">
		<a href="#" class="add-row button button-primary button-large" rel="<?php esc_attr_e( $post->ID ); ?>"><?php esc_html_e( 'Add Question', BFAQ_TEXT_DOMAIN ); ?></a>
	</div>

	<?php wp_nonce_field( $this->action, $this->nonce_field ); ?>
</div>
<?php
	}

	/**
	 * Save post meta data that whether feature post or not.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function save_meta( $post_id ) {
		// Prevent action if new post or trash
		if ( empty( $_POST ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Nonce verification
		if ( ! isset( $_POST[ $this->nonce_field ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ $this->nonce_field ], $this->action ) ) {
			return;
		}

		// User capability verification
		$cap = apply_filters( 'bfaq_save_meta_cap', 'edit_post', $post_id );
		if ( ! current_user_can( $cap, $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ $this->field ] ) ) {

			delete_post_meta( $post_id, $this->meta_key );

		} else {

			$faqs = (array) $_POST[ $this->field ];
			update_post_meta( $post_id, $this->meta_key, $faqs );

		}

	}
}
