<?php
function bb_highcharts_scripts() {
	//load scripts if page is using the music template
	wp_register_script('highcharts', plugins_url( "js/highcharts.js", __FILE__ ), array('jquery'), null, true);
	wp_register_script('exporting', plugins_url( "js/modules/exporting.js", __FILE__ ), array('jquery'), null, true);
	

	wp_enqueue_script('highcharts');
	wp_enqueue_script('exporting');
}

add_action( 'admin_enqueue_scripts', 'bb_highcharts_scripts' );