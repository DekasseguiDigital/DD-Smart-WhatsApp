# DD Smart WhatsApp - Rendered Admin i18n Audit

This audit applies the plugin translation catalogs to the visible strings rendered by the admin, Elementor, Gutenberg and related UI configuration callbacks.

It is intentionally catalog-based instead of source-wrapper-based: a string only passes when the locale catalog resolves the visible Portuguese source text to translated output.

## Summary

| Metric | Count |
| --- | ---: |
| Visible Portuguese source strings checked | 107 |
| Locales checked | 8 |
| Catalog failures | 0 |
| Real WordPress en_US forbidden Portuguese matches | 0 |
| Real WordPress en_US required English matches | 16 |

## Required String Matrix

| Locale | File | Source string | Rendered/catalog output |
| --- | --- | --- | --- |
| pt_BR | `admin/class-ddsw-admin.php:270` | Botão | Botão |
| pt_BR | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. |
| pt_BR | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. |
| pt_BR | `admin/class-ddsw-admin.php:204` | Destino padrão | Destino padrão |
| pt_BR | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Enviar eventos para GA4 quando gtag estiver disponível |
| pt_BR | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Eventos locais por botão, sem armazenar IP bruto. |
| pt_BR | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Feedback do modo tradicional |
| pt_BR | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Gerar hash de IP para deduplicação estatística |
| pt_BR | `admin/class-ddsw-admin.php:249` | Hoje | Hoje |
| pt_BR | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Idioma do modelo |
| pt_BR | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Limpar estatísticas |
| pt_BR | `admin/class-ddsw-admin.php:392` | Modo de envio | Modo de envio |
| pt_BR | `admin/class-ddsw-admin.php:206` | Nova aba | Nova aba |
| pt_BR | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remover configurações e estatísticas ao desinstalar |
| pt_BR | `admin/class-ddsw-admin.php:227` | Salvar configurações | Salvar configurações |
| pt_BR | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variáveis disponíveis para mensagens dinâmicas. |
| en_US | `admin/class-ddsw-admin.php:270` | Botão | Button |
| en_US | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages. |
| en_US | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Each button can use Traditional or Smart Copy mode without depending on Elementor. |
| en_US | `admin/class-ddsw-admin.php:204` | Destino padrão | Default target |
| en_US | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Send events to GA4 when gtag is available |
| en_US | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Local events by button, without storing raw IP addresses. |
| en_US | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Traditional mode feedback |
| en_US | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generate IP hash for statistical deduplication |
| en_US | `admin/class-ddsw-admin.php:249` | Hoje | Today |
| en_US | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Template language |
| en_US | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Clear statistics |
| en_US | `admin/class-ddsw-admin.php:392` | Modo de envio | Send mode |
| en_US | `admin/class-ddsw-admin.php:206` | Nova aba | New tab |
| en_US | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remove settings and statistics on uninstall |
| en_US | `admin/class-ddsw-admin.php:227` | Salvar configurações | Save settings |
| en_US | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| es_ES | `admin/class-ddsw-admin.php:270` | Botão | Botón |
| es_ES | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Botones reutilizables con modo tradicional y Smart Copy para preservar mensajes formateados. |
| es_ES | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Cada botón puede usar el modo tradicional o Smart Copy sin depender de Elementor. |
| es_ES | `admin/class-ddsw-admin.php:204` | Destino padrão | Destino predeterminado |
| es_ES | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Enviar eventos a GA4 cuando gtag esté disponible |
| es_ES | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Eventos locales por botón, sin almacenar IP sin procesar. |
| es_ES | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Feedback del modo tradicional |
| es_ES | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generar hash de IP para deduplicación estadística |
| es_ES | `admin/class-ddsw-admin.php:249` | Hoje | Hoy |
| es_ES | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Idioma del modelo |
| es_ES | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Limpiar estadísticas |
| es_ES | `admin/class-ddsw-admin.php:392` | Modo de envio | Modo de envío |
| es_ES | `admin/class-ddsw-admin.php:206` | Nova aba | Nueva pestaña |
| es_ES | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Eliminar configuraciones y estadísticas al desinstalar |
| es_ES | `admin/class-ddsw-admin.php:227` | Salvar configurações | Guardar configuración |
| es_ES | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| ja | `admin/class-ddsw-admin.php:270` | Botão | ボタン |
| ja | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | 書式付きメッセージを保持するための従来モードとSmart Copyモードを備えた再利用可能なボタン。 |
| ja | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | 各ボタンはElementorに依存せず、従来モードまたはSmart Copyを使用できます。 |
| ja | `admin/class-ddsw-admin.php:204` | Destino padrão | 既定の表示先 |
| ja | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | gtagが利用可能な場合にGA4へイベントを送信 |
| ja | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | 生のIPを保存しない、ボタン別のローカルイベント。 |
| ja | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | 従来モードのフィードバック |
| ja | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | 統計の重複排除用にIPハッシュを生成 |
| ja | `admin/class-ddsw-admin.php:249` | Hoje | 今日 |
| ja | `admin/class-ddsw-admin.php:374` | Idioma do modelo | テンプレート言語 |
| ja | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | 統計をクリア |
| ja | `admin/class-ddsw-admin.php:392` | Modo de envio | 送信モード |
| ja | `admin/class-ddsw-admin.php:206` | Nova aba | 新しいタブ |
| ja | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | アンインストール時に設定と統計を削除 |
| ja | `admin/class-ddsw-admin.php:227` | Salvar configurações | 設定を保存 |
| ja | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| fr_FR | `admin/class-ddsw-admin.php:270` | Botão | Button |
| fr_FR | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages. |
| fr_FR | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Each button can use Traditional or Smart Copy mode without depending on Elementor. |
| fr_FR | `admin/class-ddsw-admin.php:204` | Destino padrão | Default target |
| fr_FR | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Send events to GA4 when gtag is available |
| fr_FR | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Local events by button, without storing raw IP addresses. |
| fr_FR | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Traditional mode feedback |
| fr_FR | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generate IP hash for statistical deduplication |
| fr_FR | `admin/class-ddsw-admin.php:249` | Hoje | Today |
| fr_FR | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Template language |
| fr_FR | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Clear statistics |
| fr_FR | `admin/class-ddsw-admin.php:392` | Modo de envio | Send mode |
| fr_FR | `admin/class-ddsw-admin.php:206` | Nova aba | New tab |
| fr_FR | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remove settings and statistics on uninstall |
| fr_FR | `admin/class-ddsw-admin.php:227` | Salvar configurações | Save settings |
| fr_FR | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| de_DE | `admin/class-ddsw-admin.php:270` | Botão | Button |
| de_DE | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages. |
| de_DE | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Each button can use Traditional or Smart Copy mode without depending on Elementor. |
| de_DE | `admin/class-ddsw-admin.php:204` | Destino padrão | Default target |
| de_DE | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Send events to GA4 when gtag is available |
| de_DE | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Local events by button, without storing raw IP addresses. |
| de_DE | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Traditional mode feedback |
| de_DE | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generate IP hash for statistical deduplication |
| de_DE | `admin/class-ddsw-admin.php:249` | Hoje | Today |
| de_DE | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Template language |
| de_DE | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Clear statistics |
| de_DE | `admin/class-ddsw-admin.php:392` | Modo de envio | Send mode |
| de_DE | `admin/class-ddsw-admin.php:206` | Nova aba | New tab |
| de_DE | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remove settings and statistics on uninstall |
| de_DE | `admin/class-ddsw-admin.php:227` | Salvar configurações | Save settings |
| de_DE | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| it_IT | `admin/class-ddsw-admin.php:270` | Botão | Button |
| it_IT | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages. |
| it_IT | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Each button can use Traditional or Smart Copy mode without depending on Elementor. |
| it_IT | `admin/class-ddsw-admin.php:204` | Destino padrão | Default target |
| it_IT | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Send events to GA4 when gtag is available |
| it_IT | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Local events by button, without storing raw IP addresses. |
| it_IT | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Traditional mode feedback |
| it_IT | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generate IP hash for statistical deduplication |
| it_IT | `admin/class-ddsw-admin.php:249` | Hoje | Today |
| it_IT | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Template language |
| it_IT | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Clear statistics |
| it_IT | `admin/class-ddsw-admin.php:392` | Modo de envio | Send mode |
| it_IT | `admin/class-ddsw-admin.php:206` | Nova aba | New tab |
| it_IT | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remove settings and statistics on uninstall |
| it_IT | `admin/class-ddsw-admin.php:227` | Salvar configurações | Save settings |
| it_IT | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |
| nl_NL | `admin/class-ddsw-admin.php:270` | Botão | Button |
| nl_NL | `admin/class-ddsw-admin.php:125` | Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas. | Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages. |
| nl_NL | `admin/class-ddsw-admin.php:149` | Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor. | Each button can use Traditional or Smart Copy mode without depending on Elementor. |
| nl_NL | `admin/class-ddsw-admin.php:204` | Destino padrão | Default target |
| nl_NL | `admin/class-ddsw-admin.php:213` | Enviar eventos para GA4 quando gtag estiver disponível | Send events to GA4 when gtag is available |
| nl_NL | `admin/class-ddsw-admin.php:238` | Eventos locais por botão, sem armazenar IP bruto. | Local events by button, without storing raw IP addresses. |
| nl_NL | `admin/class-ddsw-admin.php:199` | Feedback do modo tradicional | Traditional mode feedback |
| nl_NL | `admin/class-ddsw-admin.php:218` | Gerar hash de IP para deduplicação estatística | Generate IP hash for statistical deduplication |
| nl_NL | `admin/class-ddsw-admin.php:249` | Hoje | Today |
| nl_NL | `admin/class-ddsw-admin.php:374` | Idioma do modelo | Template language |
| nl_NL | `admin/class-ddsw-admin.php:300` | Limpar estatísticas | Clear statistics |
| nl_NL | `admin/class-ddsw-admin.php:392` | Modo de envio | Send mode |
| nl_NL | `admin/class-ddsw-admin.php:206` | Nova aba | New tab |
| nl_NL | `admin/class-ddsw-admin.php:223` | Remover configurações e estatísticas ao desinstalar | Remove settings and statistics on uninstall |
| nl_NL | `admin/class-ddsw-admin.php:227` | Salvar configurações | Save settings |
| nl_NL | `admin/class-ddsw-admin.php:313` | Variáveis disponíveis para mensagens dinâmicas. | Variables available for dynamic messages. |

## Failures

| Locale | File | String found | Correction applied |
| --- | --- | --- | --- |
| all | - | No required or visible Portuguese admin string remains untranslated in non-pt_BR catalogs. | No correction remaining. |

## Real WordPress Render Validation

The plugin was temporarily copied to the LocalWP test site `experimento`, activated, rendered through WordPress with `switch_to_locale('en_US')`, then deactivated and removed from the test site.

Result:

```text
rendered_admin_locale=en_US
forbidden_portuguese_matches=0
required_english_matches=16
html_length=41727
```
