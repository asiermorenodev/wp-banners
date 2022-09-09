<?php
namespace WP_Banners;

abstract class WPB_Core {

	public function __construct() {
		$this->define_constants();
	}

	private function define_constants() {
		self::define( 'WP_BANNERS_PLUGIN_NAME', 'WP Banners' );
		self::define( 'WP_BANNERS_PLUGIN_VERSION', '1.0.14' );
	}

	public function run() {
		$this->register_hooks();
	}

	/**
	 * Register the particular hooks required in the child classes.
	 *
	 * @return void
	 */
	public abstract function register_hooks();

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	public static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
}
