<?php
/**
 * Booster for WooCommerce - Reports - Product Sales (Daily)
 *
 * @version 2.8.3
 * @since   2.8.3
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Reports_Product_Sales_Daily' ) ) :

class WCJ_Reports_Product_Sales_Daily {

	/**
	 * Constructor.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 */
	function __construct( $args = null ) {
		return true;
	}

	/**
	 * get_report.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 */
	function get_report() {
		$this->get_report_args();
		$this->get_report_data();
		return $this->output_report_data();
	}

	/*
	 * get_report_args.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 */
	function get_report_args() {
		$this->start_date    = isset( $_GET['start_date'] )    ? $_GET['start_date']    : date( 'Y-m-d', strtotime( '-7 days' ) );
		$this->end_date      = isset( $_GET['end_date'] )      ? $_GET['end_date']      : date( 'Y-m-d' );
		$this->product_title = isset( $_GET['product_title'] ) ? $_GET['product_title'] : '';
	}

	/*
	 * get_report_data.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 * @todo    (maybe) currency conversion
	 * @todo    recheck if `wc_get_product_purchase_price()` working correctly for variations
	 */
	function get_report_data() {
		$include_taxes    = ( 'yes' === get_option( 'wcj_reports_products_sales_daily_include_taxes', 'no' ) );
		$count_variations = ( 'yes' === get_option( 'wcj_reports_products_sales_daily_count_variations', 'no' ) );
		$order_statuses   = get_option( 'wcj_reports_products_sales_daily_order_statuses', 'any' );
		if ( 1 == count( $order_statuses ) ) {
			$order_statuses = $order_statuses[0];
		}
		$this->sales_by_day       = array();
		$this->total_sales_by_day = array();
		$this->purchase_data      = array();
		$this->last_sale_data     = array();
		$this->total_orders       = 0;
		$offset     = 0;
		$block_size = 512;
		while( true ) {
			$args_orders = array(
				'post_type'      => 'shop_order',
				'post_status'    => $order_statuses,
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'offset'         => $offset,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'after'     => $this->start_date,
						'before'    => $this->end_date,
						'inclusive' => true,
					),
				),
			);
			$loop_orders = new WP_Query( $args_orders );
			if ( ! $loop_orders->have_posts() ) {
				break;
			}
			foreach ( $loop_orders->posts as $order_id ) {
				$order = wc_get_order( $order_id );
				$items = $order->get_items();
				foreach ( $items as $item ) {
					// Filtering by product title
					if ( '' != $this->product_title && false === stripos( $item['name'], $this->product_title ) ) {
						continue;
					}
					// Preparing data
					$product_id = ( 0 != $item['variation_id'] && $count_variations ) ? $item['variation_id'] : $item['product_id'];
					$order_day_date  = get_the_date( 'Y-m-d', $order_id );
					$sale_line_total = $item['line_total'] + ( $include_taxes ? $item['line_tax'] : 0 );
					// Total sales by day
					if ( ! isset( $this->total_sales_by_day[ $order_day_date ] ) ) {
						$this->total_sales_by_day[  $order_day_date ] = array( 'qty' => 0, 'sum' => 0 );
					}
					$this->total_sales_by_day[  $order_day_date ]['qty'] += $item['qty'];
					$this->total_sales_by_day[  $order_day_date ]['sum'] += $sale_line_total;
					// Sales by day by product
					if ( ! isset( $this->sales_by_day[ $order_day_date ] ) ) {
						$this->sales_by_day[ $order_day_date ] = array();
					}
					if ( ! isset( $this->sales_by_day[ $order_day_date ][ $product_id ] ) ) {
						$this->sales_by_day[ $order_day_date ][ $product_id ] = array( 'qty' => 0, 'sum' => 0 );
					}
					if ( $count_variations ) {
						$this->sales_by_day[ $order_day_date ][ $product_id ]['name'] = $item['name'];
					} else {
						$this->sales_by_day[ $order_day_date ][ $product_id ]['name'][] = $item['name'];
					}
					$this->sales_by_day[ $order_day_date ][ $product_id ]['qty'] += $item['qty'];
					$this->sales_by_day[ $order_day_date ][ $product_id ]['sum'] += $sale_line_total;
					// Purchase data
					if ( ! isset( $this->purchase_data[ $product_id ] ) ) {
						$this->purchase_data[ $product_id ] = wc_get_product_purchase_price( $product_id );
					}
					// Last Sale Time
					if ( ! isset( $this->last_sale_data[ $product_id ] ) ) {
						$this->last_sale_data[ $product_id ] = get_the_time( 'Y-m-d H:i:s', $order_id )
							. ' '  . ' <em><span style="color:gray;">' . sprintf(
								__( 'Order ID: %d (%s)', 'woocommerce-jetpack' ), $order_id, get_post_status( $order_id )
							) . '</span></em>';
					}
				}
				$this->total_orders++;
			}
			$offset += $block_size;
		}
	}

	/*
	 * output_report_data.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 */
	function output_report_data() {
		return $this->output_report_header() . $this->output_report_results();
	}

	/*
	 * output_report_header.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 * @todo    date range as datepicker
	 */
	function output_report_header() {
		// Settings link and dates menu
		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=jetpack&wcj-cat=emails_and_misc&section=reports' ) . '">' .
			'<< ' . __( 'Reports Settings', 'woocommerce-jetpack' ) . '</a>';
		$menu = '';
		$menu .= '<ul class="subsubsub">';
		foreach ( array_merge( wcj_get_reports_standard_ranges(), wcj_get_reports_custom_ranges() ) as $custom_range ) {
			$menu .= '<li><a ' .
				'href="' . add_query_arg( array( 'start_date' => $custom_range['start_date'], 'end_date' => $custom_range['end_date'] ) ) . '" ' .
				'class="' . ( ( $this->start_date == $custom_range['start_date'] && $this->end_date == $custom_range['end_date'] ) ? 'current' : '' ) . '"' .
			'>' . $custom_range['title'] . '</a> | </li>';
		}
		$menu .= '</ul>';
		$menu .= '<br class="clear">';
		// Product and date filter form
		$filter_form = '';
		$filter_form .= '<form method="get" action="">';
		$filter_form .= '<input type="hidden" name="page" value="'       . $_GET['page']     . '" />';
		$filter_form .= '<input type="hidden" name="tab" value="'        . $_GET['tab']      . '" />';
		$filter_form .= '<input type="hidden" name="report" value="'     . $_GET['report']   . '" />';
		$filter_form .= '<label style="font-style:italic;" for="start_date">' . __( 'From:', 'woocommerce-jetpack' ) . '</label>' . ' ' .
			'<input type="text" name="start_date" title="" value="' . $this->start_date . '" />';
		$filter_form .= ' ';
		$filter_form .= '<label style="font-style:italic;" for="end_date">' . __( 'To:', 'woocommerce-jetpack' ) . '</label>' . ' ' .
			'<input type="text" name="end_date" title="" value="' . $this->end_date . '" />';
		$filter_form .= ' ';
		$filter_form .= '<label style="font-style:italic;" for="product_title">' . __( 'Product:', 'woocommerce-jetpack' ) . '</label>' . ' ' .
			'<input type="text" name="product_title" id="product_title" title="" value="' . $this->product_title . '" />';
		$filter_form .= '<input type="submit" value="' . __( 'Filter', 'woocommerce-jetpack' ) . '" />';
		$filter_form .= '</form>';
		// Final result
		return '<p>' . $settings_link . '</p>' . '<p>' . $menu . '</p>' . '<p>' . $filter_form . '</p>';
	}

	/*
	 * output_report_results.
	 *
	 * @version 2.8.3
	 * @since   2.8.3
	 * @todo    option: selectable result columns (profit etc.) // `wcj_reports_products_sales_daily_display_profit`
	 */
	function output_report_results() {
		$table_data = array();
		$table_header = array(
			__( 'Date', 'woocommerce-jetpack' ),
			__( 'Product ID', 'woocommerce-jetpack' ),
			__( 'Title', 'woocommerce-jetpack' ),
			__( 'Quantity', 'woocommerce-jetpack' ),
			__( 'Sum', 'woocommerce-jetpack' ),
		);
		$display_profit = ( 'yes' === get_option( 'wcj_reports_products_sales_daily_display_profit', 'no' ) );
		if ( $display_profit ) {
			$table_header[] = __( 'Profit', 'woocommerce-jetpack' );
		}
		$table_header[] = __( 'Last Sale', 'woocommerce-jetpack' );
		$table_data[] = $table_header;
		foreach ( $this->sales_by_day as $day_date => $day_sales ) {
			foreach ( $day_sales as $product_id => $product_day_sales ) {
				if ( '' != $day_date ) {
					$day_date .= ' <em><span style="color:gray;">' . sprintf(
						__( 'Total: %s (%d)', 'woocommerce-jetpack' ),
						wc_price( $this->total_sales_by_day[ $day_date ]['sum'] ),
						$this->total_sales_by_day[ $day_date ]['qty']
					) . '</span></em>';
				}
				$row = array(
					$day_date,
					$product_id,
					( is_array( $product_day_sales['name'] ) ? implode( ', ', array_unique( $product_day_sales['name'] ) ) : $product_day_sales['name'] ),
					$product_day_sales['qty'],
					wc_price( $product_day_sales['sum'] ),
				);
				$day_date = '';
				if ( $display_profit ) {
					$row[] = wc_price( $product_day_sales['sum'] - $this->purchase_data[ $product_id ] * $product_day_sales['qty'] );
				}
				$row[] = $this->last_sale_data[ $product_id ];
				$table_data[] = $row;
			}
		}
		$result = ( ! empty( $this->sales_by_day ) ) ?
			wcj_get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'none' ) ) .
				'<p><em>' . sprintf( __( 'Total orders: %d', 'woocommerce-jetpack' ), $this->total_orders ) . '</em></p>' :
			'<p><em>' . __( 'No sales data for current period.', 'woocommerce-jetpack' ) . '</em></p>';
		return $result;
	}
}

endif;