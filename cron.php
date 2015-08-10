<?php
/**
 * Script to be run nightly as a cron job which updates all the KPI and history data
 */

$start = microtime(true);

require_once(ABSPATH.'wp-load.php');
global $wpdb;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('KPI_VER')) { // Make sure the plugin is enabled
    echo 'Cron was unable to run - the KPI plugin is disabled!'."\n";
} else {
    $dir = opendir(dirname(__FILE__));
    $files = array();
    while (false !== ($filename = readdir($dir))) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }

        if (strpos($filename, '.php') !== false && $filename !== '_sample.php') {
            echo 'Running'.$filename."\n";
            require_once($dir_name.$filename);
            $part_end = microtime(true);
            echo $filename.' complete in '.($part_end-$start)." seconds\n";
        }
    }

    $end = microtime(true);
    $time = $end-$start;

    echo 'All done in '.$time." seconds\n";
}
