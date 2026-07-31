<?php
/**
 * WordPress Dashboard analytics widget.
 *
 * @package DD_Smart_WhatsApp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Dashboard_Widget
{
    public function init()
    {
        add_action('wp_dashboard_setup', [$this, 'register']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            'ddsw_analytics_dashboard',
            __('DD Smart WhatsApp Analytics', 'dd-smart-whatsapp'),
            [$this, 'render']
        );
    }

    public function enqueue_assets($hook)
    {
        if ('index.php' !== $hook) {
            return;
        }

        wp_enqueue_style('ddsw-admin', DDSW_PLUGIN_URL . 'assets/css/admin.css', [], DDSW_VERSION);
        wp_enqueue_script('ddsw-admin', DDSW_PLUGIN_URL . 'assets/js/admin.js', ['wp-i18n'], DDSW_VERSION, true);
        wp_set_script_translations('ddsw-admin', 'dd-smart-whatsapp', DDSW_PLUGIN_DIR . 'languages');
    }

    public function render()
    {
        $stats = DDSW_Tracker::stats();
        $button_champion = is_array($stats['button_champion']) ? $stats['button_champion'] : null;
        $action_champion = is_array($stats['action_champion']) ? $stats['action_champion'] : null;
        ?>
        <div class="ddsw-dashboard-widget">
            <div class="ddsw-dashboard-widget__metrics">
                <?php $this->metric(__('Total de cliques', 'dd-smart-whatsapp'), $stats['total']); ?>
                <?php $this->metric(__('WhatsApp aberto', 'dd-smart-whatsapp'), $stats['open']); ?>
                <?php $this->metric(__('Smart Copy', 'dd-smart-whatsapp'), $stats['smart_copy']); ?>
                <?php $this->metric(__('Hoje', 'dd-smart-whatsapp'), $stats['today']); ?>
                <?php $this->metric(__('Últimos 7 dias', 'dd-smart-whatsapp'), $stats['last_7_days_total']); ?>
                <?php $this->metric(__('Conversão', 'dd-smart-whatsapp'), number_format_i18n($stats['conversion'], 2) . '%'); ?>
            </div>

            <canvas class="ddsw-chart ddsw-dashboard-widget__chart" width="420" height="140" data-ddsw-chart="<?php echo esc_attr(wp_json_encode($stats['last_30_days'])); ?>" aria-label="<?php esc_attr_e('Últimos 30 dias', 'dd-smart-whatsapp'); ?>"></canvas>

            <div class="ddsw-dashboard-widget__leaders">
                <p>
                    <strong><?php esc_html_e('Botão mais utilizado', 'dd-smart-whatsapp'); ?></strong>
                    <span><?php echo esc_html($button_champion ? $button_champion['button_id'] : __('Ainda sem dados', 'dd-smart-whatsapp')); ?></span>
                </p>
                <p>
                    <strong><?php esc_html_e('Ação campeã', 'dd-smart-whatsapp'); ?></strong>
                    <span><?php echo esc_html($action_champion ? $action_champion['action_type'] : __('Ainda sem dados', 'dd-smart-whatsapp')); ?></span>
                </p>
                <p>
                    <strong><?php esc_html_e('Último clique', 'dd-smart-whatsapp'); ?></strong>
                    <span><?php echo esc_html($stats['last_click'] ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $stats['last_click']) : __('Ainda sem dados', 'dd-smart-whatsapp')); ?></span>
                </p>
            </div>

            <p>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=dd-smart-whatsapp')); ?>">
                    <?php esc_html_e('Abrir estatísticas', 'dd-smart-whatsapp'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    private function metric($label, $value)
    {
        ?>
        <div class="ddsw-dashboard-widget__metric">
            <strong><?php echo esc_html(is_numeric($value) ? number_format_i18n($value) : $value); ?></strong>
            <span><?php echo esc_html($label); ?></span>
        </div>
        <?php
    }
}
