<?php
/**
 * WooCommerce Jetpack Exporter Orders
 *
 * The WooCommerce Jetpack Exporter Orders class.
 *
 * @version 2.5.9
 * @since   2.5.9
 * @author  Algoritmika Ltd.
 * @todo    filter export by date
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Exporter_Orders' ) ) :

class WCJ_Exporter_Orders {

	/**
	 * Constructor.
	 *
	 * @version 2.5.9
	 * @since   2.5.9
	 */
	function __construct() {
		return true;
	}

	/**
	 * get_meta_info.
	 *
	 * from woocommerce\includes\admin\meta-boxes\views\html-order-item-meta.php
	 *
	 * @version 2.5.9
	 * @since   2.5.9
	 * @todo    ! it's almost the same function as in class-wcj-order-items-shortcodes.php
	 * @todo    ! (maybe) pass $item instead $the_product
	 */
	function get_meta_info( $item_id, $the_product, $_order, $exclude_wcj_meta = false ) {
		$meta_info = '';
		if ( $metadata = $_order->has_meta( $item_id ) ) {
			$meta_info = array();
			foreach ( $metadata as $meta ) {

				// Skip hidden core fields
				if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
					'_qty',
					'_tax_class',
					'_product_id',
					'_variation_id',
					'_line_subtotal',
					'_line_subtotal_tax',
					'_line_total',
					'_line_tax',
					'method_id',
					'cost'
				) ) ) ) {
					continue;
				}

				if ( $exclude_wcj_meta && ( 'wcj' === substr( $meta['meta_key'], 0, 3 ) || '_wcj' === substr( $meta['meta_key'], 0, 4 ) ) ) {
					continue;
				}

				// Skip serialised meta
				if ( is_serialized( $meta['meta_value'] ) ) {
					continue;
				}

				// Get attribute data
				if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
					$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
					$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
					$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
				} else {
					$meta['meta_key']   = ( is_object( $the_product ) ) ? wc_attribute_label( $meta['meta_key'], $the_product ) : $meta['meta_key'];
				}
				$meta_info[] = wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ': ' . wp_kses_post( rawurldecode( $meta['meta_value'] ) );
			}
			$meta_info = implode( ', ', $meta_info );
		}
		return $meta_info;
	}

	/**
	 * get_export_orders_row.
	 *
	 * @version 2.5.7
	 * @since   2.5.7
	 */
	function get_export_orders_row( $fields_ids, $order_id, $order, $items, $items_product_input_fields, $item, $item_id ) {
		$row = array();
		foreach( $fields_ids as $field_id ) {
			switch ( $field_id ) {
				case 'item-product-input-fields':
					$row[] = wcj_get_product_input_fields( $item );
					break;
				case 'item-debug':
					$row[] = '<pre>' . print_r( $item, true ) . '</pre>';
					break;
				case 'item-name':
					$row[] = $item['name'];
					break;
				case 'item-meta':
					$row[] = $this->get_meta_info( $item_id, $order->get_product_from_item( $item ), $order );
					break;
				case 'item-variation-meta':
					$row[] = ( 0 != $item['variation_id'] ) ? $this->get_meta_info( $item_id, $order->get_product_from_item( $item ), $order, true ) : '';
					break;
				case 'item-qty':
					$row[] = $item['qty'];
					break;
				case 'item-tax-class':
					$row[] = $item['tax_class'];
					break;
				case 'item-product-id':
					$row[] = $item['product_id'];
					break;
				case 'item-variation-id':
					$row[] = $item['variation_id'];
					break;
				case 'item-line-subtotal':
					$row[] = $item['line_subtotal'];
					break;
				case 'item-line-total':
					$row[] = $item['line_total'];
					break;
				case 'item-line-subtotal-tax':
					$row[] = $item['line_subtotal_tax'];
					break;
				case 'item-line-tax':
					$row[] = $item['line_tax'];
					break;
				case 'item-line-total-plus-tax':
					$row[] = $item['line_total'] + $item['line_tax'];
					break;
				case 'item-line-subtotal-plus-tax':
					$row[] = $item['line_subtotal'] + $item['line_subtotal_tax'];
					break;
				case 'order-id':
					$row[] = $order_id;
					break;
				case 'order-number':
					$row[] = $order->get_order_number();
					break;
				case 'order-status':
					$row[] = $order->get_status();
					break;
				case 'order-date':
					$row[] = get_the_date( get_option( 'date_format' ), $order_id );
					break;
				case 'order-time':
					$row[] = get_the_time( get_option( 'time_format' ), $order_id );
					break;
				case 'order-item-count':
					$row[] = $order->get_item_count();
					break;
				case 'order-items':
					$row[] = $items;
					break;
				case 'order-items-product-input-fields':
					$row[] = $items_product_input_fields;
					break;
				case 'order-currency':
					$row[] = $order->get_order_currency();
					break;
				case 'order-total':
					$row[] = $order->get_total();
					break;
				case 'order-total-tax':
					$row[] = $order->get_total_tax();
					break;
				case 'order-payment-method':
					$row[] = $order->payment_method_title;
					break;
				case 'order-notes':
					$row[] = $order->customer_note;
					break;
				case 'billing-first-name':
					$row[] = $order->billing_first_name;
					break;
				case 'billing-last-name':
					$row[] = $order->billing_last_name;
					break;
				case 'billing-company':
					$row[] = $order->billing_company;
					break;
				case 'billing-address-1':
					$row[] = $order->billing_address_1;
					break;
				case 'billing-address-2':
					$row[] = $order->billing_address_2;
					break;
				case 'billing-city':
					$row[] = $order->billing_city;
					break;
				case 'billing-state':
					$row[] = $order->billing_state;
					break;
				case 'billing-postcode':
					$row[] = $order->billing_postcode;
					break;
				case 'billing-country':
					$row[] = $order->billing_country;
					break;
				case 'billing-phone':
					$row[] = $order->billing_phone;
					break;
				case 'billing-email':
					$row[] = $order->billing_email;
					break;
				case 'shipping-first-name':
					$row[] = $order->shipping_first_name;
					break;
				case 'shipping-last-name':
					$row[] = $order->shipping_last_name;
					break;
				case 'shipping-company':
					$row[] = $order->shipping_company;
					break;
				case 'shipping-address-1':
					$row[] = $order->shipping_address_1;
					break;
				case 'shipping-address-2':
					$row[] = $order->shipping_address_2;
					break;
				case 'shipping-city':
					$row[] = $order->shipping_city;
					break;
				case 'shipping-state':
					$row[] = $order->shipping_state;
					break;
				case 'shipping-postcode':
					$row[] = $order->shipping_postcode;
					break;
				case 'shipping-country':
					$row[] = $order->shipping_country;
					break;
			}
		}
		return $row;
	}

	/**
	 * export_orders.
	 *
	 * @version 2.5.9
	 * @since   2.4.8
	 * @todo    (maybe) metainfo as separate column
	 */
	function export_orders( $fields_helper ) {

		// Standard Fields
		$all_fields = $fields_helper->get_order_export_fields();
		$fields_ids = get_option( 'wcj_export_orders_fields', $fields_helper->get_order_export_default_fields_ids() );
		$titles = array();
		foreach( $fields_ids as $field_id ) {
			$titles[] = $all_fields[ $field_id ];
		}

		// Additional Fields
		$total_number = apply_filters( 'wcj_get_option_filter', 1, get_option( 'wcj_export_orders_fields_additional_total_number', 1 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( 'yes' === get_option( 'wcj_export_orders_fields_additional_enabled_' . $i, 'no' ) ) {
				$titles[] = get_option( 'wcj_export_orders_fields_additional_title_' . $i, '' );
			}
		}

		$data = array();
		$data[] = $titles;
		$offset = 0;
		$block_size = 1024;
		while( true ) {
			$args_orders = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'fields'         => 'ids',
			);
			$loop_orders = new WP_Query( $args_orders );
			if ( ! $loop_orders->have_posts() ) break;
			foreach ( $loop_orders->posts as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( isset( $_POST['wcj_filter_by_order_billing_country'] ) && '' != $_POST['wcj_filter_by_order_billing_country'] ) {
					if ( $order->billing_country != $_POST['wcj_filter_by_order_billing_country'] ) {
						continue;
					}
				}

				$filter_by_product_title = true;
				if ( isset( $_POST['wcj_filter_by_product_title'] ) && '' != $_POST['wcj_filter_by_product_title'] ) {
					$filter_by_product_title = false;
				}
				$items = array();
				$items_product_input_fields = array();
				foreach ( $order->get_items() as $item_id => $item ) {
					if ( in_array( 'order-items', $fields_ids ) ) {
						$meta_info = ( 0 != $item['variation_id'] ) ? $this->get_meta_info( $item_id, $order->get_product_from_item( $item ), $order, true ) : '';
						if ( '' != $meta_info ) {
							$meta_info = ' [' . $meta_info . ']';
						}
						$items[] = $item['name'] . $meta_info;
					}
					if ( in_array( 'order-items-product-input-fields', $fields_ids ) ) {
						$item_product_input_fields = wcj_get_product_input_fields( $item );
						if ( '' != $item_product_input_fields ) {
							$items_product_input_fields[] = $item_product_input_fields;
						}
					}
					if ( ! $filter_by_product_title ) {
//						if ( $item['name'] === $_POST['wcj_filter_by_product_title'] ) {
						if ( false !== strpos( $item['name'], $_POST['wcj_filter_by_product_title'] ) ) {
							$filter_by_product_title = true;
						}
					}
				}
				$items = implode( ' / ', $items );
				$items_product_input_fields = implode( ' / ', $items_product_input_fields );
				if ( ! $filter_by_product_title ) {
					continue;
				}

				$row = $this->get_export_orders_row( $fields_ids, $order_id, $order, $items, $items_product_input_fields, null, null );

				// Additional Fields
				$total_number = apply_filters( 'wcj_get_option_filter', 1, get_option( 'wcj_export_orders_fields_additional_total_number', 1 ) );
				for ( $i = 1; $i <= $total_number; $i++ ) {
					if ( 'yes' === get_option( 'wcj_export_orders_fields_additional_enabled_' . $i, 'no' ) ) {
						if ( '' != ( $additional_field_value = get_option( 'wcj_export_orders_fields_additional_value_' . $i, '' ) ) ) {
							if ( 'meta' === get_option( 'wcj_export_orders_fields_additional_type_' . $i, 'meta' ) ) {
								$row[] = get_post_meta( $order_id, $additional_field_value, true );
							} else {
								global $post;
								$post = get_post( $order_id );
								setup_postdata( $post );
								$row[] = do_shortcode( $additional_field_value );
								wp_reset_postdata();
							}
						} else {
							$row[] = '';
						}
					}
				}

				$data[] = $row;
			}
			$offset += $block_size;
		}
		return $data;
	}

	/**
	 * export_orders_items.
	 *
	 * @version 2.5.9
	 * @since   2.5.9
	 * @todo    ! *products* meta and shortcodes in "Additional Fields"
	 */
	function export_orders_items( $fields_helper ) {

		// Standard Fields
		$all_fields = $fields_helper->get_order_items_export_fields();
		$fields_ids = get_option( 'wcj_export_orders_items_fields', $fields_helper->get_order_items_export_default_fields_ids() );
		$titles = array();
		foreach( $fields_ids as $field_id ) {
			$titles[] = $all_fields[ $field_id ];
		}

		// Additional Fields
		$total_number = apply_filters( 'wcj_get_option_filter', 1, get_option( 'wcj_export_orders_items_fields_additional_total_number', 1 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( 'yes' === get_option( 'wcj_export_orders_items_fields_additional_enabled_' . $i, 'no' ) ) {
				$titles[] = get_option( 'wcj_export_orders_items_fields_additional_title_' . $i, '' );
			}
		}

		$data = array();
		$data[] = $titles;
		$offset = 0;
		$block_size = 1024;
		while( true ) {
			$args_orders = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'fields'         => 'ids',
			);
			$loop_orders = new WP_Query( $args_orders );
			if ( ! $loop_orders->have_posts() ) break;
			foreach ( $loop_orders->posts as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( isset( $_POST['wcj_filter_by_order_billing_country'] ) && '' != $_POST['wcj_filter_by_order_billing_country'] ) {
					if ( $order->billing_country != $_POST['wcj_filter_by_order_billing_country'] ) {
						continue;
					}
				}

				foreach ( $order->get_items() as $item_id => $item ) {

					$row = $this->get_export_orders_row( $fields_ids, $order_id, $order, null, null, $item, $item_id );

					// Additional Fields
					$total_number = apply_filters( 'wcj_get_option_filter', 1, get_option( 'wcj_export_orders_items_fields_additional_total_number', 1 ) );
					for ( $i = 1; $i <= $total_number; $i++ ) {
						if ( 'yes' === get_option( 'wcj_export_orders_items_fields_additional_enabled_' . $i, 'no' ) ) {
							if ( '' != ( $additional_field_value = get_option( 'wcj_export_orders_items_fields_additional_value_' . $i, '' ) ) ) {
								if ( 'meta' === get_option( 'wcj_export_orders_items_fields_additional_type_' . $i, 'meta' ) ) {
									$row[] = get_post_meta( $order_id, $additional_field_value, true );
								} else {
									global $post;
									$post = get_post( $order_id );
									setup_postdata( $post );
									$row[] = do_shortcode( $additional_field_value );
									wp_reset_postdata();
								}
							} else {
								$row[] = '';
							}
						}
					}

					$data[] = $row;
				}
			}
			$offset += $block_size;
		}
		return $data;
	}

}

endif;

return new WCJ_Exporter_Orders();