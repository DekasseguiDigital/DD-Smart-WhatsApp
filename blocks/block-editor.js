(function (blocks, element, components, i18n, blockEditor) {
    'use strict';

    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = blockEditor.InspectorControls;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var ToggleControl = components.ToggleControl;
    var PanelBody = components.PanelBody;
    var blockData = window.DDSWBlock || {};

    blocks.registerBlockType('dd-smart-whatsapp/button', {
        title: __('DD Smart WhatsApp', 'dd-smart-whatsapp'),
        description: __('Button with Traditional or Smart Copy mode.', 'dd-smart-whatsapp'),
        icon: 'format-chat',
        category: 'widgets',
        attributes: {
            id: { type: 'string', default: 'principal' },
            mode: { type: 'string', default: '' },
            label: { type: 'string', default: '' },
            message: { type: 'string', default: '' },
            style: { type: 'string', default: '' },
            variant: { type: 'string', default: '' },
            align: { type: 'string', default: '' },
            width: { type: 'string', default: '' },
            showIcon: { type: 'boolean', default: true },
            autoOpen: { type: 'string', default: '' },
            autoClose: { type: 'string', default: '' },
            hideAgain: { type: 'string', default: '' }
        },
        edit: function (props) {
            var attrs = props.attributes;
            var setAttributes = props.setAttributes;
            var selected = (blockData.buttons || []).find(function (button) {
                return button.value === attrs.id;
            });
            var label = attrs.label || (selected ? selected.label.replace(/\s\([^)]+\)$/, '') : (blockData.defaultLabel || __('Chat on WhatsApp', 'dd-smart-whatsapp')));

            return [
                el(InspectorControls, { key: 'controls' },
                    el(PanelBody, { title: __('Settings', 'dd-smart-whatsapp'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Saved button', 'dd-smart-whatsapp'),
                            value: attrs.id,
                            options: blockData.buttons || [],
                            onChange: function (value) {
                                setAttributes({ id: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Send mode', 'dd-smart-whatsapp'),
                            value: attrs.mode,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Traditional', 'dd-smart-whatsapp'), value: 'traditional' },
                                { label: __('Smart Copy', 'dd-smart-whatsapp'), value: 'smart' }
                            ],
                            onChange: function (value) {
                                setAttributes({ mode: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Custom label', 'dd-smart-whatsapp'),
                            value: attrs.label,
                            onChange: function (value) {
                                setAttributes({ label: value });
                            }
                        }),
                        el(TextareaControl, {
                            label: __('Custom message', 'dd-smart-whatsapp'),
                            value: attrs.message,
                            onChange: function (value) {
                                setAttributes({ message: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Style', 'dd-smart-whatsapp'),
                            value: attrs.style,
                            options: blockData.styles || [],
                            onChange: function (value) {
                                setAttributes({ style: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Alignment', 'dd-smart-whatsapp'),
                            value: attrs.align,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Left', 'dd-smart-whatsapp'), value: 'left' },
                                { label: __('Center', 'dd-smart-whatsapp'), value: 'center' },
                                { label: __('Right', 'dd-smart-whatsapp'), value: 'right' }
                            ],
                            onChange: function (value) {
                                setAttributes({ align: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Width', 'dd-smart-whatsapp'),
                            value: attrs.width,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Automatic', 'dd-smart-whatsapp'), value: 'auto' },
                                { label: __('100%', 'dd-smart-whatsapp'), value: 'full' }
                            ],
                            onChange: function (value) {
                                setAttributes({ width: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Show icon', 'dd-smart-whatsapp'),
                            checked: attrs.showIcon,
                            onChange: function (value) {
                                setAttributes({ showIcon: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Open automatically after copying', 'dd-smart-whatsapp'),
                            value: attrs.autoOpen,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Enabled', 'dd-smart-whatsapp'), value: '1' },
                                { label: __('Disabled', 'dd-smart-whatsapp'), value: '0' }
                            ],
                            onChange: function (value) {
                                setAttributes({ autoOpen: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Close modal automatically', 'dd-smart-whatsapp'),
                            value: attrs.autoClose,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Enabled', 'dd-smart-whatsapp'), value: '1' },
                                { label: __('Disabled', 'dd-smart-whatsapp'), value: '0' }
                            ],
                            onChange: function (value) {
                                setAttributes({ autoClose: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Do not show again on this browser', 'dd-smart-whatsapp'),
                            value: attrs.hideAgain,
                            options: [
                                { label: __('Use button setting', 'dd-smart-whatsapp'), value: '' },
                                { label: __('Enabled', 'dd-smart-whatsapp'), value: '1' },
                                { label: __('Disabled', 'dd-smart-whatsapp'), value: '0' }
                            ],
                            onChange: function (value) {
                                setAttributes({ hideAgain: value });
                            }
                        })
                    )
                ),
                el('div', { key: 'preview', className: 'ddsw-block-preview ddsw-block-preview--' + (attrs.style || 'saved') },
                    el('span', { className: 'ddsw-block-preview__button' }, label),
                    el('small', {}, attrs.mode === 'smart' ? __('Smart Copy', 'dd-smart-whatsapp') : __('Traditional or button default', 'dd-smart-whatsapp'))
                )
            ];
        },
        save: function () {
            return null;
        }
    });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.i18n, window.wp.blockEditor);
