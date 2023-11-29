<?php
/**
 * Plugin Name: WooCommerce Swiss VAT Updater 2024
 * Description: This plugin uses the WooCommerce action scheduler to adjust Swiss and Liechtenstein tax rates automatically on January 1st, 2024 to 8.1% and 2.6%
 * Plugin URI: https://www.openstream.ch/erhoehung-mwst-2024
 * Version: 1.0.0
 * Author: Openstream Internet Solutions
 * Author URI: https://www.openstream.ch
 * WC requires at least: 3.0.0
 * WC tested up to: 8.3
 * License: GPL v3 or later
 * License URI: https://github.com/openstream/swiss-vat-updater-for-woocommerce/blob/main/LICENSE
 *
 * @author openstream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'init', 'wc_swiss_vat_schedule_end_event' );
add_action( 'wc_swiss_vat_adjust_taxes', 'wc_swiss_vat_adjust_taxes_callback' );

/**
 * Queue Woo events
 */
function wc_swiss_vat_schedule_end_event(): void {
	$queue = WC()->queue();

	$end_date     = defined( 'WC_SWISS_VAT_ADJUST_DATE' ) ? WC_SWISS_VAT_ADJUST_DATE : '2024-01-01';
	$end_date_obj = new DateTime( $end_date, new DateTimeZone( 'UTC' ) );
	$now          = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

	if ( $now < $end_date_obj ) {
		if ( ! $queue->get_next( 'wc_swiss_vat_adjust_taxes', array(), 'woocommerce-swiss-vat' ) ) {
			$queue->schedule_single( intval( $end_date_obj->getTimestamp() ), 'wc_swiss_vat_adjust_taxes', array(), 'woocommerce-swiss-vat' );
		}
	}
}

function wc_swiss_vat_adjust_taxes_callback(): void {

	wc_swiss_vat_update_tax_rate();

	wc_get_logger()->log( 'info', 'Swiss and Liechtenstein tax rates updated to 8,1% and 2,6% successfully' );

}

function wc_swiss_vat_update_tax_rate(): void {

	global $wpdb;

	$tax_rates     = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates` WHERE `tax_rate_country` IN ( 'CH','LI' )" );

	foreach ( $tax_rates as $id => $tax_rate ) {

		$tax_rate = (array) $tax_rate;

		$tax_rate_id = $tax_rate[ 'tax_rate_id' ];

		$vat_prefix = 'MwSt';

		if( 7.7000 == $tax_rate[ 'tax_rate' ] ) {

			$tax_rate[ 'tax_rate' ] 	 = number_format( (float) '8.1', 4, '.', '' );

			if( str_contains( $tax_rate[ 'tax_rate_name' ], $vat_prefix )) {

				$tax_rate[ 'tax_rate_name' ] = $vat_prefix . ' (8,1%)';

			}
			else {

				$tax_rate[ 'tax_rate_name' ] = '8,1%';

			}

		}

		if( 2.5000 == $tax_rate[ 'tax_rate' ] ) {

			$tax_rate[ 'tax_rate' ] 	 = number_format( (float) '2.6', 4, '.', '' );

			if( str_contains( $tax_rate[ 'tax_rate_name' ], $vat_prefix ) ) {

				$tax_rate[ 'tax_rate_name' ] = $vat_prefix . ' (2,6%)';

			}
			else {

				$tax_rate[ 'tax_rate_name' ] = '2,6%';

			}

		}

		unset( $tax_rate[ 'tax_rate_id' ] );

		WC_Tax::_update_tax_rate( $tax_rate_id, $tax_rate );

	}

}

function wc_swiss_vat_maybe_show_notice(): void {
	$end_date = new DateTime( defined( 'WC_SWISS_VAT_ADJUST_DATE' ) ? WC_SWISS_VAT_ADJUST_DATE : '2024-01-01' );
	$now      = new DateTime();

	if ( $now > $end_date ) {
		$plugin_file           = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
		$deactivate_plugin_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode( $plugin_file ), 'deactivate-plugin_' . $plugin_file );
		?>
		<div id="message" class="notice notice-warning">
			<p>
				<?php printf( 'The Swiss tax increase was scheduled. You might <a href="%s">deactivate</a> this plugin.', $deactivate_plugin_url ); ?>
			</p>
		</div>
		<?php
	}
}

function wc_swiss_vat_clear_schedule(): void {
	if ( $queue = WC()->queue() ) {
		$queue->cancel_all( 'wc_swiss_vat_adjust_taxes', array(), 'woocommerce-swiss-vat' );
	}
}

add_action( 'admin_notices', 'wc_swiss_vat_maybe_show_notice', 20 );
register_activation_hook( __FILE__, 'wc_swiss_vat_clear_schedule' );

