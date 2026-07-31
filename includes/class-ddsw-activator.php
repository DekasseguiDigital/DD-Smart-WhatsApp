<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Activator
{
    public const DB_VERSION = '1.2.0';

    public static function init_multisite_hooks()
    {
        add_action('wp_initialize_site', [self::class, 'initialize_new_site']);
    }

    public static function activate($network_wide = false)
    {
        if (is_multisite() && $network_wide) {
            $site_ids = get_sites(['fields' => 'ids']);

            foreach ($site_ids as $site_id) {
                switch_to_blog((int) $site_id);
                self::create_table();
                DDSW_Settings::ensure_defaults();
                restore_current_blog();
            }

            return;
        }

        self::create_table();
        DDSW_Settings::ensure_defaults();
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('ddsw_daily_cleanup');
    }

    public static function create_table()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            button_id varchar(80) NOT NULL DEFAULT '',
            action_id varchar(80) NOT NULL DEFAULT '',
            action_type varchar(40) NOT NULL DEFAULT '',
            event_type varchar(60) NOT NULL DEFAULT 'dd_smart_whatsapp_click',
            copy_status varchar(20) NOT NULL DEFAULT '',
            whatsapp_opened tinyint(1) unsigned NOT NULL DEFAULT 0,
            device varchar(20) NOT NULL DEFAULT '',
            page_url text NULL,
            referrer text NULL,
            user_agent text NULL,
            ip_hash char(64) NOT NULL DEFAULT '',
            clicked_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY button_id (button_id),
            KEY action_id (action_id),
            KEY action_type (action_type),
            KEY event_type (event_type),
            KEY copy_status (copy_status),
            KEY clicked_at (clicked_at)
        ) {$charset_collate};";

        dbDelta($sql);
        update_option('ddsw_db_version', self::DB_VERSION, false);
    }

    public static function maybe_upgrade()
    {
        $version = get_option('ddsw_db_version', '0');

        if (version_compare((string) $version, self::DB_VERSION, '<')) {
            self::create_table();
        }
    }

    public static function initialize_new_site($new_site)
    {
        if (!is_multisite() || empty($new_site->blog_id)) {
            return;
        }

        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!is_plugin_active_for_network(plugin_basename(DDSW_PLUGIN_FILE))) {
            return;
        }

        switch_to_blog((int) $new_site->blog_id);
        self::create_table();
        DDSW_Settings::ensure_defaults();
        restore_current_blog();
    }

    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . 'ddsw_clicks';
    }
}
