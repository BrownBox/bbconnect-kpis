<?php
/**
 * Calculate the current financial year
 * @param $inputDate string The date to calculate for
 * @param $fyStart string The start of the financial year, mm-dd format
 * @param $fyEnd string The end of the financial year, mm-dd format
 * @return integer
 */
function bbconnect_kpi_calculate_fiscal_year_for_date($inputDate, $fyStart = '07-01', $fyEnd = '06-30'){
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
