# Accessibility Report

## Smart Copy Modal

- Uses `role="dialog"` and `aria-modal="true"`.
- Provides labelled title and description.
- Provides close button with translated aria-label.
- Supports ESC close.
- Traps Tab focus inside the modal.
- Restores focus to the triggering element when closed.
- Error state exposes the full message in a selectable textarea.

## Buttons

- Frontend buttons include `aria-label`.
- Icon SVG is marked `aria-hidden`.
- Focus-visible styles are present in plugin CSS.

## Admin UX

- Model-change confirmation uses dialog semantics.
- Wizard and controls use native form elements.

## Status

Implemented to WCAG-oriented keyboard and labelling standards. Manual screen-reader testing should be completed in staging before public submission.
