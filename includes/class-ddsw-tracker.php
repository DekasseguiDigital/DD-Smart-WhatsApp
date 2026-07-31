<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Tracker
{
    public function init()
    {
        add_action('wp_ajax_ddsw_track_event', [$this, 'track_event']);
        add_action('wp_ajax_nopriv_ddsw_track_event', [$this, 'track_event']);
        add_action('wp_ajax_ddsw_track_click', [$this, 'track_event']);
        add_action('wp_ajax_nopriv_ddsw_track_click', [$this, 'track_event']);
    }

    public function track_event()
    {
        if (!check_ajax_referer('ddsw_click', 'nonce', false)) {
            wp_send_json_error(['message' => __('Requisição inválida.', 'dd-smart-whatsapp')], 403);
        }

        $button_id = isset($_POST['button_id']) ? sanitize_key(wp_unslash($_POST['button_id'])) : '';
        if (!$button_id) {
            wp_send_json_error(['message' => __('Botão inválido.', 'dd-smart-whatsapp')], 400);
        }

        $action_id = isset($_POST['action_id']) ? sanitize_key(wp_unslash($_POST['action_id'])) : '';
        $action_type = isset($_POST['action_type']) ? sanitize_key(wp_unslash($_POST['action_type'])) : '';
        if (!array_key_exists($action_type, DDSW_Settings::floating_action_types())) {
            $action_type = '';
        }

        $allowed_events = [
            'dd_smart_whatsapp_copy_success',
            'dd_smart_whatsapp_copy_error',
            'dd_smart_whatsapp_open',
            'dd_smart_whatsapp_click',
            'smart_copy_platform',
        ];

        $event_type = isset($_POST['event_type']) ? sanitize_key(wp_unslash($_POST['event_type'])) : 'dd_smart_whatsapp_click';
        if (!in_array($event_type, $allowed_events, true)) {
            $event_type = 'dd_smart_whatsapp_click';
        }

        $copy_status = isset($_POST['copy_status']) ? sanitize_key(wp_unslash($_POST['copy_status'])) : '';
        if (!in_array($copy_status, ['success', 'error', ''], true)) {
            $copy_status = '';
        }

        $device = isset($_POST['device']) ? sanitize_key(wp_unslash($_POST['device'])) : '';
        if (!in_array($device, ['desktop', 'ios', 'android'], true)) {
            $device = '';
        }

        do_action('ddsw_before_track_event', $button_id, $event_type, $copy_status, $device);

        global $wpdb;

        $wpdb->insert(
            DDSW_Activator::table_name(),
            [
                'button_id' => $button_id,
                'action_id' => $action_id,
                'action_type' => $action_type,
                'event_type' => $event_type,
                'copy_status' => $copy_status,
                'whatsapp_opened' => 'dd_smart_whatsapp_open' === $event_type ? 1 : 0,
                'device' => $device,
                'page_url' => isset($_POST['page_url']) ? esc_url_raw(wp_unslash($_POST['page_url'])) : '',
                'referrer' => isset($_POST['referrer']) ? esc_url_raw(wp_unslash($_POST['referrer'])) : '',
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])), 0, 500) : '',
                'ip_hash' => $this->ip_hash(),
                'clicked_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        wp_send_json_success(['tracked' => true]);
    }

    public static function stats()
    {
        global $wpdb;

        $table = DDSW_Activator::table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $today = (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE clicked_at >= %s", wp_date('Y-m-d 00:00:00'))
        );
        $last_7_total = (int) $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE clicked_at >= %s", wp_date('Y-m-d 00:00:00', strtotime('-6 days')))
        );
        $by_button = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT button_id, COUNT(*) as total, MAX(clicked_at) as last_click FROM {$table} GROUP BY button_id ORDER BY total DESC LIMIT 20",
            ARRAY_A
        );
        $by_action = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT action_id, action_type, COUNT(*) as total, MAX(clicked_at) as last_click FROM {$table} WHERE action_id <> '' GROUP BY action_id, action_type ORDER BY total DESC LIMIT 20",
            ARRAY_A
        );
        $by_event = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT event_type, COUNT(*) as total FROM {$table} GROUP BY event_type ORDER BY total DESC",
            ARRAY_A
        );
        $last_30_days = $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->prepare("SELECT DATE(clicked_at) as event_date, COUNT(*) as total FROM {$table} WHERE clicked_at >= %s GROUP BY DATE(clicked_at) ORDER BY event_date ASC", wp_date('Y-m-d 00:00:00', strtotime('-29 days'))),
            ARRAY_A
        );
        $last_click = $wpdb->get_var(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT MAX(clicked_at) FROM {$table}"
        );

        $event_totals = [];
        foreach ((array) $by_event as $event) {
            $event_totals[$event['event_type']] = (int) $event['total'];
        }

        $copy_success = $event_totals['dd_smart_whatsapp_copy_success'] ?? 0;
        $opens = $event_totals['dd_smart_whatsapp_open'] ?? 0;
        $conversion = $copy_success > 0 ? round(($opens / $copy_success) * 100, 2) : 0;

        return [
            'total' => $total,
            'today' => $today,
            'last_7_days_total' => $last_7_total,
            'by_button' => is_array($by_button) ? $by_button : [],
            'by_action' => is_array($by_action) ? $by_action : [],
            'by_event' => is_array($by_event) ? $by_event : [],
            'last_30_days' => is_array($last_30_days) ? $last_30_days : [],
            'smart_copy' => $copy_success,
            'traditional' => $event_totals['dd_smart_whatsapp_click'] ?? 0,
            'open' => $opens,
            'conversion' => $conversion,
            'button_champion' => isset($by_button[0]) ? $by_button[0] : null,
            'action_champion' => isset($by_action[0]) ? $by_action[0] : null,
            'last_click' => $last_click,
        ];
    }

    public static function csv_rows()
    {
        global $wpdb;

        $table = DDSW_Activator::table_name();

        return $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT button_id, action_id, action_type, event_type, copy_status, whatsapp_opened, device, page_url, referrer, clicked_at FROM {$table} ORDER BY clicked_at DESC LIMIT 5000",
            ARRAY_A
        );
    }

    public static function clear_stats()
    {
        global $wpdb;

        $table_name = DDSW_Activator::table_name();
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query("TRUNCATE TABLE {$table_name}");
    }

    private function ip_hash()
    {
        $settings = DDSW_Settings::get();
        if (empty($settings['hash_ip'])) {
            return '';
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $salt = defined('AUTH_SALT') ? AUTH_SALT : wp_salt('auth');

        return hash_hmac('sha256', $ip, $salt);
    }
}
