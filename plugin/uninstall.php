<?php
// If uninstall is not called from WordPress, exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( \Korobochkin\CurrencyConverter\Models\Settings\General::$option_name );
delete_option( \Korobochkin\CurrencyConverter\Plugin::NAME . '_rates' );

delete_transient( \Korobochkin\CurrencyConverter\Plugin::NAME . '_providers' );

wp_clear_scheduled_hook( \Korobochkin\CurrencyConverter\Plugin::NAME . \Korobochkin\CurrencyConverter\Cron\UpdateCurrency::$action_name );
