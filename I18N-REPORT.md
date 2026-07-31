# Internationalization Report

## Locale Strategy

- Frontend: real rendered page locale, resolved through multilingual plugins, URL structure, WordPress locale filters and WordPress locale fallbacks.
- Admin: site locale by default, optional logged-in user locale.
- Fallback: English when a locale-specific catalog does not exist.

## Catalogs

Generated for:

- `pt_BR`
- `en_US`
- `es_ES`
- `ja`
- `fr_FR`
- `de_DE`
- `it_IT`
- `nl_NL`

Both domain-prefixed and compatibility `.po/.mo` files are included.

## Audit Results

| Audit | Result |
| --- | ---: |
| Source visible strings | 306 |
| Internationalized strings | 306 |
| Remaining visible strings | 0 |
| Rendered/catalog strings checked | 107 |
| Catalog failures | 0 |

## Smart Copy Modal Resolution

Modal copy now resolves from the current rendered page locale and selected template through gettext before checking real user customizations. Previously saved default text in another language is detected as default content and no longer blocks automatic locale switching.

## Frontend Language Resolution

Frontend payloads are generated after resolving the page language with the central `DDSW_Language_Resolver`. Priority is Polylang, WPML, TranslatePress, URL subdirectory, language subdomain, locale filter, `determine_locale()` and `get_locale()`. Admin screens continue to use the site language or logged-in user language according to the plugin setting.

## JavaScript

Admin, frontend, clipboard, modal, tracking and Gutenberg scripts are prepared for `wp_set_script_translations()` where applicable.
