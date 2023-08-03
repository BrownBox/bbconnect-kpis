<?php
/**
 * Cron script which updates various custom KPI fields for users
 *
 * The following variables used here are defined in the core cron.php script:
 * @var WP_User[] $users A list of all users for the current site
 * @var DateTime $today Object for the current date
 * @var DateTime $yesterday Object for the previous day
 * @var DateTime $last_run_date Object for the date the cron last successfully ran
 * @var string $kpi_prefix Prefix for KPI fields
 * @var DateTimeZone $tz Site timezone
 * @var wpdb $wpdb Database connection object
 */

$current_financial_year = bbconnect_kpi_calculate_fiscal_year_for_date($today->format("Y-m-d"));

// Work out which dates we need to recalculate transactions for
$dates = $missing_dates = array();
$target_date = clone $last_run_date;

// Start by working out days between last run and today
while ($target_date->getTimestamp() < $today->getTimestamp()) {
	$missing_dates[] = $target_date->format('Y-m-d');
	$target_date->add(new DateInterval('P1D'));
}

// Now for each of those days work out the days that are going to drop out of rolling numbers
foreach ($missing_dates as $missing_date) {
	$base_date = new DateTime($missing_date, $tz);
	$dates[] = $missing_date;

	// Day
	$day_date = clone $base_date;
	$day_date->sub(new DateInterval('P1D'));
	$dates[] = $day_date->format('Y-m-d');

	// Week
	$week_date = clone $base_date;
	$week_date->sub(new DateInterval('P7D'));
	$dates[] = $week_date->format('Y-m-d');

	// Year
	$year_date = clone $base_date;
	for ($i = 1; $i <= 5; $i++) {
		$year_date->sub(new DateInterval('P1Y'));
		$dates[] = $year_date->format('Y-m-d');
	}
}
unset($missing_dates);

$dates = array_unique($dates);

// Get list of users who have transactions for the above dates
$placeholders = implode(', ', array_fill(0, count($dates), '%s'));
$sql = 'SELECT DISTINCT(post_author) from '.$wpdb->posts.' WHERE post_type = "transaction" AND DATE(post_date) IN ('.$placeholders.')';
$query = $wpdb->prepare($sql, $dates);
$dirty_users = $wpdb->get_col($query);

$force_kpis = false;
$boundary_users = array();
if (bbconnect_kpi_calculate_fiscal_year_for_date($last_run_date->format('Y-m-d')) != $current_financial_year) {
	// FYs aren't rolling as such but if we've crossed a boundary we need everyone who has donated in the last 6 years. Just to be safe we'll do everyone who has ever donated.
	$sql = 'SELECT DISTINCT(post_author) from '.$wpdb->posts.' WHERE post_type = "transaction"';
	$query = $wpdb->prepare($sql, $dates);
	$dirty_users = $wpdb->get_col($query);
	echo '    New FY! Recalculating everyone\'s KPIs'."\n";
} else/*if ($last_run_date->format('Y-m') != $today->format('Y-m'))*/ {
	// Months also aren't truly rolling; if we're in the same FY but have crossed a month boundary we need everyone who has donated in the last 4 months
	// Even that doesn't seem to work properly for us - let's just always do everyone who has donated in the past 4 months
	$month_4_year = $today->format('Y');
	$month_4 = $today->format('n')-4;
	if ($month_4 <= 0) {
		$month_4 += 12;
		$month_4_year--;
	}
	if (strlen($month_4) == 1) {
		$month_4 = '0'.$month_4;
	}
	$query = 'SELECT DISTINCT(post_author) from '.$wpdb->posts.' WHERE post_type = "transaction" AND post_date >= "'.$month_4_year.'-'.$month_4.'-01"';
	$boundary_users = $wpdb->get_col($query);

	$dirty_users = array_unique(array_merge($dirty_users, $boundary_users));
	echo '    '.count($dirty_users).' users require rolling KPI recalculation'."\n";
}

$user_count = 0;
foreach ($users as $user) {
	set_time_limit(3600);

	echo '    '.$user->display_name."\n";
	if ($force_kpis || in_array($user->ID, $dirty_users) || strtotime($user->user_registered) >= $last_run_date->getTimestamp()) {
		echo '        Recalculating KPIs'."\n";
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
				$kpi_prefix.'donation_count_lifetime' => !empty($user->{$kpi_prefix.'offset_donation_count'}) ? $user->{$kpi_prefix.'offset_donation_count'} : 0,

				// Donation Amount last 5 yrs rolling
				$kpi_prefix.'donation_amount_5y_rolling' => 0,
				// Donation Amount last 5 FYs
				$kpi_prefix.'donation_amount_5fy' => 0,
				// Donation Amount Lifetime
				$kpi_prefix.'donation_amount_lifetime' => !empty($user->{$kpi_prefix.'offset_donation_amount'}) ? $user->{$kpi_prefix.'offset_donation_amount'} : 0,

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
		$user_meta = apply_filters('bbconnect_kpis_cron_kpi_defaults', $user_meta, $kpi_prefix, $user);

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

		foreach ($transactions as $transaction) {
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

			$user_meta = apply_filters('bbconnect_kpis_cron_transaction_kpis', $user_meta, $kpi_prefix, $user, $transaction, $amount, $today);
		}
		unset($transactions);

		// Use historical tranasction data where relevant
		if (!empty($user->{$kpi_prefix.'offset_first_donation_date'})) {
			$user_meta[$kpi_prefix.'first_donation_date'] = $user->{$kpi_prefix.'offset_first_donation_date'};
		}
		if (empty($user_meta[$kpi_prefix.'last_donation_date']) && !empty($user->{$kpi_prefix.'offset_last_donation_date'})) {
			$user_meta[$kpi_prefix.'last_donation_date'] = $user->{$kpi_prefix.'offset_last_donation_date'};
			$user_meta[$kpi_prefix.'last_donation_amount'] = $user->{$kpi_prefix.'offset_last_donation_amount'};
		}

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
	}

	// Days since last donation for segment searching
	if (!empty($user_meta[$kpi_prefix.'last_donation_date'])) {
		$date_last_donation = new DateTime($user_meta[$kpi_prefix.'last_donation_date']);
	} else { // Users we haven't had to recalculate rolling KPIs for still need updating if they've ever donated
		$last_donation = get_user_meta($user->ID, $kpi_prefix.'last_donation_date', true);
		if (!empty($last_donation)) {
			$date_last_donation = new DateTime($last_donation);
		}
	}
	if (isset($date_last_donation) && $date_last_donation instanceof DateTime) {
		$days_since_last_donation = $date_last_donation->diff($today, true);
		$user_meta[$kpi_prefix.'days_since_last_donation'] = $days_since_last_donation->days;
	}

	// Days since created for segment searching
	$date_registered = new DateTime($user->user_registered);
	$days_since_created = $date_registered->diff($today, true);

	$user_meta[$kpi_prefix.'days_since_created'] = $days_since_created->days;
	unset($date_registered, $days_since_created);

	$user_meta = apply_filters('bbconnect_kpis_cron_kpis', $user_meta, $kpi_prefix, $user, $today);

	// Now we can update all the relevant meta fields
	$table = _get_meta_table('user');
	$values = array_keys($user_meta);
	$keys = array();
	$keys = array_pad($keys, count($values), '%s');
	$keys = implode(',', $keys);
	$values[] = $user->ID;
	$sql = "DELETE FROM $table WHERE meta_key IN ($keys) AND user_id = %d";
	$wpdb->query($wpdb->prepare($sql, $values));
	foreach ($user_meta as $meta_key => $meta_value) {
		$sql = "INSERT INTO $table (meta_value, meta_key, user_id) VALUES (%s, %s, %d)";
		$wpdb->query($wpdb->prepare($sql, array($meta_value, $meta_key, $user->ID)));
	}

	unset($user_meta, $keys, $values);
	gc_collect_cycles();

	$user_count++;
	if ($user_count % 100 == 0) {
		echo $user_count.' users processed'."\n";
	}

	bbconnect_kpi_cron_flush();
}
unset($dates);
gc_collect_cycles();
