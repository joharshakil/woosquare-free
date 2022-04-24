<?php
/*
  Plugin Name: APIExperts Square for WooCommerce
  Plugin URI: https://wpexperts.io/products/woosquare/
  Description: WooSquare purpose is to migrate & synchronize data (sales customers-invoices-products inventory) between Square system point of sale & Woo commerce plug-in. 
  Version: 4.1.1
  Author: Wpexpertsio
  Author URI: https://wpexperts.io/
  License: GPLv2 or later
 */
 
 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( !function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data = get_plugin_data( __FILE__ );

	$WOOSQU_PLUS_PLUGIN_NAME = $plugin_data['Name'];
	if (!defined('WOOSQU_PLUS_PLUGIN_NAME')) define('WOOSQU_PLUS_PLUGIN_NAME',$WOOSQU_PLUS_PLUGIN_NAME);
	
	define( 'WooSquare_VERSION',$plugin_data['Version']);
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION_WOOSQUARE_PLUS', '1.0.0' );
define('WOO_SQUARE_TABLE_DELETED_DATA','woo_square_integration_deleted_data');
define('WOO_SQUARE_TABLE_SYNC_LOGS','woo_square_integration_logs');
define('WOO_SQUARE_PLUGIN_URL_PLUS', plugin_dir_url(__FILE__));
define('WOO_SQUARE_PLUS_PLUGIN_PATH', plugin_dir_path(__FILE__));
//if (!defined('WOO_SQUARE_PLUGIN_URL')) define('WOO_SQUARE_PLUGIN_URL',plugin_dir_url(__FILE__));
//inc freemius
// require_once( plugin_dir_path(__FILE__) . 'includes/square_freemius.php' );


//connection auth credentials

if (!defined('WOOSQU_PLUS_CONNECTURL')) define('WOOSQU_PLUS_CONNECTURL','https://connect.apiexperts.io');

$woocommerce_square_plus_settings = get_option('woocommerce_square_plus_settings');
if(@$woocommerce_square_plus_settings['enable_sandbox'] == 'yes'){
	if (!defined('WOOSQU_PLUS_APPID')) define('WOOSQU_PLUS_APPID',$woocommerce_square_plus_settings['sandbox_application_id']);
} else {
	if (!defined('WOOSQU_PLUS_APPID')) define('WOOSQU_PLUS_APPID','sq0idp-OkzqrnM_vuWKYJUvDnwT-g');
}


if (!defined('WOOSQU_PLUS_APPNAME')) define('WOOSQU_PLUS_APPNAME','API Experts');




	if(!defined('WOO_SQUARE_MAX_SYNC_TIME')){
		//max sync running time
		// numofpro*60
		if (get_option('_transient_timeout_transient_get_products' ) > time()){
			$total_productcount = get_transient( 'transient_get_products');
		} else {
			$args     = array( 	'post_type' => 'product', 
								'posts_per_page' => -1 
			);
			$products = get_posts( $args ); 		
			$total_productcount = count($products);
			set_transient( 'transient_get_products', $total_productcount , 720 );
			
		}
		if($total_productcount > 1){
			define('WOO_SQUARE_MAX_SYNC_TIME', $total_productcount*60 );
		} else {
			define('WOO_SQUARE_MAX_SYNC_TIME', 10*60 );
		}
	}


// define( 'WooSquare_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
// define( 'WooSquare_PLUGIN_URL_PAYMENT', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
$woocommerce_square_plus_settings = get_option('woocommerce_square_plus_settings');
if(@$woocommerce_square_plus_settings['enable_sandbox'] == 'yes'){
	if ( ! defined( 'WC_SQUARE_ENABLE_STAGING' ) ) {
		define( 'WC_SQUARE_ENABLE_STAGING', true );
		define( 'WC_SQUARE_STAGING_URL', 'squareupsandbox' );
	}
} else {
	if ( ! defined( 'WC_SQUARE_ENABLE_STAGING' ) ) {
		define( 'WC_SQUARE_ENABLE_STAGING', false );
		define( 'WC_SQUARE_STAGING_URL', 'squareup' );
	}
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woosquare-plus-activator.php
 */
function activate_woosquare_plus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woosquare-plus-activator.php';
	Woosquare_Plus_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woosquare-plus-deactivator.php
 */
function deactivate_woosquare_plus() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woosquare-plus-deactivator.php';
	Woosquare_Plus_Deactivator::deactivate();
}

add_action( 'plugins_loaded', 'activate_woosquare_plus' );
add_action( 'plugins_loaded', 'deactivate_woosquare_plus' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woosquare-plus.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woosquare_plus() {

	$plugin = new Woosquare_Plus();
	// global $qu_fs;
	// if (qu_fs()->can_use_premium_code()) {
		$plugin->run();
	// }

}

add_action('plugins_loaded', 'run_woosquare_plus', 0);
function woosquare_init() {
	//these key exist in woosquare free so need to migrate in woosquare option..
	$woo_square_access_token_free = get_option('woo_square_access_token_free');
	$woocommerce_square_settings = get_option('woocommerce_square_settings');
	$woo_square_location_id_free = get_option('woo_square_location_id_free');
	$is_moved_from_free = get_option('is_moved_from_free');
	if(!empty($woo_square_access_token_free) and empty($is_moved_from_free)){
		$activate_modules_woosquare_plus = get_option('activate_modules_woosquare_plus');
		$activate_modules_woosquare_plus['items_sync']['module_activate'] = true;
		$activate_modules_woosquare_plus['woosquare_payment']['module_activate'] = true;
		update_option('woo_square_access_token',$woo_square_access_token_free);
		update_option('woo_square_access_token_cauth',$woo_square_access_token_free);
		update_option('woosquare_plus_reauth_notification',$woo_square_access_token_free);
		update_option('woocommerce_square_plus_settings',$woocommerce_square_settings);
		if(!empty($woo_square_location_id_free)){
		    update_option('woo_square_location_id',$woo_square_location_id_free);
	    }
		
		update_option('activate_modules_woosquare_plus', $activate_modules_woosquare_plus);
		update_option('is_moved_from_free',true);
	}

	
}

add_action('init', 'woosquare_init', 0);


