# DD Smart WhatsApp

DD Smart WhatsApp is a WordPress plugin for premium WhatsApp call-to-action buttons with Smart Copy, Elementor integration, Gutenberg support, shortcodes, local analytics, dynamic placeholders and complete internationalization.

## Highlights

- Smart Copy mode copies the fully formatted message before opening WhatsApp without the `text=` parameter.
- Traditional mode keeps the classic WhatsApp `text=` URL behavior.
- Multiple reusable buttons with CTA, message, style, modal and tracking settings.
- Optional Smart Floating Actions hubs for WhatsApp, phone, email, social links, maps, booking, form and custom links.
- Adaptive Auto style that can inherit colors, typography, radius, border, spacing and shadow from visible site buttons.
- Elementor widget plus Dynamic Tags for URL, phone, CTA, message and Smart Copy shortcode.
- Native Gutenberg block with visual controls.
- Admin wizard, live preview, template library and shortcode generator.
- Local analytics with Smart Copy, Traditional, opens, conversion, action metadata, Dashboard widget, 30-day chart, CSV export and reset.
- Accessible Smart Copy modal with focus handling, ESC close, keyboard navigation and translated labels.
- Complete i18n for `pt_BR`, `en_US`, `es_ES`, `ja`, `fr_FR`, `de_DE`, `it_IT` and `nl_NL`, with English fallback.
- Central `DDSW_Language` service and modular `templates/{locale}/` libraries.
- No external frontend dependencies.

## Requirements

- WordPress 6.8 or higher recommended.
- PHP 8.0 or higher.
- Elementor is optional.

## Internationalization

Frontend rendering follows the real rendered page locale. The admin area uses the site locale by default and can optionally follow the logged-in user locale.

Translations are loaded through WordPress locale APIs and `load_plugin_textdomain()`. JavaScript handles use `wp_set_script_translations()`. New button defaults and model library text come from gettext catalogs, not hardcoded per-locale arrays.

Final v2.1.0 i18n results:

- Source audit: 367 visible strings, 367 translated, 0 remaining.
- Rendered admin catalog audit: 107 visible Portuguese source strings checked across 8 locales, 0 failures.

Smart Copy modal text is resolved through `DDSW_I18n::resolve_modal_strings()`: current site locale, selected template, gettext, internal fallback, then real user customizations. Old saved defaults are treated as defaults, not custom text.

## Adding Languages

1. Create a folder under `templates/{locale}/`, for example `templates/es_MX/`.
2. Add only the template files that need localized or market-specific overrides, using the same file names as `templates/en_US/`.
3. Regenerate the POT/PO/MO catalogs when new gettext strings are introduced.
4. The plugin falls back to `templates/en_US/` for missing template files and to `en_US` when a locale is unsupported.

## Shortcode Examples

```text
[dd_smart_whatsapp id="principal" mode="smart"]
[dd_smart_whatsapp id="principal" mode="traditional"]
[dd_smart_whatsapp id="principal" style="custom" background="#B52418" color="#ffffff" hover_background="#831A13" radius="8" align="center" width="full" icon="yes"]
```

## Smart Floating Actions

Smart Floating Actions is disabled by default for full backward compatibility. Enable it in `DD WhatsApp > Smart Floating Actions`, then configure one or more hubs. Each hub has its own layout, position, colors, animation speed and action list. WhatsApp actions reuse the existing Smart Copy flow; every other action behaves as a normal link with local analytics tracking.

## PHP Template

```php
echo DDSW_Renderer::render([
    'id' => 'principal',
    'mode' => 'smart',
]);
```

## Privacy

The plugin stores local interaction events for statistics. It does not store raw IP addresses. Optional IP hashing uses HMAC-SHA256 with the WordPress auth salt and can be disabled.

GA4 events are sent only when enabled and when `gtag` already exists on the site.

## Development Checks

```bash
python tests/build-i18n.py
python tests/source-i18n-audit.py
powershell -ExecutionPolicy Bypass -File tests/i18n-audit.ps1
```

PHPCS/WPCS and Plugin Check should be executed in a release environment where those tools are installed.

## License

GPL v2 or later.
