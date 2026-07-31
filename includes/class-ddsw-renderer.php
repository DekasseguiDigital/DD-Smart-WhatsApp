<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Renderer
{
    public static function render(array $atts = [])
    {
        $atts = shortcode_atts(
            [
                'id' => '',
                'label' => '',
                'phone' => '',
                'message' => '',
                'style' => '',
                'variant' => '',
                'class' => '',
                'target' => '',
                'mode' => '',
                'new_tab' => '',
                'modal_title' => '',
                'modal_success' => '',
                'desktop_instruction' => '',
                'ios_instruction' => '',
                'android_instruction' => '',
                'open_label' => '',
                'close_label' => '',
                'retry_label' => '',
                'error_message' => '',
                'auto_open' => '',
                'auto_open_delay' => '',
                'auto_close' => '',
                'auto_close_delay' => '',
                'hide_again' => '',
                'modal_style' => '',
                'background' => '',
                'color' => '',
                'hover_background' => '',
                'hover_color' => '',
                'border' => '',
                'border_color' => '',
                'border_width' => '',
                'radius' => '',
                'padding' => '',
                'padding_y' => '',
                'padding_x' => '',
                'align' => '',
                'width' => '',
                'full_width' => '',
                'icon' => '',
                'show_icon' => '',
                'icon_color' => '',
                'icon_hover_color' => '',
                'icon_size' => '',
                'icon_spacing' => '',
                'svg' => '',
                'shadow' => '',
                'hover_shadow' => '',
                'font_size' => '',
                'font_family' => '',
                'font_weight' => '',
                'text_transform' => '',
                'transition' => '',
                'custom_attributes' => '',
                'action_id' => '',
                'action_type' => '',
            ],
            $atts,
            'dd_smart_whatsapp'
        );

        $button = DDSW_Settings::get_button($atts['id']);
        if (!$button) {
            return '';
        }

        $settings = DDSW_Settings::get();
        $button = DDSW_Settings::normalize_button($button);
        $button_id = sanitize_key($button['id']);
        $template_locale = DDSW_Language::template_locale($button, 'frontend');
        $template_defaults = DDSW_Language::template_defaults($template_locale, $button['template_key'] ?? 'support');
        $label = $atts['label'] ? sanitize_text_field($atts['label']) : self::resolved_text($button, 'label', $template_defaults);
        $label = apply_filters('ddsw_button_label', $label, $button, $atts);
        $phone = $atts['phone'] ? DDSW_Settings::sanitize_phone($atts['phone']) : $button['phone'];
        $phone = apply_filters('ddsw_button_phone', $phone, $button, $atts);
        $message_template = $atts['message'] ? sanitize_textarea_field($atts['message']) : self::resolved_text($button, 'message', $template_defaults);
        $message = DDSW_Placeholders::replace($message_template);
        $message = apply_filters('ddsw_button_message', $message, $button, $atts);
        $style_name = self::style_name($atts, $button);
        $mode = DDSW_Settings::normalize_mode($atts['mode']) ?: $button['mode'];
        $modal_style = DDSW_Settings::normalize_modal_style($atts['modal_style']) ?: $button['modal_style'];
        $align = self::select_value($atts['align'], $button['align'], ['left', 'center', 'right']);
        $width = self::width_value($atts, $button);
        $show_icon = self::show_icon_value($atts, $button);

        $target = in_array($atts['target'], ['_blank', '_self'], true) ? $atts['target'] : $settings['default_target'];
        if ('' !== $atts['new_tab']) {
            $target = rest_sanitize_boolean($atts['new_tab']) ? '_blank' : '_self';
        }

        if (!$phone) {
            return current_user_can('manage_options')
                ? '<span class="ddsw-admin-warning">' . esc_html__('DD Smart WhatsApp: configure o telefone do botão.', 'dd-smart-whatsapp') . '</span>'
                : '';
        }

        self::enqueue_assets();

        $phone_for_url = preg_replace('/\D/', '', $phone);
        $base_url = 'https://wa.me/' . rawurlencode($phone_for_url);
        $traditional_url = $base_url . '?text=' . rawurlencode($message);
        $url = 'smart' === $mode ? $base_url : $traditional_url;
        $classes = array_filter([
            'ddsw-button',
            'ddsw-style-' . $style_name,
            'ddsw-button--width-' . $width,
            sanitize_html_class($atts['class']),
        ]);
        $wrap_classes = [
            'ddsw-wrap',
            'ddsw-align-' . $align,
            'ddsw-align-' . $width,
        ];
        $style = self::style_attr($atts, $button, $style_name, $width);

        $resolved_modal = DDSW_I18n::resolve_modal_strings($button, $atts);
        $modal = [
            'title' => $resolved_modal['title'],
            'description' => $resolved_modal['description'],
            'success' => $resolved_modal['success'],
            'desktop' => $resolved_modal['desktop'],
            'ios' => $resolved_modal['ios'],
            'android' => $resolved_modal['android'],
            'instruction' => $resolved_modal['instruction'],
            'openLabel' => $resolved_modal['openLabel'],
            'button' => $resolved_modal['button'],
            'closeLabel' => $resolved_modal['closeLabel'],
            'close' => $resolved_modal['close'],
            'retryLabel' => $resolved_modal['retryLabel'],
            'retry' => $resolved_modal['retry'],
            'errorMessage' => $resolved_modal['errorMessage'],
            'failed' => $resolved_modal['failed'],
            'copyFeedback' => $resolved_modal['copyFeedback'],
            'hideAgainLabel' => $resolved_modal['hideAgainLabel'],
            'autoOpen' => '' === $atts['auto_open'] ? !empty($button['auto_open']) : rest_sanitize_boolean($atts['auto_open']),
            'autoOpenDelay' => self::delay_value($atts['auto_open_delay'], $button['auto_open_delay']),
            'autoClose' => '' === $atts['auto_close'] ? !empty($button['auto_close']) : rest_sanitize_boolean($atts['auto_close']),
            'autoCloseDelay' => '' === $atts['auto_close_delay'] ? absint($button['auto_close_delay']) : absint($atts['auto_close_delay']),
            'hideAgain' => '' === $atts['hide_again'] ? !empty($button['hide_again']) : rest_sanitize_boolean($atts['hide_again']),
            'style' => $modal_style,
            'debug' => $resolved_modal['debug'],
        ];
        $modal = apply_filters('ddsw_button_modal', $modal, $button, $atts);

        $payload = [
            'id' => $button_id,
            'label' => $label,
            'message' => $message,
            'mode' => $mode,
            'url' => $url,
            'baseUrl' => $base_url,
            'target' => $target,
            'modal' => $modal,
            'style' => $style,
            'styleName' => $style_name,
            'actionId' => sanitize_key($atts['action_id']),
            'actionType' => sanitize_key($atts['action_type']),
        ];
        $payload = apply_filters('ddsw_button_payload', $payload, $button, $atts);

        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrap_classes)); ?>" data-ddsw-style="<?php echo esc_attr($style_name); ?>"<?php echo $style ? ' style="' . esc_attr($style) . '"' : ''; ?>>
            <a
                class="<?php echo esc_attr(implode(' ', $classes)); ?>"
                href="<?php echo esc_url($url); ?>"
                target="<?php echo esc_attr($target); ?>"
                rel="noopener noreferrer"
                aria-label="<?php echo esc_attr($label); ?>"
                data-ddsw-button
                data-ddsw-id="<?php echo esc_attr($button_id); ?>"
                data-ddsw-label="<?php echo esc_attr($label); ?>"
                data-ddsw-mode="<?php echo esc_attr($mode); ?>"
                data-ddsw-style="<?php echo esc_attr($style_name); ?>"
                <?php echo self::custom_attributes($atts['custom_attributes']); ?>
            >
                <?php if ($show_icon) : ?>
                    <span class="ddsw-icon ddsw-button__icon" aria-hidden="true"><?php echo self::icon_svg($atts['svg']); ?></span>
                <?php endif; ?>
                <span class="ddsw-label ddsw-button__label"><?php echo esc_html($label); ?></span>
            </a>
            <script type="application/json" class="ddsw-payload"><?php echo wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
        </div>
        <?php
        $html = trim(ob_get_clean());

        do_action('ddsw_button_rendered', $button_id, $payload, $atts);

        return apply_filters('ddsw_button_html', $html, $payload, $button, $atts);
    }

    public static function enqueue_assets()
    {
        \DD\SmartWhatsApp\Assets::enqueue_frontend();
    }

    private static function style_name(array $atts, array $button)
    {
        $style = DDSW_Settings::normalize_style($atts['style']);

        if ('' === $style && '' !== (string) $atts['variant']) {
            $style = DDSW_Settings::legacy_style_value($atts['variant']);
        }

        return $style ?: DDSW_Settings::normalize_style($button['style']);
    }

    private static function width_value(array $atts, array $button)
    {
        if ('' !== $atts['full_width']) {
            return rest_sanitize_boolean($atts['full_width']) ? 'full' : 'auto';
        }

        return self::select_value($atts['width'], $button['width'], ['auto', 'full']);
    }

    private static function show_icon_value(array $atts, array $button)
    {
        if ('' !== $atts['show_icon']) {
            return rest_sanitize_boolean($atts['show_icon']);
        }

        return '' === $atts['icon'] ? !empty($button['icon']) : rest_sanitize_boolean($atts['icon']);
    }

    private static function override_text($value, $fallback)
    {
        $value = is_string($value) ? sanitize_text_field($value) : '';

        return '' === $value ? $fallback : $value;
    }

    private static function resolved_text(array $button, $field, array $template_defaults)
    {
        $saved = isset($button[$field]) ? (string) $button[$field] : '';
        $fallback = isset($template_defaults[$field]) ? (string) $template_defaults[$field] : '';

        if (DDSW_Language::is_default_template_value($field, $saved)) {
            return $fallback;
        }

        return '' === trim($saved) ? $fallback : $saved;
    }

    private static function delay_value($value, $fallback)
    {
        $delay = '' === $value ? absint($fallback) : absint($value);

        return in_array($delay, [500, 1000, 1500, 2000], true) ? $delay : 1000;
    }

    private static function style_attr(array $atts, array $button, $style_name, $width)
    {
        $map = [
            'background' => ['--ddsw-background', 'color', 'visual'],
            'color' => ['--ddsw-color', 'color', 'visual'],
            'hover_background' => ['--ddsw-hover-background', 'color', 'visual'],
            'hover_color' => ['--ddsw-hover-color', 'color', 'visual'],
            'border_color' => ['--ddsw-border-color', 'color', 'visual'],
            'radius' => ['--ddsw-radius', 'px', 'radius'],
            'padding_y' => ['--ddsw-padding-y', 'px', 'padding'],
            'padding_x' => ['--ddsw-padding-x', 'px', 'padding'],
            'icon_color' => ['--ddsw-icon-color', 'color_optional', 'always'],
            'icon_hover_color' => ['--ddsw-icon-hover-color', 'color_optional', 'always'],
            'icon_size' => ['--ddsw-icon-size', 'px', 'always'],
            'icon_spacing' => ['--ddsw-icon-gap', 'px', 'always'],
            'shadow' => ['--ddsw-shadow', 'shadow', 'shadow'],
            'hover_shadow' => ['--ddsw-hover-shadow', 'shadow', 'shadow'],
            'font_size' => ['--ddsw-font-size', 'px', 'font_size'],
            'font_family' => ['--ddsw-font-family', 'font', 'font'],
            'font_weight' => ['--ddsw-font-weight', 'number', 'always'],
            'transition' => ['--ddsw-transition', 'ms', 'always'],
        ];
        $styles = [];

        if (!empty($atts['border'])) {
            $button['border_width'] = self::numeric_only($atts['border']);
        }

        if (!empty($atts['border_width'])) {
            $button['border_width'] = self::numeric_only($atts['border_width']);
        }

        if (!empty($atts['padding'])) {
            $padding = self::numeric_only($atts['padding']);
            $button['padding_y'] = $padding;
            $button['padding_x'] = $padding;
        }

        foreach ($map as $key => $config) {
            [$property, $type, $group] = $config;
            if (!self::should_emit_style($key, $group, $atts, $button, $style_name)) {
                continue;
            }

            $value = '' !== (string) $atts[$key] ? $atts[$key] : ($button[$key] ?? '');
            $value = self::css_value($value, $type);

            if ('' !== $value) {
                $styles[] = $property . ':' . $value;
            }
        }

        $border_width = '' !== (string) $atts['border_width'] ? $atts['border_width'] : ($button['border_width'] ?? '1');
        $styles[] = '--ddsw-border-width:' . self::css_value($border_width, 'px');
        $styles[] = '--ddsw-width:' . ('full' === $width ? '100%' : 'auto');
        $text_transform = self::select_value($atts['text_transform'] ?? '', $button['text_transform'] ?? 'none', ['none', 'uppercase', 'lowercase', 'capitalize']);
        $styles[] = '--ddsw-text-transform:' . $text_transform;

        return implode(';', $styles);
    }

    private static function should_emit_style($key, $group, array $atts, array $button, $style_name)
    {
        if ('' !== (string) ($atts[$key] ?? '')) {
            return true;
        }

        if ('custom' !== $style_name) {
            return in_array($group, ['always'], true);
        }

        $inherit_map = [
            'font' => 'inherit_font',
            'font_size' => 'inherit_font_size',
            'radius' => 'inherit_radius',
            'shadow' => 'inherit_shadow',
            'padding' => 'inherit_padding',
        ];

        if (isset($inherit_map[$group]) && !empty($button[$inherit_map[$group]])) {
            return false;
        }

        return true;
    }

    private static function css_value($value, $type)
    {
        $value = trim((string) $value);

        if ('' === $value) {
            return '';
        }

        if ('color' === $type) {
            return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $value) ? $value : '';
        }

        if ('color_optional' === $type) {
            return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $value) ? $value : '';
        }

        if ('px' === $type) {
            return self::numeric_only($value) . 'px';
        }

        if ('ms' === $type) {
            return self::numeric_only($value) . 'ms';
        }

        if ('number' === $type) {
            return self::numeric_only($value);
        }

        if ('shadow' === $type) {
            return preg_match('/^[a-zA-Z0-9#.,%() \-]+$/', $value) ? $value : 'none';
        }

        if ('font' === $type) {
            return preg_match('/^[a-zA-Z0-9 ,"\'\-_]+$/', $value) ? $value : 'inherit';
        }

        return '';
    }

    private static function numeric_only($value)
    {
        $value = preg_replace('/[^0-9.]/', '', (string) $value);

        return '' === $value ? '0' : $value;
    }

    private static function select_value($value, $fallback, array $allowed)
    {
        $value = sanitize_key((string) $value);
        $fallback = sanitize_key((string) $fallback);

        if (in_array($value, $allowed, true)) {
            return $value;
        }

        return in_array($fallback, $allowed, true) ? $fallback : $allowed[0];
    }

    private static function whatsapp_svg()
    {
        return '<svg viewBox="0 0 32 32" focusable="false" aria-hidden="true"><path fill="currentColor" d="M16.02 3.2A12.72 12.72 0 0 0 5.1 22.42L3.2 29l6.74-1.77A12.69 12.69 0 0 0 16.02 28.8h.01A12.8 12.8 0 0 0 16.02 3.2Zm0 23.43h-.01a10.6 10.6 0 0 1-5.4-1.48l-.39-.23-4 .99 1.07-3.89-.25-.4a10.57 10.57 0 1 1 8.98 5.01Zm5.8-7.93c-.32-.16-1.88-.93-2.17-1.03-.29-.11-.5-.16-.72.16-.21.32-.82 1.03-1.01 1.25-.19.21-.37.24-.69.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.59-1.9-1.78-2.22-.19-.32-.02-.49.14-.65.15-.15.32-.37.48-.56.16-.19.21-.32.32-.53.11-.21.05-.4-.03-.56-.08-.16-.72-1.73-.98-2.37-.26-.62-.52-.54-.72-.55h-.61c-.21 0-.56.08-.85.4-.29.32-1.12 1.09-1.12 2.66s1.15 3.09 1.31 3.3c.16.21 2.27 3.46 5.5 4.85.77.33 1.37.53 1.84.68.77.24 1.47.21 2.03.13.62-.09 1.88-.77 2.15-1.51.27-.75.27-1.39.19-1.52-.08-.13-.29-.21-.61-.37Z"/></svg>';
    }

    public static function icon_preview_svg()
    {
        return self::whatsapp_svg();
    }

    private static function icon_svg($svg)
    {
        if (!is_string($svg) || '' === trim($svg)) {
            return self::whatsapp_svg();
        }

        $allowed = [
            'svg' => [
                'viewbox' => true,
                'viewBox' => true,
                'xmlns' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'focusable' => true,
                'aria-hidden' => true,
                'role' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
        ];

        return wp_kses($svg, $allowed);
    }

    private static function custom_attributes($raw)
    {
        if (!is_string($raw) || '' === trim($raw)) {
            return '';
        }

        $attributes = [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);

        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            if (2 !== count($parts)) {
                continue;
            }

            [$name, $value] = $parts;
            $name = strtolower($name);

            if (!preg_match('/^(data-[a-z0-9_-]+|aria-[a-z0-9_-]+|role|title)$/', $name)) {
                continue;
            }

            $attributes[] = esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        return implode(' ', $attributes);
    }
}
