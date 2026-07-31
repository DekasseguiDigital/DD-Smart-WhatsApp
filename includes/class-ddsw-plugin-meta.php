<?php
/**
 * Plugin list metadata and links.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Plugin_Meta
{
    public function init()
    {
        add_filter('plugin_action_links_' . DDSW_Version::basename(), [$this, 'action_links']);
        add_filter('plugin_row_meta', [$this, 'row_meta'], 10, 2);
    }

    public function action_links(array $links)
    {
        $custom = [
            'settings' => $this->link(
                admin_url('admin.php?page=dd-smart-whatsapp'),
                __('Configurações', 'dd-smart-whatsapp')
            ),
            'documentation' => $this->link(
                DDSW_Version::repository_url() . '#readme',
                __('Documentação', 'dd-smart-whatsapp')
            ),
            'support' => $this->link(
                DDSW_Version::repository_url() . '/issues',
                __('Suporte', 'dd-smart-whatsapp')
            ),
            'github' => $this->link(
                DDSW_Version::repository_url(),
                __('GitHub', 'dd-smart-whatsapp')
            ),
            'changelog' => $this->link(
                DDSW_Version::repository_url() . '/blob/main/CHANGELOG.md',
                __('Changelog', 'dd-smart-whatsapp')
            ),
        ];

        foreach ($custom as $key => $link) {
            if (!isset($links[$key])) {
                $links[$key] = $link;
            }
        }

        return array_merge($custom, $links);
    }

    public function row_meta(array $links, $file)
    {
        if (DDSW_Version::basename() !== $file) {
            return $links;
        }

        $meta = [
            'github' => $this->link(
                DDSW_Version::repository_url(),
                __('GitHub', 'dd-smart-whatsapp')
            ),
            'changelog' => $this->link(
                DDSW_Version::repository_url() . '/blob/main/CHANGELOG.md',
                __('Changelog', 'dd-smart-whatsapp')
            ),
        ];

        foreach ($meta as $key => $link) {
            if (!isset($links[$key])) {
                $links[$key] = $link;
            }
        }

        return $links;
    }

    private function link($url, $label)
    {
        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url($url),
            esc_html($label)
        );
    }
}
