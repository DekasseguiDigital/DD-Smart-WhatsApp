<?php
/**
 * Smart Floating Actions frontend renderer.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Floating_Actions
{
    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render'], 20);
    }

    public function enqueue_assets()
    {
        if (is_admin() || empty(DDSW_Settings::get_floating_hubs(true))) {
            return;
        }

        \DD\SmartWhatsApp\Assets::enqueue_frontend();
        wp_enqueue_style('ddsw-floating-actions');
        wp_enqueue_script('ddsw-floating-actions');
    }

    public function render()
    {
        if (is_admin()) {
            return;
        }

        $hubs = DDSW_Settings::get_floating_hubs(true);
        if (empty($hubs)) {
            return;
        }

        $this->enqueue_assets();

        foreach ($hubs as $hub) {
            $this->render_hub($hub);
        }
    }

    private function render_hub(array $hub)
    {
        $hub = DDSW_Settings::normalize_floating_hub($hub);
        $actions = array_values(array_filter($hub['actions'], static function ($action) {
            return !empty($action['visible']);
        }));

        if (empty($actions)) {
            return;
        }

        $hub_id = sanitize_key($hub['id']);
        $label = sprintf(
            /* translators: %s: floating hub name. */
            __('Abrir ações flutuantes: %s', 'dd-smart-whatsapp'),
            $hub['name']
        );
        $styles = [
            '--ddsw-float-x:' . absint($hub['offset_x']) . 'px',
            '--ddsw-float-y:' . absint($hub['offset_y']) . 'px',
            '--ddsw-float-size:' . absint($hub['size']) . 'px',
            '--ddsw-float-main:' . esc_attr($hub['main_color']),
            '--ddsw-float-bg:' . esc_attr($hub['background']),
            '--ddsw-float-hover:' . esc_attr($hub['hover_color']),
        ];
        ?>
        <div
            class="ddsw-floating-hub ddsw-floating-hub--<?php echo esc_attr($hub['layout']); ?> ddsw-floating-hub--<?php echo esc_attr($hub['position']); ?> ddsw-floating-hub--<?php echo esc_attr($hub['speed']); ?> ddsw-floating-hub--<?php echo esc_attr($hub['animation']); ?>"
            data-ddsw-floating-hub
            data-ddsw-floating-id="<?php echo esc_attr($hub_id); ?>"
            data-ddsw-show-labels="<?php echo esc_attr($hub['show_labels']); ?>"
            data-ddsw-mobile-behavior="<?php echo esc_attr($hub['mobile_behavior']); ?>"
            style="<?php echo esc_attr(implode(';', $styles)); ?>"
        >
            <button
                type="button"
                class="ddsw-floating-hub__trigger"
                aria-label="<?php echo esc_attr($label); ?>"
                aria-expanded="false"
                aria-controls="ddsw-floating-menu-<?php echo esc_attr($hub_id); ?>"
                data-ddsw-floating-toggle
            >
                <span class="ddsw-floating-hub__trigger-icon" aria-hidden="true"><?php echo self::icon_svg($hub['main_icon']); ?></span>
            </button>
            <div class="ddsw-floating-hub__menu" id="ddsw-floating-menu-<?php echo esc_attr($hub_id); ?>" role="menu" aria-hidden="true">
                <?php foreach ($actions as $index => $action) : ?>
                    <?php $this->render_action($hub, $action, $index); ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function render_action(array $hub, array $action, $index)
    {
        $action = DDSW_Settings::normalize_floating_action($action);
        $label = $action['name'];
        $style = '--ddsw-action-color:' . esc_attr($action['color']) . ';--ddsw-action-index:' . absint($index);

        if ('whatsapp' === $action['type']) {
            echo '<div class="ddsw-floating-action ddsw-floating-action--whatsapp" role="none" style="' . esc_attr($style) . '">';
            echo DDSW_Renderer::render([
                'id' => $action['button_id'],
                'mode' => 'smart',
                'class' => 'ddsw-floating-action__whatsapp',
                'label' => $label,
                'action_id' => $action['id'],
                'action_type' => $action['type'],
                'custom_attributes' => 'role|menuitem',
            ]);
            echo '</div>';
            return;
        }

        $message = $this->action_message($action);
        $url = $this->action_url($action, $message);
        if ('' === $url) {
            return;
        }

        $target = !empty($action['new_tab']) ? '_blank' : '_self';
        $smart_copy_type = self::smart_copy_type($action, $url);
        $uses_smart_copy = DDSW_Settings::supports_universal_smart_copy($smart_copy_type)
            && ('none' !== $action['message_mode'] || ('facebook' === $action['type'] && 'messenger' === $smart_copy_type))
            && '' !== trim($message);
        $modal_action = array_merge($action, ['type' => $smart_copy_type]);
        $modal = DDSW_I18n::resolve_universal_copy_strings($modal_action);
        $payload = [
            'id' => sanitize_key($action['button_id']),
            'label' => $label,
            'message' => $message,
            'mode' => 'universal_smart',
            'messageMode' => $action['message_mode'],
            'platform' => $smart_copy_type,
            'url' => $url,
            'baseUrl' => $url,
            'target' => $target,
            'modal' => [
                'title' => $modal['title'],
                'description' => $modal['description'],
                'instruction' => $modal['instruction'],
                'button' => $modal['button'],
                'close' => $modal['close'],
                'success' => $modal['title'],
                'failed' => $modal['failed'],
                'retryLabel' => $modal['retry'],
                'copyFeedback' => $modal['copyFeedback'],
                'confirmTitle' => $modal['confirmTitle'],
                'confirmDescription' => $modal['confirmDescription'],
                'confirmInstruction' => $modal['confirmInstruction'],
                'toastTitle' => $modal['title'],
                'toastMessage' => $modal['instruction'],
                'style' => 'clean',
            ],
            'actionId' => sanitize_key($action['id']),
            'actionType' => sanitize_key($action['type']),
        ];
        $payload = apply_filters('ddsw_floating_action_payload', $payload, $action, $hub);
        ?>
        <a
            class="ddsw-floating-action ddsw-floating-action--<?php echo esc_attr($action['type']); ?>"
            href="<?php echo esc_url($url); ?>"
            target="<?php echo esc_attr($target); ?>"
            rel="noopener noreferrer"
            role="menuitem"
            style="<?php echo esc_attr($style); ?>"
            data-ddsw-floating-action
            data-ddsw-floating-hub-id="<?php echo esc_attr($hub['id']); ?>"
            data-ddsw-floating-action-id="<?php echo esc_attr($action['id']); ?>"
            data-ddsw-floating-action-type="<?php echo esc_attr($action['type']); ?>"
            data-ddsw-floating-action-label="<?php echo esc_attr($label); ?>"
            data-ddsw-floating-smart-copy="<?php echo esc_attr($uses_smart_copy ? '1' : '0'); ?>"
            aria-label="<?php echo esc_attr($label); ?>"
        >
            <span class="ddsw-floating-action__icon" aria-hidden="true"><?php echo self::icon_svg($action['icon']); ?></span>
            <span class="ddsw-floating-action__label"><?php echo esc_html($label); ?></span>
        </a>
        <?php if ($uses_smart_copy) : ?>
            <script type="application/json" class="ddsw-floating-action-payload"><?php echo wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
        <?php endif; ?>
        <?php
    }

    private function action_url(array $action, $message = '')
    {
        $url = trim((string) $action['url']);

        if ('' === $url) {
            return '';
        }

        if ('phone' === $action['type']) {
            return 'tel:' . preg_replace('/[^\d+]/', '', $url);
        }

        if ('email' === $action['type']) {
            if (!is_email($url)) {
                return esc_url_raw($url);
            }

            $suggestion = DDSW_Settings::floating_action_suggestion('email');
            $subject = isset($action['email_subject']) ? trim((string) $action['email_subject']) : '';
            if ('' === $subject) {
                $subject = isset($suggestion['subject']) ? (string) $suggestion['subject'] : $action['name'];
            }
            $subject = DDSW_Placeholders::replace($subject);

            $parts = [
                'subject' => $subject,
                'body' => $message,
            ];

            return 'mailto:' . sanitize_email($url) . '?' . http_build_query($parts, '', '&', PHP_QUERY_RFC3986);
        }

        if ('maps' === $action['type'] && '' !== $url && false === strpos($url, '://')) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($url);
        }

        return esc_url_raw($url);
    }

    private static function smart_copy_type(array $action, $url)
    {
        $type = sanitize_key((string) ($action['type'] ?? ''));

        if ('facebook' === $type && self::is_messenger_url($url)) {
            return 'messenger';
        }

        return $type;
    }

    private static function is_messenger_url($url)
    {
        $host = strtolower((string) wp_parse_url((string) $url, PHP_URL_HOST));
        $path = strtolower((string) wp_parse_url((string) $url, PHP_URL_PATH));

        if (in_array($host, ['m.me', 'www.m.me', 'messenger.com', 'www.messenger.com'], true)) {
            return true;
        }

        return false !== strpos($host, 'facebook.com') && 0 === strpos($path, '/messages');
    }

    private function action_message(array $action)
    {
        $message = isset($action['initial_message']) ? trim((string) $action['initial_message']) : '';

        if ('' === $message) {
            $message = $this->linked_button_message($action);
        }

        $message = DDSW_Placeholders::replace($message);

        return apply_filters('ddsw_floating_action_message', $message, $action);
    }

    private function linked_button_message(array $action)
    {
        $button = DDSW_Settings::get_button($action['button_id'] ?? '');

        if (!$button) {
            return '';
        }

        $button = DDSW_Settings::normalize_button($button);
        $locale = DDSW_Language::template_locale($button, 'frontend');
        $defaults = DDSW_Language::template_defaults($locale, $button['template_key'] ?? 'support');
        $saved = isset($button['message']) ? (string) $button['message'] : '';
        $fallback = isset($defaults['message']) ? (string) $defaults['message'] : '';

        if (DDSW_Language::is_default_template_value('message', $saved)) {
            return $fallback;
        }

        return '' === trim($saved) ? $fallback : $saved;
    }

    public static function icon_svg($icon)
    {
        $icon = sanitize_key($icon);
        $paths = [
            'whatsapp' => 'M16.02 3.2A12.72 12.72 0 0 0 5.1 22.42L3.2 29l6.74-1.77A12.69 12.69 0 0 0 16.02 28.8h.01A12.8 12.8 0 0 0 16.02 3.2Zm5.8 15.5c-.32-.16-1.88-.93-2.17-1.03-.29-.11-.5-.16-.72.16-.21.32-.82 1.03-1.01 1.25-.19.21-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.59-1.9-1.78-2.22-.19-.32-.02-.49.14-.65.15-.15.32-.37.48-.56.16-.19.21-.32.32-.53.11-.21.05-.4-.03-.56-.08-.16-.72-1.73-.98-2.37-.26-.62-.52-.54-.72-.55h-.61c-.21 0-.56.08-.85.4-.29.32-1.12 1.09-1.12 2.66s1.15 3.09 1.31 3.3c.16.21 2.27 3.46 5.5 4.85.77.33 1.37.53 1.84.68.77.24 1.47.21 2.03.13.62-.09 1.88-.77 2.15-1.51.27-.75.27-1.39.19-1.52-.08-.13-.29-.21-.61-.37Z',
            'phone' => 'M21.7 19.2c-1.5 1.5-1.5 3-4.4.1-2.9-2.9-1.4-2.9.1-4.4.9-.9-.7-3.5-1.7-4.5-.9-.9-2.6.1-3.4.9-2.2 2.2.6 7.5 4.6 11.5 4 4 9.3 6.8 11.5 4.6.8-.8 1.8-2.5.9-3.4-1-1-3.6-2.6-4.5-1.7Z',
            'email' => 'M4 8h24v16H4Zm2.4 2 9.6 7 9.6-7Zm-.4 12h20V12.7l-10 7.3-10-7.3Z',
            'instagram' => 'M11 4h10a7 7 0 0 1 7 7v10a7 7 0 0 1-7 7H11a7 7 0 0 1-7-7V11a7 7 0 0 1 7-7Zm5 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm7-1.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z',
            'facebook' => 'M18 29V17h4l.7-5H18V8.8c0-1.4.4-2.3 2.4-2.3H23V2.2C22.5 2.1 21 2 19.2 2 15.5 2 13 4.3 13 8.4V12H9v5h4v12Z',
            'telegram' => 'M29 5 3 15.2l7.4 2.3L13.2 26l4.2-6.1 7.4 5.5Z',
            'maps' => 'M16 2a9 9 0 0 0-9 9c0 6.8 9 19 9 19s9-12.2 9-19a9 9 0 0 0-9-9Zm0 12.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Z',
            'default' => 'M16 3a13 13 0 1 1 0 26 13 13 0 0 1 0-26Zm1 6h-2v8h8v-2h-6Z',
        ];
        $path = $paths[$icon] ?? $paths['default'];

        return '<svg viewBox="0 0 32 32" focusable="false" aria-hidden="true"><path fill="currentColor" d="' . esc_attr($path) . '"/></svg>';
    }
}
