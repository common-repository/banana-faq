<?php
/**
 * Option class.
 *
 * @since 0.1.0
 */
class Bfaq_Option {
	const OPTION_KEY = 'bfaq_option';

	private $option;

	public function __construct() {
		$this->load_option();
	}

	public function switch_option( $new_blog, $prev_blog_id ) {
		$this->load_option();
	}

	public function load_option() {
		$this->option = get_option( self::OPTION_KEY, array() );
	}

	public function get_option( $key, $default = false ) {
		$key = trim( $key );
		if ( empty( $key ) ) {
			return $default;
		}

		if ( isset( $this->option[ $key ] ) ) {
			$value = $this->option[ $key ];
		} else {
			$value = $default;
		}
		return $value;
	}

	public function update_option( $key, $value ) {
		$key = trim( $key );
		if ( empty( $key ) ) {
			return false;
		}

		$option = get_option( self::OPTION_KEY, array() );
		$option[ $key ] = $value;
		$did_update = update_option( self::OPTION_KEY, $option, false );

		if ( $did_update ) {
			$this->option[ $key ] = $value;
		}

		return $did_update;
	}

	public function __get( $name ) {
		if ( $name === 'key' ) {
			return self::OPTION_KEY;
		}
	}
}
