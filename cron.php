<?php
/**
 * Script to be run hourly as a cron job which updates all the KPI and segment data
 */
define('BBCONNECT_KPI_CRON', true);

// Lockfile to stop cron running over the top of itself
$lockfile = dirname(__FILE__).'/.LOCK';
$logfile = dirname(__FILE__).'/cron.log';
$last_line = trim(`tail -n 1 $logfile`);
if (file_exists($lockfile) && filemtime($lockfile) >= strtotime('-23 hours') && $last_line != 'Lockfile exists. Exiting.') {
    die('Lockfile exists. Exiting.'."\n");
}

$start = microtime(true);
touch($lockfile);

// Multisite requires HTTP_HOST to be defined
$http_host = basename(dirname(dirname(dirname(dirname(__FILE__))))); // Horrible, but as a last resort we'll use the main directory name
if (file_exists(dirname(__FILE__).'/config.php')) {
    include_once(dirname(__FILE__).'/config.php'); // Here is where we can define the actual host name
}
$_SERVER['HTTP_HOST'] = $http_host;

// Error reporting
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

// Load WP
echo 'Loading WP'."\n";
require_once(dirname(__FILE__).'/../../../wp-load.php');

ini_set('memory_limit', apply_filters('bbconnect_kpi_cron_memory_limit', '2056M')); // 2GB should be plenty for most sites

// Force script output to be generated as it goes rather than all at the end
ob_end_flush();
ob_implicit_flush();

global $wpdb, $blog_id;

if (!defined('BBCONNECT_KPI_VERSION')) { // Make sure the plugin is enabled
    echo 'Cron was unable to run - the KPI plugin is disabled!'."\n";
} else {
    $dir_name = BBCONNECT_KPI_DIR.'cron/';
    $dir = opendir($dir_name);
    $files = array();
    while (false !== ($filename = readdir($dir))) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }

        if (strpos($filename, '.php') !== false && strpos($filename, '_sample') === false) {
            $files[] = $dir_name.$filename;
        }
    }
    closedir($dir);
    sort($files);

    // Add support for custom files
    $files = apply_filters('bbconnect_kpi_cron_files', $files);

    if (is_multisite()) {
        // Get all blog ids
        $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE archived = 0 AND spam = 0 AND deleted = 0");
    } else {
        $blogids = array($blog_id);
    }

    foreach ($blogids as $tmp_blog_id) {
        echo 'Processing blog #'.$tmp_blog_id."\n";
        bbconnect_kpi_cron_flush();
        if (is_multisite()) {
            switch_to_blog($tmp_blog_id);
        }

        $tz = new DateTimeZone(bbconnect_get_timezone_string());
        if ($argc > 1) {
            $today = new DateTime($argv[1], $tz);
        } elseif (file_exists(dirname(__FILE__).'/next_date')) {
            $today = new DateTime(file_get_contents(dirname(__FILE__).'/next_date'), $tz);
        } else {
            $today = new DateTime(current_time('Y-m-d'), $tz);
        }

        $last_run = get_option('bbconnect_kpis_last_cron_date', 0);
        $last_run_date = DateTime::createFromFormat('U', $last_run, $tz);
        echo 'Today is '.strtotime($today->format('Y-m-d')).' ('.$today->format('Y-m-d').')'."\n";
        echo 'Last run was '.strtotime($last_run_date->format('Y-m-d')).' ('.$last_run_date->format('Y-m-d').')'."\n";
        if (strtotime($today->format('Y-m-d')) <= strtotime($last_run_date->format('Y-m-d'))) { // Already processed this site today
        	echo 'Nothing to do!'."\n";
        	continue;
        }

        $yesterday = clone $today;
        $yesterday->sub(new DateInterval('P1D'));

        echo 'Calculating data as at '.$today->format('Y-m-d').' ('.$tz->getName().')'."\n";
        bbconnect_kpi_cron_flush();

        $kpi_prefix = 'kpi_';
        $wp_prefix = '';
        if (is_multisite() && $tmp_blog_id != SITE_ID_CURRENT_SITE) {
            $kpi_prefix .= $tmp_blog_id.'_';
            $wp_prefix = $wpdb->get_blog_prefix($tmp_blog_id);
        }
        echo 'KPI prefix is '.$kpi_prefix.' and WP Prefix is '.$wp_prefix."\n";
        bbconnect_kpi_cron_flush();

        $limit = apply_filters('bbconnect_kpi_cron_users_per_page', 5000);
        $offset = 0;
        $pass = 1;
        do {
            $args = array(
                    'blog_id' => $tmp_blog_id,
                    'number' => $limit,
                    'offset' => $offset,
            		'count_total' => false,
            );
            $users = get_users($args);
            echo 'Pass #'.$pass++.': '.count($users).' users found'."\n";
            bbconnect_kpi_cron_flush();

            if (count($users) > 0) {
                foreach ($files as $filename) {
                    if (!file_exists($filename)) {
                        echo 'ERROR: file '.$filename.' not found!'."\n";
                        bbconnect_kpi_cron_flush();
                        continue;
                    }
                    $part_start = microtime(true);
                    echo 'Running '.$filename."\n";
                    bbconnect_kpi_cron_flush();
                    require($filename);
                    $part_end = microtime(true);
                    echo $filename.' complete in '.($part_end-$part_start)." seconds\n";
                    bbconnect_kpi_cron_flush();
                    gc_collect_cycles();
                }
            }

            $offset += $limit;
        } while (count($users) > 0);

        update_option('bbconnect_kpis_last_cron_date', strtotime($today->format('Y-m-d')));
    }

    if (file_exists(dirname(__FILE__).'/next_date')) {
        $today->add(new DateInterval('P1D'));
        file_put_contents(dirname(__FILE__).'/next_date', $today->format('Y-m-d'));
    }

    $end = microtime(true);
    $time = $end-$start;

    echo 'All done in '.$time." seconds\n";
}
unlink($lockfile);
