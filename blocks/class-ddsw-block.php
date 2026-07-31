<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Block
{
    public function init()
    {
        add_action('init', [$this, 'register']);
    }

    public function register()
    {
        wp_register_script(
            'ddsw-block-editor',
            DDSW_PLUGIN_URL . 'blocks/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor'],
            DDSW_VERSION,
            true
        );
        wp_set_script_translations('ddsw-block-editor', 'dd-smart-whatsapp', DDSW_PLUGIN_DIR . 'languages');

        wp_register_style(
            'ddsw-block-editor',
            DDSW_PLUGIN_URL . 'blocks/block-editor.css',
            [],
            DDSW_VERSION
        );

        wp_localize_script(
            'ddsw-block-editor',
            'DDSWBlock',
            [
                'buttons' => $this->button_options(),
                'styles' => $this->style_options(),
                'defaultLabel' => DDSW_I18n::default_button_texts(DDSW_Language::site_locale())['label'],
            ]
        );

        register_block_type(
            'dd-smart-whatsapp/button',
            [
                'api_version' => 2,
                'editor_script' => 'ddsw-block-editor',
                'editor_style' => 'ddsw-block-editor',
                'render_callback' => [$this, 'render'],
                'attributes' => [
                    'id' => ['type' => 'string', 'default' => 'principal'],
                    'mode' => ['type' => 'string', 'default' => ''],
                    'label' => ['type' => 'string', 'default' => ''],
                    'message' => ['type' => 'string', 'default' => ''],
                    'style' => ['type' => 'string', 'default' => ''],
                    'variant' => ['type' => 'string', 'default' => ''],
                    'align' => ['type' => 'string', 'default' => ''],
                    'width' => ['type' => 'string', 'default' => ''],
                    'showIcon' => ['type' => 'boolean', 'default' => true],
                    'autoOpen' => ['type' => 'string', 'default' => ''],
                    'autoClose' => ['type' => 'string', 'default' => ''],
                    'hideAgain' => ['type' => 'string', 'default' => ''],
                ],
            ]
        );
    }

    public function render($attributes)
    {
        return DDSW_Renderer::render([
            'id' => $attributes['id'] ?? '',
            'mode' => $attributes['mode'] ?? '',
            'label' => $attributes['label'] ?? '',
            'message' => $attributes['message'] ?? '',
            'style' => $attributes['style'] ?? '',
            'variant' => $attributes['variant'] ?? '',
            'align' => $attributes['align'] ?? '',
            'width' => $attributes['width'] ?? '',
            'show_icon' => empty($attributes['showIcon']) ? '0' : '1',
            'auto_open' => $attributes['autoOpen'] ?? '',
            'auto_close' => $attributes['autoClose'] ?? '',
            'hide_again' => $attributes['hideAgain'] ?? '',
        ]);
    }

    private function button_options()
    {
        $options = [];

        foreach (DDSW_Settings::get_buttons(true) as $button) {
            if (empty($button['id'])) {
                continue;
            }

            $options[] = [
                'label' => $button['label'] . ' (' . $button['id'] . ')',
                'value' => $button['id'],
            ];
        }

        return $options ?: [
            [
                'label' => __('Principal', 'dd-smart-whatsapp'),
                'value' => 'principal',
            ],
        ];
    }

    private function style_options()
    {
        $options = [
            [
                'label' => __('Use button default', 'dd-smart-whatsapp'),
                'value' => '',
            ],
        ];

        foreach (DDSW_Settings::style_options() as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $options;
    }
}
