<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Elementor
{
    public function init()
    {
        add_action('elementor/widgets/register', [$this, 'register_widget']);
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
    }

    public function register_widget($widgets_manager)
    {
        if (!did_action('elementor/loaded')) {
            return;
        }

        require_once DDSW_PLUGIN_DIR . 'elementor/class-ddsw-elementor-widget.php';
        $widgets_manager->register(new DDSW_Elementor_Widget());
    }

    public function register_dynamic_tags($dynamic_tags)
    {
        if (!did_action('elementor/loaded') || !class_exists('\Elementor\Core\DynamicTags\Tag')) {
            return;
        }

        if (method_exists($dynamic_tags, 'register_group')) {
            $dynamic_tags->register_group(
                'dd-smart-whatsapp',
                [
                    'title' => __('DD Smart WhatsApp', 'dd-smart-whatsapp'),
                ]
            );
        }

        require_once DDSW_PLUGIN_DIR . 'elementor/class-ddsw-elementor-dynamic-tags.php';

        foreach (DDSW_Elementor_Dynamic_Tags::tags() as $tag) {
            $dynamic_tags->register($tag);
        }
    }
}
