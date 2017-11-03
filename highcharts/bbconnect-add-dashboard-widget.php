<?php
function bbconnect_donations_widget($post, $callback_args) {
    if (class_exists('bbconnectKpiReports')) {
        $donation_history = bbconnectKpiReports::get_dashboard_donations();
		if (!class_exists('RGCurrency')) {
			require_once(ABSPATH.'/'.PLUGINDIR.'/gravityforms/currency.php');
		}
        $currencyOptions = get_option('bb-currency-options-group');
        	$currency = RGCurrency::get_currency($currencyOptions['bb_currency_code']);

        echo '
        <script type="text/javascript">
            var donation_months = new Array('.count($donation_history).');
            var donation_totals = new Array('.count($donation_history).');
            var donation_averages = new Array('.count($donation_history).');
            var donation_counts = new Array('.count($donation_history).');
            var prefix = "'.html_entity_decode($currency['symbol_left']).'";
            var suffix = "'.html_entity_decode($currency['symbol_right']).'";
';

        $recent_donations = array_slice($donation_history, -18);
        $i = 0;
        $report_url = '/wp-admin/users.php?page=donor_report_submenu&month=';
        foreach ($recent_donations as $month => $donation_data) {
            $url_month = date('Y-n', strtotime($month));
        	  	echo 'donation_months['.$i.'] = "'.$month.'";'."\n";
        	  	echo 'donation_totals['.$i.'] = {y:'.(float)$donation_data["total_donations"].',url:"'.$report_url.$url_month.'"};'."\n";
        	  	echo 'donation_averages['.$i.'] = {y:'.(float)$donation_data["average_donation"].',url:"'.$report_url.$url_month.'"};'."\n";
        	  	echo 'donation_counts['.$i.'] = {y:'.(int)$donation_data["donation_count"].',url:"'.$report_url.$url_month.'"};'."\n";
        	  	$i++;
        }
        echo '
        </script>';
?>
        <style type="text/css">
            ${demo.css}
        </style>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('#donations_container').highcharts({
                    chart: {
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Donations per Month',
                        x: -20 //center
                    },
                    xAxis: {
                        categories: donation_months
                    },
                    yAxis: [{ // Primary yAxis
                        title: {
                            text: 'Total Donations (<?php echo $currencyOptions['bb_currency_code']; ?>)'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                        }],
                        min: 0
                    }, { // Secondary yAxis
                        title: {
                            text: 'Average Donation (<?php echo $currencyOptions['bb_currency_code']; ?>)',
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                        }],
                        min: 0
                    }, { // Secondary yAxis
                        title: {
                            text: 'No. of Donations',
                        },
                        labels: {
                            format: '{value}',
                        },
                        opposite: true,
                        min: 0
                    }],
                    tooltip: {
                        valuePrefix: prefix
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'left',
                        x: 120,
                        verticalAlign: 'top',
                        y: 20,
                        floating: true,
                        borderWidth: 0
                    },
                    plotOptions: {
                        series: {
                            cursor: 'pointer',
                            point: {
                                events: {
                                    click: function() {
                                        window.location.href = this.options.url;
                                    }
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Total Donations',
                        type: 'column',
                        data: donation_totals,
                        tooltip: {
                            valuePrefix: prefix
                        }
                    }, {
                        name: 'Average Donation',
                        type: 'column',
                        yAxis: 1,
                        data: donation_averages,
                        tooltip: {
                            valuePrefix: prefix
                        }
                    }, {
                        name: 'No. of Donations',
                        type: 'spline',
                        yAxis: 2,
                        data: donation_counts,
                        tooltip: {
                            valuePrefix: ''
                        }
                    }]
                });
            });
        </script>
<?php
        echo "<div id='donations_container' style='min-width: 310px; height: 400px; margin: 0 auto'></div>"."\n";
    }
}

function bbconnect_contacts_widget($post, $callback_args) {
    if (class_exists('bbconnectKpiReports')) {
        $contacts_history = bbconnectKpiReports::get_dashboard_contacts();
        $max_months = 18;

        echo '
        <script type="text/javascript">
            var contact_months = new Array('.min($max_months, count($contacts_history)).');
            var new_contacts = new Array('.min($max_months, count($contacts_history)).');
            var new_donors = new Array('.min($max_months, count($contacts_history)).');
            var lapsed_donors = new Array('.min($max_months, count($contacts_history)).');
';

        if (count($contacts_history) > $max_months) {
            $recent_contacts = array_slice($contacts_history, -$max_months);
        } else {
            $recent_contacts = $contacts_history;
        }
        $i = 0;
        $report_url = '/wp-admin/users.php?page=donor_report_submenu&month=';
        foreach ($recent_contacts as $month => $contact_data) {
            $url_month = date('Y-n', strtotime($month));
            echo 'contact_months['.$i.'] = "'.$month.'";'."\n";
            echo 'new_contacts['.$i.'] = {y:'.(int)$contact_data["new_contacts"].',url:"'.$report_url.$url_month.'"};'."\n";
            echo 'new_donors['.$i.'] = {y:'.(int)$contact_data["new_donors"].',url:"'.$report_url.$url_month.'"};'."\n";
            echo 'lapsed_donors['.$i.'] = {y:'.(int)$contact_data["lapsed_donors"].',url:"'.$report_url.$url_month.'"};'."\n";
            $i++;
        }
        echo '
        </script>';
?>
        <style type="text/css">
            ${demo.css}
        </style>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('#contacts_container').highcharts({
                    chart: {
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'Contacts per Month',
                        x: -20 //center
                    },
                    xAxis: {
                        categories: contact_months
                    },
                    yAxis: [{ // Primary yAxis
                        title: {
                            text: 'Contacts'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                        }],
                        min: 0
                    }],
                    legend: {
                        layout: 'vertical',
                        align: 'left',
                        x: 120,
                        verticalAlign: 'top',
                        y: 20,
                        floating: true,
                        borderWidth: 0
                    },
                    plotOptions: {
                        series: {
                            cursor: 'pointer',
                            point: {
                                events: {
                                    click: function() {
                                        window.location.href = this.options.url;
                                    }
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'New Contacts',
                        type: 'spline',
                        data: new_contacts
                    }, {
                        name: 'New Donors',
                        type: 'spline',
                        data: new_donors
                    }, {
                        name: 'Lapsed Donors',
                        type: 'spline',
                        data: lapsed_donors
                    }]
                });
            });
        </script>
<?php
        echo "<div id='contacts_container' style='min-width: 310px; height: 400px; margin: 0 auto'></div>"."\n";
    }
}

// Function used in the action hook
function bbconnect_add_dashboard_widgets() {
    wp_add_dashboard_widget('bbconnect_donations_widget', 'Monthly Donations', 'bbconnect_donations_widget');
    wp_add_dashboard_widget('bbconnect_contacts_widget', 'Monthly Contacts', 'bbconnect_contacts_widget');
}

// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'bbconnect_add_dashboard_widgets' );
