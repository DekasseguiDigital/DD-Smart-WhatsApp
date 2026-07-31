$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$css = Get-Content -LiteralPath (Join-Path $root 'assets/css/frontend.css') -Raw
$renderer = Get-Content -LiteralPath (Join-Path $root 'includes/class-ddsw-renderer.php') -Raw
$settings = Get-Content -LiteralPath (Join-Path $root 'includes/class-ddsw-settings.php') -Raw
$frontend = Get-Content -LiteralPath (Join-Path $root 'assets/js/frontend.js') -Raw

$styles = @('auto', 'green', 'dark', 'light', 'outline', 'custom')
$failures = New-Object System.Collections.Generic.List[string]

foreach ($style in $styles) {
    if ($css -notmatch "ddsw-style-$style") {
        $failures.Add("Missing CSS class ddsw-style-$style")
    }

    if ($settings -notmatch "'$style'") {
        $failures.Add("Missing settings style value $style")
    }
}

if ($renderer -notmatch "'ddsw-style-'") {
    $failures.Add('Renderer does not concatenate ddsw-style-* classes')
}

foreach ($token in @('--ddsw-background', '--ddsw-color', '--ddsw-hover-background', '--ddsw-hover-color', '--ddsw-border-color', '--ddsw-radius', '--ddsw-shadow', '--ddsw-font-family')) {
    if ($css -notmatch [regex]::Escape($token) -and $frontend -notmatch [regex]::Escape($token)) {
        $failures.Add("Missing token $token")
    }
}

foreach ($placeholder in @('{browser}', '{device}', '{language}', '{referrer}', '{utm_source}', '{utm_medium}', '{utm_campaign}', '{utm_content}', '{utm_term}', '{page_url}')) {
    if ($frontend -notmatch [regex]::Escape($placeholder.Trim('{}'))) {
        $failures.Add("Missing frontend placeholder resolver for $placeholder")
    }
}

if ($failures.Count -gt 0) {
    $failures
    exit 1
}

'Appearance matrix OK'
