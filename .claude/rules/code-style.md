---
paths:
  - "**/*.php"
  - "**/*.js"
  - "**/*.css"
---

# Code style

Match the code that is already here. This plugin targets PHP 7.4.

## PHP

- 4-space indent. Opening brace on the next line for classes and methods.
- Prefix functions and classes with `GFBM` or `GF_Booking_Manager`. Hooks and script handles use `gfbm`.
- Text domain: `gravity-forms-booking-manager`.
- Every PHP file begins with `if (!defined('ABSPATH')) { exit; }`.
- No union types, constructor property promotion, or other PHP 8-only syntax.
- Escape on the way out (`esc_html`, `esc_attr`, `esc_url`, `wp_json_encode` inside an attribute). Sanitize on the way in.
- The main plugin header `Author` is `Duppins Technology`. Do not add a client or employer there. See `OWNERSHIP.md`.

## JavaScript and CSS

- Front-end script is an IIFE that receives jQuery. Keep that shape.
- Class names start with `gfbm-`.
- CSS custom properties already used for slot columns and height stay the layout API.

## Formatting

No PHP_CodeSniffer or Prettier config is installed. Do not reformat an entire file as a side effect of a one-line fix. `hooks/post-edit-format.sh` does not rewrite files until a formatter is added.
