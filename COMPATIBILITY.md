# Compatibility Report

## Supported Targets

- WordPress 6.0+, designed for WordPress 6.8+.
- PHP 8.0+.
- Elementor Free and Pro.
- Gutenberg block editor.
- Classic and block themes.
- WordPress Multisite.
- WPML, Polylang and TranslatePress through WordPress locale APIs.

## Implemented Compatibility Measures

- Frontend locale follows the current site/blog locale.
- Admin locale can follow the site or logged-in user setting.
- Assets are registered independently of theme CSS.
- Elementor widget remains editable and receives optional overrides.
- Elementor Dynamic Tags are registered only when Elementor dynamic tag APIs are available.
- Gutenberg block is server-rendered, preserving shortcode/PHP renderer compatibility.
- Statistics table creation remains activation and multisite aware.

## Local Validation

- PHP lint passed with PHP 8.2.29.
- JS syntax checks passed.
- Live browser/device checks for Elementor Pro, WPML, Polylang, TranslatePress, LiteSpeed Cache, Cloudflare and Hostinger were not executable in this local environment.
