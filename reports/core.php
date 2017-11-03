<?php
/**
 * A simple class to centralise report management
 */
class bbconnectKpiReports {
    /**
     * List of rules
     * @var array
     */
    private static $rules = array(
            'total_donations' => 'Total Donations',
            'average_donation' => 'Average Donation Amount',
            'donation_count' => 'No. of Donations',
            'new_contacts' => 'New Names',
            'new_donors' => 'New Donors',
            'lapsed_donors' => 'New Lapsed Donors',
            'total_live_donors' => 'Total Live Donors',
    );

    /**
     * List of users
     * @var array
     */
    private $users;

    /**
     * Segment ID
     * @var integer
     */
    private $segment_id;

    /**
     * Current date
     * @var DateTime
     */
    private $today;

    /**
     * Day before current date
     * @var DateTime
     */
    private $yesterday;

    /**
     * Place to store the new contacts so we only have to calculate them once
     * @var array
     */
    private $new_contacts;

    public function __construct(array $users = array(), $segment_id = null, DateTime $today = null, DateTime $yesterday = null) {
        $this->users = $users;
        $this->segment_id = $segment_id;
        if (empty($today)) {
            $today = new DateTime();
            if (empty($yesterday)) {
                $yesterday = clone $today;
                $yesterday->sub(new DateInterval('P1D'));
            }
        }
        $this->today = clone $today;
        $this->yesterday = clone $yesterday;
    }

    /**
     * Get rule list
     * @return array
     */
    public static function get_rules() {
        return self::$rules;
    }

    /**
     * Get rule name
     * @return string
     */
    public static function get_rule_name($rule) {
        if (isset(self::$rules[$rule])) {
            return self::$rules[$rule];
        }
        return false;
    }

    /**
     * Get the data for the specified rule
     * @param string $rule_name
     * @return array of results or boolean false if rule doesn't exist
     */
    public function process_rule($rule_name) {
        $func = $rule_name.'_rule';
        if (method_exists($this, $func)) {
            return $this->$func();
        }

        return false;
    }

    /**
     * Calculate the new contact counts
     */
    private function calculate_new_contacts() {
        if (empty($this->new_contacts)) {
            $users = $this->users;

            $var_date = clone $this->yesterday;
            $month = $this->yesterday->format("Y-m");
            $var_date->sub(new DateInterval('P31D'));
            $month_1 = $var_date->format("Y-m");
            $var_date->sub(new DateInterval('P31D'));
            $month_2 = $var_date->format("Y-m");

            $var_date = clone $this->yesterday;
            $year = bbconnect_kpi_calculate_fiscal_year_for_date($this->yesterday);
            $var_date->sub(new DateInterval('P1Y'));
            $year_1 = bbconnect_kpi_calculate_fiscal_year_for_date($var_date);
            $var_date->sub(new DateInterval('P1Y'));
            $year_2 = bbconnect_kpi_calculate_fiscal_year_for_date($var_date);

            $stats_template = array(
                    'month' => 0,
                    'month_1' => 0,
                    'month_2' => 0,
                    'year' => 0,
                    'year_1' => 0,
                    'year_2' => 0,
            );

            $contacts = array(
                    'new_contacts' => $stats_template,
            );

            foreach ($users as $user) {
                $user_registered = $user->user_registered;

                $user_registered_month = date('Y-m',strtotime($user_registered));
                $user_registered_year = bbconnect_kpi_calculate_fiscal_year_for_date($user_registered);

                if ($user_registered_month == $month) {
                    $contacts['new_contacts']['month']++;
                } else if($user_registered_month == $month_1) {
                    $contacts['new_contacts']['month_1']++;
                } else if($user_registered_month == $month_2) {
                    $contacts['new_contacts']['month_2']++;
                }

                if ($user_registered_year == $year) {
                    $contacts['new_contacts']['year']++;
                } else if($user_registered_year == $year_1) {
                    $contacts['new_contacts']['year_1']++;
                } else if($user_registered_year == $year_2) {
                    $contacts['new_contacts']['year_2']++;
                }
            }

            $this->new_contacts = $contacts;
        }
    }

    /**
     * Get counts of new contacts
     * @return array
     */
    private function new_contacts_rule() {
        $this->calculate_new_contacts();
        return $this->new_contacts['new_contacts'];
    }

    /**
     * Calculate new donors
     * @return array
     */
    private function new_donors_rule() {
        $users = $this->users;

        $var_date = clone $this->yesterday;
        $month = $this->yesterday->format("Y-m");
        $var_date->sub(new DateInterval('P31D'));
        $month_1 = $var_date->format("Y-m");
        $var_date->sub(new DateInterval('P31D'));
        $month_2 = $var_date->format("Y-m");

        $var_date = clone $this->yesterday;
        $year = bbconnect_kpi_calculate_fiscal_year_for_date($this->yesterday);
        $var_date->sub(new DateInterval('P1Y'));
        $year_1 = bbconnect_kpi_calculate_fiscal_year_for_date($var_date);
        $var_date->sub(new DateInterval('P1Y'));
        $year_2 = bbconnect_kpi_calculate_fiscal_year_for_date($var_date);

        $count_month = 0;
        $count_month_1 = 0;
        $count_month_2 = 0;
        $count_year = 0;
        $count_year_1 = 0;
        $count_year_2 = 0;

        $summary = array();

        global $blog_id;
        $kpi_prefix = 'kpi_';
        if (is_multisite() && $blog_id != SITE_ID_CURRENT_SITE) {
            $kpi_prefix .= $blog_id.'_';
        }
        foreach ($users as $key => $user) {
            $first_donation_date = get_user_meta($user->ID, $kpi_prefix.'first_donation_date', true);
            if (empty($first_donation_date)) {
                continue;
            }

            $first_donation_month = date('Y-m',strtotime($first_donation_date));
            $first_donation_year = bbconnect_kpi_calculate_fiscal_year_for_date($first_donation_date);

            if ($first_donation_month == $month) {
                $count_month = $count_month + 1;
            } else if($first_donation_month == $month_1) {
                $count_month_1 = $count_month_1 + 1;
            } else if($first_donation_month == $month_2) {
                $count_month_2 = $count_month_2 + 1;
            }

            if ($first_donation_year == $year) {
                $count_year = $count_year + 1;
            } else if($first_donation_year == $year_1) {
                $count_year_1 = $count_year_1 + 1;
            } else if($first_donation_year == $year_2) {
                $count_year_2 = $count_year_2 + 1;
            }
        }
        $summary['month'] = $count_month;
        $summary['month_1'] = $count_month_1;
        $summary['month_2'] = $count_month_2;
        $summary['year'] = $count_year;
        $summary['year_1'] = $count_year_1;
        $summary['year_2'] = $count_year_2;

        return $summary;
    }

    /**
     * Calculate lapsed donors
     * @return array
     */
    private function lapsed_donors_rule() {
        $segment_id = $this->segment_id;

        global $wpdb;
        $table_name = $wpdb->prefix.'bbconnect_user_history';

        $var_date = clone $this->yesterday;
        $month = $this->yesterday->format("Y-m");
        $var_date->sub(new DateInterval('P31D'));
        $month_1 = $var_date->format("Y-m");
        $var_date->sub(new DateInterval('P31D'));
        $month_2 = $var_date->format("Y-m");

        $var_date = clone $this->yesterday;
        $year = $this->yesterday->format("Y");
        $var_date->sub(new DateInterval('P1Y'));
        $year_1 = $var_date->format("Y");
        $var_date->sub(new DateInterval('P1Y'));
        $year_2 = $var_date->format("Y");

        $count_month = 0;
        $count_month_1 = 0;
        $count_month_2 = 0;
        $count_year = 0;
        $count_year_1 = 0;
        $count_year_2 = 0;

        $summary = array();

        $final_query_month = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y-%m') = '$month'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $final_query_month_1 = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y-%m') = '$month_1'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $final_query_month_2 = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y-%m') = '$month_2'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $final_query_year = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y') = '$year'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $final_query_year_1 = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y') = '$year_1'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $final_query_year_2 = "
        SELECT count(h.ID) AS count
        FROM $table_name h
        WHERE h.segment_id = $segment_id
            AND DATE_FORMAT(h.lapsed_date,'%Y') = '$year_2'
            AND DATE_FORMAT(h.created_at, '%Y-%m-%d') = '".$this->today->format('Y-m-d')."'";

        $lapsed_month_counts_arr = $wpdb->get_results($final_query_month);
        $lapsed_month_1_counts_arr = $wpdb->get_results($final_query_month_1);
        $lapsed_month_2_counts_arr = $wpdb->get_results($final_query_month_2);
        $lapsed_year_counts_arr = $wpdb->get_results($final_query_year);
        $lapsed_year_1_counts_arr = $wpdb->get_results($final_query_year_1);
        $lapsed_year_2_counts_arr = $wpdb->get_results($final_query_year_2);

        $summary['month'] = $lapsed_month_counts_arr[0]->count;
        $summary['month_1'] = $lapsed_month_1_counts_arr[0]->count;
        $summary['month_2'] = $lapsed_month_2_counts_arr[0]->count;
        $summary['year'] = $lapsed_year_counts_arr[0]->count;
        $summary['year_1'] = $lapsed_year_1_counts_arr[0]->count;
        $summary['year_2'] = $lapsed_year_2_counts_arr[0]->count;

        return $summary;
    }

    /**
     * Calculate total amounts
     * @return array
     */
    private function total_donations_rule() {
        $users = $this->users;

        $amount_month = 0;
        $amount_month_1 = 0;
        $amount_month_2 = 0;
        $amount_year = 0;
        $amount_year_1 = 0;
        $amount_year_2 = 0;

        $summary = array();

        global $blog_id;
        $kpi_prefix = 'kpi_';
        if (is_multisite() && $blog_id != SITE_ID_CURRENT_SITE) {
            $kpi_prefix .= $blog_id.'_';
        }
        foreach ($users as $key => $user) {
            $user_amount_month = get_user_meta($user->ID, $kpi_prefix.'donation_amount_month_to_date', true);
            $user_amount_month_1 = get_user_meta($user->ID, $kpi_prefix.'donation_amount_month_1', true);
            $user_amount_month_2 = get_user_meta($user->ID, $kpi_prefix.'donation_amount_month_2', true);
            $user_amount_year = get_user_meta($user->ID, $kpi_prefix.'donation_amount_fy_0', true);
            $user_amount_year_1 = get_user_meta($user->ID, $kpi_prefix.'donation_amount_fy_1', true);
            $user_amount_year_2 = get_user_meta($user->ID, $kpi_prefix.'donation_amount_fy_2', true);

            $amount_month = $amount_month + $user_amount_month;
            $amount_month_1 = $amount_month_1 + $user_amount_month_1;
            $amount_month_2 = $amount_month_2 + $user_amount_month_2;
            $amount_year = $amount_year + $user_amount_year;
            $amount_year_1 = $amount_year_1 + $user_amount_year_1;
            $amount_year_2 = $amount_year_2 + $user_amount_year_2;
        }
        $summary['month'] = $amount_month;
        $summary['month_1'] = $amount_month_1;
        $summary['month_2'] = $amount_month_2;
        $summary['year'] = $amount_year;
        $summary['year_1'] = $amount_year_1;
        $summary['year_2'] = $amount_year_2;

        return $summary;
    }

    /**
     * Calculate average donations
     * @return array
     */
    private function average_donation_rule() {
        $totals = $this->total_donations_rule();
        $counts = $this->donation_count_rule();

        $summary = array();
        foreach (array_keys($totals) as $key) {
            $summary[$key] = bbconnect_kpi_calculate_average($counts[$key], $totals[$key]);
        }

        return $summary;
    }

    private function donation_count_rule() {
        $users = $this->users;

        $count_month = 0;
        $count_month_1 = 0;
        $count_month_2 = 0;
        $count_year = 0;
        $count_year_1 = 0;
        $count_year_2 = 0;

        $summary = array();

        global $blog_id;
        $kpi_prefix = 'kpi_';
        if (is_multisite() && $blog_id != SITE_ID_CURRENT_SITE) {
            $kpi_prefix .= $blog_id.'_';
        }
        foreach ($users as $key => $user) {
            $user_count_month = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_to_date', true);
            $user_count_month_1 = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_1', true);
            $user_count_month_2 = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_2', true);
            $user_count_year = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_0', true);
            $user_count_year_1 = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_1', true);
            $user_count_year_2 = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_2', true);

            $count_month = $count_month + $user_count_month;
            $count_month_1 = $count_month_1 + $user_count_month_1;
            $count_month_2 = $count_month_2 + $user_count_month_2;
            $count_year = $count_year + $user_count_year;
            $count_year_1 = $count_year_1 + $user_count_year_1;
            $count_year_2 = $count_year_2 + $user_count_year_2;
        }
        $summary['month'] = $count_month;
        $summary['month_1'] = $count_month_1;
        $summary['month_2'] = $count_month_2;
        $summary['year'] = $count_year;
        $summary['year_1'] = $count_year_1;
        $summary['year_2'] = $count_year_2;

        return $summary;
    }

    private function total_live_donors_rule() {
        $users = $this->users;

        $count_month = 0;
        $count_month_1 = 0;
        $count_month_2 = 0;
        $count_year = 0;
        $count_year_1 = 0;
        $count_year_2 = 0;

        $summary = array();

        global $blog_id;
        $kpi_prefix = 'kpi_';
        if (is_multisite() && $blog_id != SITE_ID_CURRENT_SITE) {
            $kpi_prefix .= $blog_id.'_';
        }
        foreach ($users as $key => $user) {
            $donations_month = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_to_date', true);
            $donations_month_1 = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_1', true);
            $donations_month_2 = get_user_meta($user->ID, $kpi_prefix.'donation_count_month_2', true);
            $donations_year = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_0', true);
            $donations_year_1 = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_1', true);
            $donations_year_2 = get_user_meta($user->ID, $kpi_prefix.'donation_count_fy_2', true);

            if ($donations_month > 0) {
                $count_month = $count_month + 1;
            }
            if ($donations_month_1 > 0) {
                $count_month_1 = $count_month_1 + 1;
            }
            if ($donations_month_2 > 0) {
                $count_month_2 = $count_month_2 + 1;
            }

            if ($donations_year > 0) {
                $count_year = $count_year + 1;
            }
            if ($donations_year_1 > 0) {
                $count_year_1 = $count_year_1 + 1;
            }
            if ($donations_year_2 > 0) {
                $count_year_2 = $count_year_2 + 1;
            }
        }
        $summary['month'] = $count_month;
        $summary['month_1'] = $count_month_1;
        $summary['month_2'] = $count_month_2;
        $summary['year'] = $count_year;
        $summary['year_1'] = $count_year_1;
        $summary['year_2'] = $count_year_2;

        return $summary;
    }

    /**
     * Get donor report data
     * @param integer $year
     * @param integer $month
     * @param array $segments List of segment IDs to include
     */
    public static function get_donor_report($year, $month, array $segments = array()) {
        global $wpdb;

        $summary_table_name = self::get_summary_table_name();

        $where = "$summary_table_name.year = '$year' AND $summary_table_name.month = '$month'";

        $segment_string = implode(',', $segments);
        if (!empty($segment_string)) {
            $where .= " AND $summary_table_name.segment_id IN (".$segment_string.")";
        }

        $report_query = "
                SELECT $summary_table_name.rule AS rule,
                    SUM($summary_table_name.current_year) AS current_year,
                    SUM($summary_table_name.previous_year_1) AS previous_year_1,
                    SUM($summary_table_name.previous_year_2) AS previous_year_2,
                    SUM($summary_table_name.current_month) AS current_month,
                    SUM($summary_table_name.previous_month_1) AS previous_month_1,
                    SUM($summary_table_name.previous_month_2) AS previous_month_2
                FROM $summary_table_name
                WHERE $where
                GROUP BY rule
        ";

        $results = $wpdb->get_results($report_query);
        uasort($results, array(self, 'sort_results_by_rule'));
        self::format_results($results);

        return $results;
    }

    /**
     * Get segment report data
     * @param string $month YYYY-MM
     * @param string $rule Which rule to get results for
     */
    public static function get_segment_report($year, $month, $rule) {
        global $wpdb;

        $summary_table_name = self::get_summary_table_name();

        $where = "$summary_table_name.year = '$year' AND $summary_table_name.month = '$month' AND $summary_table_name.rule = '$rule'";

        $report_query = "
                SELECT segment_name,
                    current_year,
                    previous_year_1,
                    previous_year_2,
                    current_month,
                    previous_month_1,
                    previous_month_2
                FROM $summary_table_name
                WHERE $where
                ORDER BY segment_name ASC
        ";

        $results = $wpdb->get_results($report_query);
        self::format_results($results, $rule);

        return $results;
    }

    /**
     * Get donation history for dashboard widget
     */
    public static function get_dashboard_donations() {
        global $wpdb;

        $summary_table_name = self::get_summary_table_name();

        $report_query = "
                SELECT rule, year, month,
                    SUM(current_month) AS current_month
                FROM $summary_table_name
                WHERE rule IN ('total_donations', 'average_donation', 'donation_count')
                GROUP BY rule, year, month
                ORDER BY year ASC, month ASC
        ";

        $results = $wpdb->get_results($report_query);

        $donations = array();
        foreach ($results as $row) {
            $donations[date('F Y', strtotime($row->year.'-'.$row->month.'-01'))][$row->rule] = $row->current_month;
        }

        return $donations;
    }

    /**
     * Get contacts history for dashboard widget
     */
    public static function get_dashboard_contacts() {
        global $wpdb;

        $summary_table_name = self::get_summary_table_name();

        $report_query = "
                SELECT rule, year, month,
                    SUM(current_month) AS current_month
                FROM $summary_table_name
                WHERE rule IN ('new_contacts', 'new_donors', 'lapsed_donors')
                GROUP BY rule, year, month
                ORDER BY year ASC, month ASC
        ";

        $results = $wpdb->get_results($report_query);

        $contacts = array();
        foreach ($results as $row) {
            $contacts[date('F Y', strtotime($row->year.'-'.$row->month.'-01'))][$row->rule] = $row->current_month;
        }

        return $contacts;
    }

    private static function sort_results_by_rule($a, $b) {
        $rule_order = array_flip(array_keys(self::get_rules()));

        return $rule_order[$a->rule] >= $rule_order[$b->rule] ? 1 : -1;
    }

    private static function format_results(array &$results, $global_rule = null) {
        $vars = array(
                'current_year',
                'previous_year_1',
                'previous_year_2',
                'current_month',
                'previous_month_1',
                'previous_month_2',
        );

        // Fix up averages
        foreach ($results as &$result) {
            if (!empty($result->rule)) {
                switch ($result->rule) {
                    case 'total_donations':
                        $totals =& $result;
                        break;
                    case 'donation_count':
                        $counts =& $result;
                        break;
                    case 'average_donation':
                        $averages =& $result;
                        break;
                }
            }
        }
        if (isset($totals) && isset($counts) && isset($averages)) {
            foreach ($vars as $var) {
                $averages->$var = number_format($totals->$var/$counts->$var, 2);
            }
        }

    	if (!class_exists('RGCurrency')) {
    		require_once(ABSPATH.'/'.PLUGINDIR.'/gravityforms/currency.php');
    	}
        $currencyOptions = get_option('bb-currency-options-group');
    	$currency = new RGCurrency($currencyOptions['bb_currency_code']);

        // Now format the values
        foreach ($results as &$result) {
            $rule = $global_rule;
            if (empty($rule)) {
                $rule = $result->rule;
            }
            if (strpos($rule, 'donation') !== false && strpos($rule, 'count') === false) { // Dollar amount
                foreach ($vars as $var) {
                    $result->$var = $currency->to_money($result->$var);
                }
            } else { // Non-dollar amount
                foreach ($vars as $var) {
                    $result->$var = (int)$result->$var;
                }
            }
        }
    }

    /**
     * Get DB table name
     * @return string
     */
    public static function get_summary_table_name() {
        global $wpdb;
        return $wpdb->prefix.'bbconnect_monthly_history';
    }

    public static function get_user_history_table_name() {
        global $wpdb;
        return $wpdb->prefix.'bbconnect_user_history';
    }

    public static function get_transactions_by_source_table_name() {
        global $wpdb;
        return $wpdb->prefix.'bbconnect_monthly_source_history';
    }
}
