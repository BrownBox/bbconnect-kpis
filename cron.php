<?php
/**
 * Script to be run nightly as a cron job which updates all the KPI and history data
 */

$lockfile = dirname(__FILE__).'/.LOCK';
if (file_exists($lockfile) && filemtime($lockfile) >= strtotime('-12 hours')) {
    die('Lockfile exists. Exiting.');
}

$start = microtime(true);
touch($lockfile);

// Multisite requires HTTP_HOST to be defined
$http_host = basename(dirname(dirname(dirname(dirname(__FILE__))))); // Horrible, but as a last resort we'll use the main directory name
if (file_exists(dirname(__FILE__).'/config.php')) {
    include_once(dirname(__FILE__).'/config.php'); // Here is where we can define the actual host name
}
$_SERVER['HTTP_HOST'] = $http_host;

require_once(dirname(__FILE__).'/../../../wp-load.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            $files[] = $filename;
        }
    }
    closedir($dir);

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

        $last_run = get_option('last_cron_date', 0);
        echo 'Last run was '.$last_run.' ('.date('Y-m-d H:i:s', $last_run).'); today is '.strtotime($today->format('Y-m-d')).' ('.$today->format('Y-m-d H:i:s').')'."\n";
        if (strtotime($today->format('Y-m-d')) <= $last_run) { // Already processed this site today
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

        $args = array(
                'blog_id' => $tmp_blog_id,
        );
        $users = get_users($args);
        echo count($users).' users found'."\n";

        foreach ($files as $filename) {
            $part_start = microtime(true);
            echo 'Running '.$filename."\n";
            bbconnect_kpi_cron_flush();
            require_once($dir_name.$filename);
            $part_end = microtime(true);
            echo $filename.' complete in '.($part_end-$part_start)." seconds\n";
            bbconnect_kpi_cron_flush();
            gc_collect_cycles();
        }
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
