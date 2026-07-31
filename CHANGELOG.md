# Changelog

## 2.2.0-beta.4 - Unified Floating Hub Smart Copy

### Changed

- Unified the Floating Hub Smart Copy flow with the standard Smart Copy modal used by regular buttons.
- Added backward compatibility for stored `smart_auto` message mode values by normalizing them to `smart_copy`.

### Fixed

- Fixed Floating Hub Smart Copy actions opening the target channel immediately without showing the confirmation modal first.
- Added WhatsApp to the Universal Smart Copy supported action list so Floating Hub WhatsApp actions can use the same modal flow.

## 2.2.0-beta.3 - Beta Polish

### Added

- Added contextual suggested messages for WhatsApp, Messenger, Facebook, Instagram and e-mail Floating Actions.
- Added dedicated e-mail subject support for Floating Actions while reusing the existing initial message field as the e-mail body.
- Added plugin action links for Settings, Documentation, Support, GitHub and Changelog on the WordPress Plugins screen.

### Changed

- Updated the plugin header with Plugin URI, Author URI, Update URI and a more product-focused description.
- Improved Floating Actions admin UX so suggested messages can be applied manually or after confirming replacement of existing custom content.

### Fixed

- Fixed a corrupted "Smart Copy automático" admin label in message mode options.
- Unified Universal Smart Copy in Floating Actions with the standard Smart Copy modal before opening the selected channel.

## 2.2.0 - Universal Smart Copy

### Added

- Added Universal Smart Copy infrastructure for floating actions beyond WhatsApp.
- Added per-action initial messages with existing placeholder support.
- Added per-action message modes: none, automatic Smart Copy and ask before copying.
- Added Smart Copy support for Messenger, Instagram, Telegram and LINE actions.
- Added optional Smart Copy support for custom links.
- Added `smart_copy_platform` analytics events with the platform available through action metadata.

### Changed

- Email actions now generate `mailto:` links with subject and body when an initial message is configured.
- The existing Smart Copy modal is reused for confirm/error flows, with a lightweight toast after successful automatic copy.

### Fixed

- Fixed frontend style loading on internal pages that render DD Smart WhatsApp buttons through shortcodes, Gutenberg blocks or Elementor widgets.

### Compatibility

- WhatsApp Smart Copy, traditional WhatsApp links, shortcodes, Elementor, Gutenberg, Dashboard analytics and existing Floating Actions behavior remain backward compatible.

## 2.1.0 - Smart Floating Actions Free and Dashboard Analytics

### Added

- Added optional Smart Floating Actions hubs with Vertical and Compact List layouts.
- Added configurable floating actions for WhatsApp Smart Copy, phone, email, social links, maps, booking, form and custom links.
- Added per-action metadata in local analytics without storing raw IP addresses.
- Added WordPress Dashboard widget with total clicks, conversations, Smart Copy, today, last 7 days, conversion, top button, top action and 30-day mini chart.
- Added dedicated frontend assets for Floating Actions using CSS and vanilla JavaScript only.

### Changed

- Extended the existing renderer payload with optional action identifiers so Floating WhatsApp actions reuse the current Smart Copy flow.
- Extended CSV export with `action_id` and `action_type`.

### Compatibility

- Floating Actions remains disabled by default, preserving existing shortcodes, Elementor widgets, Gutenberg blocks and legacy button behavior.

### Security

- No raw IP addresses, visitor emails, visitor phone numbers, message contents or fingerprint data are stored by the new analytics metadata.

### Language Architecture

### Added

- Added `DDSW_Language` as the single language service for admin, frontend, template and preview resolution.
- Added modular template loading from `templates/{locale}/`, with `templates/en_US/` as the fallback library.
- Added locale-aware template fallback so new languages can override individual template files without changing core classes.

### Changed

- Changed `DDSW_Language_Resolver` into a backward-compatible facade over `DDSW_Language`.
- Changed admin preview payloads to use the current site template locale while the admin UI can still follow the user locale.
- Changed frontend rendering so saved default CTA/message values are replaced by the resolved current-language template, preserving real user customizations.
- Moved business template definitions out of `DDSW_I18n` and into template files.

### Fixed

- Fixed Automatic — current site language selecting stale saved defaults instead of the current WordPress/site language library.
- Fixed Spanish and Japanese catalog entries that were falling back to English for critical admin/template labels.
- Fixed live preview so language/template changes update default CTA, message and modal fields immediately without saving.
- Fixed Smart Floating Actions frontend positioning so the closed hub renders as a circular fixed button and the action list opens above it without affecting page flow or causing viewport clipping.

## 2.0.3 - Adaptive Auto stability fix

### Fixed

- Prevented recursive MutationObserver loops in Adaptive Auto style.
- Replaced DOM-based color parsing with a pure RGB/hex color parser.
- Added early return when no Auto buttons are present on the page.
- Reduced repeated Adaptive Theme Engine executions during dynamic page rendering.

### Validation

- Confirmed `rgbParts()` no longer creates, appends or removes temporary DOM nodes.
- Confirmed the MutationObserver callback is debounced through a single scheduled frame.

## 2.0.2 - Adaptive Auto style

### Fixed

- Improved Auto style detection using visible site buttons.
- Auto style now inherits typography, colors, radius, border, spacing and shadow more reliably.
- Improved Auto style hover behavior with safe derived hover colors.
- Admin preview now better reflects the frontend Auto style and updates immediately when Auto is selected.

### Audit

- Source i18n audit: `306` visible strings, `306` internationalized, `0` remaining.
- PHP syntax check: passed for all plugin PHP files with PHP 8.2.29.
- JS syntax check: passed for admin, frontend, modal and Gutenberg scripts with Node `--check`.

## 2.0.1 - Smart Copy modal resolution architecture

### Added

- Added `DDSW_I18n::resolve_modal_strings()` as the single source of truth for Smart Copy modal copy.
- Added modal metadata fields: `template_version`, `last_template`, `modal_template_hash`, `customized` and `modal_customized`.
- Added settings migration that refreshes non-customized saved modal text to the current translated defaults while preserving truly customized fields.
- Added localized debug payload for `DDSW Debug`, locale, template, modal source, resolver, custom override, translation loaded, MO loaded and PO loaded.
- Added `DDSW_Language_Resolver` as the central frontend/admin language resolver.
- Added frontend language diagnostics for resolved locale, language source, loaded template, gettext locale, payload language, HTML lang and document lang.

### Changed

- Updated version to `2.0.1`.
- Changed frontend locale resolution to prioritize the real page language from Polylang, WPML, TranslatePress, URL subdirectories, language subdomains, locale filters, `determine_locale()` and finally `get_locale()`.
- Changed gettext loading so Smart Copy modal strings load the `.mo` file for the resolved frontend page locale before payload generation.
- Changed renderer modal payloads to use resolved gettext/current-locale strings before applying user customizations.
- Changed Traditional feedback to prefer the resolved button payload instead of old global database defaults.
- Removed hardcoded modal fallback phrases from JavaScript; modal text now comes from PHP localization or the resolved payload.
- Regenerated POT, PO and MO catalogs for all supported locales.

### Fixed

- Fixed Smart Copy modal showing old Portuguese database defaults such as "Mensagem copiada", "Clique em Abrir WhatsApp" and "Abrir WhatsApp" on English, Spanish, Japanese and other localized frontends.
- Fixed Japanese modal title, desktop instruction and Open WhatsApp label catalog values.
- Fixed stale modal customization flags causing legacy default descriptions and instructions saved in the database to override current gettext translations.
- Fixed Smart Copy modal DOM rendering so the visible modal fields now prioritize the canonical resolved keys: `title`, `description`, `instruction`, `button`, `close`, `success` and `failed`.
- Added field-level Smart Copy modal debug output for the canonical modal fields when DDSW Debug is enabled.
- Fixed the remaining Smart Copy modal description leak where a saved legacy Portuguese success phrase could still override gettext in non-Portuguese frontends.
- Forced the Smart Copy modal description catalog entry to overwrite stale PO values in every supported locale.
- Fixed frontends in `/es/`, `/en/`, `/ja/` or multilingual plugin contexts still resolving the modal payload as the base site locale.

### Audit

- Source i18n audit: `306` visible strings, `306` internationalized, `0` remaining.
- Rendered admin catalog audit: `107` visible Portuguese source strings checked across 8 locales, `0` catalog failures.
- PHP syntax check: passed for all plugin PHP files with PHP 8.2.29.
- JS syntax check: passed for admin, frontend, modal and Gutenberg scripts with Node `--check`.

## 2.0.0 - UX, internationalization and final polish

### Added

- Added smart admin language mode: site language by default, optional logged-in user language for the admin area.
- Added intelligent template update modal when changing a button model language or model type.
- Added safe default restoration for the current button and for all buttons, preserving customized CTA, message and modal fields.
- Added model library for tourism, restaurant, lawyer, doctor, hotel, barbershop, real estate, support, store, freelancer and consulting.
- Added first-run wizard with language, WhatsApp number, first button and shortcode steps.
- Added live admin preview using the same frontend button classes and `frontend.css`.
- Added shortcode generator with live output and clipboard copy.
- Added Smart Copy options for auto close and "do not show again" through `localStorage`.
- Added 30-day statistics chart, Smart Copy/Traditional metrics, conversion percentage and CSV export.
- Added Elementor Dynamic Tags for principal WhatsApp URL, support WhatsApp URL, Smart Copy shortcode, message, phone and CTA.
- Added Gutenberg controls for Smart Copy auto open, auto close and hide-again behavior.
- Added dedicated reports: `RELEASE.md`, `COMPATIBILITY.md`, `PERFORMANCE.md`, `I18N-REPORT.md`, `ACCESSIBILITY.md` and `SECURITY.md`.

### Changed

- Updated version to `2.0.0`.
- Regenerated POT, PO and MO catalogs for `pt_BR`, `en_US`, `es_ES`, `ja`, `fr_FR`, `de_DE`, `it_IT` and `nl_NL`.
- Strengthened English fallback so non-Portuguese locales never inherit untranslated Portuguese admin strings.
- Updated admin assets so the live preview does not depend on theme or Elementor CSS.
- Updated source and rendered-admin i18n audit scripts for version `2.0.0`.

### Fixed

- Fixed missing translations for the new wizard, restore flow, language mode, shortcode generator and Smart Copy modal options.
- Fixed catalog-level i18n regressions where wrapped strings could still render in Portuguese in non-`pt_BR` locales.
- Fixed source i18n audit false positives for technical selectors and Elementor namespace strings.

### Audit

- Source i18n audit: `323` visible strings, `323` internationalized, `0` remaining.
- Rendered admin catalog audit: `116` visible Portuguese source strings checked across 8 locales, `0` catalog failures.
- PHP syntax check: passed for all plugin PHP files with PHP 8.2.29.
- JS syntax check: passed for admin, frontend, modal, tracking and Gutenberg scripts with Node `--check`.
- WordPress Plugin Check, PHPCS/WPCS and WP-CLI were not available in the local PATH; reports document this limitation.

## 1.3.1 - Complete source i18n hardening

- Removed hardcoded localized starter text sets from PHP source.
- Regenerated `.pot`, `.po` and `.mo` files for 8 locales.
- Added stricter source and rendered-admin i18n audits.
- Removed `innerHTML` usage from modal/admin row creation.
- Fixed incomplete English and non-Portuguese catalogs.

## 1.3.0 - Final internationalization pass

- Added source i18n audit for PHP, JavaScript and JSON files.
- Added admin JavaScript translation registration.
- Updated Gutenberg editor strings to use `wp.i18n.__()`.
- Prevented mixed-language Smart Copy modal text.

## 1.2.0 - Complete internationalization

- Added `DDSW_I18n` loader with per-site locale resolution and English fallback.
- Added localized starter content for 8 locales.
- Added per-button template language.
- Added frontend and block script translations.

## 1.1.0 - Automatic visual integration and WordPress.org hardening

- Fixed frontend style rendering.
- Added Auto, Green, Dark, Light, Outline and Custom styles.
- Added browser-side placeholders and admin statistics cleanup.
- Added multisite initialization support.

## 1.0.0 - WordPress.org-ready public release candidate

- Added WordPress.org readme, GPL license, uninstall handler and separated assets.
- Added Elementor widget, Gutenberg block, shortcodes, Smart Copy, tracking and developer hooks.
