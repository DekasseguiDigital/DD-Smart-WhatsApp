# Changelog

## 2.2.0-beta.5 - Messenger Smart Copy Modal Fix

### Fixed

- Fixed Facebook Messenger floating actions saved as Facebook links bypassing the Universal Smart Copy modal.
- Preserved the existing WhatsApp, Instagram and e-mail flows while routing Messenger links through the shared Smart Copy modal.

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
