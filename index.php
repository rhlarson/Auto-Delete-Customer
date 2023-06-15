<?php
/**
 * Plugin Name: Auto Delete Customer
 * Description: Auto Delete Customer plugin will delete users with the 'customer' role every minute
 * Version: 1.0
 * Author: Richard Larson
 * License: GNU General Public License v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

if (!wp_next_scheduled('my_dailyClearOut')) {
	wp_schedule_event(time(), 'daily', 'my_dailyClearOut');
}

function my_clearOldUsers() {
	global $wpdb;

	$query = $wpdb->prepare("
        SELECT ID 
        FROM wp_users
        WHERE 
            TIMESTAMPDIFF(MINUTE, user_registered, NOW()) > 1
            AND ID IN (
                SELECT user_id 
                FROM wp_usermeta
                WHERE meta_key = 'wp_capabilities' 
                AND meta_value LIKE '%customer%'
            )
    ");

	if ($oldUsers = $wpdb->get_results($query, ARRAY_N)) {
		foreach ($oldUsers as $user_id) {
			wp_delete_user($user_id[0]);
		}
	}
}

add_action('my_dailyClearOut', 'my_clearOldUsers');