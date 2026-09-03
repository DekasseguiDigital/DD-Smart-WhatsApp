<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Plugin
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        DDSW_I18n::load();
        DDSW_Activator::maybe_upgrade();
        DDSW_Settings::maybe_upgrade();
        DDSW_Activator::init_multisite_hooks();
        \DD\SmartWhatsApp\Assets::init();

        (new DDSW_Shortcode())->init();
        (new DDSW_Block())->init();
        (new DDSW_Tracker())->init();
        (new DDSW_Floating_Actions())->init();

        if (is_admin()) {
            (new DDSW_Plugin_Meta())->init();
            (new DDSW_Admin())->init();
            (new DDSW_Dashboard_Widget())->init();
            (new DDSW_Update_Checker())->init();
        }

        (new DDSW_Elementor())->init();
    }
}
