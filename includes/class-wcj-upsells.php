<?php
/**
 * Booster for WooCommerce - Module - Upsells
 *
 * @version 3.5.4
 * @since   3.5.3
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Upsells' ) ) :

class WCJ_Upsells extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 3.5.4
	 * @since   3.5.3
	 * @todo    (maybe) use `apply_filters( 'woocommerce_upsell_display_args', array( 'posts_per_page' => $limit, 'orderby' => $orderby, 'columns' => $columns ) );`
	 * @todo    (maybe) Global Upsells - on per category/tag basis
	 * @todo    (maybe) Global Upsells - ids instead of list
	 */
	function __construct() {

		$this->id         = 'upsells';
		$this->short_desc = __( 'Upsells', 'woocommerce-jetpack' );
		$this->extra_desc = __( 'Upsells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'woocommerce' );
		$this->desc       = __( 'Customize WooCommerce upsells products display.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-upsells';
		parent::__construct();

		if ( $this->is_enabled() ) {
			add_filter( 'woocommerce_upsells_total',      array( $this, 'upsells_total' ),   PHP_INT_MAX );
			add_filter( 'woocommerce_upsells_columns',    array( $this, 'upsells_columns' ), PHP_INT_MAX );
			add_filter( 'woocommerce_upsells_orderby',    array( $this, 'upsells_orderby' ), PHP_INT_MAX );
			if ( 'yes' === get_option( 'wcj_upsells_global_enabled', 'no' ) ) {
				$upsell_ids_filter = ( WCJ_IS_WC_VERSION_BELOW_3 ? 'woocommerce_product_upsell_ids' : 'woocommerce_product_get_upsell_ids' );
				add_filter( $upsell_ids_filter, array( $this, 'upsell_ids' ), PHP_INT_MAX, 2 );
			}
			if ( 'yes' === get_option( 'wcj_upsells_hide', 'no' ) ) {
				add_action( 'init', array( $this, 'hide_upsells' ), PHP_INT_MAX );
			}
		}

	}

	/**
	 * hide_upsells.
	 *
	 * @version 3.5.4
	 * @since   3.5.4
	 */
	function hide_upsells() {
		remove_action( 'woocommerce_after_single_product_summary', 'storefront_upsell_display',  15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	}

	/**
	 * upsell_ids.
	 *
	 * @version 3.5.4
	 * @since   3.5.4
	 */
	function upsell_ids( $ids, $_product ) {
		$global_upsells = get_option( 'wcj_upsells_global_ids', '' );
		if ( ! empty( $global_upsells ) ) {
			$global_upsells = array_unique( $global_upsells );
			$product_id     = wcj_get_product_id_or_variation_parent_id( $_product );
			if ( false !== ( $key = array_search( $product_id, $global_upsells ) ) ) {
				unset( $global_upsells[ $key ] );
			}
		}
		return ( empty( $global_upsells ) ? $ids : array_unique( array_merge( $ids, $global_upsells ) ) );
	}

	/**
	 * upsells_orderby.
	 *
	 * @version 3.5.3
	 * @since   3.5.3
	 * @todo    (maybe) check for `isset( $args['orderby'] )`
	 */
	function upsells_orderby( $orderby ) {
		return ( 'no_changes' != ( $_orderby = get_option( 'wcj_upsells_orderby', 'no_changes' ) ) ? $_orderby : $orderby );
	}

	/**
	 * upsells_columns.
	 *
	 * @version 3.5.3
	 * @since   3.5.3
	 * @todo    (maybe) check for `isset( $args['columns'] )`
	 */
	function upsells_columns( $columns ) {
		return ( 0 != ( $_columns = get_option( 'wcj_upsells_columns', 0 ) ) ? $_columns : $columns );
	}

	/**
	 * upsells_total.
	 *
	 * @version 3.5.4
	 * @since   3.5.3
	 * @todo    (maybe) check for `isset( $args['posts_per_page'] )`
	 */
	function upsells_total( $limit ) {
		return ( 0 != ( $_limit = get_option( 'wcj_upsells_total', 0 ) ) ? $_limit : $limit );
	}

}

endif;

return new WCJ_Upsells();
