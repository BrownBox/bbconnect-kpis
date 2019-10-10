<?php
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
                ),
        ),
);
$segments = get_posts($args);
echo '  '.count($segments).' to process'."\n";
bbconnect_kpi_cron_flush();

//LOOP THROUGH ALL SEARCH CRITERIA OR SEGMENTS
foreach ($segments as $segment) {
    echo '    '.$segment->post_title."\n";

    $args = array(
            'blog_id' => $blog_id,
            'orderby' => 'nicename',
            'meta_query' => array(
                    array(
                            'key' => 'bbconnect_'.$wp_prefix.'segment_id',
                            'value' => $segment->ID,
                    ),
            ),
    );
    $segment_users = get_users($args);
    foreach ($segment_users as $k => $user) {
        if (strtotime($user->user_registered) >= $today->getTimestamp()) { // Skip if user created after date we're doing calculations for
            unset($segment_users[$k]);
        }
    }
    echo '      '.count($segment_users).' users match'."\n";
    bbconnect_kpi_cron_flush();

    $report_manager = new bbconnectKpiReports($segment_users, $segment->ID, $today, $yesterday);
    $rules = $report_manager->get_rules();

    $summary_table_name = $report_manager->get_summary_table_name();
    foreach ($rules as $rule => $label) {
        set_time_limit(3600);
        if (false !== $summary = $report_manager->process_rule($rule)) {
            // Check first if any record for the month exists
            $check_monthly_record_query = "
                SELECT id
                FROM $summary_table_name
                WHERE year = ".$yesterday->format('Y')." AND month = ".$yesterday->format('n')." AND segment_id = ".$segment->ID." AND rule = '$rule' LIMIT 1";
            $summary_id_arr = $wpdb->get_results($check_monthly_record_query);

            // If no record found then create one else update it
            if (empty($summary_id_arr)) {
                $args = array(
                        'year' => $yesterday->format('Y'),
                        'month' => $yesterday->format('n'),
                        'rule' => $rule,
                        'segment_id' => $segment->ID,
                        'segment_name' => $segment->post_title,
                        'current_year' => $summary['year'],
                        'previous_year_1' => $summary['year_1'],
                        'previous_year_2' => $summary['year_2'],
                        'current_month' => $summary['month'],
                        'previous_month_1' => $summary['month_1'],
                        'previous_month_2' => $summary['month_2']
                );

                $wpdb->insert($summary_table_name, $args);
            } else {
                $summary_id = $summary_id_arr[0]->id;

                $wpdb->update($summary_table_name, array(
                        'current_year' => $summary['year'],
                        'previous_year_1' => $summary['year_1'],
                        'previous_year_2' => $summary['year_2'],
                        'current_month' => $summary['month'],
                        'previous_month_1' => $summary['month_1'],
                        'previous_month_2' => $summary['month_2'],
                        'updated_at' => date('Y-m-d h:i:s')
                ), array(
                        'id' => $summary_id
                ), array(
                        '%f',
                        '%f',
                        '%f',
                        '%f',
                        '%f',
                        '%f',
                        '%s'
                ), array(
                        '%d'
                ));
            }
        }
    }
    unset($segment_users, $report_manager, $rules, $summary, $summary_id_arr);
    gc_collect_cycles();
    bbconnect_kpi_cron_flush();
}
