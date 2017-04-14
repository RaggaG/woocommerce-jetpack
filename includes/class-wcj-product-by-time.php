<?php
/**
 * WooCommerce Jetpack Product by Time
 *
 * The WooCommerce Jetpack Product by Time class.
 *
 * @version 2.7.2
 * @since   2.7.2
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Product_By_Time' ) ) :

class WCJ_Product_By_Time extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.7.2
	 * @since   2.7.2
	 * @todo    by date
	 * @todo    redirect to custom URL when product is not available
	 */
	function __construct() {

		$this->id         = 'product_by_time';
		$this->short_desc = __( 'Product by Time', 'woocommerce-jetpack' );
		$this->desc       = __( 'WooCommerce product availability by time.', 'woocommerce-jetpack' );
		$this->link       = 'http://booster.io/features/woocommerce-product-by-time/';
		parent::__construct();

		$this->time_now = current_time( 'timestamp' );

		if ( $this->is_enabled() ) {
			if ( 'yes' === get_option( 'wcj_product_by_time_section_enabled', 'no' ) ) {
				$this->day_of_week_now = intval( date( 'N', $this->time_now ) ) - 1;
				$this->hours_now       = intval( date( 'H', $this->time_now ) );
				$this->minutes_now     = intval( date( 'i', $this->time_now ) );
				add_filter( 'woocommerce_is_purchasable',         array( $this, 'check_is_purchasable_by_time' ),          PHP_INT_MAX, 2 );
				add_action( 'woocommerce_single_product_summary', array( $this, 'maybe_add_unavailable_by_time_message' ), 30 );
			}
		}
	}

	/**
	 * maybe_add_unavailable_by_time_message.
	 *
	 * @version 2.7.2
	 * @version 2.7.2
	 */
	function maybe_add_unavailable_by_time_message() {
		if ( ! $this->check_is_purchasable_by_time( true, null ) ) {
			$purchasable_by_time = explode( PHP_EOL, get_option( 'wcj_product_by_time', '' ) );
			$_time = '';
			if ( isset( $purchasable_by_time[ $this->day_of_week_now ] ) ) {
				$_time = str_replace( array( "\n", "\r", ' ' ), array( '', '', '' ), $purchasable_by_time[ $this->day_of_week_now ] );
			}
			echo str_replace(
				'%time_today%',
				$_time,
				do_shortcode( get_option( 'wcj_product_by_time_unavailable_message', __( '<p style="color:red;">Today the product is available only at %time_today%.</p>', 'woocommerce-jetpack' ) ) )
			);
		}
	}

	/**
	 * check_is_purchasable_by_time.
	 *
	 * @version 2.7.2
	 * @version 2.7.2
	 */
	function check_is_purchasable_by_time( $purchasable, $_product ) {
		if ( $purchasable ) {
			$purchasable_by_time = explode( PHP_EOL, get_option( 'wcj_product_by_time', '' ) );
			if ( isset( $purchasable_by_time[ $this->day_of_week_now ] ) ) {
				$_time = str_replace( array( "\n", "\r", ' ' ), array( '', '', '' ), $purchasable_by_time[ $this->day_of_week_now ] );
				if ( '-' === $_time ) {
					return false;
				}
				$_time = explode( '-', $_time );
				if ( isset( $_time[0] ) ) {
					$time_from = explode( ':', $_time[0] );
					if ( isset( $time_from[0] ) && $this->hours_now < $time_from[0] ) {
						return false;
					}
					if ( isset( $time_from[1] ) && $time_from[0] == $this->hours_now && $this->minutes_now < $time_from[1] ) {
						return false;
					}
				}
				if ( isset( $_time[1] ) ) {
					$time_to = explode( ':', $_time[1] );
					if ( isset( $time_to[0] ) && $this->hours_now > $time_to[0] ) {
						return false;
					}
					if ( isset( $time_to[1] ) && $time_to[0] == $this->hours_now && $this->minutes_now >= $time_to[1] ) {
						return false;
					}
				}
			}
		}
		return $purchasable;
	}

	/**
	 * get_settings.
	 *
	 * @version 2.7.2
	 * @version 2.7.2
	 */
	function get_settings() {
		$settings = array(
			array(
				'title'    => __( 'Options', 'woocommerce-jetpack' ),
				'desc'     => '<span id="local-time">' . sprintf( __( 'Local time is <code>%s</code>.', 'woocommerce-jetpack' ), date( 'l, H:i:s', $this->time_now ) ) . '</span>',
				'type'     => 'title',
				'id'       => 'wcj_product_by_time_options',
			),
			array(
				'title'    => __( 'Product by Time', 'woocommerce-jetpack' ),
				'desc'     => __( 'Enable Section', 'woocommerce-jetpack' ),
				'id'       => 'wcj_product_by_time_section_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'     => __( 'Time Table', 'woocommerce-jetpack' ),
				'id'       => 'wcj_product_by_time',
				'default'  =>
					'8:00-20:00' . PHP_EOL .
					'8:00-20:00' . PHP_EOL .
					'8:00-20:00' . PHP_EOL .
					'8:00-20:00' . PHP_EOL .
					'8:00-18:00' . PHP_EOL .
					'-'          . PHP_EOL .
					'-'          . PHP_EOL,
				'type'     => 'textarea',
				'css'      => 'height:200px;',
			),
			array(
				'title'    => __( 'Message', 'woocommerce-jetpack' ),
				'desc'     => __( 'Message when product is not available by time.', 'woocommerce-jetpack' ),
				'id'       => 'wcj_product_by_time_unavailable_message',
				'default'  => __( '<p style="color:red;">Today the product is available only at %time_today%.</p>', 'woocommerce-jetpack' ),
				'type'     => 'custom_textarea',
				'css'      => 'width:66%;min-width:300px;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'wcj_product_by_time_options',
			),
		);
		return $this->add_standard_settings( $settings );
	}

}

endif;

return new WCJ_Product_By_Time();
