<?php
function bbconnect_kpi_segment_report() {
    global $wpdb;

    $selected_rule = isset($_GET['rule']) ? $_GET['rule'] : '';
    $selected_month = isset($_GET['month']) ? $_GET['month'] : '';
    list($year, $month) = explode('-', $selected_month);

    $rule_options = '';
    foreach (bbconnectKpiReports::get_rules() as $rule => $label) {
        if (empty($selected_rule)) {
            $selected_rule = $rule;
        }
        $rule_options .= "<option value='$rule' ".selected($rule, $selected_rule, false).">$label</option>";
    }

    $summary_table_name = bbconnectKpiReports::get_summary_table_name();

    $month_options = '';
    $get_months_query = "
    SELECT DISTINCT $summary_table_name.year, $summary_table_name.month AS month
    FROM $summary_table_name ORDER BY year DESC, month DESC";

    $months_arr = $wpdb->get_results($get_months_query);

    foreach ($months_arr as $key => $monthsObj) {
        if (empty($year)) {
            $year = $monthsObj->year;
        }
        if (empty($month)) {
            $month = $monthsObj->month;
        }
        $value = $monthsObj->year.'-'.$monthsObj->month;
        $text = date('F', strtotime($monthsObj->year.'-'.$monthsObj->month.'-01')).' '.$monthsObj->year;
        $month_options .= "<option value='$value' ".selected($value, $selected_month, false).">$text</option>";
    }

    $path_to_image = BBCONNECT_KPI_URL . 'utils/excel.png';

    $results = bbconnectKpiReports::get_segment_report($year, $month, $selected_rule);
    $results_html = "";
    foreach ($results as $row) {
        $results_html .= '
                <tr>
                    <td style="font-weight: bold;">'.$row->segment_name.'</td>
                    <td>'.$row->previous_year_2.'</td>
                    <td>'.$row->previous_year_1.'</td>
                    <td>'.$row->current_year.'</td>
                    <td>'.$row->previous_month_2.'</td>
                    <td>'.$row->previous_month_1.'</td>
                    <td>'.$row->current_month.'</td>
                </tr>';
    }

    $month_time = mktime(0, 0, 0, $month, 15, $year);
    $fy_0 = bbconnect_kpi_calculate_fiscal_year_for_date($month_time);
    $fy_1 = bbconnect_kpi_calculate_fiscal_year_for_date(strtotime('-1 year', $month_time));
    $fy_2 = bbconnect_kpi_calculate_fiscal_year_for_date(strtotime('-2 years', $month_time));
    $month_name = date('F', $month_time);
    $month_1 = date('F', strtotime('-1 month', $month_time));
    $month_2 = date('F', strtotime('-2 months', $month_time));

    $html = "
    <div class='wrap'>
        <button name='segment-report-download' id='segment-report-download' class='button action' style='display:none;'><img src='$path_to_image'> Export to Excel</button>
        <form action='' method='get'>
        <input type='hidden' name='page' value='segment_report_submenu'>
        <select name='month' id='summary-months'>
        $month_options
        </select>
        <select name='rule' id='summary-rules'>
        $rule_options
        </select>
        </form>
        <table id='table-segment-report' class='bbconnect-kpi-report'>
            <thead>
                <tr>
                    <th width='22%'>Segment</th>
                    <th id='s-report-two-previous-years' width='13%'>FY $fy_2</th>
                    <th id='s-report-one-previous-years' width='13%'>FY $fy_1</th>
                    <th id='s-report-current-years' width='13%'>FY $fy_0</th>
                    <th id='s-report-two-previous-months' width='13%'>$month_2</th>
                    <th id='s-report-one-previous-months' width='13%'>$month_1</th>
                    <th id='s-report-current-months' width='13%'>$month_name</th>
                </tr>
            </thead>
            <tbody class='report-body'>
                $results_html
            </tbody>
        </table>
    </div>
";
    echo $html;
}