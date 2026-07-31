<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class DDSW_Elementor_Dynamic_Tag_Base extends \Elementor\Core\DynamicTags\Tag
{
    protected const FIELD = 'label';
    protected const DEFAULT_BUTTON_ID = 'principal';

    public function get_group()
    {
        return 'dd-smart-whatsapp';
    }

    public function get_categories()
    {
        if (!class_exists('\Elementor\Modules\DynamicTags\Module')) {
            return [];
        }

        if ('url' === static::FIELD) {
            return [\Elementor\Modules\DynamicTags\Module::URL_CATEGORY];
        }

        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls()
    {
        if ('' !== static::DEFAULT_BUTTON_ID) {
            return;
        }

        $this->add_control(
            'button_id',
            [
                'label' => __('Botão salvo', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->button_options(),
                'default' => 'principal',
            ]
        );
    }

    public function render()
    {
        $button_id = static::DEFAULT_BUTTON_ID;

        if ('' === $button_id) {
            $settings = $this->get_settings();
            $button_id = isset($settings['button_id']) ? sanitize_key($settings['button_id']) : 'principal';
        }

        $button = DDSW_Settings::get_button($button_id);
        if (!$button) {
            return;
        }

        $button = DDSW_Settings::normalize_button($button);
        $value = $this->value_for_field($button);
        echo esc_html($value);
    }

    private function value_for_field(array $button)
    {
        if ('phone' === static::FIELD) {
            return $button['phone'];
        }

        if ('message' === static::FIELD) {
            return DDSW_Placeholders::replace($button['message']);
        }

        if ('url' === static::FIELD) {
            $phone = preg_replace('/\D/', '', $button['phone']);

            return $phone ? 'https://wa.me/' . rawurlencode($phone) : '';
        }

        if ('smart_copy' === static::FIELD) {
            return sprintf(
                '[dd_smart_whatsapp id="%s" mode="smart"]',
                sanitize_key($button['id'])
            );
        }

        return $button['label'];
    }

    private function button_options()
    {
        $options = [];

        foreach (DDSW_Settings::get_buttons(true) as $button) {
            if (empty($button['id'])) {
                continue;
            }

            $options[$button['id']] = $button['label'] . ' (' . $button['id'] . ')';
        }

        return $options ?: ['principal' => __('Principal', 'dd-smart-whatsapp')];
    }
}

final class DDSW_Elementor_Tag_Principal extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'url';
    protected const DEFAULT_BUTTON_ID = 'principal';

    public function get_name()
    {
        return 'ddsw_whatsapp_principal';
    }

    public function get_title()
    {
        return __('DD WhatsApp Principal', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Tag_Support extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'url';
    protected const DEFAULT_BUTTON_ID = 'suporte';

    public function get_name()
    {
        return 'ddsw_whatsapp_support';
    }

    public function get_title()
    {
        return __('DD WhatsApp Suporte', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Tag_Smart_Copy extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'smart_copy';
    protected const DEFAULT_BUTTON_ID = '';

    public function get_name()
    {
        return 'ddsw_smart_copy';
    }

    public function get_title()
    {
        return __('DD Smart Copy', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Tag_Message extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'message';
    protected const DEFAULT_BUTTON_ID = '';

    public function get_name()
    {
        return 'ddsw_whatsapp_message';
    }

    public function get_title()
    {
        return __('Mensagem', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Tag_Phone extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'phone';
    protected const DEFAULT_BUTTON_ID = '';

    public function get_name()
    {
        return 'ddsw_whatsapp_phone';
    }

    public function get_title()
    {
        return __('Número', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Tag_CTA extends DDSW_Elementor_Dynamic_Tag_Base
{
    protected const FIELD = 'label';
    protected const DEFAULT_BUTTON_ID = '';

    public function get_name()
    {
        return 'ddsw_whatsapp_cta';
    }

    public function get_title()
    {
        return __('CTA', 'dd-smart-whatsapp');
    }
}

final class DDSW_Elementor_Dynamic_Tags
{
    public static function tags()
    {
        return [
            new DDSW_Elementor_Tag_Principal(),
            new DDSW_Elementor_Tag_Support(),
            new DDSW_Elementor_Tag_Smart_Copy(),
            new DDSW_Elementor_Tag_Message(),
            new DDSW_Elementor_Tag_Phone(),
            new DDSW_Elementor_Tag_CTA(),
        ];
    }
}
