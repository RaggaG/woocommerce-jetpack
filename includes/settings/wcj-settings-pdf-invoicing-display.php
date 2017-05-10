<?php
/**
 * Booster for WooCommerce - Settings - PDF Invoicing - Display
 *
 * @version 2.8.0
 * @since   2.8.0
 * @author  Algoritmika Ltd.
 * @todo    "edit order" metabox
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$settings = array();
$invoice_types = ( 'yes' === get_option( 'wcj_invoicing_hide_disabled_docs_settings', 'no' ) ) ? wcj_get_enabled_invoice_types() : wcj_get_invoice_types();
foreach ( $invoice_types as $invoice_type ) {

	$settings[] = array(
		'title'        => strtoupper( $invoice_type['desc'] ),
		'type'         => 'title',
		'id'           => 'wcj_invoicing_' . $invoice_type['id'] . '_display_options',
	);

	$settings = array_merge( $settings, array(

		array(
			'title'    => __( 'Admin\'s "Orders" Page', 'woocommerce-jetpack' ),
			'desc'     => __( 'Add Column', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_page_column',
			'default'  => 'yes',
			'type'     => 'checkbox',
		),

		array(
			'title'    => '',
			'desc'     => __( 'Column Title', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_page_column_text',
			'default'  => $invoice_type['title'],
			'type'     => 'text',
		),

		/* array(
			'title'    => '',
			'desc'     => __( 'Create Button', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set empty to disable the button', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_column_create_btn',
			'default'  => __( 'Create', 'woocommerce-jetpack' ),
			'type'     => 'text',
		),

		array(
			'title'    => '',
			'desc'     => __( 'Delete Button', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Set empty to disable the button', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_column_delete_btn',
			'default'  => __( 'Delete', 'woocommerce-jetpack' ),
			'type'     => 'text',
		), */

		array(
			'desc'     => __( 'Add View Button', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_view_btn',
			'default'  => 'no',
			'type'     => 'checkbox',
		),

		array(
			'desc'     => __( 'Add Create Button', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_create_btn',
			'default'  => 'yes',
			'type'     => 'checkbox',
		),

		array(
			'desc'     => __( 'Add Delete Button', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_delete_btn',
			'default'  => 'yes',
			'type'     => 'checkbox',
		),

		array(
			'desc'     => __( 'Create Button Requires Confirmation', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_create_btn_confirm',
			'default'  => 'yes',
			'type'     => 'checkbox',
		),

		array(
			'desc'     => __( 'Delete Button Requires Confirmation', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_admin_orders_delete_btn_confirm',
			'default'  => 'yes',
			'type'     => 'checkbox',
		),

		array(
			'title'    => __( 'Customer\'s "My Account" Page', 'woocommerce-jetpack' ),
			'desc'     => __( 'Add link', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_enabled_for_customers',
			'default'  => 'no',
			'type'     => 'checkbox',
		),

		array(
			'title'    => '',
			'desc'     => __( 'Link Text', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_link_text',
			'default'  => $invoice_type['title'],
			'type'     => 'text',
		),

		array(
			'title'    => __( 'Enable "Save as"', 'woocommerce-jetpack' ),
			'desc'     => __( 'Enable', 'woocommerce-jetpack' ),
			'desc_tip' => __( 'Enable "save as" pdf instead of view pdf in browser', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_save_as_enabled',
			'default'  => 'no',
			'type'     => 'checkbox',
		),

		array(
			'title'    => __( 'PDF File Name', 'woocommerce-jetpack' ),
			'desc'     => __( 'Enter file name for PDF documents. You can use shortcodes here, e.g. [wcj_' . $invoice_type['id'] . '_number]', 'woocommerce-jetpack' ),
			'id'       => 'wcj_invoicing_' . $invoice_type['id'] . '_file_name',
			'default'  => '[wcj_' . $invoice_type['id'] . '_number]',
			'type'     => 'text',
		),
	) );

	$settings[] = array(
		'type'         => 'sectionend',
		'id'           => 'wcj_invoicing_' . $invoice_type['id'] . '_display_options',
	);
}

return $settings;
