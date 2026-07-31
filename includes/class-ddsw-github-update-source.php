<?php
/**
 * GitHub Releases update source.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_GitHub_Update_Source
{
    private const TRANSIENT = 'ddsw_github_release';

    public function latest()
    {
        $cached = get_site_transient(self::TRANSIENT);
        if (is_array($cached)) {
            return $cached;
        }

        $release = $this->fetch_latest_release();
        if (is_wp_error($release)) {
            return $release;
        }

        $ttl = (int) apply_filters('ddsw_update_cache_ttl', 6 * HOUR_IN_SECONDS);
        set_site_transient(self::TRANSIENT, $release, max(MINUTE_IN_SECONDS, $ttl));

        return $release;
    }

    private function fetch_latest_release()
    {
        $endpoint = sprintf(
            'https://api.github.com/repos/%s/releases/latest',
            rawurlencode(DDSW_Version::repository())
        );
        $endpoint = str_replace('%2F', '/', $endpoint);

        /**
         * Filters the release API endpoint.
         *
         * This allows moving update metadata to a Dekassegui Digital server later
         * without changing the update checker.
         *
         * @param string $endpoint Release API endpoint.
         */
        $endpoint = (string) apply_filters('ddsw_update_api_url', $endpoint);

        $response = wp_remote_get(
            $endpoint,
            [
                'timeout' => 10,
                'headers' => [
                    chr(65) . 'ccept' => 'application/vnd.github+json',
                    chr(85) . 'ser-' . chr(65) . 'gent' => 'DD-Smart-WhatsApp/' . DDSW_Version::current(),
                ],
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if (200 !== $code) {
            return new WP_Error(
                'ddsw_update_http_error',
                sprintf(
                    /* translators: %d: HTTP status code. */
                    __('DD Smart WhatsApp update check failed with HTTP status %d.', 'dd-smart-whatsapp'),
                    $code
                )
            );
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data)) {
            return new WP_Error('ddsw_update_invalid_response', __('Invalid DD Smart WhatsApp update response.', 'dd-smart-whatsapp'));
        }

        return $this->normalize_release($data);
    }

    private function normalize_release(array $data)
    {
        $version = $this->normalize_version($data['tag_name'] ?? '');
        $package = $this->package_url($data);

        return [
            'version' => $version,
            'tag' => sanitize_text_field((string) ($data['tag_name'] ?? '')),
            'name' => sanitize_text_field((string) ($data['name'] ?? '')),
            'url' => esc_url_raw((string) ($data['html_url'] ?? DDSW_Version::repository_url())),
            'package' => $package,
            'body' => wp_kses_post((string) ($data['body'] ?? '')),
            'published_at' => sanitize_text_field((string) ($data['published_at'] ?? '')),
        ];
    }

    private function package_url(array $data)
    {
        $assets = isset($data['assets']) && is_array($data['assets']) ? $data['assets'] : [];

        foreach ($assets as $asset) {
            $name = isset($asset['name']) ? (string) $asset['name'] : '';
            $url = isset($asset['browser_download_url']) ? (string) $asset['browser_download_url'] : '';

            if ('' === $url || !preg_match('/\\.zip$/i', $name)) {
                continue;
            }

            if (false !== stripos($name, 'dd-smart-whatsapp')) {
                return esc_url_raw($url);
            }
        }

        $fallback = isset($data['zipball_url']) ? esc_url_raw((string) $data['zipball_url']) : '';

        /**
         * Filters the package URL used by the WordPress updater.
         *
         * Prefer an installable release asset that contains a dd-smart-whatsapp/
         * root directory.
         *
         * @param string $fallback GitHub fallback ZIP URL.
         * @param array  $data     Raw release payload.
         */
        return (string) apply_filters('ddsw_update_package_url', $fallback, $data);
    }

    private function normalize_version($version)
    {
        $version = trim((string) $version);
        $version = preg_replace('/^v/i', '', $version);

        return sanitize_text_field((string) $version);
    }
}
