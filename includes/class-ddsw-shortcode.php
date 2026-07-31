<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Shortcode
{
    public function init()
    {
        add_shortcode('dd_smart_whatsapp', [$this, 'render']);
        add_shortcode('dd_whatsapp', [$this, 'render']);
    }

    public function render($atts = [])
    {
        return DDSW_Renderer::render((array) $atts);
    }
}
