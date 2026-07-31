# Security Report

## Data Handling

- Settings are sanitized through `DDSW_Settings::sanitize()`.
- Phone numbers, IDs, modes, styles, colors, dimensions and text fields are normalized.
- Frontend output is escaped with `esc_attr()`, `esc_html()`, `esc_url()`, `esc_textarea()` or controlled KSES for SVG.

## Admin Actions

- Clear statistics requires `manage_options` and `ddsw_clear_stats` nonce.
- CSV export requires `manage_options` and `ddsw_export_stats` nonce.
- Settings saving uses the WordPress Settings API.

## Tracking

- Raw IP addresses are not stored.
- Optional IP hashing uses HMAC-SHA256 with WordPress auth salt.
- GA4 sends only when enabled and when `gtag` exists.

## Validation

- PHP lint passed for all plugin PHP files.
- No direct trust of request data was introduced in v2.0.0 changes.
