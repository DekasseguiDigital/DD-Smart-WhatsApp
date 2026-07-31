<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Admin
{
    public function init()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_ddsw_clear_stats', [$this, 'clear_stats']);
        add_action('admin_post_ddsw_export_stats', [$this, 'export_stats']);
    }

    public function register_menu()
    {
        add_menu_page(
            __('DD Smart WhatsApp', 'dd-smart-whatsapp'),
            __('DD WhatsApp', 'dd-smart-whatsapp'),
            'manage_options',
            'dd-smart-whatsapp',
            [$this, 'render_page'],
            'dashicons-format-chat',
            58
        );
    }

    public function register_settings()
    {
        register_setting(
            'ddsw_settings_group',
            DDSW_Settings::OPTION,
            [
                'type' => 'array',
                'sanitize_callback' => ['DDSW_Settings', 'sanitize'],
                'default' => DDSW_Settings::defaults(),
            ]
        );
    }

    public function enqueue_assets($hook)
    {
        if ('toplevel_page_dd-smart-whatsapp' !== $hook) {
            return;
        }

        wp_enqueue_style('ddsw-frontend', DDSW_PLUGIN_URL . 'assets/css/frontend.css', [], DDSW_VERSION);
        wp_enqueue_style('ddsw-admin', DDSW_PLUGIN_URL . 'assets/css/admin.css', ['ddsw-frontend'], DDSW_VERSION);
        wp_enqueue_script('ddsw-admin', DDSW_PLUGIN_URL . 'assets/js/admin.js', ['wp-i18n'], DDSW_VERSION, true);
        wp_set_script_translations('ddsw-admin', 'dd-smart-whatsapp', DDSW_PLUGIN_DIR . 'languages');

        wp_localize_script(
            'ddsw-admin',
            'DDSWAdminI18n',
            [
                'currentLocale' => DDSW_Language::site_locale(),
                'adminLocale' => DDSW_Language::admin_locale(),
                'siteLocale' => DDSW_Language::site_locale(),
                'templateDefaults' => DDSW_I18n::default_button_text_sets(),
                'templateLibrary' => $this->template_library_payload(),
                'confirmRestore' => __('Restaurar os textos padrão para o idioma selecionado?', 'dd-smart-whatsapp'),
                'confirmRestoreAll' => __('Restaurar todos os botões conforme o idioma/modelo selecionado?', 'dd-smart-whatsapp'),
                'templateChangeTitle' => __('O idioma do modelo foi alterado.', 'dd-smart-whatsapp'),
                'templateChangeDescription' => __('Deseja atualizar automaticamente os textos que ainda usam o padrão anterior?', 'dd-smart-whatsapp'),
                'updateCtaLabel' => __('CTA', 'dd-smart-whatsapp'),
                'updateMessageLabel' => __('Mensagem', 'dd-smart-whatsapp'),
                'updateModalLabel' => __('Modal Smart Copy', 'dd-smart-whatsapp'),
                'updateButtonLabel' => __('Atualizar', 'dd-smart-whatsapp'),
                'cancelButtonLabel' => __('Cancelar', 'dd-smart-whatsapp'),
                'copiedLabel' => __('Shortcode copiado.', 'dd-smart-whatsapp'),
            ]
        );
    }

    public function clear_stats()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Você não tem permissão para executar esta ação.', 'dd-smart-whatsapp'));
        }

        check_admin_referer('ddsw_clear_stats');
        DDSW_Tracker::clear_stats();
        wp_safe_redirect(add_query_arg('ddsw_stats_cleared', '1', admin_url('admin.php?page=dd-smart-whatsapp')));
        exit;
    }

    public function export_stats()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Você não tem permissão para executar esta ação.', 'dd-smart-whatsapp'));
        }

        check_admin_referer('ddsw_export_stats');
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=dd-smart-whatsapp-stats.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['button_id', 'action_id', 'action_type', 'event_type', 'copy_status', 'whatsapp_opened', 'device', 'page_url', 'referrer', 'clicked_at']);
        foreach (DDSW_Tracker::csv_rows() as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Você não tem permissão para acessar esta página.', 'dd-smart-whatsapp'));
        }

        $settings = DDSW_Settings::get();
        $buttons = DDSW_Settings::get_buttons(false);
        $stats = DDSW_Tracker::stats();
        ?>
        <div class="wrap ddsw-admin">
            <div class="ddsw-admin__hero">
                <div>
                    <p class="ddsw-admin__eyebrow"><?php esc_html_e('Dekassegui Digital', 'dd-smart-whatsapp'); ?></p>
                    <h1><?php esc_html_e('DD Smart WhatsApp', 'dd-smart-whatsapp'); ?></h1>
                    <p><?php esc_html_e('Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.', 'dd-smart-whatsapp'); ?></p>
                </div>
                <div class="ddsw-admin__shortcode">
                    <span><?php esc_html_e('Smart Copy shortcode', 'dd-smart-whatsapp'); ?></span>
                    <code>[dd_smart_whatsapp id="principal" mode="smart" style="auto"]</code>
                </div>
            </div>

            <?php settings_errors(); ?>

            <?php if (!empty($_GET['ddsw_stats_cleared']) && '1' === sanitize_text_field(wp_unslash($_GET['ddsw_stats_cleared']))) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Estatísticas limpas com sucesso.', 'dd-smart-whatsapp'); ?></p>
                </div>
            <?php endif; ?>

            <div class="ddsw-admin__grid">
                <section class="ddsw-panel ddsw-panel--wide">
                    <form method="post" action="options.php">
                        <?php settings_fields('ddsw_settings_group'); ?>

                        <div class="ddsw-panel__header">
                            <div>
                                <h2><?php esc_html_e('Botões configurados', 'dd-smart-whatsapp'); ?></h2>
                                <p><?php esc_html_e('Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.', 'dd-smart-whatsapp'); ?></p>
                            </div>
                            <button type="button" class="button button-secondary" data-ddsw-add-row>
                                <?php esc_html_e('Adicionar botão', 'dd-smart-whatsapp'); ?>
                            </button>
                        </div>

                        <div class="ddsw-buttons-table" data-ddsw-rows>
                            <?php foreach ($buttons as $index => $button) : ?>
                                <?php $this->render_button_row($button, $index); ?>
                            <?php endforeach; ?>
                        </div>

                        <template data-ddsw-template>
                            <?php $this->render_button_row(DDSW_Settings::defaults()['buttons'][0], '__INDEX__'); ?>
                        </template>

                        <div class="ddsw-settings">
                            <div class="ddsw-settings__group">
                                <h3><?php esc_html_e('Idioma inteligente', 'dd-smart-whatsapp'); ?></h3>
                                <p><?php esc_html_e('O frontend sempre usa o idioma do site. A área administrativa pode seguir o idioma do usuário logado.', 'dd-smart-whatsapp'); ?></p>
                                <label class="ddsw-toggle">
                                    <input type="radio" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[admin_locale_source]" value="site" <?php checked($settings['admin_locale_source'], 'site'); ?>>
                                    <span><?php esc_html_e('Utilizar idioma do site', 'dd-smart-whatsapp'); ?></span>
                                </label>
                                <label class="ddsw-toggle">
                                    <input type="radio" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[admin_locale_source]" value="user" <?php checked($settings['admin_locale_source'], 'user'); ?>>
                                    <span><?php esc_html_e('Utilizar idioma do usuário logado', 'dd-smart-whatsapp'); ?></span>
                                </label>
                            </div>

                            <?php if (empty($settings['wizard_completed'])) : ?>
                                <div class="ddsw-wizard" aria-label="<?php esc_attr_e('Wizard inicial', 'dd-smart-whatsapp'); ?>">
                                    <h3><?php esc_html_e('Bem-vindo ao DD Smart WhatsApp', 'dd-smart-whatsapp'); ?></h3>
                                    <ol>
                                        <li><?php esc_html_e('Passo 1: escolha o idioma.', 'dd-smart-whatsapp'); ?></li>
                                        <li><?php esc_html_e('Passo 2: informe o número WhatsApp.', 'dd-smart-whatsapp'); ?></li>
                                        <li><?php esc_html_e('Passo 3: crie o primeiro botão.', 'dd-smart-whatsapp'); ?></li>
                                        <li><?php esc_html_e('Passo 4: copie o shortcode.', 'dd-smart-whatsapp'); ?></li>
                                    </ol>
                                    <label class="ddsw-toggle">
                                        <input type="checkbox" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[wizard_completed]" value="1">
                                        <span><?php esc_html_e('Concluir wizard', 'dd-smart-whatsapp'); ?></span>
                                    </label>
                                </div>
                            <?php else : ?>
                                <input type="hidden" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[wizard_completed]" value="1">
                            <?php endif; ?>

                            <label class="ddsw-field">
                                <span><?php esc_html_e('Feedback do modo tradicional', 'dd-smart-whatsapp'); ?></span>
                                <input type="text" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[copy_feedback]" value="<?php echo esc_attr($settings['copy_feedback']); ?>">
                            </label>

                            <label class="ddsw-field">
                                <span><?php esc_html_e('Destino padrão', 'dd-smart-whatsapp'); ?></span>
                                <select name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[default_target]">
                                    <option value="_blank" <?php selected($settings['default_target'], '_blank'); ?>><?php esc_html_e('Nova aba', 'dd-smart-whatsapp'); ?></option>
                                    <option value="_self" <?php selected($settings['default_target'], '_self'); ?>><?php esc_html_e('Mesma aba', 'dd-smart-whatsapp'); ?></option>
                                </select>
                            </label>

                            <label class="ddsw-toggle">
                                <input type="checkbox" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[ga4_enabled]" value="1" <?php checked($settings['ga4_enabled'], '1'); ?>>
                                <span><?php esc_html_e('Enviar eventos para GA4 quando gtag estiver disponível', 'dd-smart-whatsapp'); ?></span>
                            </label>

                            <label class="ddsw-toggle">
                                <input type="checkbox" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[hash_ip]" value="1" <?php checked($settings['hash_ip'], '1'); ?>>
                                <span><?php esc_html_e('Gerar hash de IP para deduplicação estatística', 'dd-smart-whatsapp'); ?></span>
                            </label>

                            <label class="ddsw-toggle">
                                <input type="checkbox" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[delete_on_uninstall]" value="1" <?php checked($settings['delete_on_uninstall'], '1'); ?>>
                                <span><?php esc_html_e('Remover configurações e estatísticas ao desinstalar', 'dd-smart-whatsapp'); ?></span>
                            </label>
                        </div>

                        <?php submit_button(__('Salvar configurações', 'dd-smart-whatsapp')); ?>
                        <div class="ddsw-floating-admin">
                            <div class="ddsw-panel__header">
                                <div>
                                    <h2><?php esc_html_e('Smart Floating Actions', 'dd-smart-whatsapp'); ?></h2>
                                    <p><?php esc_html_e('Transforme o WhatsApp em um hub premium de ações flutuantes. Tudo é opcional e preserva os botões existentes.', 'dd-smart-whatsapp'); ?></p>
                                </div>
                                <button type="button" class="button button-secondary" data-ddsw-add-floating-hub>
                                    <?php esc_html_e('Adicionar Floating Hub', 'dd-smart-whatsapp'); ?>
                                </button>
                            </div>

                            <label class="ddsw-toggle">
                                <input type="checkbox" name="<?php echo esc_attr(DDSW_Settings::OPTION); ?>[floating_actions_enabled]" value="1" <?php checked($settings['floating_actions_enabled'], '1'); ?>>
                                <span><?php esc_html_e('Ativar Smart Floating Actions', 'dd-smart-whatsapp'); ?></span>
                            </label>

                            <div class="ddsw-floating-hubs" data-ddsw-floating-hubs>
                                <?php foreach ((array) $settings['floating_hubs'] as $hub_index => $hub) : ?>
                                    <?php $this->render_floating_hub($hub, $hub_index, $buttons); ?>
                                <?php endforeach; ?>
                            </div>

                            <template data-ddsw-floating-hub-template>
                                <?php $this->render_floating_hub(DDSW_Settings::default_floating_hub(), '__HUB_INDEX__', $buttons); ?>
                            </template>
                            <template data-ddsw-floating-action-template>
                                <?php $this->render_floating_action(DDSW_Settings::default_floating_hub()['actions'][0], '__HUB_INDEX__', '__ACTION_INDEX__', $buttons); ?>
                            </template>
                        </div>

                        <?php submit_button(__('Salvar configurações', 'dd-smart-whatsapp')); ?>
                        <button type="button" class="button button-secondary" data-ddsw-restore-all>
                            <?php esc_html_e('Restaurar TODOS os botões', 'dd-smart-whatsapp'); ?>
                        </button>
                    </form>
                </section>

                <aside class="ddsw-panel">
                    <div class="ddsw-panel__header">
                        <div>
                            <h2><?php esc_html_e('Estatísticas', 'dd-smart-whatsapp'); ?></h2>
                            <p><?php esc_html_e('Eventos locais por botão, sem armazenar IP bruto.', 'dd-smart-whatsapp'); ?></p>
                        </div>
                    </div>

                    <div class="ddsw-metrics">
                        <div>
                            <strong><?php echo esc_html(number_format_i18n($stats['total'])); ?></strong>
                            <span><?php esc_html_e('Eventos totais', 'dd-smart-whatsapp'); ?></span>
                        </div>
                        <div>
                            <strong><?php echo esc_html(number_format_i18n($stats['today'])); ?></strong>
                            <span><?php esc_html_e('Hoje', 'dd-smart-whatsapp'); ?></span>
                        </div>
                        <div>
                            <strong><?php echo esc_html(number_format_i18n($stats['smart_copy'])); ?></strong>
                            <span><?php esc_html_e('Smart Copy', 'dd-smart-whatsapp'); ?></span>
                        </div>
                        <div>
                            <strong><?php echo esc_html(number_format_i18n($stats['traditional'])); ?></strong>
                            <span><?php esc_html_e('Tradicional', 'dd-smart-whatsapp'); ?></span>
                        </div>
                        <div>
                            <strong><?php echo esc_html(number_format_i18n($stats['conversion'], 2)); ?>%</strong>
                            <span><?php esc_html_e('Conversão', 'dd-smart-whatsapp'); ?></span>
                        </div>
                    </div>

                    <canvas class="ddsw-chart" width="320" height="120" data-ddsw-chart="<?php echo esc_attr(wp_json_encode($stats['last_30_days'])); ?>" aria-label="<?php esc_attr_e('Últimos 30 dias', 'dd-smart-whatsapp'); ?>"></canvas>

                    <table class="widefat striped ddsw-stats-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Botão', 'dd-smart-whatsapp'); ?></th>
                                <th><?php esc_html_e('Eventos', 'dd-smart-whatsapp'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['by_button'])) : ?>
                                <tr><td colspan="2"><?php esc_html_e('Ainda sem eventos registrados.', 'dd-smart-whatsapp'); ?></td></tr>
                            <?php else : ?>
                                <?php foreach ($stats['by_button'] as $row) : ?>
                                    <tr>
                                        <td>
                                            <code><?php echo esc_html($row['button_id']); ?></code>
                                            <small><?php echo esc_html($row['last_click']); ?></small>
                                        </td>
                                        <td><?php echo esc_html(number_format_i18n((int) $row['total'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="ddsw-event-list">
                        <?php foreach ($stats['by_event'] as $event) : ?>
                            <p><code><?php echo esc_html($event['event_type']); ?></code> <?php echo esc_html(number_format_i18n((int) $event['total'])); ?></p>
                        <?php endforeach; ?>
                    </div>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ddsw-clear-stats">
                        <input type="hidden" name="action" value="ddsw_clear_stats">
                        <?php wp_nonce_field('ddsw_clear_stats'); ?>
                        <?php submit_button(__('Limpar estatísticas', 'dd-smart-whatsapp'), 'secondary', 'submit', false); ?>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ddsw-export-stats">
                        <input type="hidden" name="action" value="ddsw_export_stats">
                        <?php wp_nonce_field('ddsw_export_stats'); ?>
                        <?php submit_button(__('Exportar CSV', 'dd-smart-whatsapp'), 'secondary', 'submit', false); ?>
                    </form>
                </aside>

                <aside class="ddsw-panel">
                    <div class="ddsw-panel__header">
                        <div>
                            <h2><?php esc_html_e('Placeholders', 'dd-smart-whatsapp'); ?></h2>
                            <p><?php esc_html_e('Variáveis disponíveis para mensagens dinâmicas.', 'dd-smart-whatsapp'); ?></p>
                        </div>
                    </div>
                    <div class="ddsw-placeholders">
                        <?php foreach (DDSW_Placeholders::available() as $placeholder) : ?>
                            <code><?php echo esc_html($placeholder); ?></code>
                        <?php endforeach; ?>
                    </div>
                </aside>
                <aside class="ddsw-panel">
                    <div class="ddsw-panel__header">
                        <div>
                            <h2><?php esc_html_e('Gerador de shortcode', 'dd-smart-whatsapp'); ?></h2>
                            <p><?php esc_html_e('Escolha as opções e copie o shortcode automaticamente.', 'dd-smart-whatsapp'); ?></p>
                        </div>
                    </div>
                    <div class="ddsw-shortcode-generator">
                        <label class="ddsw-field">
                            <span><?php esc_html_e('Botão', 'dd-smart-whatsapp'); ?></span>
                            <select data-ddsw-shortcode-button>
                                <?php foreach ($buttons as $button) : ?>
                                    <option value="<?php echo esc_attr($button['id']); ?>"><?php echo esc_html($button['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="ddsw-field">
                            <span><?php esc_html_e('Modo', 'dd-smart-whatsapp'); ?></span>
                            <select data-ddsw-shortcode-mode>
                                <option value="smart"><?php esc_html_e('Smart Copy', 'dd-smart-whatsapp'); ?></option>
                                <option value="traditional"><?php esc_html_e('Tradicional', 'dd-smart-whatsapp'); ?></option>
                            </select>
                        </label>
                        <input class="regular-text" type="text" readonly data-ddsw-shortcode-output value="">
                        <button type="button" class="button" data-ddsw-copy-shortcode><?php esc_html_e('Copiar shortcode', 'dd-smart-whatsapp'); ?></button>
                    </div>
                </aside>
            </div>
        </div>
        <?php
    }

    private function render_button_row(array $button, $index)
    {
        $button = DDSW_Settings::normalize_button($button);
        $option = DDSW_Settings::OPTION;
        ?>
        <article class="ddsw-button-row">
            <div class="ddsw-button-row__top">
                <label class="ddsw-field">
                    <span><?php esc_html_e('ID estável', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($button['id']); ?>" placeholder="<?php echo esc_attr_x('principal', 'Default button ID placeholder', 'dd-smart-whatsapp'); ?>">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Rótulo', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][label]" value="<?php echo esc_attr($button['label']); ?>" placeholder="<?php esc_attr_e('Falar no WhatsApp', 'dd-smart-whatsapp'); ?>" data-ddsw-template-field="label">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Telefone', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][phone]" value="<?php echo esc_attr($button['phone']); ?>" placeholder="<?php echo esc_attr_x('+819012345678', 'Phone number placeholder', 'dd-smart-whatsapp'); ?>">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Idioma do modelo', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][template_locale]" data-ddsw-template-locale data-ddsw-previous-locale="<?php echo esc_attr(DDSW_Language::template_locale($button, 'admin')); ?>">
                        <?php foreach (DDSW_I18n::template_locale_options() as $locale_key => $locale_label) : ?>
                            <option value="<?php echo esc_attr($locale_key); ?>" <?php selected($button['template_locale'], $locale_key); ?>><?php echo esc_html($locale_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Biblioteca de modelos', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][template_key]" data-ddsw-template-key data-ddsw-previous-key="<?php echo esc_attr($button['template_key']); ?>">
                        <?php foreach (DDSW_I18n::template_library(DDSW_Language::template_locale($button, 'admin')) as $template_key => $template) : ?>
                            <option value="<?php echo esc_attr($template_key); ?>" <?php selected($button['template_key'], $template_key); ?>><?php echo esc_html($template['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <fieldset class="ddsw-mode-field">
                <legend><?php esc_html_e('Modo de envio', 'dd-smart-whatsapp'); ?></legend>
                <label>
                    <input type="radio" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][mode]" value="traditional" <?php checked($button['mode'], 'traditional'); ?>>
                    <strong><?php esc_html_e('Tradicional', 'dd-smart-whatsapp'); ?></strong>
                    <span><?php esc_html_e('Abre o WhatsApp com a mensagem pelo parâmetro text=', 'dd-smart-whatsapp'); ?></span>
                </label>
                <label>
                    <input type="radio" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][mode]" value="smart" <?php checked($button['mode'], 'smart'); ?>>
                    <strong><?php esc_html_e('Smart Copy', 'dd-smart-whatsapp'); ?></strong>
                    <span><?php esc_html_e('Copia a mensagem formatada e abre o WhatsApp sem texto', 'dd-smart-whatsapp'); ?></span>
                </label>
            </fieldset>

            <label class="ddsw-field">
                <span><?php esc_html_e('Mensagem', 'dd-smart-whatsapp'); ?></span>
                <textarea name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][message]" rows="6" data-ddsw-template-field="message"><?php echo esc_textarea($button['message']); ?></textarea>
            </label>

            <p>
                <button type="button" class="button" data-ddsw-restore-defaults>
                    <?php esc_html_e('Restaurar botão atual', 'dd-smart-whatsapp'); ?>
                </button>
            </p>

            <details class="ddsw-advanced">
                <summary><?php esc_html_e('Modal Smart Copy', 'dd-smart-whatsapp'); ?></summary>
                <div class="ddsw-modal-fields">
                    <?php $this->text_input($button, $index, 'modal_title', __('Título', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'modal_success', __('Mensagem de sucesso', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'desktop_instruction', __('Instrução desktop', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'ios_instruction', __('Instrução iPhone/iPad', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'android_instruction', __('Instrução Android', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'open_label', __('Rótulo do botão abrir', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'close_label', __('Rótulo do botão fechar', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'retry_label', __('Rótulo copiar novamente', 'dd-smart-whatsapp')); ?>
                    <?php $this->text_input($button, $index, 'error_message', __('Mensagem de erro', 'dd-smart-whatsapp')); ?>

                    <label class="ddsw-field">
                        <span><?php esc_html_e('Estilo do modal', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][modal_style]">
                            <option value="clean" <?php selected($button['modal_style'], 'clean'); ?>><?php esc_html_e('Clean', 'dd-smart-whatsapp'); ?></option>
                            <option value="soft" <?php selected($button['modal_style'], 'soft'); ?>><?php esc_html_e('Soft', 'dd-smart-whatsapp'); ?></option>
                            <option value="dark" <?php selected($button['modal_style'], 'dark'); ?>><?php esc_html_e('Dark', 'dd-smart-whatsapp'); ?></option>
                        </select>
                    </label>

                    <label class="ddsw-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][auto_open]" value="1" <?php checked($button['auto_open'], '1'); ?>>
                        <span><?php esc_html_e('Abrir automaticamente após copiar', 'dd-smart-whatsapp'); ?></span>
                    </label>

                    <label class="ddsw-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][auto_close]" value="1" <?php checked($button['auto_close'], '1'); ?>>
                        <span><?php esc_html_e('Fechar modal automaticamente', 'dd-smart-whatsapp'); ?></span>
                    </label>

                    <?php $this->number_input($button, $index, 'auto_close_delay', __('Tempo para fechar modal', 'dd-smart-whatsapp')); ?>

                    <label class="ddsw-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][hide_again]" value="1" <?php checked($button['hide_again'], '1'); ?>>
                        <span><?php esc_html_e('Não mostrar novamente neste navegador', 'dd-smart-whatsapp'); ?></span>
                    </label>

                    <label class="ddsw-field ddsw-field--compact">
                        <span><?php esc_html_e('Delay', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][auto_open_delay]">
                            <?php foreach ([500, 1000, 1500, 2000] as $delay) : ?>
                                <option value="<?php echo esc_attr($delay); ?>" <?php selected((int) $button['auto_open_delay'], $delay); ?>>
                                    <?php
                                    printf(
                                        /* translators: %d: delay in milliseconds. */
                                        esc_html__('%d ms', 'dd-smart-whatsapp'),
                                        absint($delay)
                                    );
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </details>

            <details class="ddsw-advanced">
                <summary><?php esc_html_e('Aparência do botão', 'dd-smart-whatsapp'); ?></summary>
                <div class="ddsw-modal-fields" data-ddsw-appearance>
                    <label class="ddsw-field">
                        <span><?php esc_html_e('Estilo do botão', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][style]" data-ddsw-style-select>
                            <?php foreach (DDSW_Settings::style_options() as $style_key => $style_label) : ?>
                                <option value="<?php echo esc_attr($style_key); ?>" <?php selected($button['style'], $style_key); ?>><?php echo esc_html($style_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <div class="ddsw-custom-style-fields" data-ddsw-custom-style>
                        <?php $this->color_input($button, $index, 'background', __('Cor fundo', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'color', __('Cor texto', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'hover_background', __('Hover fundo', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'hover_color', __('Hover texto', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'border_color', __('Borda', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'icon_color', __('Cor ícone', 'dd-smart-whatsapp')); ?>
                        <?php $this->color_input($button, $index, 'icon_hover_color', __('Hover ícone', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'border_width', __('Espessura da borda', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'radius', __('Raio', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'padding_y', __('Padding vertical', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'padding_x', __('Padding horizontal', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'icon_size', __('Tamanho ícone', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'icon_spacing', __('Distância entre ícone e texto', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'font_size', __('Tamanho do texto', 'dd-smart-whatsapp')); ?>
                        <?php $this->text_input($button, $index, 'font_family', __('Família da fonte', 'dd-smart-whatsapp')); ?>
                        <?php $this->number_input($button, $index, 'font_weight', __('Peso da fonte', 'dd-smart-whatsapp'), 100); ?>
                        <?php $this->number_input($button, $index, 'transition', __('Transição', 'dd-smart-whatsapp')); ?>
                        <?php $this->text_input($button, $index, 'shadow', __('Sombra', 'dd-smart-whatsapp')); ?>
                        <?php $this->text_input($button, $index, 'hover_shadow', __('Sombra no hover', 'dd-smart-whatsapp')); ?>
                    </div>

                    <?php $this->toggle_input($button, $index, 'inherit_font', __('Herdar fonte do tema', 'dd-smart-whatsapp')); ?>
                    <?php $this->toggle_input($button, $index, 'inherit_font_size', __('Herdar tamanho de texto', 'dd-smart-whatsapp')); ?>
                    <?php $this->toggle_input($button, $index, 'inherit_radius', __('Herdar raio das bordas', 'dd-smart-whatsapp')); ?>
                    <?php $this->toggle_input($button, $index, 'inherit_shadow', __('Herdar sombra', 'dd-smart-whatsapp')); ?>
                    <?php $this->toggle_input($button, $index, 'inherit_padding', __('Herdar padding', 'dd-smart-whatsapp')); ?>

                    <label class="ddsw-toggle">
                        <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][icon]" value="1" <?php checked($button['icon'], '1'); ?>>
                        <span><?php esc_html_e('Mostrar ícone', 'dd-smart-whatsapp'); ?></span>
                    </label>

                    <label class="ddsw-field">
                        <span><?php esc_html_e('Largura', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][width]">
                            <option value="auto" <?php selected($button['width'], 'auto'); ?>><?php esc_html_e('Automática', 'dd-smart-whatsapp'); ?></option>
                            <option value="full" <?php selected($button['width'], 'full'); ?>><?php esc_html_e('100%', 'dd-smart-whatsapp'); ?></option>
                        </select>
                    </label>

                    <label class="ddsw-field">
                        <span><?php esc_html_e('Alinhamento', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][align]">
                            <option value="left" <?php selected($button['align'], 'left'); ?>><?php esc_html_e('Esquerda', 'dd-smart-whatsapp'); ?></option>
                            <option value="center" <?php selected($button['align'], 'center'); ?>><?php esc_html_e('Centro', 'dd-smart-whatsapp'); ?></option>
                            <option value="right" <?php selected($button['align'], 'right'); ?>><?php esc_html_e('Direita', 'dd-smart-whatsapp'); ?></option>
                        </select>
                    </label>

                    <label class="ddsw-field">
                        <span><?php esc_html_e('Transformação', 'dd-smart-whatsapp'); ?></span>
                        <select name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][text_transform]">
                            <option value="none" <?php selected($button['text_transform'], 'none'); ?>><?php esc_html_e('Nenhuma', 'dd-smart-whatsapp'); ?></option>
                            <option value="uppercase" <?php selected($button['text_transform'], 'uppercase'); ?>><?php esc_html_e('Maiúsculas', 'dd-smart-whatsapp'); ?></option>
                            <option value="lowercase" <?php selected($button['text_transform'], 'lowercase'); ?>><?php esc_html_e('Minúsculas', 'dd-smart-whatsapp'); ?></option>
                            <option value="capitalize" <?php selected($button['text_transform'], 'capitalize'); ?>><?php esc_html_e('Capitalizar', 'dd-smart-whatsapp'); ?></option>
                        </select>
                    </label>
                </div>
            </details>

            <div class="ddsw-button-row__bottom">
                <input type="hidden" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][variant]" value="<?php echo esc_attr($button['variant']); ?>">
                <label class="ddsw-toggle">
                    <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked($button['enabled'], '1'); ?>>
                    <span><?php esc_html_e('Ativo', 'dd-smart-whatsapp'); ?></span>
                </label>
                <button type="button" class="button button-link-delete" data-ddsw-remove-row>
                    <?php esc_html_e('Remover', 'dd-smart-whatsapp'); ?>
                </button>
            </div>
            <div class="ddsw-live-preview" data-ddsw-preview>
                <strong><?php esc_html_e('Preview em tempo real', 'dd-smart-whatsapp'); ?></strong>
                <div class="ddsw-wrap ddsw-align-left" data-ddsw-style="<?php echo esc_attr($button['style']); ?>">
                    <span class="ddsw-button ddsw-style-auto" data-ddsw-preview-button>
                        <span class="ddsw-icon ddsw-button__icon" aria-hidden="true"><?php echo DDSW_Renderer::icon_preview_svg(); ?></span>
                        <span class="ddsw-label ddsw-button__label" data-ddsw-preview-label><?php echo esc_html($button['label']); ?></span>
                    </span>
                </div>
                <small data-ddsw-preview-message><?php echo esc_html(wp_trim_words($button['message'], 18)); ?></small>
            </div>
        </article>
        <?php
    }

    private function render_floating_hub(array $hub, $index, array $buttons)
    {
        $hub = DDSW_Settings::normalize_floating_hub($hub);
        $option = DDSW_Settings::OPTION;
        ?>
        <article class="ddsw-floating-admin__hub" data-ddsw-floating-hub-row>
            <div class="ddsw-button-row__top">
                <label class="ddsw-field">
                    <span><?php esc_html_e('Nome do hub', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($hub['name']); ?>">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('ID', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($hub['id']); ?>">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Tipo', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][layout]">
                        <?php foreach (DDSW_Settings::floating_layout_options() as $layout_key => $layout_label) : ?>
                            <option value="<?php echo esc_attr($layout_key); ?>" <?php selected($hub['layout'], $layout_key); ?>><?php echo esc_html($layout_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Posição', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][position]">
                        <option value="bottom-right" <?php selected($hub['position'], 'bottom-right'); ?>><?php esc_html_e('Inferior direita', 'dd-smart-whatsapp'); ?></option>
                        <option value="bottom-left" <?php selected($hub['position'], 'bottom-left'); ?>><?php esc_html_e('Inferior esquerda', 'dd-smart-whatsapp'); ?></option>
                    </select>
                </label>
            </div>

            <div class="ddsw-floating-admin__settings">
                <?php $this->number_named_input($index, 'offset_x', __('Distância horizontal', 'dd-smart-whatsapp'), $hub['offset_x']); ?>
                <?php $this->number_named_input($index, 'offset_y', __('Distância vertical', 'dd-smart-whatsapp'), $hub['offset_y']); ?>
                <?php $this->number_named_input($index, 'size', __('Tamanho', 'dd-smart-whatsapp'), $hub['size']); ?>
                <?php $this->color_named_input($index, 'main_color', __('Cor principal', 'dd-smart-whatsapp'), $hub['main_color']); ?>
                <?php $this->color_named_input($index, 'background', __('Cor do fundo', 'dd-smart-whatsapp'), $hub['background']); ?>
                <?php $this->color_named_input($index, 'hover_color', __('Cor do hover', 'dd-smart-whatsapp'), $hub['hover_color']); ?>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Velocidade', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][speed]">
                        <option value="slow" <?php selected($hub['speed'], 'slow'); ?>><?php esc_html_e('Lenta', 'dd-smart-whatsapp'); ?></option>
                        <option value="normal" <?php selected($hub['speed'], 'normal'); ?>><?php esc_html_e('Normal', 'dd-smart-whatsapp'); ?></option>
                        <option value="fast" <?php selected($hub['speed'], 'fast'); ?>><?php esc_html_e('Rápida', 'dd-smart-whatsapp'); ?></option>
                    </select>
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Animação', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][animation]">
                        <option value="lift" <?php selected($hub['animation'], 'lift'); ?>><?php esc_html_e('Elevação', 'dd-smart-whatsapp'); ?></option>
                        <option value="fade" <?php selected($hub['animation'], 'fade'); ?>><?php esc_html_e('Fade', 'dd-smart-whatsapp'); ?></option>
                        <option value="scale" <?php selected($hub['animation'], 'scale'); ?>><?php esc_html_e('Escala', 'dd-smart-whatsapp'); ?></option>
                    </select>
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Ícone principal', 'dd-smart-whatsapp'); ?></span>
                    <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][main_icon]" value="<?php echo esc_attr($hub['main_icon']); ?>">
                </label>
                <label class="ddsw-field">
                    <span><?php esc_html_e('Comportamento no mobile', 'dd-smart-whatsapp'); ?></span>
                    <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][mobile_behavior]">
                        <option value="labels" <?php selected($hub['mobile_behavior'], 'labels'); ?>><?php esc_html_e('Exibir rótulos', 'dd-smart-whatsapp'); ?></option>
                        <option value="icons" <?php selected($hub['mobile_behavior'], 'icons'); ?>><?php esc_html_e('Somente ícones', 'dd-smart-whatsapp'); ?></option>
                    </select>
                </label>
            </div>

            <div class="ddsw-floating-admin__actions" data-ddsw-floating-actions>
                <?php foreach ($hub['actions'] as $action_index => $action) : ?>
                    <?php $this->render_floating_action($action, $index, $action_index, $buttons); ?>
                <?php endforeach; ?>
            </div>

            <div class="ddsw-button-row__bottom">
                <label class="ddsw-toggle">
                    <input type="checkbox" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked($hub['enabled'], '1'); ?>>
                    <span><?php esc_html_e('Hub ativo', 'dd-smart-whatsapp'); ?></span>
                </label>
                <label class="ddsw-toggle">
                    <input type="checkbox" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($index); ?>][show_labels]" value="1" <?php checked($hub['show_labels'], '1'); ?>>
                    <span><?php esc_html_e('Exibir rótulos das ações', 'dd-smart-whatsapp'); ?></span>
                </label>
                <button type="button" class="button" data-ddsw-add-floating-action>
                    <?php esc_html_e('Adicionar ação', 'dd-smart-whatsapp'); ?>
                </button>
                <button type="button" class="button button-link-delete" data-ddsw-remove-floating-hub>
                    <?php esc_html_e('Remover hub', 'dd-smart-whatsapp'); ?>
                </button>
            </div>
        </article>
        <?php
    }

    private function render_floating_action(array $action, $hub_index, $action_index, array $buttons)
    {
        $action = DDSW_Settings::normalize_floating_action($action);
        $option = DDSW_Settings::OPTION;
        ?>
        <div class="ddsw-floating-admin__action" draggable="true" data-ddsw-floating-action-row>
            <span class="ddsw-floating-admin__drag" aria-hidden="true">⋮⋮</span>
            <label class="ddsw-field">
                <span><?php esc_html_e('Nome', 'dd-smart-whatsapp'); ?></span>
                <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][name]" value="<?php echo esc_attr($action['name']); ?>">
            </label>
            <label class="ddsw-field">
                <span><?php esc_html_e('Tipo', 'dd-smart-whatsapp'); ?></span>
                <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][type]" data-ddsw-floating-action-type>
                    <?php foreach (DDSW_Settings::floating_action_types() as $type_key => $type_label) : ?>
                        <option value="<?php echo esc_attr($type_key); ?>" <?php selected($action['type'], $type_key); ?>><?php echo esc_html($type_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="ddsw-field">
                <span><?php esc_html_e('URL / telefone', 'dd-smart-whatsapp'); ?></span>
                <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][url]" value="<?php echo esc_attr($action['url']); ?>">
            </label>
            <label class="ddsw-field">
                <span><?php esc_html_e('Botão WhatsApp', 'dd-smart-whatsapp'); ?></span>
                <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][button_id]">
                    <?php foreach ($buttons as $button) : ?>
                        <option value="<?php echo esc_attr($button['id']); ?>" <?php selected($action['button_id'], $button['id']); ?>><?php echo esc_html($button['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="ddsw-field">
                <span><?php esc_html_e('Mensagem inicial', 'dd-smart-whatsapp'); ?></span>
                <textarea rows="3" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][initial_message]" placeholder="<?php echo esc_attr__('Olá {{name}}, encontrei seu site e gostaria de solicitar um orçamento.', 'dd-smart-whatsapp'); ?>"><?php echo esc_textarea($action['initial_message']); ?></textarea>
            </label>
            <label class="ddsw-field">
                <span><?php esc_html_e('Modo da mensagem', 'dd-smart-whatsapp'); ?></span>
                <select name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][message_mode]" data-ddsw-floating-message-mode>
                    <?php foreach (DDSW_Settings::floating_message_modes() as $mode_key => $mode_label) : ?>
                        <option value="<?php echo esc_attr($mode_key); ?>" <?php selected($action['message_mode'], $mode_key); ?>><?php echo esc_html($mode_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="ddsw-field ddsw-field--compact">
                <span><?php esc_html_e('Ícone', 'dd-smart-whatsapp'); ?></span>
                <input type="text" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][icon]" value="<?php echo esc_attr($action['icon']); ?>">
            </label>
            <label class="ddsw-field ddsw-field--compact">
                <span><?php esc_html_e('Cor', 'dd-smart-whatsapp'); ?></span>
                <input type="color" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][color]" value="<?php echo esc_attr($action['color']); ?>">
            </label>
            <label class="ddsw-field ddsw-field--compact">
                <span><?php esc_html_e('Ordem', 'dd-smart-whatsapp'); ?></span>
                <input type="number" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][order]" value="<?php echo esc_attr($action['order']); ?>">
            </label>
            <input type="hidden" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][id]" value="<?php echo esc_attr($action['id']); ?>">
            <label class="ddsw-toggle">
                <input type="checkbox" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][visible]" value="1" <?php checked($action['visible'], '1'); ?>>
                <span><?php esc_html_e('Exibir', 'dd-smart-whatsapp'); ?></span>
            </label>
            <label class="ddsw-toggle">
                <input type="checkbox" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][actions][<?php echo esc_attr($action_index); ?>][new_tab]" value="1" <?php checked($action['new_tab'], '1'); ?>>
                <span><?php esc_html_e('Nova aba', 'dd-smart-whatsapp'); ?></span>
            </label>
            <button type="button" class="button button-link-delete" data-ddsw-remove-floating-action>
                <?php esc_html_e('Remover', 'dd-smart-whatsapp'); ?>
            </button>
        </div>
        <?php
    }

    private function number_named_input($hub_index, $key, $label, $value)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-field">
            <span><?php echo esc_html($label); ?></span>
            <input type="number" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>">
        </label>
        <?php
    }

    private function color_named_input($hub_index, $key, $label, $value)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-field">
            <span><?php echo esc_html($label); ?></span>
            <input type="color" name="<?php echo esc_attr($option); ?>[floating_hubs][<?php echo esc_attr($hub_index); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>">
        </label>
        <?php
    }

    private function template_library_payload()
    {
        $payload = [];

        foreach (DDSW_I18n::supported_template_locales() as $locale) {
            $payload[$locale] = DDSW_I18n::template_library($locale);
        }

        return $payload;
    }

    private function text_input(array $button, $index, $key, $label)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-field">
            <span><?php echo esc_html($label); ?></span>
            <input type="text" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($button[$key]); ?>" data-ddsw-template-field="<?php echo esc_attr($key); ?>">
        </label>
        <?php
    }

    private function color_input(array $button, $index, $key, $label)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-field">
            <span><?php echo esc_html($label); ?></span>
            <input class="ddsw-color-field" type="color" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($button[$key] ?: '#000000'); ?>">
        </label>
        <?php
    }

    private function number_input(array $button, $index, $key, $label, $step = 1)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-field">
            <span><?php echo esc_html($label); ?></span>
            <input type="number" step="<?php echo esc_attr($step); ?>" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($button[$key]); ?>">
        </label>
        <?php
    }

    private function toggle_input(array $button, $index, $key, $label)
    {
        $option = DDSW_Settings::OPTION;
        ?>
        <label class="ddsw-toggle">
            <input type="checkbox" name="<?php echo esc_attr($option); ?>[buttons][<?php echo esc_attr($index); ?>][<?php echo esc_attr($key); ?>]" value="1" <?php checked($button[$key], '1'); ?>>
            <span><?php echo esc_html($label); ?></span>
        </label>
        <?php
    }
}
