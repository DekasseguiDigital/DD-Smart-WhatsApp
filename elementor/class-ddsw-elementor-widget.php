<?php

if (!defined('ABSPATH')) {
    exit;
}

final class DDSW_Elementor_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'dd_smart_whatsapp';
    }

    public function get_title()
    {
        return __('DD Smart WhatsApp', 'dd-smart-whatsapp');
    }

    public function get_icon()
    {
        return 'eicon-whatsapp';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return [
            _x('whatsapp', 'Elementor search keyword', 'dd-smart-whatsapp'),
            _x('copy', 'Elementor search keyword', 'dd-smart-whatsapp'),
            _x('smart copy', 'Elementor search keyword', 'dd-smart-whatsapp'),
            _x('dekassegui', 'Elementor search keyword', 'dd-smart-whatsapp'),
            _x('cta', 'Elementor search keyword', 'dd-smart-whatsapp'),
        ];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Conteúdo', 'dd-smart-whatsapp'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'button_id',
            [
                'label' => __('Botão salvo', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->button_options(),
                'default' => 'principal',
            ]
        );

        $this->add_control(
            'phone',
            [
                'label' => __('Telefone', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Usar telefone do painel', 'dd-smart-whatsapp'),
                'dynamic' => ['active' => true],
            ]
        );

        $this->add_control(
            'mode',
            [
                'label' => __('Modo de envio', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do botão', 'dd-smart-whatsapp'),
                    'traditional' => __('Tradicional', 'dd-smart-whatsapp'),
                    'smart' => __('Smart Copy', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'label',
            [
                'label' => __('Substituir rótulo', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Usar rótulo do painel', 'dd-smart-whatsapp'),
                'dynamic' => ['active' => true],
            ]
        );

        $this->add_control(
            'message',
            [
                'label' => __('Sobrescrever mensagem', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => __('Usar mensagem do painel', 'dd-smart-whatsapp'),
                'dynamic' => ['active' => true],
            ]
        );

        $this->add_control(
            'new_tab',
            [
                'label' => __('Abrir em nova aba', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => '1',
                'default' => '1',
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => __('Mostrar ícone', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => '1',
                'default' => '1',
            ]
        );

        $this->add_control(
            'svg',
            [
                'label' => __('SVG do ícone', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Opcional. Deixe vazio para usar o ícone padrão do WhatsApp.', 'dd-smart-whatsapp'),
            ]
        );

        $this->add_control(
            'width',
            [
                'label' => __('Largura', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do botão', 'dd-smart-whatsapp'),
                    'auto' => __('Automática', 'dd-smart-whatsapp'),
                    'full' => __('100%', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alinhamento', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Esquerda', 'dd-smart-whatsapp'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Centro', 'dd-smart-whatsapp'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Direita', 'dd-smart-whatsapp'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'custom_class',
            [
                'label' => __('Classe CSS', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'custom_attributes',
            [
                'label' => __('Custom Attributes', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => __('Use uma linha por atributo no formato nome|valor. Permitidos: data-*, aria-*, role, title.', 'dd-smart-whatsapp'),
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => __('Estilo do botão', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array_merge(
                    ['' => __('Usar padrão do botão', 'dd-smart-whatsapp')],
                    DDSW_Settings::style_options()
                ),
                'default' => '',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_button_section',
            [
                'label' => __('Botão', 'dd-smart-whatsapp'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background',
            [
                'label' => __('Background', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'color',
            [
                'label' => __('Text Color', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_background',
            [
                'label' => __('Hover Background', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-hover-background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_color',
            [
                'label' => __('Hover Color', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-hover-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => __('Border Color', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_width',
            [
                'label' => __('Border Width', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 12]],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'radius',
            [
                'label' => __('Border Radius', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 80]],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => __('Padding', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin',
            [
                'label' => __('Margin', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .ddsw-button',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .ddsw-button',
            ]
        );

        $this->add_control(
            'transition',
            [
                'label' => __('Transition', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 0, 'max' => 1000]],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-transition: {{SIZE}}ms;',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_icon_section',
            [
                'label' => __('Ícone', 'dd-smart-whatsapp'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Cor', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-icon-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_hover_color',
            [
                'label' => __('Hover', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-icon-hover-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Tamanho', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 8, 'max' => 64]],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-icon-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Espaçamento', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 40]],
                'selectors' => [
                    '{{WRAPPER}} .ddsw-wrap' => '--ddsw-icon-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'smart_section',
            [
                'label' => __('Smart Copy Modal', 'dd-smart-whatsapp'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $modal_controls = [
            'modal_title' => __('Título do modal', 'dd-smart-whatsapp'),
            'modal_success' => __('Mensagem de sucesso', 'dd-smart-whatsapp'),
            'desktop_instruction' => __('Instrução desktop', 'dd-smart-whatsapp'),
            'ios_instruction' => __('Instrução iPhone/iPad', 'dd-smart-whatsapp'),
            'android_instruction' => __('Instrução Android', 'dd-smart-whatsapp'),
            'open_label' => __('Rótulo Abrir WhatsApp', 'dd-smart-whatsapp'),
            'close_label' => __('Rótulo Fechar', 'dd-smart-whatsapp'),
            'retry_label' => __('Rótulo Copiar novamente', 'dd-smart-whatsapp'),
            'error_message' => __('Mensagem de erro', 'dd-smart-whatsapp'),
        ];

        foreach ($modal_controls as $key => $label) {
            $this->add_control(
                $key,
                [
                    'label' => $label,
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'placeholder' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                ]
            );
        }

        $this->add_control(
            'auto_open',
            [
                'label' => __('Abrir automaticamente após copiar', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                    '1' => __('Ativado', 'dd-smart-whatsapp'),
                    '0' => __('Desativado', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'auto_open_delay',
            [
                'label' => __('Delay automático', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                    '500' => sprintf(
                        /* translators: %d: delay in milliseconds. */
                        __('%d ms', 'dd-smart-whatsapp'),
                        500
                    ),
                    '1000' => sprintf(
                        /* translators: %d: delay in milliseconds. */
                        __('%d ms', 'dd-smart-whatsapp'),
                        1000
                    ),
                    '1500' => sprintf(
                        /* translators: %d: delay in milliseconds. */
                        __('%d ms', 'dd-smart-whatsapp'),
                        1500
                    ),
                    '2000' => sprintf(
                        /* translators: %d: delay in milliseconds. */
                        __('%d ms', 'dd-smart-whatsapp'),
                        2000
                    ),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'auto_close',
            [
                'label' => __('Fechar modal automaticamente', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                    '1' => __('Ativado', 'dd-smart-whatsapp'),
                    '0' => __('Desativado', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'auto_close_delay',
            [
                'label' => __('Tempo para fechar modal', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1000,
                'max' => 30000,
                'step' => 500,
                'placeholder' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
            ]
        );

        $this->add_control(
            'hide_again',
            [
                'label' => __('Não mostrar novamente neste navegador', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                    '1' => __('Ativado', 'dd-smart-whatsapp'),
                    '0' => __('Desativado', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'modal_style',
            [
                'label' => __('Estilo do modal', 'dd-smart-whatsapp'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Usar configuração do painel', 'dd-smart-whatsapp'),
                    'clean' => __('Clean', 'dd-smart-whatsapp'),
                    'soft' => __('Soft', 'dd-smart-whatsapp'),
                    'dark' => __('Dark', 'dd-smart-whatsapp'),
                ],
                'default' => '',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        echo DDSW_Renderer::render([
            'id' => $settings['button_id'] ?? '',
            'phone' => $settings['phone'] ?? '',
            'mode' => $settings['mode'] ?? '',
            'label' => $settings['label'] ?? '',
            'message' => $settings['message'] ?? '',
            'style' => $settings['style'] ?? '',
            'class' => $settings['custom_class'] ?? '',
            'custom_attributes' => $settings['custom_attributes'] ?? '',
            'width' => $settings['width'] ?? '',
            'align' => $settings['align'] ?? '',
            'icon' => !empty($settings['show_icon']) ? '1' : '0',
            'svg' => $settings['svg'] ?? '',
            'new_tab' => !empty($settings['new_tab']) ? '1' : '0',
            'background' => $settings['background'] ?? '',
            'color' => $settings['color'] ?? '',
            'hover_background' => $settings['hover_background'] ?? '',
            'hover_color' => $settings['hover_color'] ?? '',
            'border_color' => $settings['border_color'] ?? '',
            'icon_color' => $settings['icon_color'] ?? '',
            'icon_hover_color' => $settings['icon_hover_color'] ?? '',
            'modal_title' => $settings['modal_title'] ?? '',
            'modal_success' => $settings['modal_success'] ?? '',
            'desktop_instruction' => $settings['desktop_instruction'] ?? '',
            'ios_instruction' => $settings['ios_instruction'] ?? '',
            'android_instruction' => $settings['android_instruction'] ?? '',
            'open_label' => $settings['open_label'] ?? '',
            'close_label' => $settings['close_label'] ?? '',
            'retry_label' => $settings['retry_label'] ?? '',
            'error_message' => $settings['error_message'] ?? '',
            'auto_open' => $settings['auto_open'] ?? '',
            'auto_open_delay' => $settings['auto_open_delay'] ?? '',
            'auto_close' => $settings['auto_close'] ?? '',
            'auto_close_delay' => $settings['auto_close_delay'] ?? '',
            'hide_again' => $settings['hide_again'] ?? '',
            'modal_style' => $settings['modal_style'] ?? '',
        ]);
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
