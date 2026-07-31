<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Settings
{
    public const OPTION = 'ddsw_settings';

    public static function defaults()
    {
        $texts = DDSW_I18n::default_button_texts(DDSW_Language::site_locale());

        return [
            'admin_locale_source' => 'site',
            'wizard_completed' => '0',
            'ga4_enabled' => '1',
            'copy_feedback' => $texts['copy_feedback'],
            'copy_feedback_customized' => '0',
            'default_target' => '_blank',
            'debug_console' => defined('WP_DEBUG') && WP_DEBUG ? '1' : '0',
            'delete_on_uninstall' => '0',
            'hash_ip' => '1',
            'floating_actions_enabled' => '0',
            'floating_hubs' => [
                self::default_floating_hub(),
            ],
            'buttons' => [
                [
                    'id' => 'principal',
                    'template_locale' => '',
                    'label' => $texts['label'],
                    'phone' => '',
                    'message' => $texts['message'],
                    'style' => 'auto',
                    'variant' => 'primary',
                    'mode' => 'traditional',
                    'template_key' => 'support',
                    'template_version' => DDSW_VERSION,
                    'last_template' => DDSW_Language::site_locale() . '|support',
                    'modal_template_hash' => DDSW_I18n::modal_template_hash(DDSW_Language::site_locale(), 'support'),
                    'customized' => '0',
                    'modal_customized' => [],
                    'modal_title' => $texts['modal_title'],
                    'modal_success' => $texts['modal_success'],
                    'desktop_instruction' => $texts['desktop_instruction'],
                    'ios_instruction' => $texts['ios_instruction'],
                    'android_instruction' => $texts['android_instruction'],
                    'open_label' => $texts['open_label'],
                    'close_label' => $texts['close_label'],
                    'retry_label' => $texts['retry_label'],
                    'error_message' => $texts['error_message'],
                    'auto_open' => '0',
                    'auto_open_delay' => '1000',
                    'auto_close' => '0',
                    'auto_close_delay' => '5000',
                    'hide_again' => '0',
                    'modal_style' => 'clean',
                    'background' => '#25D366',
                    'color' => '#ffffff',
                    'hover_background' => '#128C7E',
                    'hover_color' => '#ffffff',
                    'border_color' => '#25D366',
                    'border_width' => '1',
                    'radius' => '8',
                    'shadow' => '0 10px 24px rgba(37, 211, 102, 0.24)',
                    'hover_shadow' => '0 14px 30px rgba(18, 140, 126, 0.28)',
                    'padding_y' => '14',
                    'padding_x' => '28',
                    'icon' => '1',
                    'icon_color' => '',
                    'icon_hover_color' => '',
                    'icon_size' => '20',
                    'icon_spacing' => '9',
                    'width' => 'auto',
                    'align' => 'left',
                    'font_size' => '16',
                    'font_family' => 'inherit',
                    'font_weight' => '700',
                    'text_transform' => 'none',
                    'transition' => '200',
                    'inherit_font' => '1',
                    'inherit_font_size' => '0',
                    'inherit_radius' => '1',
                    'inherit_shadow' => '0',
                    'inherit_padding' => '0',
                    'enabled' => '1',
                ],
            ],
        ];
    }

    public static function ensure_defaults()
    {
        if (false === get_option(self::OPTION, false)) {
            add_option(self::OPTION, self::defaults(), '', false);
        }
    }

    public static function get()
    {
        $settings = get_option(self::OPTION, []);
        $settings = is_array($settings) ? $settings : [];
        $settings = self::merge_defaults(self::defaults(), $settings);
        $settings['buttons'] = is_array($settings['buttons']) ? $settings['buttons'] : self::defaults()['buttons'];
        $settings['buttons'] = array_map([self::class, 'normalize_button'], $settings['buttons']);

        return $settings;
    }

    public static function get_buttons($enabled_only = false)
    {
        $settings = self::get();
        $buttons = isset($settings['buttons']) && is_array($settings['buttons']) ? $settings['buttons'] : [];

        if (!$enabled_only) {
            return array_values($buttons);
        }

        return array_values(array_filter($buttons, static function ($button) {
            return !empty($button['enabled']);
        }));
    }

    public static function get_button($id = '')
    {
        $buttons = self::get_buttons(true);
        $id = sanitize_key($id);

        if ($id) {
            foreach ($buttons as $button) {
                if (!empty($button['id']) && $button['id'] === $id) {
                    return self::normalize_button($button);
                }
            }
        }

        return isset($buttons[0]) ? self::normalize_button($buttons[0]) : null;
    }

    public static function sanitize($input)
    {
        $input = is_array($input) ? $input : [];
        $defaults = self::defaults();

        $sanitized = [
            'admin_locale_source' => (isset($input['admin_locale_source']) && 'user' === sanitize_key(wp_unslash($input['admin_locale_source']))) ? 'user' : 'site',
            'wizard_completed' => empty($input['wizard_completed']) ? '0' : '1',
            'ga4_enabled' => empty($input['ga4_enabled']) ? '0' : '1',
            'copy_feedback' => isset($input['copy_feedback']) ? sanitize_text_field(wp_unslash($input['copy_feedback'])) : $defaults['copy_feedback'],
            'copy_feedback_customized' => '0',
            'default_target' => (isset($input['default_target']) && '_self' === $input['default_target']) ? '_self' : '_blank',
            'debug_console' => defined('WP_DEBUG') && WP_DEBUG ? '1' : '0',
            'delete_on_uninstall' => empty($input['delete_on_uninstall']) ? '0' : '1',
            'hash_ip' => empty($input['hash_ip']) ? '0' : '1',
            'floating_actions_enabled' => empty($input['floating_actions_enabled']) ? '0' : '1',
            'floating_hubs' => [],
            'buttons' => [],
        ];
        $sanitized['copy_feedback_customized'] = DDSW_I18n::is_default_modal_value('copy_feedback', $sanitized['copy_feedback']) ? '0' : '1';

        $buttons = isset($input['buttons']) && is_array($input['buttons']) ? $input['buttons'] : [];
        $used_ids = [];

        foreach ($buttons as $index => $button) {
            if (!is_array($button)) {
                continue;
            }

            $label = isset($button['label']) ? sanitize_text_field(wp_unslash($button['label'])) : '';
            $phone = isset($button['phone']) ? self::sanitize_phone(wp_unslash($button['phone'])) : '';
            $message = isset($button['message']) ? sanitize_textarea_field(wp_unslash($button['message'])) : '';

            if ('' === $label && '' === $phone && '' === $message) {
                continue;
            }

            $id = isset($button['id']) ? sanitize_key(wp_unslash($button['id'])) : '';
            if ('' === $id) {
                $id = sanitize_key($label ?: 'botao-' . ((int) $index + 1));
            }

            if (isset($used_ids[$id])) {
                $id .= '-' . ((int) $index + 1);
            }
            $used_ids[$id] = true;

            $style = isset($button['style'])
                ? self::normalize_style(wp_unslash($button['style']))
                : self::legacy_style_value($button['variant'] ?? '');
            if ('' === $style) {
                $style = 'auto';
            }

            $variant = self::style_to_legacy_variant($style);

            $mode = isset($button['mode']) ? sanitize_key(wp_unslash($button['mode'])) : 'traditional';
            if (!in_array($mode, ['traditional', 'smart'], true)) {
                $mode = 'traditional';
            }

            $template_locale = isset($button['template_locale'])
                ? self::normalize_template_locale(wp_unslash($button['template_locale']))
                : '';
            $template_key = isset($button['template_key'])
                ? self::normalize_template_key(wp_unslash($button['template_key']))
                : 'support';
            $resolved_template_locale = DDSW_Language::template_locale(['template_locale' => $template_locale], 'admin');
            $template_texts = DDSW_I18n::default_button_texts($resolved_template_locale);
            $button_defaults = array_merge($defaults['buttons'][0], $template_texts);
            $modal_customized = self::modal_customized_values($button);
            $customized = in_array('1', $modal_customized, true) ? '1' : '0';
            $last_template = $resolved_template_locale . '|' . $template_key;

            $delay = isset($button['auto_open_delay']) ? absint($button['auto_open_delay']) : 1000;
            if (!in_array($delay, [500, 1000, 1500, 2000], true)) {
                $delay = 1000;
            }

            $modal_style = isset($button['modal_style']) ? sanitize_key(wp_unslash($button['modal_style'])) : 'clean';
            if (!in_array($modal_style, ['clean', 'soft', 'dark'], true)) {
                $modal_style = 'clean';
            }

            $align = isset($button['align']) ? sanitize_key(wp_unslash($button['align'])) : 'left';
            if (!in_array($align, ['left', 'center', 'right'], true)) {
                $align = 'left';
            }

            $width = isset($button['width']) ? sanitize_key(wp_unslash($button['width'])) : 'auto';
            if (!in_array($width, ['auto', 'full'], true)) {
                $width = 'auto';
            }

            $text_transform = isset($button['text_transform']) ? sanitize_key(wp_unslash($button['text_transform'])) : 'none';
            if (!in_array($text_transform, ['none', 'uppercase', 'lowercase', 'capitalize'], true)) {
                $text_transform = 'none';
            }

            $sanitized['buttons'][] = [
                'id' => $id,
                'template_locale' => $template_locale,
                'template_key' => $template_key,
                'label' => $label ?: $button_defaults['label'],
                'phone' => $phone,
                'message' => $message ?: $button_defaults['message'],
                'style' => $style,
                'variant' => $variant,
                'mode' => $mode,
                'template_version' => DDSW_VERSION,
                'last_template' => $last_template,
                'modal_template_hash' => DDSW_I18n::modal_template_hash($resolved_template_locale, $template_key),
                'customized' => $customized,
                'modal_customized' => $modal_customized,
                'modal_title' => self::text_value($button, 'modal_title', $button_defaults['modal_title']),
                'modal_success' => self::text_value($button, 'modal_success', $button_defaults['modal_success']),
                'desktop_instruction' => self::text_value($button, 'desktop_instruction', $button_defaults['desktop_instruction']),
                'ios_instruction' => self::text_value($button, 'ios_instruction', $button_defaults['ios_instruction']),
                'android_instruction' => self::text_value($button, 'android_instruction', $button_defaults['android_instruction']),
                'open_label' => self::text_value($button, 'open_label', $button_defaults['open_label']),
                'close_label' => self::text_value($button, 'close_label', $button_defaults['close_label']),
                'retry_label' => self::text_value($button, 'retry_label', $button_defaults['retry_label']),
                'error_message' => self::text_value($button, 'error_message', $button_defaults['error_message']),
                'auto_open' => empty($button['auto_open']) ? '0' : '1',
                'auto_open_delay' => (string) $delay,
                'auto_close' => empty($button['auto_close']) ? '0' : '1',
                'auto_close_delay' => self::number_value($button, 'auto_close_delay', $defaults['buttons'][0]['auto_close_delay'], 1000, 30000),
                'hide_again' => empty($button['hide_again']) ? '0' : '1',
                'modal_style' => $modal_style,
                'background' => self::color_value($button, 'background', $defaults['buttons'][0]['background']),
                'color' => self::color_value($button, 'color', $defaults['buttons'][0]['color']),
                'hover_background' => self::color_value($button, 'hover_background', $defaults['buttons'][0]['hover_background']),
                'hover_color' => self::color_value($button, 'hover_color', $defaults['buttons'][0]['hover_color']),
                'border_color' => self::color_value($button, 'border_color', $defaults['buttons'][0]['border_color']),
                'border_width' => self::number_value($button, 'border_width', $defaults['buttons'][0]['border_width'], 0, 12),
                'radius' => self::number_value($button, 'radius', $defaults['buttons'][0]['radius'], 0, 80),
                'shadow' => self::css_shadow_value($button, 'shadow', $defaults['buttons'][0]['shadow']),
                'hover_shadow' => self::css_shadow_value($button, 'hover_shadow', $defaults['buttons'][0]['hover_shadow']),
                'padding_y' => self::number_value($button, 'padding_y', $defaults['buttons'][0]['padding_y'], 0, 80),
                'padding_x' => self::number_value($button, 'padding_x', $defaults['buttons'][0]['padding_x'], 0, 120),
                'icon' => empty($button['icon']) ? '0' : '1',
                'icon_color' => self::color_value($button, 'icon_color', ''),
                'icon_hover_color' => self::color_value($button, 'icon_hover_color', ''),
                'icon_size' => self::number_value($button, 'icon_size', $defaults['buttons'][0]['icon_size'], 8, 64),
                'icon_spacing' => self::number_value($button, 'icon_spacing', $defaults['buttons'][0]['icon_spacing'], 0, 40),
                'width' => $width,
                'align' => $align,
                'font_size' => self::number_value($button, 'font_size', $defaults['buttons'][0]['font_size'], 10, 42),
                'font_family' => self::font_family_value($button, 'font_family', $defaults['buttons'][0]['font_family']),
                'font_weight' => self::number_value($button, 'font_weight', $defaults['buttons'][0]['font_weight'], 100, 900),
                'text_transform' => $text_transform,
                'transition' => self::number_value($button, 'transition', $defaults['buttons'][0]['transition'], 0, 1000),
                'inherit_font' => empty($button['inherit_font']) ? '0' : '1',
                'inherit_font_size' => empty($button['inherit_font_size']) ? '0' : '1',
                'inherit_radius' => empty($button['inherit_radius']) ? '0' : '1',
                'inherit_shadow' => empty($button['inherit_shadow']) ? '0' : '1',
                'inherit_padding' => empty($button['inherit_padding']) ? '0' : '1',
                'enabled' => empty($button['enabled']) ? '0' : '1',
            ];
        }

        if (empty($sanitized['buttons'])) {
            $sanitized['buttons'] = $defaults['buttons'];
        }

        $hubs = isset($input['floating_hubs']) && is_array($input['floating_hubs']) ? $input['floating_hubs'] : [];
        foreach ($hubs as $index => $hub) {
            if (!is_array($hub)) {
                continue;
            }

            $sanitized['floating_hubs'][] = self::sanitize_floating_hub($hub, $index);
        }

        if (empty($sanitized['floating_hubs'])) {
            $sanitized['floating_hubs'] = $defaults['floating_hubs'];
        }

        return $sanitized;
    }

    public static function maybe_upgrade()
    {
        $installed = get_option('ddsw_version', '0');

        if (version_compare((string) $installed, DDSW_VERSION, '>=')) {
            return;
        }

        $settings = get_option(self::OPTION, []);
        if (is_array($settings)) {
            $settings = self::migrate_modal_templates($settings);
            update_option(self::OPTION, $settings, false);
        }

        update_option('ddsw_version', DDSW_VERSION, false);
    }

    private static function migrate_modal_templates(array $settings)
    {
        $locale = DDSW_Language::site_locale();
        $defaults = DDSW_I18n::default_button_texts($locale);
        $modal_keys = self::modal_text_keys();

        if (isset($settings['copy_feedback'])) {
            $copy_custom = empty($settings['copy_feedback_customized'])
                ? !DDSW_I18n::is_default_modal_value('copy_feedback', $settings['copy_feedback'])
                : !empty($settings['copy_feedback_customized']);

            $settings['copy_feedback_customized'] = $copy_custom ? '1' : '0';
            if (!$copy_custom) {
                $settings['copy_feedback'] = $defaults['copy_feedback'];
            }
        }

        if (empty($settings['buttons']) || !is_array($settings['buttons'])) {
            return $settings;
        }

        foreach ($settings['buttons'] as $index => $button) {
            if (!is_array($button)) {
                continue;
            }

            $template_key = isset($button['template_key']) ? self::normalize_template_key($button['template_key']) : 'support';
            $customized = [];

            foreach ($modal_keys as $key) {
                $is_custom = isset($button['modal_customized'][$key])
                    ? !empty($button['modal_customized'][$key])
                    : !DDSW_I18n::is_default_modal_value($key, $button[$key] ?? '');

                $customized[$key] = $is_custom ? '1' : '0';
                if (!$is_custom) {
                    $settings['buttons'][$index][$key] = $defaults[$key] ?? '';
                }
            }

            $settings['buttons'][$index]['template_version'] = DDSW_VERSION;
            $settings['buttons'][$index]['last_template'] = $locale . '|' . $template_key;
            $settings['buttons'][$index]['modal_template_hash'] = DDSW_I18n::modal_template_hash($locale, $template_key);
            $settings['buttons'][$index]['modal_customized'] = $customized;
            $settings['buttons'][$index]['customized'] = in_array('1', $customized, true) ? '1' : '0';
        }

        return $settings;
    }

    public static function sanitize_phone($phone)
    {
        $phone = preg_replace('/[^\d+]/', '', (string) $phone);

        if (substr_count($phone, '+') > 1) {
            $phone = '+' . str_replace('+', '', $phone);
        }

        if ('+' !== substr($phone, 0, 1)) {
            $phone = str_replace('+', '', $phone);
        }

        return $phone;
    }

    public static function normalize_button(array $button)
    {
        $defaults = self::defaults();

        if (!array_key_exists('style', $button) || '' === (string) $button['style']) {
            $button['style'] = self::legacy_style_value($button['variant'] ?? '') ?: $defaults['buttons'][0]['style'];
        }

        $button = self::merge_defaults($defaults['buttons'][0], $button);
        $button['modal_customized'] = is_array($button['modal_customized']) ? $button['modal_customized'] : [];
        $button['style'] = self::normalize_style($button['style']) ?: $defaults['buttons'][0]['style'];
        $button['variant'] = self::style_to_legacy_variant($button['style']);

        return $button;
    }

    public static function normalize_mode($mode)
    {
        $mode = sanitize_key($mode);

        return in_array($mode, ['traditional', 'smart'], true) ? $mode : '';
    }

    public static function normalize_modal_style($style)
    {
        $style = sanitize_key($style);

        return in_array($style, ['clean', 'soft', 'dark'], true) ? $style : '';
    }

    public static function normalize_template_locale($locale)
    {
        $locale = DDSW_Language::normalize_locale($locale);

        return in_array($locale, DDSW_I18n::supported_template_locales(), true) ? $locale : '';
    }

    public static function normalize_template_key($key)
    {
        $key = sanitize_key((string) $key);

        return array_key_exists($key, DDSW_I18n::template_library(DDSW_Language::site_locale())) ? $key : 'support';
    }

    public static function normalize_style($style)
    {
        $style = sanitize_key((string) $style);
        $style = 'primary' === $style ? 'green' : $style;

        return in_array($style, ['auto', 'green', 'dark', 'light', 'outline', 'custom'], true) ? $style : '';
    }

    public static function get_floating_hubs($enabled_only = false)
    {
        $settings = self::get();
        $hubs = isset($settings['floating_hubs']) && is_array($settings['floating_hubs'])
            ? $settings['floating_hubs']
            : [self::default_floating_hub()];
        $hubs = array_map([self::class, 'normalize_floating_hub'], $hubs);

        if (!$enabled_only) {
            return array_values($hubs);
        }

        if (empty($settings['floating_actions_enabled'])) {
            return [];
        }

        return array_values(array_filter($hubs, static function ($hub) {
            return !empty($hub['enabled']);
        }));
    }

    public static function default_floating_hub()
    {
        return [
            'id' => 'principal',
            'name' => __('Botão Principal', 'dd-smart-whatsapp'),
            'enabled' => '0',
            'layout' => 'vertical',
            'position' => 'bottom-right',
            'offset_x' => '24',
            'offset_y' => '24',
            'speed' => 'normal',
            'main_icon' => 'whatsapp',
            'main_color' => '#25D366',
            'background' => '#ffffff',
            'hover_color' => '#128C7E',
            'size' => '58',
            'animation' => 'lift',
            'show_labels' => '1',
            'mobile_behavior' => 'labels',
            'actions' => [
                [
                    'id' => 'whatsapp',
                    'type' => 'whatsapp',
                    'name' => __('WhatsApp', 'dd-smart-whatsapp'),
                    'icon' => 'whatsapp',
                    'color' => '#25D366',
                    'url' => '',
                    'button_id' => 'principal',
                    'email_subject' => '',
                    'initial_message' => '',
                    'message_mode' => 'none',
                    'order' => '1',
                    'visible' => '1',
                    'new_tab' => '1',
                ],
            ],
        ];
    }

    public static function floating_layout_options()
    {
        return [
            'vertical' => __('Vertical', 'dd-smart-whatsapp'),
            'compact' => __('Lista compacta', 'dd-smart-whatsapp'),
        ];
    }

    public static function floating_action_types()
    {
        return [
            'whatsapp' => __('WhatsApp (Smart Copy)', 'dd-smart-whatsapp'),
            'phone' => __('Telefone', 'dd-smart-whatsapp'),
            'email' => __('Email', 'dd-smart-whatsapp'),
            'instagram' => __('Instagram', 'dd-smart-whatsapp'),
            'facebook' => __('Facebook', 'dd-smart-whatsapp'),
            'messenger' => __('Messenger', 'dd-smart-whatsapp'),
            'telegram' => __('Telegram', 'dd-smart-whatsapp'),
            'line' => __('LINE', 'dd-smart-whatsapp'),
            'maps' => __('Google Maps', 'dd-smart-whatsapp'),
            'form' => __('Formulário', 'dd-smart-whatsapp'),
            'booking' => __('Reservar', 'dd-smart-whatsapp'),
            'custom' => __('Link personalizado', 'dd-smart-whatsapp'),
        ];
    }

    public static function floating_message_modes()
    {
        return [
            'none' => __('Nenhuma', 'dd-smart-whatsapp'),
            'smart_auto' => __('Smart Copy automático', 'dd-smart-whatsapp'),
            'ask' => __('Sempre perguntar antes de copiar', 'dd-smart-whatsapp'),
        ];
    }

    public static function floating_action_suggestions()
    {
        return [
            'whatsapp' => [
                'message' => __(
                    "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.",
                    'dd-smart-whatsapp'
                ),
            ],
            'messenger' => [
                'message' => __(
                    "Hola {{name}},\n\nVi su página de Facebook desde el sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría indicarme disponibilidad, precios y cómo continuar?\n\nMuchas gracias.",
                    'dd-smart-whatsapp'
                ),
            ],
            'facebook' => [
                'message' => __(
                    "Hola {{name}},\n\nVi su página de Facebook desde el sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría indicarme disponibilidad, precios y cómo continuar?\n\nMuchas gracias.",
                    'dd-smart-whatsapp'
                ),
            ],
            'instagram' => [
                'message' => __(
                    "Hola {{name}} 👋\n\nEncontré su perfil de Instagram desde el sitio web.\n\nMe gustaría recibir más información sobre sus servicios y disponibilidad.\n\n¡Muchas gracias!",
                    'dd-smart-whatsapp'
                ),
            ],
            'email' => [
                'subject' => __('Consulta desde el sitio web', 'dd-smart-whatsapp'),
                'message' => __(
                    "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.",
                    'dd-smart-whatsapp'
                ),
            ],
        ];
    }

    public static function floating_action_suggestion($type)
    {
        $type = sanitize_key((string) $type);
        $suggestions = self::floating_action_suggestions();

        return $suggestions[$type] ?? [];
    }

    public static function supports_universal_smart_copy($type)
    {
        $type = sanitize_key((string) $type);

        return in_array($type, ['messenger', 'instagram', 'telegram', 'line', 'custom'], true);
    }

    public static function default_floating_message_mode($type)
    {
        $type = sanitize_key((string) $type);

        if (in_array($type, ['messenger', 'instagram', 'telegram', 'line'], true)) {
            return 'smart_auto';
        }

        return 'none';
    }

    public static function normalize_floating_hub(array $hub)
    {
        $defaults = self::default_floating_hub();
        $hub = self::merge_defaults($defaults, $hub);
        $hub['layout'] = self::select_key($hub['layout'], array_keys(self::floating_layout_options()), $defaults['layout']);
        $hub['position'] = self::select_key($hub['position'], ['bottom-right', 'bottom-left'], $defaults['position']);
        $hub['speed'] = self::select_key($hub['speed'], ['slow', 'normal', 'fast'], $defaults['speed']);
        $hub['animation'] = self::select_key($hub['animation'], ['lift', 'fade', 'scale'], $defaults['animation']);
        $hub['mobile_behavior'] = self::select_key($hub['mobile_behavior'], ['labels', 'icons'], $defaults['mobile_behavior']);
        $hub['actions'] = isset($hub['actions']) && is_array($hub['actions']) ? $hub['actions'] : $defaults['actions'];
        $hub['actions'] = array_map([self::class, 'normalize_floating_action'], $hub['actions']);
        usort($hub['actions'], static function ($a, $b) {
            return (int) $a['order'] <=> (int) $b['order'];
        });

        return $hub;
    }

    public static function normalize_floating_action(array $action)
    {
        $defaults = self::default_floating_hub()['actions'][0];
        $action = self::merge_defaults($defaults, $action);
        $action['type'] = self::select_key($action['type'], array_keys(self::floating_action_types()), 'custom');
        $action['id'] = sanitize_key($action['id'] ?: $action['type']);
        $action['icon'] = sanitize_key($action['icon'] ?: $action['type']);
        $action['button_id'] = sanitize_key($action['button_id'] ?: 'principal');
        $action['email_subject'] = isset($action['email_subject']) ? (string) $action['email_subject'] : '';
        $action['initial_message'] = isset($action['initial_message']) ? (string) $action['initial_message'] : '';
        $action['message_mode'] = self::select_key(
            $action['message_mode'] ?? '',
            array_keys(self::floating_message_modes()),
            self::default_floating_message_mode($action['type'])
        );

        if (!self::supports_universal_smart_copy($action['type'])) {
            $action['message_mode'] = 'none';
        }

        return $action;
    }

    private static function sanitize_floating_hub(array $hub, $index)
    {
        $defaults = self::default_floating_hub();
        $id = isset($hub['id']) ? sanitize_key(wp_unslash($hub['id'])) : '';
        $id = $id ?: 'hub-' . ((int) $index + 1);
        $actions = isset($hub['actions']) && is_array($hub['actions']) ? $hub['actions'] : [];
        $sanitized_actions = [];

        foreach ($actions as $action_index => $action) {
            if (!is_array($action)) {
                continue;
            }

            $sanitized_actions[] = self::sanitize_floating_action($action, $action_index);
        }

        return [
            'id' => $id,
            'name' => isset($hub['name']) ? sanitize_text_field(wp_unslash($hub['name'])) : $defaults['name'],
            'enabled' => empty($hub['enabled']) ? '0' : '1',
            'layout' => self::select_key($hub['layout'] ?? '', array_keys(self::floating_layout_options()), $defaults['layout']),
            'position' => self::select_key($hub['position'] ?? '', ['bottom-right', 'bottom-left'], $defaults['position']),
            'offset_x' => self::number_value($hub, 'offset_x', $defaults['offset_x'], 0, 160),
            'offset_y' => self::number_value($hub, 'offset_y', $defaults['offset_y'], 0, 160),
            'speed' => self::select_key($hub['speed'] ?? '', ['slow', 'normal', 'fast'], $defaults['speed']),
            'main_icon' => sanitize_key(isset($hub['main_icon']) ? wp_unslash($hub['main_icon']) : $defaults['main_icon']),
            'main_color' => self::color_value($hub, 'main_color', $defaults['main_color']),
            'background' => self::color_value($hub, 'background', $defaults['background']),
            'hover_color' => self::color_value($hub, 'hover_color', $defaults['hover_color']),
            'size' => self::number_value($hub, 'size', $defaults['size'], 42, 92),
            'animation' => self::select_key($hub['animation'] ?? '', ['lift', 'fade', 'scale'], $defaults['animation']),
            'show_labels' => empty($hub['show_labels']) ? '0' : '1',
            'mobile_behavior' => self::select_key($hub['mobile_behavior'] ?? '', ['labels', 'icons'], $defaults['mobile_behavior']),
            'actions' => empty($sanitized_actions) ? $defaults['actions'] : $sanitized_actions,
        ];
    }

    private static function sanitize_floating_action(array $action, $index)
    {
        $defaults = self::default_floating_hub()['actions'][0];
        $type = self::select_key($action['type'] ?? '', array_keys(self::floating_action_types()), 'custom');
        $name = isset($action['name']) ? sanitize_text_field(wp_unslash($action['name'])) : '';

        return [
            'id' => isset($action['id']) ? sanitize_key(wp_unslash($action['id'])) : $type . '-' . ((int) $index + 1),
            'type' => $type,
            'name' => '' === $name ? (self::floating_action_types()[$type] ?? $defaults['name']) : $name,
            'icon' => sanitize_key(isset($action['icon']) ? wp_unslash($action['icon']) : $type),
            'color' => self::color_value($action, 'color', $defaults['color']),
            'url' => self::sanitize_floating_action_url($action, 'url', $type),
            'button_id' => isset($action['button_id']) ? sanitize_key(wp_unslash($action['button_id'])) : 'principal',
            'email_subject' => isset($action['email_subject']) ? sanitize_text_field(wp_unslash($action['email_subject'])) : '',
            'initial_message' => isset($action['initial_message']) ? sanitize_textarea_field(wp_unslash($action['initial_message'])) : '',
            'message_mode' => self::select_key(
                $action['message_mode'] ?? '',
                array_keys(self::floating_message_modes()),
                self::default_floating_message_mode($type)
            ),
            'order' => self::number_value($action, 'order', (string) ((int) $index + 1), 0, 1000),
            'visible' => empty($action['visible']) ? '0' : '1',
            'new_tab' => empty($action['new_tab']) ? '0' : '1',
        ];
    }

    private static function select_key($value, array $allowed, $fallback)
    {
        $value = sanitize_key((string) $value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private static function sanitize_floating_action_url(array $action, $key, $type)
    {
        if (!isset($action[$key])) {
            return '';
        }

        $value = trim((string) wp_unslash($action[$key]));

        if ('email' === $type) {
            if (0 === stripos($value, 'mailto:')) {
                $value = substr($value, 7);
                $value = strtok($value, '?') ?: '';
            }

            return sanitize_email($value);
        }

        if ('phone' === $type || ('maps' === $type && false === strpos($value, '://'))) {
            return sanitize_text_field($value);
        }

        return esc_url_raw($value);
    }

    public static function legacy_style_value($variant)
    {
        $variant = sanitize_key((string) $variant);
        $map = [
            'primary' => 'green',
            'green' => 'green',
            'dark' => 'dark',
            'light' => 'light',
            'outline' => 'outline',
            'custom' => 'custom',
            'auto' => 'auto',
        ];

        return $map[$variant] ?? '';
    }

    public static function style_to_legacy_variant($style)
    {
        $style = self::normalize_style($style);

        if (in_array($style, ['dark', 'light', 'outline'], true)) {
            return $style;
        }

        return 'primary';
    }

    public static function style_options()
    {
        return [
            'auto' => __('Auto — usar identidade visual do site', 'dd-smart-whatsapp'),
            'green' => __('Verde WhatsApp', 'dd-smart-whatsapp'),
            'dark' => __('Escuro', 'dd-smart-whatsapp'),
            'light' => __('Claro', 'dd-smart-whatsapp'),
            'outline' => __('Contorno', 'dd-smart-whatsapp'),
            'custom' => __('Personalizado', 'dd-smart-whatsapp'),
        ];
    }

    private static function text_value(array $button, $key, $default)
    {
        if (!isset($button[$key])) {
            return $default;
        }

        $value = sanitize_text_field(wp_unslash($button[$key]));

        return '' === $value ? $default : $value;
    }

    private static function modal_text_keys()
    {
        return ['modal_title', 'modal_success', 'desktop_instruction', 'ios_instruction', 'android_instruction', 'open_label', 'close_label', 'retry_label', 'error_message'];
    }

    private static function modal_customized_values(array $button)
    {
        $customized = [];

        foreach (self::modal_text_keys() as $key) {
            if (isset($button['modal_customized'][$key])) {
                $customized[$key] = empty($button['modal_customized'][$key]) ? '0' : '1';
                continue;
            }

            $customized[$key] = DDSW_I18n::is_default_modal_value($key, $button[$key] ?? '') ? '0' : '1';
        }

        return $customized;
    }

    public static function color_value(array $button, $key, $default)
    {
        if (!isset($button[$key])) {
            return $default;
        }

        $value = sanitize_text_field(wp_unslash($button[$key]));

        return preg_match('/^#([A-Fa-f0-9]{3}){1,2}$/', $value) ? $value : $default;
    }

    public static function number_value(array $button, $key, $default, $min, $max)
    {
        if (!isset($button[$key])) {
            return (string) $default;
        }

        $value = (float) sanitize_text_field(wp_unslash($button[$key]));
        $value = min(max($value, (float) $min), (float) $max);

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private static function css_shadow_value(array $button, $key, $default)
    {
        if (!isset($button[$key])) {
            return $default;
        }

        $value = sanitize_text_field(wp_unslash($button[$key]));

        return preg_match('/^[a-zA-Z0-9#.,%() \-]+$/', $value) ? $value : $default;
    }

    private static function font_family_value(array $button, $key, $default)
    {
        if (!isset($button[$key])) {
            return $default;
        }

        $value = sanitize_text_field(wp_unslash($button[$key]));

        return preg_match('/^[a-zA-Z0-9 ,"\'\-_]+$/', $value) ? $value : $default;
    }

    private static function merge_defaults(array $defaults, array $settings)
    {
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $settings)) {
                $settings[$key] = $value;
                continue;
            }

            if (is_array($value) && is_array($settings[$key]) && 'buttons' !== $key) {
                $settings[$key] = self::merge_defaults($value, $settings[$key]);
            }
        }

        return $settings;
    }
}
