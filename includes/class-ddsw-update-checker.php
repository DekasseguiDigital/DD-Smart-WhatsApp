<?php
/**
 * WordPress plugin update checker.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Update_Checker
{
    private $source;

    public function __construct($source = null)
    {
        $source = $source ?: new DDSW_GitHub_Update_Source();

        /**
         * Filters the update source object.
         *
         * Custom sources must expose a latest() method that returns normalized
         * release data or WP_Error.
         *
         * @param object $source Update source.
         */
        $this->source = apply_filters('ddsw_update_source', $source);
    }

    public function init()
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'filter_update_transient']);
        add_filter('plugins_api', [$this, 'filter_plugin_information'], 20, 3);
    }

    public function filter_update_transient($transient)
    {
        if (!is_object($transient)) {
            $transient = new stdClass();
        }

        if (empty($transient->checked) || !is_array($transient->checked)) {
            return $transient;
        }

        if (empty($transient->response) || !is_array($transient->response)) {
            $transient->response = [];
        }

        $release = $this->update_release();
        $plugin = DDSW_Version::basename();

        if (is_wp_error($release)) {
            unset($transient->response[$plugin]);
            return $transient;
        }

        if (empty($release['package'])) {
            unset($transient->response[$plugin]);
            return $transient;
        }

        if (!version_compare($release['version'], DDSW_Version::current(), '>')) {
            unset($transient->response[$plugin]);
            return $transient;
        }

        $transient->response[$plugin] = $this->update_payload($release);

        return $transient;
    }

    public function filter_plugin_information($result, $action, $args)
    {
        if ('plugin_information' !== $action || empty($args->slug) || DDSW_Version::slug() !== $args->slug) {
            return $result;
        }

        $release = $this->latest_release();
        if (is_wp_error($release)) {
            return $result;
        }

        return (object) [
            'name' => DDSW_Version::name(),
            'slug' => DDSW_Version::slug(),
            'version' => $release['version'] ?: DDSW_Version::current(),
            'author' => '<a href="https://dekasseguidigital.com">' . esc_html__('Dekassegui Digital', 'dd-smart-whatsapp') . '</a>',
            'homepage' => DDSW_Version::repository_url(),
            'requires' => '6.0',
            'requires_php' => '8.0',
            'tested' => '6.8',
            'download_link' => $release['package'],
            'last_updated' => $release['published_at'],
            'sections' => [
                'description' => esc_html__('Smart WhatsApp buttons with Smart Copy, floating actions, analytics, Elementor, Gutenberg and shortcodes.', 'dd-smart-whatsapp'),
                'changelog' => $this->release_notes($release),
            ],
        ];
    }

    private function update_release()
    {
        $release = $this->latest_release();
        if (is_wp_error($release)) {
            return $release;
        }

        /**
         * Filters normalized release data before WordPress receives an update.
         *
         * @param array $release Normalized release data.
         */
        return apply_filters('ddsw_update_release_data', $release);
    }

    private function latest_release()
    {
        if (!is_object($this->source) || !method_exists($this->source, 'latest')) {
            return new WP_Error('ddsw_update_source_invalid', __('Invalid DD Smart WhatsApp update source.', 'dd-smart-whatsapp'));
        }

        return $this->source->latest();
    }

    private function update_payload(array $release)
    {
        return (object) [
            'id' => DDSW_Version::repository_url(),
            'slug' => DDSW_Version::slug(),
            'plugin' => DDSW_Version::basename(),
            'new_version' => $release['version'],
            'url' => $release['url'],
            'package' => $release['package'],
            'icons' => [
                '1x' => DDSW_PLUGIN_URL . 'assets/icon-128x128.png',
                '2x' => DDSW_PLUGIN_URL . 'assets/icon-256x256.png',
            ],
            'banners' => [
                'low' => DDSW_PLUGIN_URL . 'assets/banner-772x250.png',
                'high' => DDSW_PLUGIN_URL . 'assets/banner-1544x500.png',
            ],
            'requires' => '6.0',
            'requires_php' => '8.0',
            'tested' => '6.8',
        ];
    }

    private function release_notes(array $release)
    {
        if (!empty($release['body'])) {
            return wp_kses_post(wpautop($release['body']));
        }

        return esc_html__('No changelog was provided for this release.', 'dd-smart-whatsapp');
    }
}
