<?php
add_action('admin_menu', 'bbconnect_register_reports_menu_pages');
function bbconnect_register_reports_menu_pages() {
    add_submenu_page('users.php', 'Donor Reports', 'Donor Reports', 'list_users', 'donor_report_submenu','bbconnect_kpi_donor_report');
    add_submenu_page('users.php', 'Segment Report', 'Segment Report', 'list_users', 'segment_report_submenu','bbconnect_kpi_segment_report');
}

add_action('admin_enqueue_scripts', 'bbconnect_enqueue_date_picker');
function bbconnect_enqueue_date_picker() {
    //jQuery UI date picker file
    wp_enqueue_script('jquery-ui-datepicker');
    //jQuery UI theme css file
    wp_enqueue_style('envoyconnect-jquery-ui-css','http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css',false,"1.9.0",false);
}
