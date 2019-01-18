<?php
function bbconnect_kpi_updates() {
    // Get current version
    $dbv = get_option('bbconnect_kpi_db_version', 0);

    // If it's not the latest, run our updates
    if (version_compare($dbv, BBCONNECT_KPI_VERSION, '<')) {
        // List of versions that involved a DB update - each one must have a corresponding function below
        $db_versions = array(
                '0.2',
                '0.3',
                '0.3.2',
                '0.3.5',
        );

        foreach ($db_versions as $version) {
            if (version_compare($version, $dbv, '>')) {
                call_user_func('bbconnect_kpi_db_update_'.str_replace('.', '_', $version));
                update_option('bbconnect_kpi_db_version', $version);
            }
        }
        update_option('bbconnect_kpi_db_version', BBCONNECT_KPI_VERSION);
    }
}

function bbconnect_kpi_db_update_0_2() {
    $source = 'bbconnect-kpis';

    $field = array(
            array(
                    'source' => $source,
                    'meta_key' => 'recurring_donation',
                    'tag' => '',
                    'name' => __('Recurring Donor', 'bbconnect'),
                    'options' => array(
                            'admin' => true,
                            'user' => false,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'field_type' => 'checkbox',
                            'choices' => 'false',
                    ),
                    'help' => '',
            ),
            array(
                    'source' => $source,
                    'meta_key' => 'kpis',
                    'tag' => '',
                    'name' => __('KPIs', 'bbconnect'),
                    'options' => array(
                            'admin' => true,
                            'user' => false,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'field_type' => 'section',
                            'choices' => bbconnect_process_defaults(bbconnect_kpis_transaction_amount_fields(), 'section_kpis', 'kpis'),
                    ),
                    'help' => '',
            ),
    );

    $field_keys = array();

    foreach ($field as $key => $value) {
        $field_keys[] = $value['meta_key'];
        update_option('bbconnect_'.$value['meta_key'], $value);
    }

    $umo = get_option('_bbconnect_user_meta');
    if (!empty($field_keys)) {
        foreach ($umo as $uk => $uv) {
            // Add to column 2
            if ('column_2' == $uk) {
                foreach ($field_keys as $fk => $fv) {
                    $umo[$uk][] = 'bbconnect_' . $fv;
                }
                update_option('_bbconnect_user_meta', $umo);
            }
        }
    }

    $searches = array(
            'Segment - 000' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:13:"regular_donor";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:24:"kpi_last_donation_amount";s:8:"operator";s:0:"";s:5:"query";s:0:"";}}',
            'Segment - 010 - Recurring (Cume $1000+)' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:18:"recurring_donation";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 020 - Recurring (Cume $0.01 - $999.99)' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:18:"recurring_donation";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 030 - $10,000+ &amp; Multiple gifts' => 'a:5:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}}',
            'Segment - 040 - $5000 - $9999.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:7:"4999.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:5:"10000";}}',
            'Segment - 050 - $1000 - $4999.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:4:"5000";}}',
            'Segment - 060 - $500 - $999.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:6:"499.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 070 - $250 - $499.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:6:"249.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:3:"500";}}',
            'Segment - 080 - $100 - $249.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:3:"250";}}',
            'Segment - 090 - $50 - $99.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:5:"49.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 100 - $25 - $49.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:5:"24.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:2:"50";}}',
            'Segment - 110 - $15 - $24.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:5:"14.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:2:"25";}}',
            'Segment - 120 - $10.00 - $14.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:4:"9.99";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:2:"15";}}',
            'Segment - 130 - $0.01 - $9.99 last 24m &amp; multiple gifts' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:1:"0";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"gt";s:5:"query";s:4:"0.01";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_donation_amount_24m_rolling";s:8:"operator";s:2:"lt";s:5:"query";s:2:"10";}}',
            'Segment - 200 - Cume $1000+ - 1+ gift 24m - 36m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"730";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1096";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 201 - Cume $100 - $999.99 - 1+ gift 24m - 36m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"730";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1096";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 202 - Cume $30 - $99.99 - 1+ gift 24m - 36m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"730";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1096";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:6:"100.00";}}',
            'Segment - 210 - Cume ≥ $30 &#038; 1+ gift 24m - 36m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"730";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1096";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_is_multi_in_single_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 220 - Cume $1000+ - 1+ gift 36m - 48m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1095";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1461";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 221 - Cume $100 - $999.99 - 1+ gift 36m - 48m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1095";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1461";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 222 - Cume $30 - $99.99 - 1+ gift 36m - 48m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1095";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1461";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 230 - Cume ≥ $30 &#038; 1+ gift 36m - 48m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1095";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1461";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_is_multi_in_single_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 240 - Cume $1000+ - 1+ gift 48m - 60m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1460";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1826";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 241 - Cume $100 - $999.99 - 1+ gift 48m - 60m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1460";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1826";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 242 - Cume $30 - $99.9 - 1+ gift 48m - 60m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1460";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1826";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 250 - Cume ≥ $30 &amp; 1+ gift 48m - 60m ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1460";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1826";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:31:"kpi_is_multi_in_single_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 260 - Cume $1000+ - 1+ gift 60m - 72 ago' => 'a:9:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1825";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"2191";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}}',
            'Segment - 261 - Cume $100 - $999.99 1+ gift 60m - 72m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1825";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"2191";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 262 - Cume $30 - $99.99 - 1+ gift 60m - 72m ago' => 'a:10:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"29.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:34:"kpi_max_annual_donation_amount_5fy";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:4:"1825";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:4:"2191";}i:9;a:4:{s:4:"type";s:4:"user";s:5:"field";s:21:"kpi_is_multi_fin_year";s:8:"operator";s:2:"is";s:5:"query";s:4:"true";}i:10;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 300 - $1000+ &#038; 1 gift  0m - 6m ago' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}}',
            'Segment - 301 - $500 - $999.99 &#038; 1 gift  0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"499.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 302 - $250 - $499.99 &#038; 1 gift  0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"249.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"500";}}',
            'Segment - 303 - $100 - $250 &#038; 1 gift 0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"250";}}',
            'Segment - 304 - $50 - $99.99 &#038; 1 gift 0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"49.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 305 - $25 - $49.99 &#038; 1 gift 0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"24.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"50";}}',
            'Segment - 306 - $15 - $24.99 &#038; 1 gift 0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"14.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"25";}}',
            'Segment - 307 - $0.01 - $14.99 &#038; 1 gift 0m - 6m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:4:"0.01";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"15";}}',
            'Segment - 310 - $1000+  &#038; 1 gift 7m - 12m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}}',
            'Segment - 311 - $500 - $999.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"499.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 312 - $250 - $499.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"249.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"500";}}',
            'Segment - 313 - $100 - $249.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"250";}}',
            'Segment - 314 - $50 - $99.99  &#038; 1 gift 7m - 12m ag' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"49.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 315 - $25 - $49.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"24.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"50";}}',
            'Segment - 316 - $15 - $24.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"14.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"25";}}',
            'Segment - 317 - $0.01 - $14.99  &#038; 1 gift 7m - 12m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:4:"0.01";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"182";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"366";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:2:"15";}}',
            'Segment - 320 - $1000+  &#038; 1 gift 13m - 24m ago' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:6:"999.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"365";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"731";}}',
            'Segment - 321 - $100 - $999.99 + &amp; 1 gift 13m - 24m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"99.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"365";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"731";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:4:"1000";}}',
            'Segment - 322 - $20 - $99.99 + &amp; 1 gift 13m - 24m ago' => 'a:8:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"gt";s:5:"query";s:5:"19.99";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"gt";s:5:"query";s:3:"365";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_days_since_last_donation";s:8:"operator";s:2:"lt";s:5:"query";s:3:"731";}i:8;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:3:"100";}}',
            'Segment - 400 - New Non-Donors added 0m - 3m' => 'a:6:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";a:2:{i:0;s:0:"";i:1;s:0:"";}}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"lt";s:5:"query";s:1:"1";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_days_since_created";s:8:"operator";s:2:"lt";s:5:"query";s:2:"91";}}',
            'Segment - 410 - New Non-Donors added 4m - 6m' => 'a:7:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:2:"is";s:5:"query";s:1:"0";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:6;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_days_since_created";s:8:"operator";s:2:"gt";s:5:"query";s:2:"90";}i:7;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_days_since_created";s:8:"operator";s:2:"lt";s:5:"query";s:3:"183";}}',
            'Segment - 999 - All Others' => 'a:5:{i:1;a:4:{s:4:"type";s:4:"user";s:5:"field";s:23:"kpi_first_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:2;a:4:{s:4:"type";s:4:"user";s:5:"field";s:22:"kpi_last_donation_date";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:3;a:4:{s:4:"type";s:4:"user";s:5:"field";s:28:"kpi_donation_amount_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:4;a:4:{s:4:"type";s:4:"user";s:5:"field";s:27:"kpi_donation_count_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}i:5;a:4:{s:4:"type";s:4:"user";s:5:"field";s:29:"kpi_donation_average_lifetime";s:8:"operator";s:0:"";s:5:"query";s:0:"";}}',
    );
    foreach ($searches as $title => $content) {
        $post = array(
                'post_title'    => $title,
                'post_status'   => 'publish',
                'post_type'     => 'savedsearch',
                'post_content'  => $content,
        );
        $post_id = wp_insert_post($post);
        if ($post_id) {
            add_post_meta($post_id, 'private', 'false');
            add_post_meta($post_id, 'segment', 'true');
        }
    }
}

function bbconnect_kpi_db_update_0_3() {
    global $wpdb;
    $table_name_user_history = bbconnectKpiReports::get_user_history_table_name();
    $table_name_giving_summary = bbconnectKpiReports::get_summary_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    $sql_user_history = "
        CREATE TABLE IF NOT EXISTS $table_name_user_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            segment_id mediumint(9) NOT NULL,
            transaction_count_today mediumint(9) NOT NULL DEFAULT 0,
            transaction_total_amount_today decimal(10,0) NOT NULL DEFAULT 0,
            lapsed_date TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY id (id),
            KEY user_id (user_id),
            KEY segment_id (segment_id)
        ) $charset_collate;";

    $sql_giving_summary = "
        CREATE TABLE IF NOT EXISTS $table_name_giving_summary (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            year MEDIUMINT(4) NOT NULL,
            month TINYINT(2) NOT NULL,
            rule varchar(100) NOT NULL,
            segment_id mediumint(9) NOT NULL,
            segment_name varchar(100) NOT NULL,
            current_year decimal(10,2) NOT NULL DEFAULT 0,
            previous_year_1 decimal(10,2) NOT NULL DEFAULT 0,
            previous_year_2 decimal(10,2) NOT NULL DEFAULT 0,
            current_month decimal(10,2) NOT NULL DEFAULT 0,
            previous_month_1 decimal(10,2) NOT NULL DEFAULT 0,
            previous_month_2 decimal(10,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            PRIMARY KEY id (id),
            KEY segment_id (segment_id),
            KEY date (year, month),
            KEY rule (rule)
        ) $charset_collate;";

    dbDelta($sql_user_history);
    dbDelta($sql_giving_summary);
}

function bbconnect_kpi_db_update_0_3_2() {
    $kpi_prefix = 'kpi_';
    if (is_multisite() && get_current_blog_id() != SITE_ID_CURRENT_SITE) {
        $kpi_prefix .= get_current_blog_id().'_';
    }
    $source = 'bbconnect-kpis';

    $field = array(
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'days_since_created',
                    'tag' => '',
                    'name' => __('Days Since Created', 'bbconnect'),
                    'options' => array(
                            'admin' => true,
                            'user' => false,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'field_type' => 'number',
                            'choices' => 'false',
                    ),
                    'help' => '',
            ),
    );

    $field_keys = array();

    foreach ($field as $key => $value) {
        $field_keys[] = $value['meta_key'];
        update_option('bbconnect_'.$value['meta_key'], $value);
    }

    $umo = get_option('_bbconnect_user_meta');
    if (!empty($field_keys)) {
        foreach ($umo as $uk => $uv) {
            // Add to the account info section
            foreach ($uv as $suk => $suv) {
                if ('bbconnect_account_information' == $suv) {
                    $acct = get_option($suv);
                    foreach ($field_keys as $fk => $fv) {
                        $acct['options']['choices'][] = $fv;
                    }
                    update_option($suv, $acct);
                    $aok = true;
                }
            }
            // If we couldn't find the account info section try the KPIs section
            if (!isset($aok)) {
                foreach ($uv as $suk => $suv) {
                    if ('bbconnect_kpis' == $suv) {
                        $kpis = get_option($suv);
                        foreach ($field_keys as $fk => $fv) {
                            $kpis['options']['choices'][] = $fv;
                        }
                        update_option($suv, $kpis);
                        $aok = true;
                    }
                }
            }
        }
        // If we couldn't find either section just add to column 3
        if (!isset($aok)) {
            foreach ($field_keys as $fk => $fv) {
                $umo['column_3'][] = 'bbconnect_' . $fv;
            }

            update_option('_bbconnect_user_meta', $umo);
        }
    }
}

function bbconnect_kpi_db_update_0_3_5() {
    $kpi_prefix = 'kpi_';
    if (is_multisite() && get_current_blog_id() != SITE_ID_CURRENT_SITE) {
        $kpi_prefix .= get_current_blog_id().'_';
    }
    $source = 'bbconnect-kpis';

    $field = array(
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'days_since_last_donation',
                    'tag' => '',
                    'name' => __('Days Since Last Donation', 'bbconnect'),
                    'options' => array(
                            'admin' => true,
                            'user' => false,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'field_type' => 'number',
                            'choices' => 'false',
                    ),
                    'help' => '',
            ),
    );

    $field_keys = array();

    foreach ($field as $key => $value) {
        $field_keys[] = $value['meta_key'];
        update_option('bbconnect_'.$value['meta_key'], $value);
    }

    $umo = get_option('_bbconnect_user_meta');
    if (!empty($field_keys)) {
        foreach ($umo as $uk => $uv) {
            // Add to the KPIs section
            foreach ($uv as $suk => $suv) {
                if ('bbconnect_kpis' == $suv) {
                    $acct = get_option($suv);
                    foreach ($field_keys as $fk => $fv) {
                        $acct['options']['choices'][] = $fv;
                    }
                    update_option($suv, $acct);
                    $aok = true;
                }
            }
            // If we couldn't find the account info section try the account info section
            if (!isset($aok)) {
                foreach ($uv as $suk => $suv) {
                    if ('bbconnect_account_information' == $suv) {
                        $kpis = get_option($suv);
                        foreach ($field_keys as $fk => $fv) {
                            $kpis['options']['choices'][] = $fv;
                        }
                        update_option($suv, $kpis);
                        $aok = true;
                    }
                }
            }
        }
        // If we couldn't find either section just add to column 3
        if (!isset($aok)) {
            foreach ($field_keys as $fk => $fv) {
                $umo['column_3'][] = 'bbconnect_' . $fv;
            }

            update_option('_bbconnect_user_meta', $umo);
        }
    }
}

function bbconnect_kpis_transaction_amount_fields() {
    $kpi_prefix = 'kpi_';
    if (is_multisite() && get_current_blog_id() != SITE_ID_CURRENT_SITE) {
        $kpi_prefix .= get_current_blog_id().'_';
    }
    $source = 'bbconnect-kpis';
    return array(
            array(
                    'source' => $source,
                    'meta_key' =>  $kpi_prefix.'first_donation_date',
                    'tag' => 'First Donation Date',
                    'name' => __( 'First Donation Date', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'date',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'last_donation_date',
                    'tag' => 'Last Donation Date',
                    'name' => __( 'Last Donation Date', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'date',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'last_donation_amount',
                    'tag' => 'Last Donation Amount',
                    'name' => __( 'Last Donation Amount', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_month_to_date',
                    'tag' => 'Number of donations so far this month',
                    'name' => __( 'Number of donations so far this month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_month_to_date',
                    'tag' => 'Total donation amount so far this month',
                    'name' => __( 'Total donation amount so far this month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_month_to_date',
                    'tag' => 'Average donation amount so far this month',
                    'name' => __( 'Average donation amount so far this month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_month_1',
                    'tag' => 'Number of donations last month',
                    'name' => __( 'Number of donations last month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_month_1',
                    'tag' => 'Total donation amount last month',
                    'name' => __( 'Total donation amount last month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_month_1',
                    'tag' => 'Average donation amount last month',
                    'name' => __( 'Average donation amount last month', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_month_2',
                    'tag' => 'Number of donations month before last',
                    'name' => __( 'Number of donations month before last', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_month_2',
                    'tag' => 'Total donation amount month before last',
                    'name' => __( 'Total donation amount month before last', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_month_2',
                    'tag' => 'Average donation amount month before last',
                    'name' => __( 'Average donation amount month before last', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_last_24h',
                    'tag' => 'Donation amount last 24 hours',
                    'name' => __( 'Donation amount last 24 hours', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_last_24h',
                    'tag' => 'Donation count last 24 hours',
                    'name' => __( 'Donation count last 24 hours', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_last_24h',
                    'tag' => 'Donation average last 24 hours',
                    'name' => __( 'Donation average last 24 hours', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_last_7d',
                    'tag' => 'Donation amount last 7 days',
                    'name' => __( 'Donation amount last 7 days', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_last_7d',
                    'tag' => 'Donation count last 7 days',
                    'name' => __( 'Donation count last 7 days', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_last_7d',
                    'tag' => 'Donation average last 7 days',
                    'name' => __( 'Donation average last 7 days', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_5y_rolling',
                    'tag' => 'Donation Count last 5 yrs rolling',
                    'name' => __( 'Donation Count last 5 yrs rolling', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_5fy',
                    'tag' => 'Donation Count last 5 FYs',
                    'name' => __( 'Donation Count last 5 FYs', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_lifetime',
                    'tag' => 'Donation Count Lifetime',
                    'name' => __( 'Donation Count Lifetime', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_5y_rolling',
                    'tag' => 'Donation Amount last 5 yrs rolling',
                    'name' => __( 'Donation Amount last 5 yrs rolling', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_5fy',
                    'tag' => 'Donation Amount last 5 FYs',
                    'name' => __( 'Donation Amount last 5 FYs', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_lifetime',
                    'tag' => 'Donation Amount Lifetime',
                    'name' => __( 'Donation Amount Lifetime', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_5y_rolling',
                    'tag' => 'Donation Average last 5 yrs rolling',
                    'name' => __( 'Donation Average last 5 yrs rolling', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_5fy',
                    'tag' => 'Donation Average last 5 FYs',
                    'name' => __( 'Donation Average last 5 FYs', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_lifetime',
                    'tag' => 'Donation Average Lifetime',
                    'name' => __( 'Donation Average Lifetime', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_5',
                    'tag' => 'Donation Count FY-5',
                    'name' => __( 'Donation Count FY-5', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_5',
                    'tag' => 'Donation Count FY-5',
                    'name' => __( 'Donation Count FY-5', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_4',
                    'tag' => 'Donation Count FY-4',
                    'name' => __( 'Donation Count FY-4', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_3',
                    'tag' => 'Donation Count FY-3',
                    'name' => __( 'Donation Count FY-3', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_2',
                    'tag' => 'Donation Count FY-2',
                    'name' => __( 'Donation Count FY-2', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_1',
                    'tag' => 'Donation Count FY-1',
                    'name' => __( 'Donation Count FY-1', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_fy_0',
                    'tag' => 'Donation Count FY-0',
                    'name' => __( 'Donation Count FY-0', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_24m_rolling',
                    'tag' => 'Donation count last 24 months',
                    'name' => __( 'Donation count last 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_24m_rolling',
                    'tag' => 'Donation amount last 24 months',
                    'name' => __( 'Donation amount last 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_24m_rolling',
                    'tag' => 'Donation average last 24 months',
                    'name' => __( 'Donation average last 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_12m_rolling_m_0',
                    'tag' => 'Donation Count last 12 months',
                    'name' => __( 'Donation Count last 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_12m_rolling_m_12',
                    'tag' => 'Donation Count same period - 12 months',
                    'name' => __( 'Donation Count same period - 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_12m_rolling_m_24',
                    'tag' => 'Donation Count same period - 24 months',
                    'name' => __( 'Donation Count same period - 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_12m_rolling_m_36',
                    'tag' => 'Donation Count same period - 36 months',
                    'name' => __( 'Donation Count same period - 36 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_count_12m_rolling_m_48',
                    'tag' => 'Donation Count same period - 48 months',
                    'name' => __( 'Donation Count same period - 48 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_5',
                    'tag' => 'Donation Amount FY-5',
                    'name' => __( 'Donation Amount FY-5', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_4',
                    'tag' => 'Donation Amount FY-4',
                    'name' => __( 'Donation Amount FY-4', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_3',
                    'tag' => 'Donation Amount FY-3',
                    'name' => __( 'Donation Amount FY-3', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_2',
                    'tag' => 'Donation Amount FY-2',
                    'name' => __( 'Donation Amount FY-2', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_1',
                    'tag' => 'Donation Amount FY-1',
                    'name' => __( 'Donation Amount FY-1', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_fy_0',
                    'tag' => 'Donation Amount FY-0',
                    'name' => __( 'Donation Amount FY-0', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_12m_rolling_m_0',
                    'tag' => 'Donation Amount last 12 months',
                    'name' => __( 'Donation Amount last 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_12m_rolling_m_12',
                    'tag' => 'Donation Amount same period - 12 months',
                    'name' => __( 'Donation Amount same period - 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_12m_rolling_m_24',
                    'tag' => 'Donation Amount same period - 24 months',
                    'name' => __( 'Donation Amount same period - 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_12m_rolling_m_36',
                    'tag' => 'Donation Amount same period - 36 months',
                    'name' => __( 'Donation Amount same period - 36 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_amount_12m_rolling_m_48',
                    'tag' => 'Donation Amount same period - 48 months',
                    'name' => __( 'Donation Amount same period - 48 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_5',
                    'tag' => 'Donation Average FY-5',
                    'name' => __( 'Donation Average FY-5', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_4',
                    'tag' => 'Donation Average FY-4',
                    'name' => __( 'Donation Average FY-4', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_3',
                    'tag' => 'Donation Average FY-3',
                    'name' => __( 'Donation Average FY-3', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_2',
                    'tag' => 'Donation Average FY-2',
                    'name' => __( 'Donation Average FY-2', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_1',
                    'tag' => 'Donation Average FY-1',
                    'name' => __( 'Donation Average FY-1', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_fy_0',
                    'tag' => 'Donation Average FY-0',
                    'name' => __( 'Donation Average FY-0', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_12m_rolling_m_0',
                    'tag' => 'Donation Average last 12 months',
                    'name' => __( 'Donation Average last 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_12m_rolling_m_12',
                    'tag' => 'Donation Average same period - 12 months',
                    'name' => __( 'Donation Average same period - 12 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_12m_rolling_m_24',
                    'tag' => 'Donation Average same period - 24 months',
                    'name' => __( 'Donation Average same period - 24 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_12m_rolling_m_36',
                    'tag' => 'Donation Average same period - 36 months',
                    'name' => __( 'Donation Average same period - 36 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'donation_average_12m_rolling_m_48',
                    'tag' => 'Donation Average same period - 48 months',
                    'name' => __( 'Donation Average same period - 48 months', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                            'choices' => array()
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'is_multi_fin_year',
                    'tag' => 'Is Multi Fin Year Donor',
                    'name' => __( 'Is Multi Fin Year Donor', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'checkbox',
                            'options' => false,
                            'choices' => 'false',
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'is_multi_in_single_fin_year',
                    'tag' => 'Is Multi Gift in Single Fin Year Donor',
                    'name' => __( 'Is Multi Gift in Single Fin Year Donor', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'checkbox',
                            'options' => false,
                            'choices' => 'false',
                    ),
                    'help' => ''
            ),
            array(
                    'source' => $source,
                    'meta_key' => $kpi_prefix.'max_annual_donation_amount_5fy' ,
                    'tag' => 'Max Annual Donation Amount (Last 5 FYs)',
                    'name' => __( 'Max Annual Donation Amount (Last 5 FYs)', 'bbconnect' ),
                    'options' => array(
                            'admin' => true,
                            'user' => true,
                            'signup' => false,
                            'reports' => true,
                            'public' => false,
                            'req' => false,
                            'unique' => true,
                            'field_type' => 'number',
                            'is_currency' => true,
                    ),
                    'help' => ''
            ),
    );
}
