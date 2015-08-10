<?php
/**
 * Plugin Name: BBConnect KPI Addon
 * Plugin URI: n/a
 * Description: A framework for adding KPI functionality to BB Connect
 * Version: 0.0.1
 * Author: Brown Box
 * Author URI: http://brownbox.net.au
 * License: Proprietary Brown Box
 */
define( 'KPI_VER', '0.0.1' );
define( 'KPI_URL', plugin_dir_url( __FILE__ ) );
define( 'KPI_DIR', plugin_dir_path(__FILE__) );
define( 'KPI_SLUG', plugin_basename( __FILE__ ) );

function bbconnect_kpi_init() {
    if (!defined('BBCONNECT_VER')) {
        add_action('admin_init', 'bbconnect_kpi_deactivate');
        add_action('admin_notices', 'bbconnect_kpi_deactivate_notice');
    }
}
add_action('plugins_loaded', 'bbconnect_kpi_init');

function bbconnect_kpi_deactivate() {
    deactivate_plugins(plugin_basename( __FILE__ ));
}

function bbconnect_kpi_deactivate_notice() {
    echo '<div class="updated"><p><strong>BBConnect KPI Addon</strong> has been <strong>deactivated</strong> as it requires BB Connect.</p></div>';
    if (isset( $_GET['activate']))
        unset( $_GET['activate']);
}
