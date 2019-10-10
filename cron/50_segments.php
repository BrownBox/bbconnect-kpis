<?php
/**
 * Sample cron script which updates user segments. Needs to be run after the sample KPI script as the segmentation generally depends on KPI data
 * The following variables used here are defined in the core cron.php script:
 *    $users array A list of all users for the current site
 *    $today DateTime Object for the current date
 */

$args = array(
        'post_type' => 'savedsearch',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'post_title',
        'order' => 'DESC',
        'meta_query' => array(
                array(
                        'key' => 'segment',
                        'value' => 'true'
                )
        )
);
$searches = get_posts($args);

foreach ($searches as $search) {
    set_time_limit(3600);
    echo '    '.$search->post_title."\n";
    bbconnect_kpi_cron_flush();
    $search_array = unserialize($search->post_content);
    $post_data = array(
            'search' => $search_array,
            'users_per_page' => 'All',
            'mod_results' => 'AND',
    );

    $search_results = bbconnect_filter_process($post_data);
    $userids = $search_results['all_search'];
    echo '      '.count($userids).' users match'."\n";
    bbconnect_kpi_cron_flush();
    foreach ($userids as $user_id) {
        $user = new WP_User($user_id);
        if (strtotime($user->user_registered) >= strtotime($today->format('Y-m-d'))) { // Skip if user created after date we're doing calculations for
            continue;
        }
        unset($user);
        update_user_meta($user_id, 'bbconnect_'.$wp_prefix.'segment_id', $search->ID);
    }
    unset($search_results, $userids);
    gc_collect_cycles();
}
unset($searches);
