<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Language
{
    public const FALLBACK_LOCALE = 'en_US';
    public const DOMAIN = 'dd-smart-whatsapp';

    private const LANGUAGE_TO_LOCALE = [
        'pt' => 'pt_BR',
        'en' => 'en_US',
        'es' => 'es_ES',
        'ja' => 'ja',
        'fr' => 'fr_FR',
        'de' => 'de_DE',
        'it' => 'it_IT',
        'nl' => 'nl_NL',
    ];

    private static $last_resolution = [];
    private static $supported_locales = null;
    private static $template_cache = [];

    public static function resolve($context = 'frontend', array $button = [], array $args = [])
    {
        $context = self::context($context);
        $manual = self::manual_locale($button, $args);
        $source = 'manual_button';
        $resolved = $manual;

        if (!$resolved) {
            $source = 'admin' === $context ? self::admin_locale_source() : 'current_site_language';
            $resolved = 'admin' === $context ? self::admin_locale() : self::frontend_locale();
        }

        $resolved = self::locale_family($resolved);
        if (!in_array($resolved, self::supported_locales(), true)) {
            $resolved = self::FALLBACK_LOCALE;
            $source = 'fallback';
        }

        $resolution = [
            'context' => $context,
            'resolved_locale' => $resolved,
            'locale_family' => $resolved,
            'template_locale' => $resolved,
            'language_source' => $source,
            'manual_locale' => $manual,
            'admin_locale' => self::admin_locale(),
            'site_locale' => self::site_locale(),
            'frontend_locale' => self::frontend_locale(),
            'determine_locale' => self::safe_determine_locale(),
            'user_locale' => function_exists('get_user_locale') ? self::normalize_locale(get_user_locale()) : '',
            'fallback_locale' => self::FALLBACK_LOCALE,
            'candidates' => self::candidates_for_context($context),
            'html_lang' => str_replace('_', '-', $resolved),
        ];

        $resolution = apply_filters('ddsw_language_resolution', $resolution, $context, $button, $args);
        $resolution['resolved_locale'] = self::locale_family($resolution['resolved_locale'] ?? $resolved);
        $resolution['locale_family'] = $resolution['resolved_locale'];
        $resolution['template_locale'] = $resolution['resolved_locale'];
        $resolution['language_source'] = sanitize_key((string) ($resolution['language_source'] ?? $source));
        self::$last_resolution[$context] = $resolution;

        return $resolution;
    }

    public static function last_resolution($context = 'frontend')
    {
        $context = self::context($context);

        return self::$last_resolution[$context] ?? self::resolve($context);
    }

    public static function admin_locale()
    {
        if ('user' === self::admin_locale_source() && function_exists('get_user_locale')) {
            return self::locale_family(get_user_locale());
        }

        return self::site_locale();
    }

    public static function site_locale()
    {
        return self::locale_family(function_exists('get_locale') ? get_locale() : self::FALLBACK_LOCALE);
    }

    public static function frontend_locale()
    {
        foreach (self::frontend_candidates() as $candidate) {
            if (!empty($candidate['locale'])) {
                return self::locale_family($candidate['locale']);
            }
        }

        return self::FALLBACK_LOCALE;
    }

    public static function template_locale(array $button = [], $context = 'frontend')
    {
        $manual = self::manual_locale($button, []);
        if ($manual) {
            return $manual;
        }

        return 'admin' === self::context($context) ? self::site_locale() : self::frontend_locale();
    }

    public static function locale_family($locale)
    {
        $locale = self::normalize_locale($locale);
        if (!$locale) {
            return self::FALLBACK_LOCALE;
        }

        if (in_array($locale, self::supported_locales(), true)) {
            return $locale;
        }

        $language = strtolower(substr($locale, 0, 2));

        return self::LANGUAGE_TO_LOCALE[$language] ?? self::FALLBACK_LOCALE;
    }

    public static function normalize_locale($locale)
    {
        $locale = str_replace('-', '_', trim((string) $locale));
        if ('' === $locale) {
            return '';
        }

        if (preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
            return $locale;
        }

        if (preg_match('/^[a-z]{2}$/', $locale)) {
            return self::LANGUAGE_TO_LOCALE[$locale] ?? $locale;
        }

        if (preg_match('/^([a-z]{2})_([a-z]{2})$/', $locale, $matches)) {
            return $matches[1] . '_' . strtoupper($matches[2]);
        }

        return '';
    }

    public static function supported_locales()
    {
        if (null !== self::$supported_locales) {
            return self::$supported_locales;
        }

        $locales = array_values(self::LANGUAGE_TO_LOCALE);
        $template_root = DDSW_PLUGIN_DIR . 'templates';
        if (is_dir($template_root)) {
            foreach (glob($template_root . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
                $locale = self::normalize_locale(basename($directory));
                if ($locale) {
                    $locales[] = $locale;
                }
            }
        }

        $locales[] = self::FALLBACK_LOCALE;
        $locales = array_values(array_unique(array_filter(array_map([self::class, 'normalize_locale'], $locales))));

        self::$supported_locales = apply_filters('ddsw_supported_template_locales', $locales);

        return self::$supported_locales;
    }

    public static function locale_options()
    {
        return [
            '' => __('Automatic — current site language', self::DOMAIN),
            'pt_BR' => __('Portuguese', self::DOMAIN),
            'en_US' => __('English', self::DOMAIN),
            'es_ES' => __('Spanish', self::DOMAIN),
            'ja' => __('Japanese', self::DOMAIN),
            'fr_FR' => __('French', self::DOMAIN),
            'de_DE' => __('German', self::DOMAIN),
            'it_IT' => __('Italian', self::DOMAIN),
            'nl_NL' => __('Dutch', self::DOMAIN),
        ];
    }

    public static function template_library($locale = '')
    {
        $target_locale = self::locale_family($locale ?: self::frontend_locale());

        if (isset(self::$template_cache[$target_locale])) {
            return self::$template_cache[$target_locale];
        }

        $previous_locale = DDSW_I18n::gettext_locale();
        if ($target_locale !== $previous_locale) {
            DDSW_I18n::load_locale($target_locale);
        }

        $templates = self::load_templates_from_directory($target_locale);
        if (self::FALLBACK_LOCALE !== $target_locale) {
            $templates = array_replace_recursive(self::load_templates_from_directory(self::FALLBACK_LOCALE), $templates);
        }

        $defaults = self::default_button_texts($target_locale);
        foreach ($templates as $key => $template) {
            $templates[$key] = array_merge($defaults, $template);
        }

        if ($target_locale !== $previous_locale) {
            DDSW_I18n::load_locale($previous_locale);
        }

        self::$template_cache[$target_locale] = apply_filters('ddsw_template_library', $templates, $target_locale);

        return self::$template_cache[$target_locale];
    }

    public static function template_defaults($locale, $template_key)
    {
        $locale = self::locale_family($locale);
        $template_key = sanitize_key((string) $template_key);
        $library = self::template_library($locale);
        $template = $library[$template_key] ?? ($library['support'] ?? []);

        return array_merge(self::default_button_texts($locale), $template);
    }

    public static function is_default_template_value($field, $value)
    {
        $field = sanitize_key((string) $field);
        $value = trim((string) $value);

        if ('' === $value) {
            return true;
        }

        foreach (self::supported_locales() as $locale) {
            $defaults = self::default_button_texts($locale);
            if (isset($defaults[$field]) && trim((string) $defaults[$field]) === $value) {
                return true;
            }

            foreach (self::template_library($locale) as $template) {
                if (isset($template[$field]) && trim((string) $template[$field]) === $value) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function default_button_texts($locale = '')
    {
        $target_locale = self::locale_family($locale ?: self::frontend_locale());
        $previous_locale = DDSW_I18n::gettext_locale();

        if ($target_locale !== $previous_locale) {
            DDSW_I18n::load_locale($target_locale);
        }

        $texts = [
            'label' => _x('Chat on WhatsApp', 'Default button label', self::DOMAIN),
            'message' => _x("Hello!\n\nI am visiting {site_title} and would like to receive more information.\n\nPage:\n{page_title}\n{page_url}\n\nThank you!", 'Default button message', self::DOMAIN),
            'modal_title' => _x('Copied successfully', 'Default Smart Copy modal title', self::DOMAIN),
            'modal_success' => _x('Your message has been copied to the clipboard.', 'Default Smart Copy modal success message', self::DOMAIN),
            'desktop_instruction' => _x('Click Open WhatsApp and press Ctrl + V in the message field.', 'Default Smart Copy desktop instruction', self::DOMAIN),
            'ios_instruction' => _x('Tap Open WhatsApp, tap the message field and choose Paste.', 'Default Smart Copy iOS instruction', self::DOMAIN),
            'android_instruction' => _x('Tap Open WhatsApp, tap and hold the message field and choose Paste.', 'Default Smart Copy Android instruction', self::DOMAIN),
            'open_label' => _x('Open WhatsApp', 'Default Smart Copy open button label', self::DOMAIN),
            'close_label' => _x('Close', 'Default Smart Copy close button label', self::DOMAIN),
            'retry_label' => _x('Copy again', 'Default Smart Copy retry button label', self::DOMAIN),
            'error_message' => _x('Automatic copy failed. Select and copy the message below.', 'Default Smart Copy error message', self::DOMAIN),
            'copy_feedback' => _x('Message copied. Opening WhatsApp...', 'Default traditional mode copy feedback', self::DOMAIN),
        ];

        if ($target_locale !== $previous_locale) {
            DDSW_I18n::load_locale($previous_locale);
        }

        return $texts;
    }

    public static function default_button_text_sets()
    {
        $sets = [];

        foreach (self::supported_locales() as $locale) {
            $sets[$locale] = self::default_button_texts($locale);
        }

        return $sets;
    }

    public static function admin_locale_source()
    {
        $settings = get_option('ddsw_settings', []);
        $source = is_array($settings) && isset($settings['admin_locale_source'])
            ? sanitize_key((string) $settings['admin_locale_source'])
            : 'site';

        return in_array($source, ['site', 'user'], true) ? $source : 'site';
    }

    private static function context($context)
    {
        $context = $context ? sanitize_key((string) $context) : (is_admin() ? 'admin' : 'frontend');

        return in_array($context, ['admin', 'frontend'], true) ? $context : 'frontend';
    }

    private static function manual_locale(array $button, array $args)
    {
        $locale = $args['template_locale'] ?? ($button['template_locale'] ?? '');
        $locale = self::normalize_locale($locale);

        return in_array($locale, self::supported_locales(), true) ? $locale : '';
    }

    private static function load_templates_from_directory($locale)
    {
        $templates = [];
        $directory = DDSW_PLUGIN_DIR . 'templates/' . $locale;

        if (!is_dir($directory)) {
            return [];
        }

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $key = sanitize_key(basename($file, '.php'));
            $template = include $file;
            if (is_array($template) && $key) {
                $templates[$key] = $template;
            }
        }

        return $templates;
    }

    private static function candidates_for_context($context)
    {
        return 'admin' === $context ? self::admin_candidates() : self::frontend_candidates();
    }

    private static function admin_candidates()
    {
        $candidates = [];

        if ('user' === self::admin_locale_source() && function_exists('get_user_locale')) {
            $candidates[] = self::candidate('get_user_locale', get_user_locale());
        }

        $candidates[] = self::candidate('get_locale', self::site_locale());
        $candidates[] = self::candidate('determine_locale', self::safe_determine_locale());

        return self::filter_candidates($candidates, 'admin');
    }

    private static function frontend_candidates()
    {
        $candidates = [
            self::candidate('polylang', self::polylang_locale()),
            self::candidate('wpml', self::wpml_locale()),
            self::candidate('translatepress', self::translatepress_locale()),
            self::candidate('url_path', self::url_path_locale()),
            self::candidate('subdomain', self::subdomain_locale()),
            self::candidate('locale_filter', self::locale_filter_value()),
            self::candidate('determine_locale', self::safe_determine_locale()),
            self::candidate('get_locale', self::site_locale()),
        ];

        return self::filter_candidates($candidates, 'frontend');
    }

    private static function candidate($source, $locale)
    {
        return [
            'source' => sanitize_key((string) $source),
            'locale' => self::normalize_locale($locale),
            'raw' => is_scalar($locale) ? (string) $locale : '',
        ];
    }

    private static function filter_candidates(array $candidates, $context)
    {
        return apply_filters(
            'ddsw_language_candidates',
            array_values(array_filter($candidates, static function ($candidate) {
                return !empty($candidate['locale']);
            })),
            $context
        );
    }

    private static function polylang_locale()
    {
        if (!function_exists('pll_current_language')) {
            return '';
        }

        return pll_current_language('locale') ?: pll_current_language('slug');
    }

    private static function wpml_locale()
    {
        $language = apply_filters('wpml_current_language', null);
        if (!$language && defined('ICL_LANGUAGE_CODE')) {
            $language = ICL_LANGUAGE_CODE;
        }

        if (!$language) {
            return '';
        }

        $active = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
        if (is_array($active) && isset($active[$language])) {
            foreach (['default_locale', 'locale', 'language_locale'] as $key) {
                if (!empty($active[$language][$key])) {
                    return $active[$language][$key];
                }
            }
        }

        return $language;
    }

    private static function translatepress_locale()
    {
        return !empty($GLOBALS['TRP_LANGUAGE']) ? $GLOBALS['TRP_LANGUAGE'] : '';
    }

    private static function url_path_locale()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return '';
        }

        $path = wp_parse_url(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH);
        $parts = is_string($path) ? array_values(array_filter(explode('/', trim($path, '/')))) : [];

        return self::locale_from_language($parts[0] ?? '');
    }

    private static function subdomain_locale()
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return '';
        }

        $host = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
        $host = strtolower(preg_replace('/:\d+$/', '', $host));
        $parts = explode('.', $host);

        return self::locale_from_language($parts[0] ?? '');
    }

    private static function locale_from_language($language)
    {
        $language = strtolower(trim((string) $language));

        return self::LANGUAGE_TO_LOCALE[$language] ?? self::normalize_locale($language);
    }

    private static function locale_filter_value()
    {
        return apply_filters('locale', self::site_locale());
    }

    private static function safe_determine_locale()
    {
        return function_exists('determine_locale') ? self::normalize_locale(determine_locale()) : self::site_locale();
    }
}
