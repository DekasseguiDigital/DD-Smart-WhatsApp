<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Language_Resolver
{
    public static function locale($context = '')
    {
        return DDSW_Language::resolve($context ?: (is_admin() ? 'admin' : 'frontend'))['resolved_locale'];
    }

    public static function resolve($context = '')
    {
        return DDSW_Language::resolve($context ?: (is_admin() ? 'admin' : 'frontend'));
    }

    public static function last_resolution($context = '')
    {
        return DDSW_Language::last_resolution($context ?: (is_admin() ? 'admin' : 'frontend'));
    }

    public static function locale_family($locale)
    {
        return DDSW_Language::locale_family($locale);
    }

    public static function normalize_locale($locale)
    {
        return DDSW_Language::normalize_locale($locale);
    }
}
