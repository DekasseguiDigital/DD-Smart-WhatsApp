$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$failures = New-Object System.Collections.Generic.List[string]

function Assert-Contains {
    param(
        [string] $Path,
        [string] $Pattern,
        [string] $Message
    )

    $content = Get-Content -LiteralPath $Path -Raw -Encoding UTF8
    if ($content -notmatch [regex]::Escape($Pattern)) {
        $failures.Add($Message)
    }
}

function Assert-File {
    param(
        [string] $Path,
        [string] $Message
    )

    if (-not (Test-Path -LiteralPath $Path -PathType Leaf)) {
        $failures.Add($Message)
        return
    }

    if ((Get-Item -LiteralPath $Path).Length -le 0) {
        $failures.Add("$Message (empty file)")
    }
}

function Assert-ContainsUtf8Hex {
    param(
        [string] $Path,
        [string] $Hex,
        [string] $Message
    )

    $content = [System.IO.File]::ReadAllBytes($Path)
    $needle = New-Object byte[] ($Hex.Length / 2)
    for ($i = 0; $i -lt $needle.Length; $i++) {
        $needle[$i] = [Convert]::ToByte($Hex.Substring($i * 2, 2), 16)
    }

    $found = $false
    for ($offset = 0; $offset -le ($content.Length - $needle.Length); $offset++) {
        $match = $true
        for ($i = 0; $i -lt $needle.Length; $i++) {
            if ($content[$offset + $i] -ne $needle[$i]) {
                $match = $false
                break
            }
        }

        if ($match) {
            $found = $true
            break
        }
    }

    if (-not $found) {
        $failures.Add($Message)
    }
}

$main = Join-Path $root 'dd-smart-whatsapp.php'
$i18n = Join-Path $root 'includes/class-ddsw-i18n.php'
$resolver = Join-Path $root 'includes/class-ddsw-language.php'
$assets = Join-Path $root 'includes/class-ddsw-assets.php'
$block = Join-Path $root 'blocks/class-ddsw-block.php'
$admin = Join-Path $root 'admin/class-ddsw-admin.php'
$settings = Join-Path $root 'includes/class-ddsw-settings.php'

Assert-Contains $main 'Version: 2.2.0-beta.5' 'Main plugin header must be version 2.2.0-beta.5.'
Assert-Contains $main "define('DDSW_VERSION', '2.2.0-beta.5')" 'DDSW_VERSION must be 2.2.0-beta.5.'
Assert-Contains $main 'class-ddsw-language.php' 'Central language service must be loaded before i18n.'
Assert-Contains $resolver 'determine_locale' 'Language resolver must inspect determine_locale().'
Assert-Contains $resolver 'pll_current_language' 'Language resolver must support Polylang.'
Assert-Contains $resolver 'wpml_current_language' 'Language resolver must support WPML.'
Assert-Contains $resolver 'REQUEST_URI' 'Language resolver must support subdirectory language detection.'
Assert-Contains $resolver 'HTTP_HOST' 'Language resolver must support subdomain language detection.'
Assert-Contains $i18n 'DDSW_Language::resolve' 'DDSW_I18n must delegate locale detection to the central language service.'
Assert-Contains $i18n "load_plugin_textdomain(self::DOMAIN, false, `$domain_path)" 'Plugin textdomain must load from the plugin languages directory.'
Assert-Contains $i18n 'supported_template_locales' 'Supported template locales helper is missing.'
Assert-Contains $resolver 'default_button_texts' 'Default template text sets are not routed through gettext.'
Assert-Contains $settings 'template_locale' 'Per-button template_locale setting is missing.'
Assert-Contains $admin 'data-ddsw-template-locale' 'Admin template locale selector is missing.'
Assert-Contains $admin 'data-ddsw-restore-defaults' 'Admin restore language default button is missing.'
Assert-Contains $admin "wp_set_script_translations('ddsw-admin'" 'Admin JavaScript translations are not registered.'
Assert-Contains $assets 'wp_set_script_translations' 'Frontend JavaScript translations are not registered.'
Assert-Contains $assets 'resolvedLocale' 'Frontend payload is missing resolved locale debug data.'
Assert-Contains $assets 'languageSource' 'Frontend payload is missing language source debug data.'
Assert-Contains $assets 'gettextLocale' 'Frontend payload is missing gettext locale debug data.'
Assert-Contains $assets 'payloadLanguage' 'Frontend payload is missing payload language debug data.'
Assert-Contains $block 'wp_set_script_translations' 'Gutenberg JavaScript translations are not registered.'
Assert-Contains (Join-Path $root 'includes/class-ddsw-renderer.php') "'closeLabel'" 'Renderer modal payload is missing closeLabel.'
Assert-Contains (Join-Path $root 'assets/js/modal.js') 'modalConfig.close' 'Modal close aria-label does not use the canonical modal payload.'

$locales = @('pt_BR', 'en_US', 'es_ES', 'ja', 'fr_FR', 'de_DE', 'it_IT', 'nl_NL')
foreach ($locale in $locales) {
    Assert-File (Join-Path $root "languages/dd-smart-whatsapp-$locale.po") "Missing domain-prefixed PO for $locale."
    Assert-File (Join-Path $root "languages/dd-smart-whatsapp-$locale.mo") "Missing domain-prefixed MO for $locale."
}

$jaPo = Join-Path $root 'languages/dd-smart-whatsapp-ja.po'
Assert-ContainsUtf8Hex $jaPo '5768617473417070e381a7e79bb8e8ab87e38199e3828b' 'Japanese CTA default is missing or corrupted.'
Assert-ContainsUtf8Hex $jaPo 'e383a1e38383e382bbe383bce382b8e38292e382b3e38394e383bce38197e381bee38197e3819f' 'Japanese Smart Copy modal title is missing or corrupted.'
Assert-ContainsUtf8Hex $jaPo 'e99689e38198e3828b' 'Japanese modal close label is missing or corrupted.'

if ($failures.Count -gt 0) {
    Write-Host 'DD Smart WhatsApp i18n audit failed:'
    foreach ($failure in $failures) {
        Write-Host " - $failure"
    }
    exit 1
}

Write-Host 'DD Smart WhatsApp i18n audit passed.'
