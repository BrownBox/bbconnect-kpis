<?php
/**
 * Stop plugins from running during KPI cron
 * @param array $plugins
 * @return array
 */
function bbconnect_kpi_exclude_plugins($plugins) {
    if (!defined('BBCONNECT_KPI_CRON') || !BBCONNECT_KPI_CRON) {
        return $plugins;
    }

    foreach ($plugins as $key => $plugin) {
        if (false !== strpos($plugin, 'bbconnect')) { // Allow Connexions and add-ons to run
            continue;
        }
        // Disable everything else
        unset($plugins[$key]);
    }

    return $plugins;
}
add_filter('option_active_plugins', 'bbconnect_kpi_exclude_plugins');
