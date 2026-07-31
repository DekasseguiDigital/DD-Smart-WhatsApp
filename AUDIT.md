# DD Smart WhatsApp v2.1.0 Audit

## v2.1.0 Smart Floating Actions Free

- Added optional Smart Floating Actions as a separate module, disabled by default to preserve existing installations.
- Added dedicated frontend renderer, CSS and vanilla JavaScript for Vertical and Compact List floating hubs.
- Reused the existing Smart Copy renderer for WhatsApp floating actions instead of duplicating clipboard/modal logic.
- Extended local analytics with `action_id` and `action_type` while continuing to avoid raw IP storage.
- Added a WordPress Dashboard analytics widget without removing third-party Dashboard widgets.
- Regenerated translation catalogs after adding the new admin, frontend and Dashboard strings.
- Validation: PHP lint passed, JS syntax checks passed, source i18n audit passed with `367/367` visible strings internationalized and `0` remaining.
- Floating Actions layout fix: the fixed wrapper was being sized by the hidden menu row and by the nested legacy WhatsApp renderer (`.ddsw-wrap`/`.ddsw-button`). The menu is now absolutely positioned above the circular trigger, Floating Actions assets are enqueued before footer rendering when hubs are active, and closed menu items are removed from keyboard tab order.

## Project Type

Custom WordPress plugin for Elementor, Gutenberg, shortcodes, widgets and PHP templates.

## Scope

This audit covered architecture, assets, internationalization, Smart Copy UX, admin UX, Elementor, Gutenberg, statistics, security, accessibility, performance and packaging readiness.

## Improvements Implemented

- Added smart admin language setting with site language default and optional logged-in user language.
- Kept frontend locale fixed to the current site locale.
- Added intelligent model-change modal with CTA, message and Smart Copy modal update choices.
- Preserved customized fields by comparing the current value with the previous language/model default before replacing it.
- Added restore current button and restore all buttons actions.
- Added business model library for tourism, restaurant, lawyer, doctor, hotel, barbershop, real estate, support, store, freelancer and consulting.
- Added first-run wizard.
- Added real-time admin preview using frontend button classes and frontend CSS.
- Added shortcode generator with clipboard copy.
- Added Smart Copy auto close and hide-again options.
- Added statistics for Smart Copy, Traditional, opens, conversion, last 30 days and CSV export.
- Added Elementor Dynamic Tags for principal URL, support URL, Smart Copy shortcode, message, phone and CTA.
- Added Gutenberg controls for auto open, auto close and hide-again behavior.
- Regenerated all language catalogs for version `2.0.2`.
- Fixed the v2.0.2 Adaptive Auto regression by replacing DOM-based color parsing with a pure parser, adding early return for pages without Auto buttons and debouncing MutationObserver executions.
- Centralized Smart Copy modal text resolution in `DDSW_I18n::resolve_modal_strings()`.
- Added automatic migration metadata for template version, selected template, modal hash and customized fields.
- Removed JavaScript modal text fallbacks so no old frontend phrase can bypass PHP locale resolution.
- Hardened modal customization detection so saved legacy defaults are treated as translatable defaults before database overrides are accepted.
- Updated the Smart Copy modal DOM renderer to consume canonical resolved modal fields first: `title`, `description`, `instruction`, `button`, `close`, `success` and `failed`.
- Added debug visibility for the resolver source of each canonical modal field.
- Added the remaining legacy saved Portuguese success description to the default-detection list so it resolves through gettext instead of the database.
- Added `DDSW_Language` to centralize frontend/admin locale detection.
- Updated frontend modal payload generation to resolve the real page language from Polylang, WPML, TranslatePress, URL subdirectories, language subdomains, locale filters, `determine_locale()` and `get_locale()`.
- Added WP_DEBUG console diagnostics for resolved locale, language source, loaded template, gettext locale, payload language, HTML lang, document lang and modal payload text.
- Rebuilt the Auto button style as an adaptive visual mode that first reads a visible site button, then falls back to global theme variables, then falls back to the WhatsApp palette.
- Updated Auto hover generation, typography inheritance, border/radius/padding/shadow mapping and admin live preview behavior.
- Added `DDSW_Language` as the central resolver for admin, frontend, preview and template library selection.
- Converted `DDSW_Language_Resolver` into a backward-compatible facade.
- Moved business templates from hardcoded arrays into `templates/en_US/*.php`, with locale folders available for overrides.
- Updated Automatic current-site-language behavior so saved default CTA/message values resolve from the active language while custom text remains untouched.
- Regenerated all language catalogs for version `2.1.0`.

## Internationalization Results

| Check | Result |
| --- | ---: |
| Source visible strings | 306 |
| Internationalized strings | 306 |
| Remaining visible hardcoded strings | 0 |
| Rendered/catalog admin strings checked | 107 |
| Locales checked | 8 |
| Catalog failures | 0 |

Locales: `pt_BR`, `en_US`, `es_ES`, `ja`, `fr_FR`, `de_DE`, `it_IT`, `nl_NL`.

## Validation Matrix

| Area | Result |
| --- | --- |
| PHP lint | Passed for all plugin PHP files with PHP 8.2.29 |
| JS syntax | Passed for admin, frontend, modal, tracking and Gutenberg scripts |
| Source i18n audit | Passed, 0 remaining visible hardcoded strings |
| Rendered admin catalog audit | Passed, 0 catalog failures |
| POT/PO/MO generation | Passed for all supported locales |
| POSIX ZIP validation | To be performed during final packaging |
| PHPCS/WPCS | Tool not available in local PATH |
| WordPress Plugin Check | Tool not available in local PATH |
| WP-CLI | Tool not available in local PATH |

## Security Notes

- Admin actions use capabilities and nonces.
- Settings are sanitized through `DDSW_Settings::sanitize()`.
- Output is escaped with WordPress escaping helpers.
- Statistics export uses capability check and nonce.
- Raw IP addresses are not stored; optional IP hashing remains available.
- Dynamic Tag output is escaped.

## Performance Notes

- Frontend assets are still loaded on render or Elementor preview/editor contexts.
- Admin preview loads `frontend.css` only on the plugin admin screen.
- The statistics chart uses a lightweight canvas renderer to avoid an additional frontend dependency.
- Modal behavior remains lazy in the sense that modal DOM is created only after Smart Copy interaction.

## Accessibility Notes

- Smart Copy modal uses `role="dialog"`, `aria-modal`, labelled title/description, close aria-label, focus handling, ESC close and Tab focus trap.
- Admin model-change modal uses dialog semantics and keyboard close.
- Buttons include accessible labels.

## Distribution Readiness

The plugin is ready for local ZIP installation and beta distribution. Before submitting to WordPress.org, run PHPCS/WPCS and Plugin Check in a release environment where those tools are installed.
