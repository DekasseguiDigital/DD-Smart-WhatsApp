<?php
/**
 * Uninstall handler for DD Smart WhatsApp.
 *
 * @package DDSmartWhatsApp
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = get_option('ddsw_settings', []);

if (!is_array($settings) || empty($settings['delete_on_uninstall'])) {
    return;
}

global $wpdb;

delete_option('ddsw_settings');
delete_option('ddsw_db_version');

if (is_multisite()) {
    $site_ids = get_sites(['fields' => 'ids']);

    foreach ($site_ids as $site_id) {
        switch_to_blog((int) $site_id);
        $table_name = $wpdb->prefix . 'ddsw_clicks';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        delete_option('ddsw_settings');
        delete_option('ddsw_db_version');
        restore_current_blog();
    }

    return;
}

$table_name = $wpdb->prefix . 'ddsw_clicks';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");
