=== DD Smart WhatsApp ===
Contributors: dekassegui-digital
Tags: whatsapp, elementor, gutenberg, shortcode, analytics
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 2.2.0-beta.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Premium WhatsApp buttons with Smart Copy, Elementor, Gutenberg, shortcodes, i18n, local analytics and dynamic placeholders.

== Description ==

DD Smart WhatsApp creates reusable WhatsApp call-to-action buttons for WordPress.

The plugin supports two sending modes:

* Traditional: opens WhatsApp using the `text=` URL parameter.
* Smart Copy: copies the full formatted message first, then opens WhatsApp without altering message formatting.

Features include:

* Multiple configurable buttons.
* Smart language mode for admin language handling.
* Template library for common business segments.
* Scalable language resolver and modular template folders.
* Optional Smart Floating Actions hubs with WhatsApp, phone, email, social, maps, booking, form and custom links.
* First-run wizard.
* Live admin preview.
* Shortcode generator.
* Elementor widget and Dynamic Tags.
* Gutenberg visual block.
* Local event statistics, Dashboard widget, 30-day chart, CSV export and reset.
* Optional IP hashing and no raw IP storage.
* GA4 events when `gtag` is available.
* Dynamic placeholders, including browser, device, language, referrer and UTM placeholders.
* Accessible Smart Copy modal with translated content and keyboard support.
* Complete i18n for pt_BR, en_US, es_ES, ja, fr_FR, de_DE, it_IT and nl_NL.
* English fallback when a locale-specific translation is not available.
* No external frontend dependencies.

== Installation ==

1. Upload the plugin ZIP from Plugins > Add New > Upload Plugin.
2. Activate DD Smart WhatsApp.
3. Go to DD WhatsApp in the WordPress admin menu.
4. Complete the wizard or configure at least one button manually.
5. Use the shortcode, Gutenberg block, Elementor widget, Dynamic Tag or PHP renderer.

== Frequently Asked Questions ==

= Does the plugin require Elementor? =

No. Elementor is optional. The plugin also works with shortcodes, Gutenberg and PHP templates.

= Does Smart Copy automatically fill the WhatsApp field? =

No. Smart Copy opens only `https://wa.me/NUMBER` and lets the user paste the copied message manually.

= Can the admin use the logged-in user language? =

Yes. The frontend always follows the site language. The admin area can use either the site language or the logged-in user language.

= Does the plugin store raw IP addresses? =

No. Raw IP addresses are not stored. Optional IP hashing can be disabled in the admin panel.

== Screenshots ==

1. Admin settings screen.
2. Smart Copy modal.
3. Elementor widget controls.
4. Gutenberg block.
5. Frontend button styles.
6. Statistics panel.
7. Placeholder reference.
8. Mobile modal.

== Changelog ==

= 2.2.0-beta.4 =
* Unified the Floating Hub Smart Copy flow with the standard Smart Copy modal used by regular buttons.
* Added backward compatibility for stored smart_auto message mode values.
* Fixed Floating Hub Smart Copy actions opening the target channel immediately without showing the confirmation modal first.
* Added WhatsApp to the Universal Smart Copy supported action list.

= 2.2.0-beta.3 =
* Added contextual suggested messages for WhatsApp, Messenger, Facebook, Instagram and e-mail actions.
* Added dedicated e-mail subject support for Floating Actions.
* Improved plugin metadata and added Settings, Documentation, Support, GitHub and Changelog links on the Plugins screen.
* Normalized beta admin labels and fixed a corrupted Smart Copy automatic label.

= 2.2.0 =
* Added Universal Smart Copy for Messenger, Instagram, Telegram, LINE and optional custom links.
* Added per-action initial messages with placeholder support in Smart Floating Actions.
* Added per-action message modes: none, automatic Smart Copy and ask before copying.
* Added mailto subject and body generation for email actions without using Smart Copy.
* Added platform analytics event tracking for Universal Smart Copy.
* Fixed frontend style loading on internal pages with DD Smart WhatsApp shortcodes, Gutenberg blocks or Elementor widgets.

= 2.1.0 =
* Added optional Smart Floating Actions hubs with Vertical and Compact List layouts.
* Added configurable floating actions for WhatsApp Smart Copy, phone, email, social links, maps, booking, form and custom links.
* Added WordPress Dashboard analytics widget with total clicks, conversations, Smart Copy, today, last 7 days, conversion, top button, top action and 30-day mini chart.
* Added per-action local analytics metadata without storing raw IP addresses.
* Extended CSV export with action metadata.
* Added `DDSW_Language` as the single language service for admin, frontend, template and preview resolution.
* Added modular template loading from `templates/{locale}/` with English fallback.
* Fixed Automatic current-site-language mode so default CTA, message and modal text resolve from the current language instead of stale saved defaults.
* Fixed admin preview updates when changing language, library or template.
* Regenerated POT, PO and MO catalogs for 8 locales.

= 2.0.3 =
* Fixed a recursive MutationObserver loop in Adaptive Auto style.
* Replaced DOM-based color parsing with a pure RGB/hex parser.
* Added an early return when no Auto buttons are present.
* Debounced Adaptive Theme Engine executions during dynamic page rendering.

= 2.0.2 =
* Improved Auto style detection using visible site buttons.
* Auto style now inherits typography, colors, radius, border, spacing and shadow more reliably.
* Improved Auto style hover behavior.
* Admin preview now better reflects the frontend Auto style.
* Source i18n audit: 306 visible strings, 306 internationalized, 0 remaining.

= 2.0.1 =
* Added `DDSW_I18n::resolve_modal_strings()` as the single source of truth for Smart Copy modal text.
* Fixed Smart Copy modal language resolution so old saved database defaults no longer override current site language translations.
* Added modal template metadata and migration that preserves custom fields while refreshing non-customized defaults.
* Removed hardcoded modal phrases from JavaScript.
* Fixed Japanese, English and Spanish modal title/instruction/button catalog validation.
* Source i18n audit: 306 visible strings, 306 internationalized, 0 remaining.
* Rendered admin catalog audit: 107 visible source strings across 8 locales, 0 catalog failures.

= 2.0.0 =
* Added smart admin language mode, first-run wizard, live preview, template library and shortcode generator.
* Added intelligent model update flow that preserves customized CTA, message and modal fields.
* Added restore current button and restore all buttons actions.
* Added Smart Copy auto close and hide-again options.
* Added 30-day chart, conversion metric and CSV export.
* Added Elementor Dynamic Tags and expanded Gutenberg controls.
* Regenerated POT, PO and MO catalogs for 8 locales.
* Source i18n audit: 323 visible strings, 323 internationalized, 0 remaining.
* Rendered admin catalog audit: 116 visible source strings across 8 locales, 0 catalog failures.

= 1.3.1 =
* Completed source i18n hardening and regenerated all translation catalogs.

= 1.3.0 =
* Added final JavaScript and modal internationalization pass.

= 1.2.0 =
* Added complete per-site i18n loading and localized defaults.

= 1.1.0 =
* Fixed frontend style rendering and added automatic visual integration.

= 1.0.0 =
* Initial WordPress.org-ready public release candidate.

== Upgrade Notice ==

= 2.2.0 =
Adds Universal Smart Copy for Messenger, Instagram, Telegram, LINE and optional custom links while preserving existing WhatsApp behavior.

= 2.1.0 =
Adds optional Smart Floating Actions and Dashboard Analytics. Existing buttons, shortcodes, Elementor widgets and Gutenberg blocks keep their current behavior unless the new module is enabled.

= 2.0.3 =
Critical stability update. Prevents Adaptive Auto style from repeatedly triggering DOM mutation processing on dynamic Elementor pages.

= 2.0.2 =
Recommended update. Improves Auto style inheritance so buttons better follow the active theme or builder visual identity.

= 2.0.1 =
Recommended update. Fixes Smart Copy modal language resolution for multilingual sites without requiring template resets or database cleanup.

= 2.0.0 =
Adds the final UX, i18n, analytics and Elementor polish for commercial distribution.

= 1.3.1 =
Recommended update for multilingual sites.

== License ==

DD Smart WhatsApp is licensed under the GPL v2 or later.

See `license.txt` for details.
