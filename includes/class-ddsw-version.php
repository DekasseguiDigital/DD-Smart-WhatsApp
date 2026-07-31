<?php
/**
 * Plugin version metadata.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Version
{
    public static function current()
    {
        return DDSW_VERSION;
    }

    public static function basename()
    {
        return plugin_basename(DDSW_PLUGIN_FILE);
    }

    public static function slug()
    {
        return dirname(self::basename());
    }

    public static function name()
    {
        return __('DD Smart WhatsApp', 'dd-smart-whatsapp');
    }

    public static function repository()
    {
        /**
         * Filters the update repository in owner/name format.
         *
         * @param string $repository GitHub repository.
         */
        return (string) apply_filters('ddsw_update_repository', 'DekasseguiDigital/DD-Smart-WhatsApp');
    }

    public static function repository_url()
    {
        return 'https://github.com/' . self::repository();
    }
}
