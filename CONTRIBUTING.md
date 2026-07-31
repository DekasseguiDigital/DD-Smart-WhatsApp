# Contributing to DD Smart WhatsApp

Thank you for helping improve DD Smart WhatsApp.

## Development Principles

- Preserve backward compatibility for existing buttons, shortcodes, Elementor widgets, Gutenberg blocks and saved settings.
- Keep every new feature optional unless it is a bug fix or security improvement.
- Use WordPress APIs for settings, security, escaping, sanitization, internationalization and database upgrades.
- Do not store raw IP addresses or unnecessary visitor personal data.
- Do not add external frontend dependencies without a strong product reason.
- Do not add PRO, licensing, checkout or activation code to the Free repository.

## Code Standards

- PHP must pass syntax checks before review.
- JavaScript must be vanilla unless the existing WordPress package is already available and appropriate.
- Visible PHP strings must use the `dd-smart-whatsapp` text domain.
- Visible JavaScript strings must use WordPress i18n helpers or localized script data.
- User-provided values must be sanitized on input and escaped on output.

## Release Checklist

Before opening a pull request:

- Run PHP lint.
- Run JavaScript syntax checks.
- Regenerate POT, PO and MO files when strings change.
- Run the source i18n audit.
- Validate that Floating Actions remains disabled by default.
- Generate an installable ZIP with POSIX paths.
- Document tests that could not be executed.

## Pull Requests

Use a feature branch and open a pull request against the main branch. Do not merge directly without review.

Include:

- Summary of changes.
- Backward compatibility notes.
- Database migration notes, if any.
- Tests executed.
- Known risks.
- Manual test instructions.
