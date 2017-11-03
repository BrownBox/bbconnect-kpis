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
add_action('bb_cart_post_purchase', 'bbconnect_kpi_set_recurring_field_value');
function bbconnect_kpi_set_recurring_field_value($cart_items, $entry, $form, $post_id) {
    $kpi_prefix = 'kpi_';
    if (is_multisite() && get_current_blog_id() != SITE_ID_CURRENT_SITE) {
        $kpi_prefix .= get_current_blog_id().'_';
    }
    foreach ($cart_items as $item) {
        if (!empty($item['frequency']) && $item['frequency'] != 'one-off') {
            foreach ($form['fields'] as $field){
                if ($field->type == 'email'){
                    $email = $entry[$field['id']];
                }
            }
            $user = get_user_by_email($email);
            update_user_meta($user->ID, $kpi_prefix.'recurring_donation', true);
        }
    }
}
