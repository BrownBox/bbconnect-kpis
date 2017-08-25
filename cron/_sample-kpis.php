<?php
/**
 * Sample cron script which updates various custom KPI fields for users
 * The following variables used here are defined in the core cron.php script:
 *    $users array A list of all users for the current site
 *    $today DateTime Object for the current date
 *    $yesterday DateTime Object for the previous day
 *    $kpi_prefix String Prefix for KPI fields
 */

$current_financial_year = bbconnect_kpi_calculate_fiscal_year_for_date($today->format("Y-m-d"));

$user_count = 0;
foreach ($users as $user) {
    set_time_limit(3600);

    echo '    '.$user->display_name."\n";
    if (strtotime($user->user_registered) >= strtotime($today->format('Y-m-d'))) { // Skip if user created after date we're doing calculations for
        continue;
    }

    $user_meta = array(
            // First/last donation date
            $kpi_prefix.'first_donation_date' => null,
            $kpi_prefix.'last_donation_date' => null,
            $kpi_prefix.'last_donation_amount' => null,

            // Number of donations so far this month
            $kpi_prefix.'donation_count_month_to_date' => 0,
            // Total donation amount so far this month
            $kpi_prefix.'donation_amount_month_to_date' => 0,
            // Average donation amount so far this month
            $kpi_prefix.'donation_average_month_to_date' => 0,

            // Number of donations last month
            $kpi_prefix.'donation_count_month_1' => 0,
            // Total donation amount last month
            $kpi_prefix.'donation_amount_month_1' => 0,
            // Average donation amount last month
            $kpi_prefix.'donation_average_month_1' => 0,

            // Number of donations month before last
            $kpi_prefix.'donation_count_month_2' => 0,
            // Total donation amount month before last
            $kpi_prefix.'donation_amount_month_2' => 0,
            // Average donation amount month before last
            $kpi_prefix.'donation_average_month_2' => 0,

            // Donation amount last 24 hours
            $kpi_prefix.'donation_amount_last_24h' => 0,
            // Donation count last 24 hours
            $kpi_prefix.'donation_count_last_24h' => 0,
            // Donation average last 24 hours
            $kpi_prefix.'donation_average_last_24h' => 0,

            // Donation amount last 7 days
            $kpi_prefix.'donation_amount_last_7d' => 0,
            // Donation count last 7 days
            $kpi_prefix.'donation_count_last_7d' => 0,
            // Donation average last 7 days
            $kpi_prefix.'donation_average_last_7d' => 0,

            // Donation Count last 5 yrs rolling
            $kpi_prefix.'donation_count_5y_rolling' => 0,
            // Donation Count last 5 FYs
            $kpi_prefix.'donation_count_5fy' => 0,
            // Donation Count Lifetime
            $kpi_prefix.'donation_count_lifetime' => 0,

            // Donation Amount last 5 yrs rolling
            $kpi_prefix.'donation_amount_5y_rolling' => 0,
            // Donation Amount last 5 FYs
            $kpi_prefix.'donation_amount_5fy' => 0,
            // Donation Amount Lifetime
            $kpi_prefix.'donation_amount_lifetime' => 0,

            // Donation Average last 5 yrs rolling
            $kpi_prefix.'donation_average_5y_rolling' => 0,
            // Donation Average last 5 FYs
            $kpi_prefix.'donation_average_5fy' => 0,
            // Donation Average Lifetime
            $kpi_prefix.'donation_average_lifetime' => 0,

            // Donation Count FY-5
            $kpi_prefix.'donation_count_fy_5' => 0,
            // Donation Count FY-4
            $kpi_prefix.'donation_count_fy_4' => 0,
            // Donation Count FY-3
            $kpi_prefix.'donation_count_fy_3' => 0,
            // Donation Count FY-2
            $kpi_prefix.'donation_count_fy_2' => 0,
            // Donation Count FY-1
            $kpi_prefix.'donation_count_fy_1' => 0,
            // Donation Count FY-0
            $kpi_prefix.'donation_count_fy_0' => 0,

            // Donation Count last 24 months
            $kpi_prefix.'donation_count_24m_rolling' => 0,
            // Donation Amount last 24 months
            $kpi_prefix.'donation_amount_24m_rolling' => 0,
            // Donation Average last 24 months
            $kpi_prefix.'donation_average_24m_rolling' => 0,

            // Donation Count last 12 months
            $kpi_prefix.'donation_count_12m_rolling_m_0' => 0,
            // Donation Count same period - 12 months
            $kpi_prefix.'donation_count_12m_rolling_m_12' => 0,
            // Donation Count same period - 24 months
            $kpi_prefix.'donation_count_12m_rolling_m_24' => 0,
            // Donation Count same period - 36 months
            $kpi_prefix.'donation_count_12m_rolling_m_36' => 0,
            // Donation Count same period - 48 months
            $kpi_prefix.'donation_count_12m_rolling_m_48' => 0,

            // Donation Amount FY-5
            $kpi_prefix.'donation_amount_fy_5' => 0,
            // Donation Amount FY-4
            $kpi_prefix.'donation_amount_fy_4' => 0,
            // Donation Amount FY-3
            $kpi_prefix.'donation_amount_fy_3' => 0,
            // Donation Amount FY-2
            $kpi_prefix.'donation_amount_fy_2' => 0,
            // Donation Amount FY-1
            $kpi_prefix.'donation_amount_fy_1' => 0,
            // Donation Amount FY-0
            $kpi_prefix.'donation_amount_fy_0' => 0,

            // Donation Amount last 12 months
            $kpi_prefix.'donation_amount_12m_rolling_m_0' => 0,
            // Donation Amount same period - 12 months
            $kpi_prefix.'donation_amount_12m_rolling_m_12' => 0,
            // Donation Amount same period - 24 months
            $kpi_prefix.'donation_amount_12m_rolling_m_24' => 0,
            // Donation Amount same period - 36 months
            $kpi_prefix.'donation_amount_12m_rolling_m_36' => 0,
            // Donation Amount same period - 48 months
            $kpi_prefix.'donation_amount_12m_rolling_m_48' => 0,

            // Donation Average FY-5
            $kpi_prefix.'donation_average_fy_5' => 0,
            // Donation Average FY-4
            $kpi_prefix.'donation_average_fy_4' => 0,
            // Donation Average FY-3
            $kpi_prefix.'donation_average_fy_3' => 0,
            // Donation Average FY-2
            $kpi_prefix.'donation_average_fy_2' => 0,
            // Donation Average FY-1
            $kpi_prefix.'donation_average_fy_1' => 0,
            // Donation Average FY-0
            $kpi_prefix.'donation_average_fy_0' => 0,

            // Donation Average last 12 months
            $kpi_prefix.'donation_average_12m_rolling_m_0' => 0,
            // Donation Average same period - 12 months
            $kpi_prefix.'donation_average_12m_rolling_m_12' => 0,
            // Donation Average same period - 24 months
            $kpi_prefix.'donation_average_12m_rolling_m_24' => 0,
            // Donation Average same period - 36 months
            $kpi_prefix.'donation_average_12m_rolling_m_36' => 0,
            // Donation Average same period - 48 months
            $kpi_prefix.'donation_average_12m_rolling_m_48' => 0,

            // Is Multi Fin Year Donor
            $kpi_prefix.'is_multi_fin_year' => 'false',
            // Is Multi Gift in Single Fin Year Donor
            $kpi_prefix.'is_multi_in_single_fin_year' => 'false',

            // Max Annual Donation Amount (Last 5 FYs)
            $kpi_prefix.'max_annual_donation_amount_5fy' => 0,
    );

    $args = array(
            'posts_per_page' => -1,
            'post_type'      => 'transaction',
            'status'         => 'publish',
            'author'         => $user->ID,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'date_query'     => array(
                    array(
                            'before' => $today->format("Y-m-d"),
                    ),
            ),
    );
    $transactions = get_posts($args);

    foreach ($transactions as $transactionkey => $transaction) {
        $transaction_metadata = get_post_meta($transaction->ID);
        $amount = isset($transaction_metadata['donation_amount'][0]) ? $transaction_metadata['donation_amount'][0] : 0;
        if ($amount <= 0) {
            continue;
        }
        $date = $transaction->post_date;
        $financial_year = bbconnect_kpi_calculate_fiscal_year_for_date($date);

        $user_meta[$kpi_prefix.'first_donation_date'] = $date;

        // Now check if this is latest donation
        if (empty($user_meta[$kpi_prefix.'last_donation_amount'])) {
            $user_meta[$kpi_prefix.'last_donation_amount'] = $amount;
        }
        if (empty($user_meta[$kpi_prefix.'last_donation_date'])) {
            $user_meta[$kpi_prefix.'last_donation_date'] = $date;
        }

        $user_meta[$kpi_prefix.'donation_count_lifetime']++;
        $user_meta[$kpi_prefix.'donation_amount_lifetime'] += $amount;

        // Check if in last 5 years
        if (strtotime($date) >= strtotime('-5 years', strtotime($today->format('Y-m-d')))) {
            $user_meta[$kpi_prefix.'donation_count_5y_rolling']++;
            $user_meta[$kpi_prefix.'donation_amount_5y_rolling'] += $amount;

            // Check if transaction was in the past week
            if (strtotime($date) >= strtotime('-7 days', strtotime($today->format('Y-m-d')))) {
                $user_meta[$kpi_prefix.'donation_count_last_7d']++;
                $user_meta[$kpi_prefix.'donation_amount_last_7d'] += $amount;
                if (strtotime($date) >= strtotime('-1 day', strtotime($today->format('Y-m-d')))) {
                    $user_meta[$kpi_prefix.'donation_count_last_24h']++;
                    $user_meta[$kpi_prefix.'donation_amount_last_24h'] += $amount;
                }
            }

            // We have to calculate previous month dates manually as strtotime does funky things with '-1 month' depending on the number of days in the month
            $month_1_year = $month_2_year = $yesterday->format('Y');
            $month_1 = $yesterday->format('n')-1;
            $month_2 = $month_1-1;
            if ($month_1 <= 0) {
                $month_1 += 12;
                $month_1_year--;
            }
            if (strlen($month_1) == 1) {
                $month_1 = '0'.$month_1;
            }
            if ($month_2 <= 0) {
                $month_2 += 12;
                $month_2_year--;
            }
            if (strlen($month_2) == 1) {
                $month_2 = '0'.$month_2;
            }
            if (strtotime($yesterday->format('Y-m-01')) <= strtotime($date)) { // Check if transaction was in the current month
                $user_meta[$kpi_prefix.'donation_count_month_to_date']++;
                $user_meta[$kpi_prefix.'donation_amount_month_to_date'] += $amount;
            } elseif ($month_1_year.'-'.$month_1 == date('Y-m', strtotime($date))) { // Check if in last month
                $user_meta[$kpi_prefix.'donation_count_month_1']++;
                $user_meta[$kpi_prefix.'donation_amount_month_1'] += $amount;
            } elseif ($month_2_year.'-'.$month_2 == date('Y-m', strtotime($date))) { // Check if in month before last
                $user_meta[$kpi_prefix.'donation_count_month_2']++;
                $user_meta[$kpi_prefix.'donation_amount_month_2'] += $amount;
            }

            // Match the financial year
            if ($financial_year == $current_financial_year) {
                $user_meta[$kpi_prefix.'donation_count_fy_0']++;
                $user_meta[$kpi_prefix.'donation_amount_fy_0'] += $amount;
            } elseif ($financial_year == $current_financial_year-1) {
                $user_meta[$kpi_prefix.'donation_count_fy_1']++;
                $user_meta[$kpi_prefix.'donation_amount_fy_1'] += $amount;
            } elseif ($financial_year == $current_financial_year-2) {
                $user_meta[$kpi_prefix.'donation_count_fy_2']++;
                $user_meta[$kpi_prefix.'donation_amount_fy_2'] += $amount;
            } elseif ($financial_year == $current_financial_year-3) {
                $user_meta[$kpi_prefix.'donation_count_fy_3']++;
                $user_meta[$kpi_prefix.'donation_amount_fy_3'] += $amount;
            } elseif ($financial_year == $current_financial_year-4) {
                $user_meta[$kpi_prefix.'donation_count_fy_4']++;
                $user_meta[$kpi_prefix.'donation_amount_fy_4'] += $amount;
            }

            // Match the rolling 12-month period
            if (strtotime('-1 year', strtotime($today->format('Y-m-d'))) <= strtotime($date)) {
                $user_meta[$kpi_prefix.'donation_count_12m_rolling_m_0']++;
                $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_0'] += $amount;
                $user_meta[$kpi_prefix.'donation_count_24m_rolling']++;
                $user_meta[$kpi_prefix.'donation_amount_24m_rolling'] += $amount;
            } elseif (strtotime('-2 years', strtotime($today->format('Y-m-d'))) <= strtotime($date) && strtotime($date) < strtotime('-1 year', strtotime($today->format('Y-m-d')))) {
                $user_meta[$kpi_prefix.'donation_count_12m_rolling_m_12']++;
                $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_12'] += $amount;
                $user_meta[$kpi_prefix.'donation_count_24m_rolling']++;
                $user_meta[$kpi_prefix.'donation_amount_24m_rolling'] += $amount;
            } elseif (strtotime('-3 years', strtotime($today->format('Y-m-d'))) <= strtotime($date) && strtotime($date) < strtotime('-2 years', strtotime($today->format('Y-m-d')))) {
                $user_meta[$kpi_prefix.'donation_count_12m_rolling_m_24']++;
                $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_24'] += $amount;
            } elseif (strtotime('-4 years', strtotime($today->format('Y-m-d'))) <= strtotime($date) && strtotime($date) < strtotime('-3 years', strtotime($today->format('Y-m-d')))) {
                $user_meta[$kpi_prefix.'donation_count_12m_rolling_m_36']++;
                $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_36'] += $amount;
            } elseif (strtotime('-5 years', strtotime($today->format('Y-m-d'))) <= strtotime($date) && strtotime($date) < strtotime('-4 years', strtotime($today->format('Y-m-d')))) {
                $user_meta[$kpi_prefix.'donation_count_12m_rolling_m_48']++;
                $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_48'] += $amount;
            }
        }

        // Have to check FY-5 outside the 5 year check as it extends further back than that
        if ($financial_year == $current_financial_year-5) {
            $user_meta[$kpi_prefix.'donation_count_fy_5']++;
            $user_meta[$kpi_prefix.'donation_amount_fy_5'] += $amount;
        }
    }
    unset($transactions);

    // Total for last 5 FY
    $user_meta[$kpi_prefix.'donation_count_5fy'] = $user_meta[$kpi_prefix.'donation_count_fy_0']+$user_meta[$kpi_prefix.'donation_count_fy_1']+$user_meta[$kpi_prefix.'donation_count_fy_2']+$user_meta[$kpi_prefix.'donation_count_fy_3']+$user_meta[$kpi_prefix.'donation_count_fy_4'];
    $user_meta[$kpi_prefix.'donation_amount_5fy'] = $user_meta[$kpi_prefix.'donation_amount_fy_0']+$user_meta[$kpi_prefix.'donation_amount_fy_1']+$user_meta[$kpi_prefix.'donation_amount_fy_2']+$user_meta[$kpi_prefix.'donation_amount_fy_3']+$user_meta[$kpi_prefix.'donation_amount_fy_4'];

    // Have they given in multiple financial years?
    $fin_years_given = 0;
    if ($user_meta[$kpi_prefix.'donation_count_fy_0'] > 0) {
        $fin_years_given++;
    }
    if ($user_meta[$kpi_prefix.'donation_count_fy_1'] > 0) {
        $fin_years_given++;
    }
    if ($user_meta[$kpi_prefix.'donation_count_fy_2'] > 0) {
        $fin_years_given++;
    }
    if ($user_meta[$kpi_prefix.'donation_count_fy_3'] > 0) {
        $fin_years_given++;
    }
    if ($user_meta[$kpi_prefix.'donation_count_fy_4'] > 0) {
        $fin_years_given++;
    }
    if ($user_meta[$kpi_prefix.'donation_count_fy_5'] > 0) {
        $fin_years_given++;
    }
    $user_meta[$kpi_prefix.'is_multi_fin_year'] = $fin_years_given > 1 ? 'true' : 'false';

    // Have they given multiple times in a single financial year?
    if ($user_meta[$kpi_prefix.'donation_count_fy_0'] > 1 || $user_meta[$kpi_prefix.'donation_count_fy_1'] > 1 || $user_meta[$kpi_prefix.'donation_count_fy_2'] > 1 || $user_meta[$kpi_prefix.'donation_count_fy_3'] > 1 || $user_meta[$kpi_prefix.'donation_count_fy_4'] > 1 || $user_meta[$kpi_prefix.'donation_count_fy_5'] > 1) {
        $user_meta[$kpi_prefix.'is_multi_in_single_fin_year'] = 'true';
    }

    // Max Annual Donation Amount (Last 5 FYs)
    $user_meta[$kpi_prefix.'max_annual_donation_amount_5fy'] = max(array($user_meta[$kpi_prefix.'donation_amount_fy_0'], $user_meta[$kpi_prefix.'donation_amount_fy_1'], $user_meta[$kpi_prefix.'donation_amount_fy_2'], $user_meta[$kpi_prefix.'donation_amount_fy_3'], $user_meta[$kpi_prefix.'donation_amount_fy_4'], $user_meta[$kpi_prefix.'donation_amount_fy_5']));

    // Averages
    $user_meta[$kpi_prefix.'donation_average_lifetime']          = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_lifetime'], $user_meta[$kpi_prefix.'donation_amount_lifetime']);
    $user_meta[$kpi_prefix.'donation_average_5y_rolling']        = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_5y_rolling'], $user_meta[$kpi_prefix.'donation_amount_5y_rolling']);
    $user_meta[$kpi_prefix.'donation_average_last_24h']          = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_last_24h'], $user_meta[$kpi_prefix.'donation_amount_last_24h']);
    $user_meta[$kpi_prefix.'donation_average_last_7d']           = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_last_7d'], $user_meta[$kpi_prefix.'donation_amount_last_7d']);
    $user_meta[$kpi_prefix.'donation_average_month_to_date']     = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_month_to_date'], $user_meta[$kpi_prefix.'donation_amount_month_to_date']);
    $user_meta[$kpi_prefix.'donation_average_month_1']           = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_month_1'], $user_meta[$kpi_prefix.'donation_amount_month_1']);
    $user_meta[$kpi_prefix.'donation_average_month_2']           = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_month_2'], $user_meta[$kpi_prefix.'donation_amount_month_2']);
    $user_meta[$kpi_prefix.'donation_average_fy_0']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_0'], $user_meta[$kpi_prefix.'donation_amount_fy_0']);
    $user_meta[$kpi_prefix.'donation_average_fy_1']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_1'], $user_meta[$kpi_prefix.'donation_amount_fy_1']);
    $user_meta[$kpi_prefix.'donation_average_fy_2']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_2'], $user_meta[$kpi_prefix.'donation_amount_fy_2']);
    $user_meta[$kpi_prefix.'donation_average_fy_3']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_3'], $user_meta[$kpi_prefix.'donation_amount_fy_3']);
    $user_meta[$kpi_prefix.'donation_average_fy_4']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_4'], $user_meta[$kpi_prefix.'donation_amount_fy_4']);
    $user_meta[$kpi_prefix.'donation_average_fy_5']              = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_fy_5'], $user_meta[$kpi_prefix.'donation_amount_fy_5']);
    $user_meta[$kpi_prefix.'donation_average_24m_rolling']       = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_24m_rolling'], $user_meta[$kpi_prefix.'donation_amount_24m_rolling']);
    $user_meta[$kpi_prefix.'donation_average_12m_rolling_m_0']   = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_12m_rolling_m_0'], $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_0']);
    $user_meta[$kpi_prefix.'donation_average_12m_rolling_m_12']  = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_12m_rolling_m_12'], $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_12']);
    $user_meta[$kpi_prefix.'donation_average_12m_rolling_m_24']  = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_12m_rolling_m_24'], $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_24']);
    $user_meta[$kpi_prefix.'donation_average_12m_rolling_m_36']  = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_12m_rolling_m_36'], $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_36']);
    $user_meta[$kpi_prefix.'donation_average_12m_rolling_m_48']  = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_12m_rolling_m_48'], $user_meta[$kpi_prefix.'donation_amount_12m_rolling_m_48']);
    $user_meta[$kpi_prefix.'donation_average_5fy']               = bbconnect_kpi_calculate_average($user_meta[$kpi_prefix.'donation_count_5fy'], $user_meta[$kpi_prefix.'donation_amount_5fy']);

    // Days since last donation for segment searching
    if (!empty($user_meta[$kpi_prefix.'last_donation_date'])) {
        $date_last_donation = new DateTime($user_meta[$kpi_prefix.'last_donation_date']);
        $days_since_last_donation = $date_last_donation->diff($today, true);
        $user_meta[$kpi_prefix.'days_since_last_donation'] = $days_since_last_donation->days;
        unset($date_last_donation, $days_since_last_donation);
    }

    // Days since created for segment searching
    $date_registered = new DateTime($user->user_registered);
    $days_since_created = $date_registered->diff($today, true);

    $user_meta[$kpi_prefix.'days_since_created'] = $days_since_created->days;
    unset($date_registered, $days_since_created);

    // Now we can update all the relevant meta fields
    $table = _get_meta_table('user');
    $values = array_keys($user_meta);
    $keys = array();
    foreach ($values as $value) {
        $keys[] = '%s';
    }
    $keys = implode(',', $keys);
    $values[] = $user->ID;
    $sql = "DELETE FROM $table WHERE meta_key IN ($keys) AND user_id = %d";
    $wpdb->query($wpdb->prepare($sql, $values));
    foreach ($user_meta as $meta_key => $meta_value) {
        $sql = "INSERT INTO $table (meta_value, meta_key, user_id) VALUES (%s, %s, %d)";
        $wpdb->query($wpdb->prepare($sql, array($meta_value, $meta_key, $user->ID)));
    }

    unset($user_meta);

    $user_count++;
    if ($user_count % 100 == 0) {
        echo $user_count.' users processed'."\n";
    }

    bbconnect_kpi_cron_flush();
}
