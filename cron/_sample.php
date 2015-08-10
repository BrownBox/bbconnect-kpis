<?php
/**
 * Sample cron script which updates various custom KPI fields for users
 */

// Get all users
$today = new DateTime();
$current_financial_year = calculateFiscalYearForDate($today->format("Y-m-d"));

$users = get_users( 'blog_id='.$GLOBALS['blog_id'].'&orderby=nicename' );

foreach ($users as $userkey => $user) {
    echo '    '.$user->display_name."\n";
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

    // First/last donation date
    $kpi_first_donation_date = null;
    $kpi_last_donation_date = null;
    $kpi_last_donation_amount = null;

    // Number of donations so far this month
    $kpi_donation_count_month_to_date = 0;
    // Total donation amount so far this month
    $kpi_donation_amount_month_to_date = 0;
    // Average donation amount so far this month
    $kpi_donation_average_month_to_date = 0;

    // Number of donations last month
    $kpi_donation_count_month_1 = 0;
    // Total donation amount last month
    $kpi_donation_amount_month_1 = 0;
    // Average donation amount last month
    $kpi_donation_average_month_1 = 0;

    // Number of donations month before last
    $kpi_donation_count_month_2 = 0;
    // Total donation amount month before last
    $kpi_donation_amount_month_2 = 0;
    // Average donation amount month before last
    $kpi_donation_average_month_2 = 0;

    // Donation amount last 24 hours
    $kpi_donation_amount_last_24h = 0;
    // Donation count last 24 hours
    $kpi_donation_count_last_24h = 0;
    // Donation average last 24 hours
    $kpi_donation_average_last_24h = 0;

    // Donation amount last 7 days
    $kpi_donation_amount_last_7d = 0;
    // Donation count last 7 days
    $kpi_donation_count_last_7d = 0;
    // Donation average last 7 days
    $kpi_donation_average_last_7d = 0;

    // Donation Count last 5 yrs rolling
    $kpi_donation_count_5y_rolling = 0;
    // Donation Count last 5 FYs
    $kpi_donation_count_5fy = 0;
    // Donation Count Lifetime
    $kpi_donation_count_lifetime = 0;

    // Donation Amount last 5 yrs rolling
    $kpi_donation_amount_5y_rolling = 0;
    // Donation Amount last 5 FYs
    $kpi_donation_amount_5fy = 0;
    // Donation Amount Lifetime
    $kpi_donation_amount_lifetime = 0;

    // Donation Average last 5 yrs rolling
    $kpi_donation_average_5y_rolling = 0;
    // Donation Average last 5 FYs
    $kpi_donation_average_5fy = 0;
    // Donation Average Lifetime
    $kpi_donation_average_lifetime = 0;

    // Donation Count FY-5
    $kpi_donation_count_fy_5 = 0;
    // Donation Count FY-4
    $kpi_donation_count_fy_4 = 0;
    // Donation Count FY-3
    $kpi_donation_count_fy_3 = 0;
    // Donation Count FY-2
    $kpi_donation_count_fy_2 = 0;
    // Donation Count FY-1
    $kpi_donation_count_fy_1 = 0;
    // Donation Count FY-0
    $kpi_donation_count_fy_0 = 0;

    // Donation Count last 24 months
    $kpi_donation_count_24m_rolling = 0;
    // Donation Amount last 24 months
    $kpi_donation_amount_24m_rolling = 0;
    // Donation Average last 24 months
    $kpi_donation_average_24m_rolling = 0;

    // Donation Count last 12 months
    $kpi_donation_count_12m_rolling_m_0 = 0;
    // Donation Count same period - 12 months
    $kpi_donation_count_12m_rolling_m_12 = 0;
    // Donation Count same period - 24 months
    $kpi_donation_count_12m_rolling_m_24 = 0;
    // Donation Count same period - 36 months
    $kpi_donation_count_12m_rolling_m_36 = 0;
    // Donation Count same period - 48 months
    $kpi_donation_count_12m_rolling_m_48 = 0;

    // Donation Amount FY-5
    $kpi_donation_amount_fy_5 = 0;
    // Donation Amount FY-4
    $kpi_donation_amount_fy_4 = 0;
    // Donation Amount FY-3
    $kpi_donation_amount_fy_3 = 0;
    // Donation Amount FY-2
    $kpi_donation_amount_fy_2 = 0;
    // Donation Amount FY-1
    $kpi_donation_amount_fy_1 = 0;
    // Donation Amount FY-0
    $kpi_donation_amount_fy_0 = 0;

    // Donation Amount last 12 months
    $kpi_donation_amount_12m_rolling_m_0 = 0;
    // Donation Amount same period - 12 months
    $kpi_donation_amount_12m_rolling_m_12 = 0;
    // Donation Amount same period - 24 months
    $kpi_donation_amount_12m_rolling_m_24 = 0;
    // Donation Amount same period - 36 months
    $kpi_donation_amount_12m_rolling_m_36 = 0;
    // Donation Amount same period - 48 months
    $kpi_donation_amount_12m_rolling_m_48 = 0;

    // Donation Average FY-5
    $kpi_donation_average_fy_5 = 0;
    // Donation Average FY-4
    $kpi_donation_average_fy_4 = 0;
    // Donation Average FY-3
    $kpi_donation_average_fy_3 = 0;
    // Donation Average FY-2
    $kpi_donation_average_fy_2 = 0;
    // Donation Average FY-1
    $kpi_donation_average_fy_1 = 0;
    // Donation Average FY-0
    $kpi_donation_average_fy_0 = 0;

    // Donation Average last 12 months
    $kpi_donation_average_12m_rolling_m_0 = 0;
    // Donation Average same period - 12 months
    $kpi_donation_average_12m_rolling_m_12 = 0;
    // Donation Average same period - 24 months
    $kpi_donation_average_12m_rolling_m_24 = 0;
    // Donation Average same period - 36 months
    $kpi_donation_average_12m_rolling_m_36 = 0;
    // Donation Average same period - 48 months
    $kpi_donation_average_12m_rolling_m_48 = 0;

    // Is Multi Fin Year Donor
    $kpi_is_multi_fin_year = false;
    // Is Multi Gift in Single Fin Year Donor
    $kpi_is_multi_in_single_fin_year = false;

    // Max Annual Donation Amount (Last 5 FYs)
    $kpi_max_annual_donation_amount_5fy = 0;

    foreach ($transactions as $transactionkey => $transaction) {
        $transaction_metadata = get_post_meta($transaction->ID);
        $amount = isset($transaction_metadata['donation_amount'][0]) ? $transaction_metadata['donation_amount'][0] : 0;
        $date = $transaction->post_date;
        $financial_year = calculateFiscalYearForDate($date);

        $kpi_first_donation_date = $date;

        // Now check if this is latest donation
        if (empty($kpi_last_donation_amount)) {
            $kpi_last_donation_amount = $amount;
        }
        if (empty($kpi_last_donation_date)) {
            $kpi_last_donation_date = $date;
        }

        $kpi_donation_count_lifetime++;
        $kpi_donation_amount_lifetime += $amount;

        // Check if in last 5 years
        if (strtotime($date) >= strtotime('-5 years', $today->getTimestamp())) {
            $kpi_donation_count_5y_rolling++;
            $kpi_donation_amount_5y_rolling += $amount;

            // Check if transaction was in the past week
            if (strtotime($date) >= strtotime('-7 days', $today->getTimestamp())) {
                $kpi_donation_count_last_7d++;
                $kpi_donation_amount_last_7d += $amount;
                if (strtotime($date) >= strtotime('-1 day', $today->getTimestamp())) {
                    $kpi_donation_count_last_24h++;
                    $kpi_donation_amount_last_24h += $amount;
                }
            }

            // Check if transaction was in the current month
            if (strtotime(date('Y-m-01')) <= strtotime($date)){
                $kpi_donation_count_month_to_date++;
                $kpi_donation_amount_month_to_date += $amount;
            } elseif (date('Y-m', strtotime('-1 month')) === date('Y-m', strtotime($date))) { // Check if in last month
                $kpi_donation_count_month_1++;
                $kpi_donation_amount_month_1 += $amount;
            } elseif (date('Y-m', strtotime('-2 months')) == date('Y-m', strtotime($date))) { // Check if in month before last
                $kpi_donation_count_month_2++;
                $kpi_donation_amount_month_2 += $amount;
            }

            // Match the financial year
            if ($financial_year == $current_financial_year) {
                $kpi_donation_count_fy_0++;
                $kpi_donation_amount_fy_0 += $amount;
            } elseif ($financial_year == $current_financial_year-1) {
                $kpi_donation_count_fy_1++;
                $kpi_donation_amount_fy_1 += $amount;
            } elseif ($financial_year == $current_financial_year-2) {
                $kpi_donation_count_fy_2++;
                $kpi_donation_amount_fy_2 += $amount;
            } elseif ($financial_year == $current_financial_year-3) {
                $kpi_donation_count_fy_3++;
                $kpi_donation_amount_fy_3 += $amount;
            } elseif ($financial_year == $current_financial_year-4) {
                $kpi_donation_count_fy_4++;
                $kpi_donation_amount_fy_4 += $amount;
            }

            // Match the rolling 12-month period
            if (strtotime('-1 year') <= strtotime($date)) {
                $kpi_donation_count_12m_rolling_m_0++;
                $kpi_donation_amount_12m_rolling_m_0 += $amount;
            } elseif (strtotime('-2 years') <= strtotime($date) && strtotime($date) < strtotime('-1 year')) {
                $kpi_donation_count_12m_rolling_m_12++;
                $kpi_donation_amount_12m_rolling_m_12 += $amount;
            } elseif (strtotime('-3 years') <= strtotime($date) && strtotime($date) < strtotime('-2 years')) {
                $kpi_donation_count_12m_rolling_m_24++;
                $kpi_donation_amount_12m_rolling_m_24 += $amount;
            } elseif (strtotime('-4 years') <= strtotime($date) && strtotime($date) < strtotime('-3 years')) {
                $kpi_donation_count_12m_rolling_m_36++;
                $kpi_donation_amount_12m_rolling_m_36 += $amount;
            } elseif (strtotime('-5 years') <= strtotime($date) && strtotime($date) < strtotime('-4 years')) {
                $kpi_donation_count_12m_rolling_m_48++;
                $kpi_donation_amount_12m_rolling_m_48 += $amount;
            }

            // Rolling 24 months
            if (strtotime('-2 years') <= strtotime($date)) {
                $kpi_donation_count_24m_rolling++;
                $kpi_donation_amount_24m_rolling += $amount;
            }
        }

        // Have to check FY-5 outside the 5 year check as it extends further back than that
        if ($financial_year == $current_financial_year-5) {
            $kpi_donation_count_fy_5++;
            $kpi_donation_amount_fy_5 += $amount;
        }
    }

    // Total for last 5 FY
    $kpi_donation_count_5fy = $kpi_donation_count_fy_0+$kpi_donation_count_fy_1+$kpi_donation_count_fy_2+$kpi_donation_count_fy_3+$kpi_donation_count_fy_4;
    $kpi_donation_amount_5fy = $kpi_donation_amount_fy_0+$kpi_donation_amount_fy_1+$kpi_donation_amount_fy_2+$kpi_donation_amount_fy_3+$kpi_donation_amount_fy_4;

    // Have they given in multiple financial years?
    $fin_years_given = 0;
    if ($kpi_donation_count_fy_0 > 0) {
        $fin_years_given++;
    }
    if ($kpi_donation_count_fy_1 > 0) {
        $fin_years_given++;
    }
    if ($kpi_donation_count_fy_2 > 0) {
        $fin_years_given++;
    }
    if ($kpi_donation_count_fy_3 > 0) {
        $fin_years_given++;
    }
    if ($kpi_donation_count_fy_4 > 0) {
        $fin_years_given++;
    }
    if ($kpi_donation_count_fy_5 > 0) {
        $fin_years_given++;
    }
    $kpi_is_multi_fin_year = $fin_years_given > 1;

    // Have they given multiple times in a single financial year?
    if ($kpi_donation_count_fy_0 > 1 || $kpi_donation_count_fy_1 > 1 || $kpi_donation_count_fy_2 > 1 || $kpi_donation_count_fy_3 > 1 || $kpi_donation_count_fy_4 > 1 || $kpi_donation_count_fy_5 > 1) {
        $kpi_is_multi_in_single_fin_year = true;
    }

    // Max Annual Donation Amount (Last 5 FYs)
    $kpi_max_annual_donation_amount_5fy = max(array($kpi_donation_amount_fy_0, $kpi_donation_amount_fy_1, $kpi_donation_amount_fy_2, $kpi_donation_amount_fy_3, $kpi_donation_amount_fy_4, $kpi_donation_amount_fy_5));

    // Averages
    $kpi_donation_average_lifetime          = calculate_average($kpi_donation_count_lifetime, $kpi_donation_amount_lifetime);
    $kpi_donation_average_5y_rolling        = calculate_average($kpi_donation_count_5y_rolling, $kpi_donation_amount_5y_rolling);
    $kpi_donation_average_last_24h          = calculate_average($kpi_donation_count_last_24h, $kpi_donation_amount_last_24h);
    $kpi_donation_average_last_7d           = calculate_average($kpi_donation_count_last_7d, $kpi_donation_amount_last_7d);
    $kpi_donation_average_month_to_date     = calculate_average($kpi_donation_count_month_to_date, $kpi_donation_amount_month_to_date);
    $kpi_donation_average_month_1           = calculate_average($kpi_donation_count_month_1, $kpi_donation_amount_month_1);
    $kpi_donation_average_month_2           = calculate_average($kpi_donation_count_month_2, $kpi_donation_amount_month_2);
    $kpi_donation_average_fy_0              = calculate_average($kpi_donation_count_fy_0, $kpi_donation_amount_fy_0);
    $kpi_donation_average_fy_1              = calculate_average($kpi_donation_count_fy_1, $kpi_donation_amount_fy_1);
    $kpi_donation_average_fy_2              = calculate_average($kpi_donation_count_fy_2, $kpi_donation_amount_fy_2);
    $kpi_donation_average_fy_3              = calculate_average($kpi_donation_count_fy_3, $kpi_donation_amount_fy_3);
    $kpi_donation_average_fy_4              = calculate_average($kpi_donation_count_fy_4, $kpi_donation_amount_fy_4);
    $kpi_donation_average_fy_5              = calculate_average($kpi_donation_count_fy_5, $kpi_donation_amount_fy_5);
    $kpi_donation_average_24m_rolling       = calculate_average($kpi_donation_count_24m_rolling, $kpi_donation_amount_24m_rolling);
    $kpi_donation_average_12m_rolling_m_0   = calculate_average($kpi_donation_count_12m_rolling_m_0, $kpi_donation_amount_12m_rolling_m_0);
    $kpi_donation_average_12m_rolling_m_12  = calculate_average($kpi_donation_count_12m_rolling_m_12, $kpi_donation_amount_12m_rolling_m_12);
    $kpi_donation_average_12m_rolling_m_24  = calculate_average($kpi_donation_count_12m_rolling_m_24, $kpi_donation_amount_12m_rolling_m_24);
    $kpi_donation_average_12m_rolling_m_36  = calculate_average($kpi_donation_count_12m_rolling_m_36, $kpi_donation_amount_12m_rolling_m_36);
    $kpi_donation_average_12m_rolling_m_48  = calculate_average($kpi_donation_count_12m_rolling_m_48, $kpi_donation_amount_12m_rolling_m_48);
    $kpi_donation_average_5fy               = calculate_average($kpi_donation_count_5fy, $kpi_donation_amount_5fy);

    // Now we can update all the relevant meta fields

    // First/last donation details
    update_user_meta($user->ID, 'kpi_first_donation_date', $kpi_first_donation_date);
    update_user_meta($user->ID, 'kpi_last_donation_amount', $kpi_last_donation_amount);
    update_user_meta($user->ID, 'kpi_last_donation_date', $kpi_last_donation_date);

    // Days since last donation for segment searching
    if (!empty($kpi_last_donation_date)) {
        $date_last_donation = new DateTime($kpi_last_donation_date);
        $days_since_last_donation = $date_last_donation->diff($today, true);
        update_user_meta($user->ID, 'kpi_days_since_last_donation', $days_since_last_donation->days);
    }

    // Days since created for segment searching
    $date_registered = new DateTime($user->user_registered);
    $days_since_created = $date_registered->diff($today, true);
    update_user_meta($user->ID, 'kpi_days_since_created', $days_since_created->days);

    // Multi donation flags
    update_user_meta($user->ID, 'kpi_is_multi_fin_year', $kpi_is_multi_fin_year ? 'true' : 'false');
    update_user_meta($user->ID, 'kpi_is_multi_in_single_fin_year', $kpi_is_multi_in_single_fin_year ? 'true' : 'false');

    // And all the calculated totals
    update_user_meta($user->ID, 'kpi_donation_count_lifetime', $kpi_donation_count_lifetime);
    update_user_meta($user->ID, 'kpi_donation_amount_lifetime', $kpi_donation_amount_lifetime);
    update_user_meta($user->ID, 'kpi_donation_average_lifetime', $kpi_donation_average_lifetime);

    update_user_meta($user->ID, 'kpi_donation_count_5y_rolling', $kpi_donation_count_5y_rolling);
    update_user_meta($user->ID, 'kpi_donation_amount_5y_rolling', $kpi_donation_amount_5y_rolling);
    update_user_meta($user->ID, 'kpi_donation_average_5y_rolling', $kpi_donation_average_5y_rolling);

    update_user_meta($user->ID, 'kpi_donation_count_last_24h', $kpi_donation_count_last_24h);
    update_user_meta($user->ID, 'kpi_donation_amount_last_24h', $kpi_donation_amount_last_24h);
    update_user_meta($user->ID, 'kpi_donation_average_last_24h', $kpi_donation_average_last_24h);

    update_user_meta($user->ID, 'kpi_donation_count_last_7d', $kpi_donation_count_last_7d);
    update_user_meta($user->ID, 'kpi_donation_amount_last_7d', $kpi_donation_amount_last_7d);
    update_user_meta($user->ID, 'kpi_donation_average_last_7d', $kpi_donation_average_last_7d);

    update_user_meta($user->ID, 'kpi_donation_count_month_to_date', $kpi_donation_count_month_to_date);
    update_user_meta($user->ID, 'kpi_donation_amount_month_to_date', $kpi_donation_amount_month_to_date);
    update_user_meta($user->ID, 'kpi_donation_average_month_to_date', $kpi_donation_average_month_to_date);

    update_user_meta($user->ID, 'kpi_donation_count_month_1', $kpi_donation_count_month_1);
    update_user_meta($user->ID, 'kpi_donation_amount_month_1', $kpi_donation_amount_month_1);
    update_user_meta($user->ID, 'kpi_donation_average_month_1', $kpi_donation_average_month_1);

    update_user_meta($user->ID, 'kpi_donation_count_month_2', $kpi_donation_count_month_2);
    update_user_meta($user->ID, 'kpi_donation_amount_month_2', $kpi_donation_amount_month_2);
    update_user_meta($user->ID, 'kpi_donation_average_month_2', $kpi_donation_average_month_2);

    update_user_meta($user->ID, 'kpi_donation_count_fy_0', $kpi_donation_count_fy_0);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_0', $kpi_donation_amount_fy_0);
    update_user_meta($user->ID, 'kpi_donation_average_fy_0', $kpi_donation_average_fy_0);

    update_user_meta($user->ID, 'kpi_donation_count_fy_1', $kpi_donation_count_fy_1);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_1', $kpi_donation_amount_fy_1);
    update_user_meta($user->ID, 'kpi_donation_average_fy_1', $kpi_donation_average_fy_1);

    update_user_meta($user->ID, 'kpi_donation_count_fy_2', $kpi_donation_count_fy_2);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_2', $kpi_donation_amount_fy_2);
    update_user_meta($user->ID, 'kpi_donation_average_fy_2', $kpi_donation_average_fy_2);

    update_user_meta($user->ID, 'kpi_donation_count_fy_3', $kpi_donation_count_fy_3);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_3', $kpi_donation_amount_fy_3);
    update_user_meta($user->ID, 'kpi_donation_average_fy_3', $kpi_donation_average_fy_3);

    update_user_meta($user->ID, 'kpi_donation_count_fy_4', $kpi_donation_count_fy_4);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_4', $kpi_donation_amount_fy_4);
    update_user_meta($user->ID, 'kpi_donation_average_fy_4', $kpi_donation_average_fy_4);

    update_user_meta($user->ID, 'kpi_donation_count_fy_5', $kpi_donation_count_fy_5);
    update_user_meta($user->ID, 'kpi_donation_amount_fy_5', $kpi_donation_amount_fy_5);
    update_user_meta($user->ID, 'kpi_donation_average_fy_5', $kpi_donation_average_fy_5);

    update_user_meta($user->ID, 'kpi_donation_count_24m_rolling', $kpi_donation_count_24m_rolling);
    update_user_meta($user->ID, 'kpi_donation_amount_24m_rolling', $kpi_donation_amount_24m_rolling);
    update_user_meta($user->ID, 'kpi_donation_average_24m_rolling', $kpi_donation_average_24m_rolling);

    update_user_meta($user->ID, 'kpi_donation_count_12m_rolling_m_0', $kpi_donation_count_12m_rolling_m_0);
    update_user_meta($user->ID, 'kpi_donation_amount_12m_rolling_m_0', $kpi_donation_amount_12m_rolling_m_0);
    update_user_meta($user->ID, 'kpi_donation_average_12m_rolling_m_0', $kpi_donation_average_12m_rolling_m_0);

    update_user_meta($user->ID, 'kpi_donation_count_12m_rolling_m_12', $kpi_donation_count_12m_rolling_m_12);
    update_user_meta($user->ID, 'kpi_donation_amount_12m_rolling_m_12', $kpi_donation_amount_12m_rolling_m_12);
    update_user_meta($user->ID, 'kpi_donation_average_12m_rolling_m_12', $kpi_donation_average_12m_rolling_m_12);

    update_user_meta($user->ID, 'kpi_donation_count_12m_rolling_m_24', $kpi_donation_count_12m_rolling_m_24);
    update_user_meta($user->ID, 'kpi_donation_amount_12m_rolling_m_24', $kpi_donation_amount_12m_rolling_m_24);
    update_user_meta($user->ID, 'kpi_donation_average_12m_rolling_m_24', $kpi_donation_average_12m_rolling_m_24);

    update_user_meta($user->ID, 'kpi_donation_count_12m_rolling_m_36', $kpi_donation_count_12m_rolling_m_36);
    update_user_meta($user->ID, 'kpi_donation_amount_12m_rolling_m_36', $kpi_donation_amount_12m_rolling_m_36);
    update_user_meta($user->ID, 'kpi_donation_average_12m_rolling_m_36', $kpi_donation_average_12m_rolling_m_36);

    update_user_meta($user->ID, 'kpi_donation_count_12m_rolling_m_48', $kpi_donation_count_12m_rolling_m_48);
    update_user_meta($user->ID, 'kpi_donation_amount_12m_rolling_m_48', $kpi_donation_amount_12m_rolling_m_48);
    update_user_meta($user->ID, 'kpi_donation_average_12m_rolling_m_48', $kpi_donation_average_12m_rolling_m_48);

    update_user_meta($user->ID, 'kpi_donation_count_5fy', $kpi_donation_count_5fy);
    update_user_meta($user->ID, 'kpi_donation_amount_5fy', $kpi_donation_amount_5fy);
    update_user_meta($user->ID, 'kpi_donation_average_5fy', $kpi_donation_average_5fy);

    update_user_meta($user->ID, 'kpi_max_annual_donation_amount_5fy', $kpi_max_annual_donation_amount_5fy);
}

/**
 * Calculate the current financial year
 * @param $inputDate string The date to calculate for
 * @param $fyStart string The start of the financial year, mm-dd format
 * @param $fyEnd string The end of the financial year, mm-dd format
 * @return integer
 */
function calculateFiscalYearForDate($inputDate, $fyStart = '07-01', $fyEnd = '06-30'){
    $date = strtotime($inputDate);
    $inputyear = strftime('%Y', $date);

    $fystartdate = strtotime($inputyear.'-'.$fyStart);
    $fyenddate = strtotime($inputyear.'-'.$fyEnd);

    if ($date <= $fyenddate) {
        $fy = intval($inputyear);
    } else {
        $fy = intval(intval($inputyear) + 1);
    }

    return $fy;
}

/**
 * Calculate an average amount
 * @param integer $count Number of items
 * @param float $amount Total amount
 * @return float
 */
function calculate_average($count, $amount) {
    return $count > 0 ? $amount/$count : 0;
}
