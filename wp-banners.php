<?php
/**
 * Plugin Name: WP Banners
 * Description: Create banners to bring traffic to your site.
 * Version: 1.0
 * Author: Asier Moreno
 * License: GPLv2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Plugin Root File.
if ( ! defined( 'WP_BANNERS_PLUGIN_FILE' ) ) {
    define( 'WP_BANNERS_PLUGIN_FILE', __FILE__ );
}

// Plugin Folder Path.
if ( ! defined( 'WP_BANNERS_PLUGIN_DIR' ) ) {
    define( 'WP_BANNERS_PLUGIN_DIR', plugin_dir_path( WP_BANNERS_PLUGIN_FILE ) );
}

// Plugin Folder URL.
if ( ! defined( 'WP_BANNERS_PLUGIN_URL' ) ) {
    define( 'WP_BANNERS_PLUGIN_URL', plugin_dir_url( WP_BANNERS_PLUGIN_FILE ) );
}

if ( ! defined( 'WPB_PLUGIN_VERSION' ) ) {
    define( 'WP_BANNERS_PLUGIN_VERSION', '1.0' );
}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_banners() {
    require_once WP_BANNERS_PLUGIN_DIR . 'includes/class-wp-banners.php';

    if ( is_admin() ) {
        require_once WP_BANNERS_PLUGIN_DIR . 'includes/class-wp-banners-admin.php';
        $plugin = new WP_Banners\WPB_Admin();
    } else {
        require_once WP_BANNERS_PLUGIN_DIR . 'includes/class-wp-banners-public.php';
        $plugin = new WP_Banners\WPB_Public();
    }

    $plugin->run();
}

run_wp_banners();