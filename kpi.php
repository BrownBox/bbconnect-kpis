<?php
/**
 * Plugin Name: Connexions KPIs
 * Plugin URI: http://connexionscrm.com/
 * Description: Stay up to date with the status of each of your contacts and the unique segment they belong to
 * Version: 0.1
 * Author: Brown Box
 * Author URI: http://brownbox.net.au
 * License: Proprietary Brown Box
 */
define('BBCONNECT_KPI_VERSION', '0.1');
define('BBCONNECT_KPI_DIR', plugin_dir_path(__FILE__));
define('BBCONNECT_KPI_URL', plugin_dir_url(__FILE__));

require_once (BBCONNECT_KPI_DIR.'fx.php');

function bbconnect_kpi_init() {
    if (!defined('BBCONNECT_VER')) {
        add_action('admin_init', 'bbconnect_kpi_deactivate');
        add_action('admin_notices', 'bbconnect_kpi_deactivate_notice');
        return;
    }
    if (is_admin()) {
        new BbConnectUpdates(__FILE__, 'BrownBox', 'bbconnect-kpis');
    }
}
add_action('plugins_loaded', 'bbconnect_kpi_init');

function bbconnect_kpi_deactivate() {
    deactivate_plugins(plugin_basename( __FILE__ ));
}

function bbconnect_kpi_deactivate_notice() {
    echo '<div class="updated"><p><strong>Connections KPIs</strong> has been <strong>deactivated</strong> as it requires Connexions.</p></div>';
    if (isset( $_GET['activate'])) {
        unset( $_GET['activate']);
    }
}
