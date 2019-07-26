<?php
/**
 * Calculate the current financial year
 * @param $inputDate string|integer|DateTime The date to calculate for. Can be a DateTime object, Unix timestamp or any parseable string recognised by strtotime()
 * @param $fyStart string The start of the financial year, mm-dd format
 * @param $fyEnd string The end of the financial year, mm-dd format
 * @return integer
 */
function bbconnect_kpi_calculate_fiscal_year_for_date($inputDate, $fyStart = '07-01', $fyEnd = '06-30') {
    if ($inputDate instanceof DateTime) {
        $date = $inputDate->getTimestamp();
    } elseif (is_int($inputDate)) {
        $date = $inputDate;
    } else {
        $date = strtotime($inputDate);
    }
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
function bbconnect_kpi_calculate_average($count, $amount) {
    return $count > 0 ? $amount/$count : 0;
}

/**
 * Flush the output so we don't have to wait for the script to finish to see it
 */
function bbconnect_kpi_cron_flush() {
    @ob_flush();
    flush();
}

/*
 * Set recurring donor to true
 */
add_action('bb_cart_post_purchase', 'bbconnect_kpi_set_recurring_field_value', 10, 4);
function bbconnect_kpi_set_recurring_field_value($cart_items, $entry, $form, $post_id) {
    if (!empty($cart_items['donations'])) {
        foreach ($cart_items['donations'] as $item) {
            if (!empty($item['frequency']) && $item['frequency'] != 'one-off') {
                $transaction = get_post($post_id);
                $user = new WP_User($transaction->post_author);
                update_user_meta($user->ID, 'recurring_donation', 'true');
                break;
            }
        }
    }
}

add_action('admin_menu', 'bbconnect_kpi_register_reports_menu_pages');
function bbconnect_kpi_register_reports_menu_pages() {
    add_submenu_page('users.php', 'Donor Reports', 'Donor Reports', 'list_users', 'donor_report_submenu','bbconnect_kpi_donor_report');
    add_submenu_page('users.php', 'Segment Report', 'Segment Report', 'list_users', 'segment_report_submenu','bbconnect_kpi_segment_report');
}

add_action('admin_enqueue_scripts', 'bbconnect_kpi_enqueue_scripts');
function bbconnect_kpi_enqueue_scripts() {
    wp_enqueue_style('bbconnect_kpi_css', BBCONNECT_KPI_URL.'css/kpi.css', array(), BBCONNECT_KPI_VERSION);
    wp_enqueue_script('bbconnect_kpi_js', BBCONNECT_KPI_URL.'js/kpi.js', array(), BBCONNECT_KPI_VERSION, true);
}
