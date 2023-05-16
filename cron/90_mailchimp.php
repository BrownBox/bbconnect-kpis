<?php
/**
 * MailChimp updates
 * Requires Connexions MailChimp add-on
 *
 * @var WP_User[] $users A list of all users for the current site
 * @var DateTime $today The current date
 * @var DateTime $yesterday The previous day
 * @var string $kpi_prefix Prefix for KPI fields
 */

// Requires MailChimp add-on >= 2.0.0
if (defined('BBCONNECT_MAILCHIMP_VERSION') && function_exists('bbconnect_mailchimp_get_client')) {
	$mailchimp = bbconnect_mailchimp_get_client();
	if ($mailchimp) {
		$list_id = get_option('bbconnect_mailchimp_list_id');

		remove_filter('update_user_metadata', 'bbconnect_mailchimp_update', 10);
		$user_count = 0;
		foreach ($users as $user) {
			// Skip non-Spark users unless on production
			if (!defined('WP_BB_ENV') || WP_BB_ENV != 'PRODUCTION') {
				if (strpos($user->user_email, 'sparkweb.com.au') === false) {
					continue;
				}
			}

			echo '	Checking details for '.$user->ID.' ('.$user->user_email.')'."\n";
			bbconnect_kpi_cron_flush();
			try {
				$is_subscribed = bbconnect_mailchimp_is_user_subscribed($user);
				if ($is_subscribed === true) {
					// Make sure the CRM knows they're subscribed
					update_user_meta($user->ID, 'bbconnect_bbc_subscription', 'true');

					// Update some meta in MailChimp
					$push_data = array_filter(apply_filters('bbconnect_kpi_cron_mailchimp_push_data', array(), $user, $kpi_prefix));

					if (!empty($push_data)) {
						echo '	Updating MailChimp meta for '.$user->ID.' ('.$user->user_email.') '."\n";
						var_dump($push_data);
						bbconnect_kpi_cron_flush();
						$mailchimp->lists->updateListMember($list_id, $user->user_email, array('merge_fields' => $push_data));
					}
				} elseif ($is_subscribed === false) {
					// Make sure the CRM knows they're not subscribed
					update_user_meta($user->ID, 'bbconnect_bbc_subscription', 'false');
				} else {
					echo '	Failed to retrieve subscription status for '.$user->ID.' ('.$user->user_email.') '."\n";
					bbconnect_kpi_cron_flush();
				}
			} catch (GuzzleHttp\Exception\ClientException $e) {
				echo '	MailChimp Error! '.$e->getResponse()->getBody()->getContents()."\n";
				bbconnect_kpi_cron_flush();
			} catch (Exception $e) {
				echo '	MailChimp Error! '.$e->getMessage()."\n";
				bbconnect_kpi_cron_flush();
			}

			$user_count++;
			if ($user_count % 100 == 0) {
				echo $user_count.' users processed'."\n";
				bbconnect_kpi_cron_flush();
			}
		}
		add_filter('update_user_metadata', 'bbconnect_mailchimp_update', 10, 5);
	}
}
