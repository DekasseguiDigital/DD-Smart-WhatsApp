<?php
/**
 * Plugin Name: DD Smart WhatsApp
 * Description: Botões inteligentes de WhatsApp com cópia automática de mensagem, estatísticas, GA4, shortcode e widget Elementor.
 * Version: 2.2.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Dekassegui Digital
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dd-smart-whatsapp
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DDSW_VERSION', '2.2.0');
define('DDSW_PLUGIN_FILE', __FILE__);
define('DDSW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DDSW_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-activator.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-version.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-language.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-language-resolver.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-i18n.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-settings.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-placeholders.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-assets.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-renderer.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-shortcode.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-tracker.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-floating-actions.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-dashboard-widget.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-github-update-source.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-update-checker.php';
require_once DDSW_PLUGIN_DIR . 'admin/class-ddsw-admin.php';
require_once DDSW_PLUGIN_DIR . 'blocks/class-ddsw-block.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-elementor.php';
require_once DDSW_PLUGIN_DIR . 'includes/class-ddsw-plugin.php';

register_activation_hook(__FILE__, ['DDSW_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['DDSW_Activator', 'deactivate']);

add_action('plugins_loaded', static function () {
    DDSW_Plugin::instance()->init();
});
