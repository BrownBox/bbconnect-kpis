<?php
/**
 * MailChimp updates
 */
$api_key = BBCONNECT_MAILCHIMP_API_KEY;
$list_id = BBCONNECT_MAILCHIMP_LIST_ID;
$mailchimp = new BB\Mailchimp\Mailchimp($api_key);
$mailchimp_Lists = new BB\Mailchimp\Mailchimp_Lists($mailchimp);

$user_count = 0;
foreach ($users as $user) {
    // Skip anyone not in BB unless on production
    if (!defined('WP_BB_ENV') || WP_BB_ENV != 'PRODUCTION') {
        if (strpos($user->user_email, 'brownbox.net.au') === false) {
            continue;
        }
    }

    echo '    Getting groups for '.$user->ID.' ('.$user->user_email.')'."\n";
    bbconnect_kpi_cron_flush();
    try {
        $user_registered = $mailchimp->call('lists/member-info', array(
                'id'        => $list_id,
                'emails'    => array(array('email' => $user->user_email)),
        ));

        if ($user_registered['success_count'] > 0) {
            // Make sure the CRM knows they're subscribed
            update_user_meta($user->ID, 'bbconnect_bbc_subscription', 'true');

            // Update some meta in MailChimp
            $push_data = array();

            // Personalisation key
            if (function_exists('bbconnect_personalisation_get_key_for_user')) {
                $push_data['KEY'] = bbconnect_personalisation_get_key_for_user($user);
            }

            $push_data = apply_filters('bbconnect_kpi_cron_mailchimp_push_data', $push_data, $user, $kpi_prefix);

            if (!empty($push_data)) {
                echo '    Updating MailChimp meta for '.$user->ID.' ('.$user->user_email.') '."\n";
                bbconnect_kpi_cron_flush();
                $mailchimp_Lists->updateMember($list_id, array('email' => $user->user_email), $push_data, '', false);
            }

            // Update Groups
            $current_groupings = $user_registered['data'][0]['merges']['GROUPINGS'];

            $groupings = array();
            if (is_array($current_groupings)) {
                foreach ($current_groupings as $grouping) {
                    $this_grouping = array(
                            'name' => $grouping['name'],
                            'groups' => array(),
                    );
                    foreach ($grouping['groups'] as $group) {
                        if ($group['interested']) {
                            $this_grouping['groups'][] = $group['name'];
                        }
                    }
                    $groupings[] = $this_grouping;
                }
            }
            $existing_groupings = $groupings; // Checksum

            $mapped_category = get_option('bbconnect_mailchimp_channels_group');
            if (!empty($mapped_category)) {
                foreach ($groupings as &$grouping) {
                    if (strtolower($grouping['name']) == $mapped_category) {
                        // Make sure new users end up in all of the default subscription groups
                        if (strtotime($user->user_registered) >= $yesterday->getTimestamp() && empty($grouping['groups'])) {
                            $default_groups = get_option('bbconnect_mailchimp_optin_groups');
                            $mapped_groups = bbconnect_mailchimp_mapped_groups();
                            foreach ($mapped_groups as $mapped_group) {
                                if ($default_groups[$mapped_group['id']] == 'true') {
                                    $grouping['groups'][] = $mapped_group['name'];
                                    $meta_key = 'bbconnect_mailchimp_group_'.bbconnect_mailchimp_clean_group_name($mapped_category, $mapped_group['name']);
                                    update_user_meta($user->ID, $meta_key, 'true');
                                }
                            }
                        }
                    }
                }
            }

            $groupings = apply_filters('bbconnect_kpi_cron_mailchimp_groupings', $groupings, $user, $kpi_prefix, $push_data);

            // Only update MailChimp if something has changed
            if (maybe_serialize($existing_groupings) != maybe_serialize($groupings)) {
                echo '    Updating groups for '.$user->ID.' ('.$user->user_email.')'."\n";
                bbconnect_kpi_cron_flush();
                $mailchimp_Lists->updateMember($list_id, array('email' => $user->user_email), array('groupings' => $groupings));
            }
        }
    } catch (BB\Mailchimp\Mailchimp_Error $e) {
        if ($e instanceof BB\Mailchimp\Mailchimp_List_NotSubscribed) {
            // Make sure the CRM knows they're not subscribed
            update_user_meta($user->ID, 'bbconnect_bbc_subscription', 'false');
        }
        echo '    MailChimp Error! '.$e->getMessage()."\n";
        bbconnect_kpi_cron_flush();
    }

    $user_count++;
    if ($user_count % 100 == 0) {
        echo $user_count.' users processed'."\n";
        bbconnect_kpi_cron_flush();
    }
}
