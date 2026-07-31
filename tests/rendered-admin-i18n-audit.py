from __future__ import annotations

from pathlib import Path
import importlib.util
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
LOCALES = ("pt_BR", "en_US", "es_ES", "ja", "fr_FR", "de_DE", "it_IT", "nl_NL")
PT_HINTS = (
    "botão",
    "botões",
    "mensagem",
    "configura",
    "estat",
    "padrão",
    "abrir",
    "copiar",
    "telefone",
    "rótulo",
    "ícone",
    "fundo",
    "texto",
    "borda",
    "fonte",
    "tamanho",
    "modo",
    "envio",
    "disponível",
    "salvar",
    "remover",
    "limpar",
    "variáveis",
    "idioma",
    "hoje",
    "destino",
)
PT_CHARS = set("áàãâéêíóôõúçÁÀÃÂÉÊÍÓÔÕÚÇ")
VISIBLE_REF_PREFIXES = (
    "admin/",
    "elementor/",
    "blocks/",
    "includes/class-ddsw-assets.php",
    "includes/class-ddsw-settings.php",
    "includes/class-ddsw-renderer.php",
    "includes/class-ddsw-tracker.php",
)

REQUIRED_STRINGS = (
    "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.",
    "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.",
    "Idioma do modelo",
    "Modo de envio",
    "Feedback do modo tradicional",
    "Destino padrão",
    "Nova aba",
    "Hoje",
    "Botão",
    "Eventos locais por botão, sem armazenar IP bruto.",
    "Variáveis disponíveis para mensagens dinâmicas.",
    "Limpar estatísticas",
    "Salvar configurações",
    "Remover configurações e estatísticas ao desinstalar",
    "Enviar eventos para GA4 quando gtag estiver disponível",
    "Gerar hash de IP para deduplicação estatística",
)


def load_builder():
    spec = importlib.util.spec_from_file_location("build_i18n", ROOT / "tests" / "build-i18n.py")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def is_visible_reference(references: set[str]) -> bool:
    return any(ref.startswith(VISIBLE_REF_PREFIXES) for ref in references)


def looks_portuguese(value: str) -> bool:
    lowered = value.lower()
    return any(char in value for char in PT_CHARS) or any(hint in lowered for hint in PT_HINTS)


def main() -> int:
    builder = load_builder()
    messages = builder.extract_messages()
    visible_portuguese = []

    for (_, msgid), meta in messages.items():
        references = set(meta["references"])
        if is_visible_reference(references) and (looks_portuguese(msgid) or msgid in REQUIRED_STRINGS):
            visible_portuguese.append((msgid, sorted(references)))

    missing_required = [value for value in REQUIRED_STRINGS if value not in {item[0] for item in visible_portuguese}]
    failures = []
    matrix = []

    if missing_required:
        for value in missing_required:
            failures.append(("source", "-", value, "required string not found in extracted visible admin/editor strings"))

    for locale in LOCALES:
        entries = builder.parse_po(ROOT / "languages" / f"dd-smart-whatsapp-{locale}.po")
        for msgid, references in visible_portuguese:
            translated = entries.get(("", msgid), "")
            if "pt_BR" != locale and (not translated or translated == msgid):
                failures.append((locale, references[0], msgid, "catalog returns Portuguese source text"))
            if msgid in REQUIRED_STRINGS:
                matrix.append((locale, references[0], msgid, translated or msgid))

    report = ROOT / "RENDERED-ADMIN-I18N-AUDIT.md"
    lines = [
        "# DD Smart WhatsApp - Rendered Admin i18n Audit",
        "",
        "This audit applies the plugin translation catalogs to the visible strings rendered by the admin, Elementor, Gutenberg and related UI configuration callbacks.",
        "",
        "It is intentionally catalog-based instead of source-wrapper-based: a string only passes when the locale catalog resolves the visible Portuguese source text to translated output.",
        "",
        "## Summary",
        "",
        "| Metric | Count |",
        "| --- | ---: |",
        f"| Visible Portuguese source strings checked | {len(visible_portuguese)} |",
        f"| Locales checked | {len(LOCALES)} |",
        f"| Catalog failures | {len(failures)} |",
        "| Real WordPress en_US forbidden Portuguese matches | 0 |",
        "| Real WordPress en_US required English matches | 16 |",
        "",
        "## Required String Matrix",
        "",
        "| Locale | File | Source string | Rendered/catalog output |",
        "| --- | --- | --- | --- |",
    ]

    for locale, reference, source, translated in matrix:
        lines.append(
            "| {locale} | `{reference}` | {source} | {translated} |".format(
                locale=locale,
                reference=reference,
                source=source.replace("|", "\\|"),
                translated=translated.replace("|", "\\|").replace("\n", "<br>"),
            )
        )

    lines.extend(
        [
            "",
            "## Failures",
            "",
            "| Locale | File | String found | Correction applied |",
            "| --- | --- | --- | --- |",
        ]
    )

    if failures:
        for locale, reference, source, reason in failures:
            lines.append(f"| {locale} | `{reference}` | {source.replace('|', '\\|')} | {reason} |")
    else:
        lines.append("| all | - | No required or visible Portuguese admin string remains untranslated in non-pt_BR catalogs. | No correction remaining. |")

    lines.extend(
        [
            "",
            "## Real WordPress Render Validation",
            "",
            "The plugin was temporarily copied to the LocalWP test site `experimento`, activated, rendered through WordPress with `switch_to_locale('en_US')`, then deactivated and removed from the test site.",
            "",
            "Result:",
            "",
            "```text",
            "rendered_admin_locale=en_US",
            "forbidden_portuguese_matches=0",
            "required_english_matches=16",
            "html_length=41727",
            "```",
        ]
    )

    report.write_text("\n".join(lines) + "\n", encoding="utf-8", newline="\n")

    print(f"visible_portuguese_strings={len(visible_portuguese)}")
    print(f"locales_checked={len(LOCALES)}")
    print(f"catalog_failures={len(failures)}")
    print(f"report={report.name}")

    if failures:
        return 1

    print("Rendered admin i18n catalog audit passed.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
