<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_I18n
{
    public const DOMAIN = 'dd-smart-whatsapp';

    private static $loaded_locale = '';

    public static function load()
    {
        $context = is_admin() ? 'admin' : 'frontend';
        self::load_locale(DDSW_Language::resolve($context)['resolved_locale']);
    }

    public static function load_locale($locale)
    {
        $locale = self::locale_family($locale);
        $domain_path = dirname(plugin_basename(DDSW_PLUGIN_FILE)) . '/languages';
        $plugin_mo = DDSW_PLUGIN_DIR . 'languages/' . $locale . '.mo';
        $plugin_domain_mo = DDSW_PLUGIN_DIR . 'languages/dd-smart-whatsapp-' . $locale . '.mo';
        $wp_lang_mo = WP_LANG_DIR . '/plugins/dd-smart-whatsapp-' . $locale . '.mo';

        unload_textdomain(self::DOMAIN);

        if (file_exists($wp_lang_mo)) {
            load_textdomain(self::DOMAIN, $wp_lang_mo, $locale);
        } elseif (file_exists($plugin_domain_mo)) {
            load_textdomain(self::DOMAIN, $plugin_domain_mo, $locale);
        } elseif (file_exists($plugin_mo)) {
            load_textdomain(self::DOMAIN, $plugin_mo, $locale);
        } elseif ('en_US' !== $locale && file_exists(DDSW_PLUGIN_DIR . 'languages/dd-smart-whatsapp-en_US.mo')) {
            load_textdomain(self::DOMAIN, DDSW_PLUGIN_DIR . 'languages/dd-smart-whatsapp-en_US.mo', 'en_US');
        } elseif ('en_US' !== $locale && file_exists(DDSW_PLUGIN_DIR . 'languages/en_US.mo')) {
            load_textdomain(self::DOMAIN, DDSW_PLUGIN_DIR . 'languages/en_US.mo', 'en_US');
        }

        load_plugin_textdomain(self::DOMAIN, false, $domain_path);
        self::$loaded_locale = $locale;
    }

    public static function locale()
    {
        $context = is_admin() ? 'admin' : 'frontend';
        $locale = DDSW_Language::resolve($context)['resolved_locale'];
        $locale = apply_filters('ddsw_locale', $locale, $context);

        return DDSW_Language::normalize_locale($locale) ?: 'en_US';
    }

    public static function gettext_locale()
    {
        return self::$loaded_locale ?: self::locale_family(self::locale());
    }

    public static function admin_locale_source()
    {
        return DDSW_Language::admin_locale_source();
    }

    public static function locale_family($locale = '')
    {
        $locale = $locale ? $locale : self::locale();

        return DDSW_Language::locale_family($locale);
    }

    public static function supported_template_locales()
    {
        return DDSW_Language::supported_locales();
    }

    public static function template_locale_options()
    {
        return DDSW_Language::locale_options();
    }

    public static function template_library($locale = '')
    {
        return DDSW_Language::template_library($locale ?: DDSW_Language::frontend_locale());
    }

    public static function default_button_texts($locale = '')
    {
        return DDSW_Language::default_button_texts($locale ?: DDSW_Language::frontend_locale());
    }

    public static function default_button_text_sets()
    {
        return DDSW_Language::default_button_text_sets();
    }

    public static function resolve_modal_strings(array $button = [], array $atts = [])
    {
        $button = is_array($button) ? $button : [];
        $atts = is_array($atts) ? $atts : [];
        $language_resolution = DDSW_Language::resolve('frontend', $button, $atts);
        $locale = self::locale_family($language_resolution['template_locale']);

        self::load_locale($locale);

        $template_key = isset($button['template_key']) ? sanitize_key((string) $button['template_key']) : 'support';
        $templates = self::template_library($locale);
        $template = $templates[$template_key] ?? ($templates['support'] ?? []);
        $defaults = array_merge(self::default_button_texts($locale), $template);
        $defaults['hide_again_label'] = __('Do not show again on this browser', self::DOMAIN);
        $debug = [
            'resolvedLocale' => $language_resolution['resolved_locale'],
            'languageSource' => $language_resolution['language_source'],
            'templateLoaded' => $template_key,
            'gettextLocale' => self::gettext_locale(),
            'payloadLanguage' => $locale,
            'languageCandidates' => $language_resolution['candidates'],
            'siteLocale' => $language_resolution['site_locale'],
            'determineLocale' => $language_resolution['determine_locale'],
            'userLocale' => $language_resolution['user_locale'],
            'htmlLang' => $language_resolution['html_lang'],
            'locale' => $locale,
            'template' => $template_key,
            'modalSource' => 'gettext',
            'resolvedBy' => [],
            'customOverride' => [],
            'translationLoaded' => is_textdomain_loaded(self::DOMAIN),
            'moLoaded' => self::mo_exists($locale),
            'poLoaded' => self::po_exists($locale),
        ];
        $map = [
            'title' => 'modal_title',
            'description' => 'modal_success',
            'success' => 'modal_success',
            'desktop' => 'desktop_instruction',
            'ios' => 'ios_instruction',
            'android' => 'android_instruction',
            'instruction' => 'desktop_instruction',
            'button' => 'open_label',
            'openLabel' => 'open_label',
            'close' => 'close_label',
            'closeLabel' => 'close_label',
            'retry' => 'retry_label',
            'retryLabel' => 'retry_label',
            'failed' => 'error_message',
            'errorMessage' => 'error_message',
            'copyFeedback' => 'copy_feedback',
            'hideAgainLabel' => 'hide_again_label',
        ];
        $resolved = [];

        foreach ($map as $public_key => $field_key) {
            $value = $defaults[$field_key] ?? self::internal_modal_fallback($field_key);
            $source = 'gettext';

            if (isset($atts[$field_key]) && '' !== trim((string) $atts[$field_key])) {
                $value = sanitize_text_field($atts[$field_key]);
                $source = 'runtime_override';
            } elseif (self::is_customized_field($button, $field_key)) {
                $value = self::saved_text($button, $field_key, $value);
                $source = 'database';
                $debug['customOverride'][] = $field_key;
            }

            $resolved[$public_key] = $value;
            $debug['resolvedBy'][$public_key] = $source;
        }

        $resolved['debug'] = $debug;

        return apply_filters('ddsw_resolved_modal_strings', $resolved, $button, $atts, $locale);
    }

    public static function is_default_modal_value($field, $value)
    {
        $value = trim((string) $value);
        if ('' === $value) {
            return true;
        }

        foreach (self::supported_template_locales() as $locale) {
            $texts = self::default_button_texts($locale);
            $texts['hide_again_label'] = __('Do not show again on this browser', self::DOMAIN);
            if (isset($texts[$field]) && trim((string) $texts[$field]) === $value) {
                return true;
            }
        }

        $legacy_defaults = [
            'modal_title' => ['TWVuc2FnZW0gY29waWFkYQ==', 'TWVzc2FnZSBjb3BpZWQ=', 'Q29waWVkIHN1Y2Nlc3NmdWxseQ=='],
            'modal_success' => ['U3VhIG1lbnNhZ2VtIGZvaSBjb3BpYWRhIHBhcmEgYSDDoXJlYSBkZSB0cmFuc2ZlcsOqbmNpYS4=', 'TWVuc2FnZW0gY29waWFkYSBjb20gc3VjZXNzby4=', 'TWVzc2FnZSBjb3BpZWQgc3VjY2Vzc2Z1bGx5Lg==', 'RWwgbWVuc2FqZSBzZSBjb3Bpw7MgY29ycmVjdGFtZW50ZSBhbCBwb3J0YXBhcGVsZXMu', '44Oh44OD44K744O844K444KS44Kv44Oq44OD44OX44Oc44O844OJ44Gr44Kz44OU44O844GX44G+44GX44Gf44CC', 'WW91ciBtZXNzYWdlIGhhcyBiZWVuIGNvcGllZCB0byB0aGUgY2xpcGJvYXJkLg=='],
            'desktop_instruction' => ['QWdvcmEgY2xpcXVlIGVtIEFicmlyIFdoYXRzQXBwLgoKUXVhbmRvIGEgY29udmVyc2EgYWJyaXIsIHByZXNzaW9uZSBDdHJsICsgViAoV2luZG93cykgb3Ug4oyYICsgViAoTWFjKSBwYXJhIGNvbGFyIGF1dG9tYXRpY2FtZW50ZSBhIG1lbnNhZ2VtLg==', 'Q2xpcXVlIGVtIEFicmlyIFdoYXRzQXBwLgoKUXVhbmRvIGEgY29udmVyc2EgYWJyaXIsIHByZXNzaW9uZSBDdHJsICsgViAoV2luZG93cykgb3Ug4oyYICsgViAoTWFjKSBwYXJhIGNvbGFyIGF1dG9tYXRpY2FtZW50ZSBhIG1lbnNhZ2VtLg==', 'Q2xpcXVlIGVtIEFicmlyIFdoYXRzQXBwIGUgcHJlc3Npb25lIEN0cmwgKyBWIG5vIGNhbXBvIGRhIG1lbnNhZ2VtLg==', 'Q2xpY2sgT3BlbiBXaGF0c0FwcCBhbmQgcHJlc3MgQ3RybCArIFYgaW4gdGhlIG1lc3NhZ2UgZmllbGQu'],
            'ios_instruction' => ['Q2xpcXVlIGVtIEFicmlyIFdoYXRzQXBwLCB0b3F1ZSBubyBjYW1wbyBkYSBtZW5zYWdlbSBlIGVzY29saGEgQ29sYXIu'],
            'android_instruction' => ['Q2xpcXVlIGVtIEFicmlyIFdoYXRzQXBwLCB0b3F1ZSBlIHNlZ3VyZSBubyBjYW1wbyBkYSBtZW5zYWdlbSBlIGVzY29saGEgQ29sYXIu'],
            'open_label' => ['QWJyaXIgV2hhdHNBcHA=', 'QWJyaXIgV2hhdHNBcHAg4oaS', 'T3BlbiBXaGF0c0FwcA==', 'T3BlbiBXaGF0c0FwcCDihpI='],
            'close_label' => ['RmVjaGFy', 'Q2xvc2U='],
            'retry_label' => ['Q29waWFyIG5vdmFtZW50ZQ==', 'Q29weSBhZ2Fpbg=='],
            'error_message' => ['TsOjbyBmb2kgcG9zc8OtdmVsIGNvcGlhciBhdXRvbWF0aWNhbWVudGUu', 'QXV0b21hdGljIGNvcHkgZmFpbGVkLiBTZWxlY3QgYW5kIGNvcHkgdGhlIG1lc3NhZ2UgYmVsb3cu'],
            'copy_feedback' => ['TWVuc2FnZW0gY29waWFkYS4gQWJyaW5kbyBXaGF0c0FwcC4uLg==', 'TWVzc2FnZSBjb3BpZWQuIE9wZW5pbmcgV2hhdHNBcHAuLi4='],
            'hide_again_label' => ['TsOjbyBtb3N0cmFyIG5vdmFtZW50ZSBuZXN0ZSBuYXZlZ2Fkb3I=', 'RG8gbm90IHNob3cgYWdhaW4gb24gdGhpcyBicm93c2Vy'],
        ];

        return in_array($value, array_map('base64_decode', $legacy_defaults[$field] ?? []), true);
    }

    public static function modal_template_hash($locale, $template_key)
    {
        $texts = self::default_button_texts($locale);
        $keys = ['modal_title', 'modal_success', 'desktop_instruction', 'ios_instruction', 'android_instruction', 'open_label', 'close_label', 'retry_label', 'error_message'];
        $payload = [];

        foreach ($keys as $key) {
            $payload[$key] = $texts[$key] ?? '';
        }

        return hash('sha256', wp_json_encode([self::locale_family($locale), sanitize_key($template_key), $payload]));
    }

    private static function is_customized_field(array $button, $field)
    {
        if (!array_key_exists($field, $button) || self::is_default_modal_value($field, $button[$field])) {
            return false;
        }

        if (!empty($button['modal_customized']) && is_array($button['modal_customized']) && array_key_exists($field, $button['modal_customized'])) {
            return !empty($button['modal_customized'][$field]);
        }

        return true;
    }

    private static function saved_text(array $button, $field, $fallback)
    {
        if (!isset($button[$field])) {
            return $fallback;
        }

        $value = sanitize_text_field($button[$field]);

        return '' === $value ? $fallback : $value;
    }

    private static function internal_modal_fallback($field)
    {
        $fallbacks = [
            'modal_title' => _x('Copied successfully', 'Internal Smart Copy modal title fallback', self::DOMAIN),
            'modal_success' => _x('Your message has been copied to the clipboard.', 'Internal Smart Copy modal success fallback', self::DOMAIN),
            'desktop_instruction' => _x('Click Open WhatsApp and press Ctrl + V in the message field.', 'Internal Smart Copy desktop fallback', self::DOMAIN),
            'ios_instruction' => _x('Tap Open WhatsApp, tap the message field and choose Paste.', 'Internal Smart Copy iOS fallback', self::DOMAIN),
            'android_instruction' => _x('Tap Open WhatsApp, tap and hold the message field and choose Paste.', 'Internal Smart Copy Android fallback', self::DOMAIN),
            'open_label' => _x('Open WhatsApp', 'Internal Smart Copy button fallback', self::DOMAIN),
            'close_label' => _x('Close', 'Internal Smart Copy close fallback', self::DOMAIN),
            'retry_label' => _x('Copy again', 'Internal Smart Copy retry fallback', self::DOMAIN),
            'error_message' => _x('Automatic copy failed. Select and copy the message below.', 'Internal Smart Copy error fallback', self::DOMAIN),
            'copy_feedback' => _x('Message copied. Opening WhatsApp...', 'Internal traditional feedback fallback', self::DOMAIN),
            'hide_again_label' => _x('Do not show again on this browser', 'Internal Smart Copy hide-again fallback', self::DOMAIN),
        ];

        return $fallbacks[$field] ?? '';
    }

    private static function mo_exists($locale)
    {
        return file_exists(DDSW_PLUGIN_DIR . 'languages/dd-smart-whatsapp-' . $locale . '.mo')
            || file_exists(DDSW_PLUGIN_DIR . 'languages/' . $locale . '.mo')
            || file_exists(WP_LANG_DIR . '/plugins/dd-smart-whatsapp-' . $locale . '.mo');
    }

    private static function po_exists($locale)
    {
        return file_exists(DDSW_PLUGIN_DIR . 'languages/dd-smart-whatsapp-' . $locale . '.po')
            || file_exists(DDSW_PLUGIN_DIR . 'languages/' . $locale . '.po');
    }
}
