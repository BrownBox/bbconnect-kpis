<?php
// require_once('../../../wp-load.php');
global $wpdb;
$donation_summary_table_name = $wpdb->prefix.'bbconnect_monthly_history';
$response = array();

$calculated_month = new DateTime('17 months ago');
while ($calculated_month->format('Y-m') <= date('Y-m')) {
	$calculated_month_label = $calculated_month->format("F Y");

    $get_donation_month_query = "
            SELECT ifnull(SUM(s.current_month),0) AS donation
            FROM $donation_summary_table_name s
            WHERE s.year = '".$calculated_month->format('Y')."' AND s.month = '".$calculated_month->format('n')."' AND s.rule='total_donations'";

    $donation_month_arr = $wpdb->get_results($get_donation_month_query);

    foreach($donation_month_arr as $donation_obj){
    	array_push($response, array("month" => $calculated_month_label, "donation" => $donation_obj->donation));
    }

	//end calculations
	$calculated_month->add(new DateInterval('P1M'));
}
echo json_encode($response);
