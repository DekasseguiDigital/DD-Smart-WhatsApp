<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Placeholders
{
    public static function replace($message, array $context = [])
    {
        $post = get_post();
        $user = wp_get_current_user();

        $page_url = array_key_exists('page_url', $context) ? esc_url_raw($context['page_url']) : '{page_url}';
        $page_title = array_key_exists('page_title', $context) ? sanitize_text_field($context['page_title']) : '{page_title}';

        $values = [
            'site_title' => get_bloginfo('name'),
            'site_url' => home_url('/'),
            'page_title' => $page_title,
            'page_url' => $page_url,
            'post_id' => $post ? (string) $post->ID : '',
            'post_type' => $post ? get_post_type($post) : '',
            'user_name' => ($user && $user->exists()) ? $user->display_name : '',
            'user_email' => ($user && $user->exists()) ? $user->user_email : '',
            'date' => wp_date(get_option('date_format')),
            'time' => wp_date(get_option('time_format')),
            'browser' => '{browser}',
            'device' => '{device}',
            'language' => '{language}',
            'referrer' => '{referrer}',
            'utm_source' => '{utm_source}',
            'utm_medium' => '{utm_medium}',
            'utm_campaign' => '{utm_campaign}',
            'utm_content' => '{utm_content}',
            'utm_term' => '{utm_term}',
        ];

        foreach ($values as $key => $value) {
            $message = str_replace('{' . $key . '}', (string) $value, (string) $message);
        }

        return apply_filters('ddsw_placeholder_message', $message, $values, $context);
    }

    public static function available()
    {
        return [
            '{site_title}',
            '{site_url}',
            '{page_title}',
            '{page_url}',
            '{post_id}',
            '{post_type}',
            '{user_name}',
            '{user_email}',
            '{date}',
            '{time}',
            '{browser}',
            '{device}',
            '{language}',
            '{referrer}',
            '{utm_source}',
            '{utm_medium}',
            '{utm_campaign}',
            '{utm_content}',
            '{utm_term}',
        ];
    }

}
