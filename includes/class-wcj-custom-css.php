<?php
/**
 * WooCommerce Jetpack Custom CSS
 *
 * The WooCommerce Jetpack Custom CSS class.
 *
 * @version 2.6.1
 * @since   2.6.1
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Custom_CSS' ) ) :

class WCJ_Custom_CSS extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.6.1
	 * @since   2.6.1
	 * @todo    automatically enable the module if v <= 2.6.0 and General module enabled and `wcj_general_custom_css` or `wcj_general_custom_admin_css` are not empty
	 * @todo    (maybe) set `add_action` `priority` to `PHP_INT_MAX`
	 */
	function __construct() {

		$this->id         = 'custom_css';
		$this->short_desc = __( 'Custom CSS', 'woocommerce-jetpack' );
		$this->desc       = __( 'Another custom CSS, if you need one.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-booster-custom-css/';
		parent::__construct();

		add_action( 'init', array( $this, 'add_settings_hook' ) );

		if ( $this->is_enabled() ) {
			if ( '' != get_option( 'wcj_general_custom_css', '' ) ) {
				add_action( 'wp_head', array( $this, 'hook_custom_css' ) );
			}
			if ( '' != get_option( 'wcj_general_custom_admin_css', '' ) ) {
				add_action( 'admin_head', array( $this, 'hook_custom_admin_css' ) );
			}
		}
	}

	/**
	 * hook_custom_css.
	 */
	public function hook_custom_css() {
		$output = '<style>' . get_option( 'wcj_general_custom_css', '' ) . '</style>';
		echo $output;
	}

	/**
	 * hook_custom_admin_css.
	 */
	public function hook_custom_admin_css() {
		$output = '<style>' . get_option( 'wcj_general_custom_admin_css', '' ) . '</style>';
		echo $output;
	}

	/**
	 * get_settings.
	 *
	 * @version 2.6.1
	 * @since   2.6.1
	 */
	function get_settings() {
		return $this->add_standard_settings( apply_filters( 'wcj_custom_css_settings', array() ) );
	}

	/*
	 * add_settings_hook.
	 *
	 * @version 2.6.1
	 * @since   2.6.1
	 */
	function add_settings_hook() {
		add_filter( 'wcj_custom_css_settings', array( $this, 'add_settings' ) );
	}

	/*
	 * add_settings.
	 *
	 * @version 2.6.1
	 * @since   2.6.1
	 */
	function add_settings() {
		return array(
			array(
				'title'    => __( 'Options', 'woocommerce-jetpack' ),
				'type'     => 'title',
				'id'       => 'wcj_custom_css_options',
			),
			array(
				'title'    => __( 'Custom CSS - Front end (Customers)', 'woocommerce-jetpack' ),
				'id'       => 'wcj_general_custom_css',
				'default'  => '',
				'type'     => 'custom_textarea',
				'css'      => 'width:66%;min-width:300px;min-height:300px;',
			),
			array(
				'title'    => __( 'Custom CSS - Back end (Admin)', 'woocommerce-jetpack' ),
				'id'       => 'wcj_general_custom_admin_css',
				'default'  => '',
				'type'     => 'custom_textarea',
				'css'      => 'width:66%;min-width:300px;min-height:300px;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'wcj_custom_css_options',
			),
		);
	}

}

endif;

return new WCJ_Custom_CSS();