<?php

namespace DD\SmartWhatsApp;

if (!defined('ABSPATH')) {
    exit;
}

final class Assets
{
    private static $localized = false;

    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'register_frontend']);
        add_action('elementor/preview/enqueue_styles', [self::class, 'enqueue_frontend']);
        add_action('elementor/editor/after_enqueue_styles', [self::class, 'enqueue_frontend']);
        add_action('elementor/frontend/after_enqueue_styles', [self::class, 'enqueue_frontend']);
    }

    public static function register_frontend(): void
    {
        wp_register_style(
            'ddsw-frontend',
            DDSW_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            DDSW_VERSION
        );

        wp_register_style(
            'ddsw-modal',
            DDSW_PLUGIN_URL . 'assets/css/modal.css',
            ['ddsw-frontend'],
            DDSW_VERSION
        );

        wp_register_style(
            'ddsw-elementor',
            DDSW_PLUGIN_URL . 'assets/css/elementor.css',
            ['ddsw-frontend'],
            DDSW_VERSION
        );

        wp_register_style(
            'ddsw-floating-actions',
            DDSW_PLUGIN_URL . 'assets/css/floating-actions.css',
            ['ddsw-frontend'],
            DDSW_VERSION
        );

        wp_register_script(
            'ddsw-clipboard',
            DDSW_PLUGIN_URL . 'assets/js/clipboard.js',
            ['wp-i18n'],
            DDSW_VERSION,
            true
        );

        wp_register_script(
            'ddsw-tracking',
            DDSW_PLUGIN_URL . 'assets/js/tracking.js',
            ['wp-i18n'],
            DDSW_VERSION,
            true
        );

        wp_register_script(
            'ddsw-modal',
            DDSW_PLUGIN_URL . 'assets/js/modal.js',
            ['wp-i18n', 'ddsw-clipboard', 'ddsw-tracking'],
            DDSW_VERSION,
            true
        );

        wp_register_script(
            'ddsw-frontend',
            DDSW_PLUGIN_URL . 'assets/js/frontend.js',
            ['wp-i18n', 'ddsw-clipboard', 'ddsw-tracking', 'ddsw-modal'],
            DDSW_VERSION,
            true
        );

        wp_register_script(
            'ddsw-floating-actions',
            DDSW_PLUGIN_URL . 'assets/js/floating-actions.js',
            ['wp-i18n', 'ddsw-tracking', 'ddsw-frontend'],
            DDSW_VERSION,
            true
        );

        foreach (['ddsw-clipboard', 'ddsw-tracking', 'ddsw-modal', 'ddsw-frontend', 'ddsw-floating-actions'] as $handle) {
            wp_set_script_translations($handle, 'dd-smart-whatsapp', DDSW_PLUGIN_DIR . 'languages');
        }
    }

    public static function enqueue_frontend(): void
    {
        if (!wp_style_is('ddsw-frontend', 'registered')) {
            self::register_frontend();
        }

        wp_enqueue_style('ddsw-frontend');
        wp_enqueue_style('ddsw-modal');
        wp_enqueue_style('ddsw-elementor');
        wp_enqueue_script('ddsw-clipboard');
        wp_enqueue_script('ddsw-tracking');
        wp_enqueue_script('ddsw-modal');
        wp_enqueue_script('ddsw-frontend');

        if (self::$localized) {
            return;
        }

        $settings = \DDSW_Settings::get();
        $modal_strings = \DDSW_I18n::resolve_modal_strings([], []);
        $language_resolution = \DDSW_Language::last_resolution('frontend');
        $copy_feedback = empty($settings['copy_feedback_customized'])
            ? $modal_strings['copyFeedback']
            : $settings['copy_feedback'];

        wp_localize_script(
            'ddsw-frontend',
            'DDSmartWhatsApp',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ddsw_click'),
                'locale' => $language_resolution['resolved_locale'],
                'resolvedLocale' => $language_resolution['resolved_locale'],
                'languageSource' => $language_resolution['language_source'],
                'gettextLocale' => \DDSW_I18n::gettext_locale(),
                'payloadLanguage' => $language_resolution['locale_family'],
                'ga4Enabled' => !empty($settings['ga4_enabled']),
                'copyFeedback' => $copy_feedback,
                'debug' => !empty($settings['debug_console']),
                'debugInvalidPayload' => esc_html__('DD Smart WhatsApp: invalid payload.', 'dd-smart-whatsapp'),
                'debugTraditionalCopyFailed' => esc_html__('DD Smart WhatsApp: failed to copy in Traditional mode.', 'dd-smart-whatsapp'),
                'debugSmartCopyFailed' => esc_html__('DD Smart WhatsApp: Smart Copy failed.', 'dd-smart-whatsapp'),
                'debugExecCommandFailed' => esc_html__('DD Smart WhatsApp: copy fallback failed.', 'dd-smart-whatsapp'),
                'debugTrackingFailed' => esc_html__('DD Smart WhatsApp: failed to record event.', 'dd-smart-whatsapp'),
                'modalClose' => $modal_strings['close'],
                'modalCloseLabel' => $modal_strings['close'],
                'modalTitle' => $modal_strings['title'],
                'modalDescription' => $modal_strings['description'],
                'modalSuccess' => $modal_strings['success'],
                'modalInstruction' => $modal_strings['instruction'],
                'modalFailed' => $modal_strings['failed'],
                'modalCopyError' => $modal_strings['failed'],
                'modalButton' => $modal_strings['button'],
                'openWhatsAppLabel' => $modal_strings['button'],
                'retryCopyLabel' => $modal_strings['retry'],
                'hideAgainLabel' => $modal_strings['hideAgainLabel'],
                'debugTitle' => esc_html__('DDSW Debug', 'dd-smart-whatsapp'),
                'debugLabels' => [
                    'locale' => esc_html__('Locale', 'dd-smart-whatsapp'),
                    'resolvedLocale' => esc_html__('Resolved Locale', 'dd-smart-whatsapp'),
                    'languageSource' => esc_html__('Language Source', 'dd-smart-whatsapp'),
                    'templateLoaded' => esc_html__('Template Loaded', 'dd-smart-whatsapp'),
                    'gettextLocale' => esc_html__('Gettext Locale', 'dd-smart-whatsapp'),
                    'payloadLanguage' => esc_html__('Payload Language', 'dd-smart-whatsapp'),
                    'htmlLang' => esc_html__('HTML Lang', 'dd-smart-whatsapp'),
                    'documentLang' => esc_html__('Document Lang', 'dd-smart-whatsapp'),
                    'template' => esc_html__('Template', 'dd-smart-whatsapp'),
                    'modalSource' => esc_html__('Modal Source', 'dd-smart-whatsapp'),
                    'resolvedBy' => esc_html__('Resolved By', 'dd-smart-whatsapp'),
                    'modalFields' => esc_html__('Modal Fields', 'dd-smart-whatsapp'),
                    'modalPayload' => esc_html__('Modal Payload', 'dd-smart-whatsapp'),
                    'titleField' => esc_html__('Title', 'dd-smart-whatsapp'),
                    'descriptionField' => esc_html__('Description', 'dd-smart-whatsapp'),
                    'instructionField' => esc_html__('Instruction', 'dd-smart-whatsapp'),
                    'buttonField' => esc_html__('Button', 'dd-smart-whatsapp'),
                    'customOverride' => esc_html__('Custom Override', 'dd-smart-whatsapp'),
                    'translationLoaded' => esc_html__('Translation Loaded', 'dd-smart-whatsapp'),
                    'moLoaded' => esc_html__('MO Loaded', 'dd-smart-whatsapp'),
                    'poLoaded' => esc_html__('PO Loaded', 'dd-smart-whatsapp'),
                ],
            ]
        );
        self::$localized = true;
    }
}
