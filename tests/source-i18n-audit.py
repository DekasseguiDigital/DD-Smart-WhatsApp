from __future__ import annotations

from dataclasses import dataclass
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
DOMAIN = "dd-smart-whatsapp"
VERSION = "2.2.0-beta.5"

SOURCE_SUFFIXES = {".php", ".js"}
SKIP_PARTS = {"languages", "tests"}

I18N_FUNCTIONS = (
    "__",
    "_e",
    "_x",
    "_n",
    "_nx",
    "esc_html__",
    "esc_html_e",
    "esc_html_x",
    "esc_attr__",
    "esc_attr_e",
    "esc_attr_x",
)

STRING_RE = re.compile(r"(?P<quote>['\"])(?P<body>(?:\\.|(?!\1).)*?)(?P=quote)", re.S)
HTML_TEXT_RE = re.compile(r">([^<>{}]*[A-Za-zÀ-ÿ\u3040-\u30ff\u4e00-\u9fff][^<>{}]*)<")
HUMAN_RE = re.compile(r"[A-Za-zÀ-ÿ\u3040-\u30ff\u4e00-\u9fff]")

TECHNICAL_EXACT = {
    DOMAIN,
    "ABSPATH",
    "ARRAY_A",
    "AUTH_SALT",
    "ArrowDown",
    "ArrowUp",
    "POST",
    "WP_DEBUG",
    "WP_LANG_DIR",
    "WP_UNINSTALL_PLUGIN",
    "JSON_UNESCAPED_UNICODE",
    "JSON_UNESCAPED_SLASHES",
    "JSON_HEX_TAG",
    "JSON_HEX_AMP",
    "JSON_HEX_APOS",
    "JSON_HEX_QUOT",
    "_blank",
    "_self",
    "auto",
    "button",
    "center",
    "checkbox",
    "clean",
    "click",
    "copy",
    "DOMContentLoaded",
    "custom",
    "dark",
    "desktop",
    "div",
    "error",
    "event",
    "Escape",
    "Edge",
    "Chrome",
    "Chromium",
    "Safari",
    "Firefox",
    "full",
    "green",
    "hidden",
    "left",
    "light",
    "none",
    "outline",
    "primary",
    "principal",
    "readonly",
    "right",
    "role|menuitem",
    "smart",
    "soft",
    "string",
    "success",
    "Tab",
    "tel:",
    "traditional",
    "widgets",
    "Y-m-d 00:00:00",
    "MacIntel",
    "a, button",
    "input:checked",
    "use strict",
    "mailto:",
    "|support",
}

TECHNICAL_PREFIXES = (
    "#",
    ".",
    "/",
    "--",
    ";",
    "<svg",
    "ABSPATH",
    "ARRAY_",
    "DDSW",
    "DDSmart",
    "data-",
    "ddsw",
    "eicon-",
    "elementor/",
    "\\Elementor\\",
    "http",
    "is_",
    "wp-",
    "wp_",
)

TECHNICAL_SUBSTRINGS = (
    "$",
    "%",
    "=>",
    "<?php",
    "GROUP BY",
    "ORDER BY",
    "SELECT ",
    "TRUNCATE TABLE",
    "DROP TABLE",
    "CREATE TABLE",
    "addEventListener",
    "admin-post.php",
    "admin.php?",
    "aria-",
    "class=",
    "currentColor",
    "format-chat",
    "querySelector",
    "querySelectorAll",
    "setAttribute",
    "viewBox",
    "wp-admin",
)


@dataclass
class Finding:
    path: str
    line: int
    value: str
    reason: str


def source_files() -> list[Path]:
    files: list[Path] = []
    for path in ROOT.rglob("*"):
        if path.suffix not in SOURCE_SUFFIXES:
            continue
        if SKIP_PARTS & set(path.relative_to(ROOT).parts):
            continue
        files.append(path)
    return sorted(files)


def line_number(text: str, offset: int) -> int:
    return text.count("\n", 0, offset) + 1


def has_i18n_prefix(text: str, start: int) -> bool:
    prefix = text[max(0, start - 220):start]
    for function in I18N_FUNCTIONS:
        if re.search(rf"(?:^|[^\w]){re.escape(function)}\s*\([^)]*$", prefix, re.S):
            return True

    return False


def is_technical(value: str, rel: str, prefix: str) -> bool:
    value = value.strip()

    if not value or not HUMAN_RE.search(value):
        return True

    if value in TECHNICAL_EXACT:
        return True

    if any(value.startswith(item) for item in TECHNICAL_PREFIXES):
        return True

    if any(item in value for item in TECHNICAL_SUBSTRINGS):
        return True

    if any(char in value for char in ("<", ">", "=", "?", "[", "]", "{{", "}}")):
        return True

    if re.fullmatch(r"[a-z]{2}_[A-Z]{2}", value):
        return True

    if re.fullmatch(r"[a-z0-9_-]+", value):
        return True

    if re.fullmatch(r"[a-z][A-Za-z0-9_]+", value):
        return True

    if re.fullmatch(r"[A-Z0-9_]+", value):
        return True

    if re.fullmatch(r"[A-Za-z0-9+/]+=*", value) and len(value) >= 8:
        return True

    if re.fullmatch(r"[a-z0-9_-]+(?:\s+[a-z0-9_-]+)+", value):
        return True

    if re.fullmatch(r"M[A-Za-z0-9.,\s-]+Z?", value):
        return True

    if re.fullmatch(r"[a-zA-Z0-9_.:/?&=#,+|{}\[\]() -]+", value):
        if "/" in value or "{" in value or "}" in value or "." in value:
            return True

    if rel.endswith(".js") and re.search(r"(className|matches|closest|querySelector|setAttribute|getAttribute)\s*\([^)]*$", prefix, re.S):
        return True

    if rel.endswith(".js") and re.search(r"(append|record|createElement|createElementNS)\s*\([^)]*$", prefix, re.S):
        return True

    if rel.endswith(".php") and re.search(r"(sanitize_key|add_action|add_shortcode|wp_enqueue|wp_register|selected|checked|admin_url|plugin_basename|add_query_arg|preg_match|preg_replace|str_replace|explode|implode|in_array|array_key_exists)\s*\([^)]*$", prefix, re.S):
        return True

    if rel.endswith(".php") and "$legacy_defaults" in prefix:
        return True

    return False


def audit() -> tuple[int, int, list[Finding]]:
    total_visible = 0
    translated = 0
    findings: list[Finding] = []

    for path in source_files():
        text = path.read_text(encoding="utf-8")
        rel = path.relative_to(ROOT).as_posix()

        for match in STRING_RE.finditer(text):
            value = match.group("body")
            prefix = text[max(0, match.start() - 220):match.start()]

            if is_technical(value, rel, prefix):
                continue

            total_visible += 1
            if has_i18n_prefix(text, match.start()):
                translated += 1
                continue

            findings.append(Finding(rel, line_number(text, match.start()), value, "unwrapped source string"))

        if path.suffix == ".php":
            for match in HTML_TEXT_RE.finditer(text):
                value = match.group(1).strip()
                if not value or "<?php" in value:
                    continue
                if any(char in value for char in ('"', "'", "=", "[", "]", "\n", "\r")):
                    continue
                if value.startswith("ddsw-"):
                    continue
                total_visible += 1
                findings.append(Finding(rel, line_number(text, match.start(1)), value, "raw HTML text"))

    return total_visible, translated, findings


def assert_contains(path: str, needle: str, message: str, findings: list[Finding]) -> None:
    text = (ROOT / path).read_text(encoding="utf-8")
    if needle not in text:
        findings.append(Finding(path, 1, needle, message))


def main() -> int:
    total_visible, translated, findings = audit()

    assert_contains("dd-smart-whatsapp.php", f"Version: {VERSION}", "Plugin header version mismatch", findings)
    assert_contains("dd-smart-whatsapp.php", f"define('DDSW_VERSION', '{VERSION}')", "DDSW_VERSION mismatch", findings)
    assert_contains("admin/class-ddsw-admin.php", "wp_set_script_translations('ddsw-admin'", "Admin script translations missing", findings)
    assert_contains("includes/class-ddsw-assets.php", "wp_set_script_translations($handle", "Frontend script translations missing", findings)
    assert_contains("blocks/class-ddsw-block.php", "wp_set_script_translations('ddsw-block-editor'", "Block script translations missing", findings)
    assert_contains("assets/js/modal.js", "replaceChildren(document.createTextNode", "Modal text setter missing", findings)

    for rel in ("assets/js/admin.js", "assets/js/modal.js"):
        text = (ROOT / rel).read_text(encoding="utf-8")
        if "innerHTML" in text:
            findings.append(Finding(rel, 1, "innerHTML", "innerHTML must not be used for visible UI"))

    remaining = len(findings)
    print(f"total_visible_strings={total_visible}")
    print(f"internationalized_strings={translated}")
    print(f"remaining_strings={remaining}")

    if findings:
        print("DD Smart WhatsApp source i18n audit failed:")
        for finding in findings:
            print(f" - {finding.path}:{finding.line}: {finding.reason}: {finding.value[:120]!r}")
        return 1

    print("DD Smart WhatsApp source i18n audit passed.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
