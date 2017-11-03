<?php
/**
 * Plugin Name: Connexions KPIs
 * Plugin URI: http://connexionscrm.com/
 * Description: Stay up to date with the status of each of your contacts and the unique segment they belong to
 * Version: 0.3.3
 * Author: Brown Box
 * Author URI: http://brownbox.net.au
 * License: Proprietary Brown Box
 */
define('BBCONNECT_KPI_VERSION', '0.3.3');
define('BBCONNECT_KPI_DIR', plugin_dir_path(__FILE__));
define('BBCONNECT_KPI_URL', plugin_dir_url(__FILE__));

require_once(BBCONNECT_KPI_DIR.'db.php');
require_once(BBCONNECT_KPI_DIR.'fx.php');
require_once(BBCONNECT_KPI_DIR.'reports/core.php');
require_once(BBCONNECT_KPI_DIR.'reports/output/donor_report.php');
require_once(BBCONNECT_KPI_DIR.'reports/output/segment_report.php');
require_once(BBCONNECT_KPI_DIR.'highcharts/bbconnect-add-dashboard-widget.php');
require_once(BBCONNECT_KPI_DIR.'highcharts/scripts.php');

function bbconnect_kpi_init() {
    if (!defined('BBCONNECT_VER')) {
        add_action('admin_init', 'bbconnect_kpi_deactivate');
        add_action('admin_notices', 'bbconnect_kpi_deactivate_notice');
        return;
    }
    if (is_admin()) {
        // DB updates
        bbconnect_kpi_updates();
        // Plugin updates
        new BbConnectUpdates(__FILE__, 'BrownBox', 'bbconnect-kpis');
        // Make sure our MU plugin is installed. @todo allow for updates of MU plugin
        $mu_file = trailingslashit(WP_CONTENT_DIR).'mu-plugins/bbconnect-kpis-mu.php';
        if (!file_exists($mu_file)) {
            copy(BBCONNECT_KPI_DIR.'bbconnect-kpis-mu.php', $mu_file);
        }
    }
}
add_action('plugins_loaded', 'bbconnect_kpi_init');

function bbconnect_kpi_deactivate() {
    deactivate_plugins(plugin_basename(__FILE__));
}

function bbconnect_kpi_deactivate_notice() {
    echo '<div class="updated"><p><strong>Connections KPIs</strong> has been <strong>deactivated</strong> as it requires Connexions.</p></div>';
    if (isset( $_GET['activate'])) {
        unset( $_GET['activate']);
    }
}
