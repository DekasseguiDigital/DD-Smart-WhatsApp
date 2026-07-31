from __future__ import annotations

from pathlib import Path
from datetime import datetime, timezone
import ast
import re
import struct

ROOT = Path(__file__).resolve().parents[1]
LANG_DIR = ROOT / "languages"
DOMAIN = "dd-smart-whatsapp"
VERSION = "2.2.0-beta.4"
LOCALES = ("pt_BR", "en_US", "es_ES", "ja", "fr_FR", "de_DE", "it_IT", "nl_NL")
SOURCE_SUFFIXES = {".php", ".js"}
SKIP_PARTS = {"languages", "tests"}
FORCE_TRANSLATIONS = {
    "Automatic — current site language",
    "Portuguese",
    "Spanish",
    "Japanese",
    "Button appearance",
    "Statistics",
    "Restore current button",
    "Default target",
    "Template language",
    "Get support on WhatsApp",
    "Support",
    "Plan my trip on WhatsApp",
    "Tourism",
    "Restaurant",
    "Lawyer",
    "Doctor",
    "Hotel",
    "Barbershop",
    "Real estate",
    "Store",
    "Freelancer",
    "Consulting",
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
    "Variáveis para mensagens dinâmicas",
    "Limpar estatísticas",
    "Salvar configurações",
    "Remover configurações e estatísticas ao desinstalar",
    "Enviar eventos para GA4 quando gtag estiver disponível",
    "Gerar hash de IP para deduplicação estatística",
    "Aparência do botão",
    "Estatísticas",
    "Restaurar botão atual",
    "Copied successfully",
    "Your message has been copied to the clipboard.",
    "Click Open WhatsApp and press Ctrl + V in the message field.",
    "Open WhatsApp",
    "Do not show again on this browser",
}

I18N_RE = re.compile(
    r"(?P<fn>esc_html__|esc_html_e|esc_html_x|esc_attr__|esc_attr_e|esc_attr_x|__|_e|_x|_n|_nx)\s*\(",
    re.S,
)

FALLBACK_TRANSLATIONS = {
    "en_US": {
        "Assunto do e-mail": "Email subject",
        "Abrir %s": "Open %s",
        "A mensagem inicial foi copiada para a área de transferência.": "The initial message has been copied to the clipboard.",
        "A mensagem inicial será copiada antes de abrir este canal.": "The initial message will be copied before opening this channel.",
        "Canal": "Channel",
        "Changelog": "Changelog",
        "Cole na conversa.": "Paste it in the conversation.",
        "Configurações": "Settings",
        "Copiar mensagem?": "Copy message?",
        "Consulta desde el sitio web": "Website inquiry",
        "Corpo do e-mail": "Email body",
        "Depois que a conversa abrir, cole a mensagem copiada.": "After the conversation opens, paste the copied message.",
        "Documentação": "Documentation",
        "Este campo já possui conteúdo. Deseja substituir pela sugestão deste canal?": "This field already has content. Do you want to replace it with this channel suggestion?",
        "Mensagem copiada.": "Message copied.",
        "Mensagem copiada. Cole na conversa.": "Message copied. Paste it in the conversation.",
        "Não foi possível copiar automaticamente. Selecione e copie a mensagem abaixo.": "Automatic copy failed. Select and copy the message below.",
        "Use placeholders como {{name}}, {{page_title}} e {{page_url}}.": "Use placeholders such as {{name}}, {{page_title}} and {{page_url}}.",
        "Usar mensagem sugerida": "Use suggested message",
        "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.": "Hello {{name}},\n\nI found your website and would like to receive information about your services.\n\nCould you help me with availability, pricing and next steps?\n\nThank you.",
        "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.": "Hello {{name}},\n\nI found your website and would like to receive more information about your services.\n\nCould you help me with availability, pricing and next steps?\n\nThank you.",
        "Hola {{name}},\n\nVi su página de Facebook desde el sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría indicarme disponibilidad, precios y cómo continuar?\n\nMuchas gracias.": "Hello {{name}},\n\nI saw your Facebook page from the website and would like to receive more information about your services.\n\nCould you tell me availability, pricing and how to continue?\n\nThank you.",
        "Hola {{name}} 👋\n\nEncontré su perfil de Instagram desde el sitio web.\n\nMe gustaría recibir más información sobre sus servicios y disponibilidad.\n\n¡Muchas gracias!": "Hello {{name}} 👋\n\nI found your Instagram profile from the website.\n\nI would like to receive more information about your services and availability.\n\nThank you!",
        "Abre o WhatsApp com a mensagem pelo parâmetro text=": "Opens WhatsApp with the message through the text= parameter",
        "Abrir WhatsApp →": "Open WhatsApp →",
        "Abrir automaticamente após copiar": "Open automatically after copying",
        "Abrir em nova aba": "Open in a new tab",
        "Adicionar botão": "Add button",
        "Agora clique em Abrir WhatsApp.\n\nQuando a conversa abrir, pressione Ctrl + V (Windows) ou ⌘ + V (Mac) para colar automaticamente a mensagem.": "Now click Open WhatsApp.\n\nWhen the conversation opens, press Ctrl + V (Windows) or ⌘ + V (Mac) to paste the message automatically.",
        "Aparência do botão": "Button appearance",
        "Ativado": "Enabled",
        "Ativo": "Active",
        "Auto — usar identidade visual do site": "Auto — use the site's visual identity",
        "Automático — idioma atual do site": "Automatic — current site language",
        "Automática": "Automatic",
        "Bem-vindo ao DD Smart WhatsApp": "Welcome to DD Smart WhatsApp",
        "Borda": "Border",
        "Botão": "Button",
        "Botão inválido.": "Invalid button.",
        "Botão salvo": "Saved button",
        "Botões configurados": "Configured buttons",
        "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.": "Reusable buttons with Traditional and Smart Copy modes to preserve formatted messages.",
        "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.": "Each button can use Traditional or Smart Copy mode without depending on Elementor.",
        "Capitalizar": "Capitalize",
        "Centro": "Center",
        "Claro": "Light",
        "Clean": "Clean",
        "Conteúdo": "Content",
        "Contorno": "Outline",
        "Concluir wizard": "Complete wizard",
        "Copia a mensagem formatada e abre o WhatsApp sem texto": "Copies the formatted message and opens WhatsApp without text",
        "Copiar novamente": "Copy again",
        "Copiar shortcode": "Copy shortcode",
        "Copied successfully": "Copied successfully",
        "Cor": "Color",
        "Cor fundo": "Background color",
        "Cor texto": "Text color",
        "Cor ícone": "Icon color",
        "Conversão": "Conversion",
        "Custom Override": "Custom Override",
        "DD Smart WhatsApp: configure o telefone do botão.": "DD Smart WhatsApp: configure the button phone number.",
        "DD Smart WhatsApp: falha ao copiar no modo tradicional.": "DD Smart WhatsApp: failed to copy in Traditional mode.",
        "DD Smart WhatsApp: falha ao registrar evento.": "DD Smart WhatsApp: failed to record event.",
        "DD Smart WhatsApp: falha no Smart Copy.": "DD Smart WhatsApp: Smart Copy failed.",
        "DD Smart WhatsApp: falha no fallback de cópia.": "DD Smart WhatsApp: copy fallback failed.",
        "DD Smart WhatsApp: payload inválido.": "DD Smart WhatsApp: invalid payload.",
        "DDSW Debug": "DDSW Debug",
        "Delay automático": "Automatic delay",
        "Deseja atualizar automaticamente os textos que ainda usam o padrão anterior?": "Do you want to automatically update texts that still use the previous default?",
        "Dekassegui Digital": "Dekassegui Digital",
        "Desativado": "Disabled",
        "Destino padrão": "Default target",
        "Direita": "Right",
        "Distância entre ícone e texto": "Distance between icon and text",
        "Enviar eventos para GA4 quando gtag estiver disponível": "Send events to GA4 when gtag is available",
        "Escuro": "Dark",
        "Espaçamento": "Spacing",
        "Escolha as opções e copie o shortcode automaticamente.": "Choose the options and copy the shortcode automatically.",
        "Espessura da borda": "Border width",
        "Esquerda": "Left",
        "Estatísticas": "Statistics",
        "Estatísticas limpas com sucesso.": "Statistics cleared successfully.",
        "Estilo do botão": "Button style",
        "Estilo do modal": "Modal style",
        "Eventos": "Events",
        "Eventos locais por botão, sem armazenar IP bruto.": "Local events by button, without storing raw IP addresses.",
        "Eventos totais": "Total events",
        "Família da fonte": "Font family",
        "Feedback do modo tradicional": "Traditional mode feedback",
        "Fechar": "Close",
        "Fechar modal automaticamente": "Close modal automatically",
        "Gerar hash de IP para deduplicação estatística": "Generate IP hash for statistical deduplication",
        "Herdar fonte do tema": "Inherit theme font",
        "Herdar padding": "Inherit padding",
        "Herdar raio das bordas": "Inherit border radius",
        "Herdar sombra": "Inherit shadow",
        "Herdar tamanho de texto": "Inherit text size",
        "Hoje": "Today",
        "Hover fundo": "Hover background",
        "Hover texto": "Hover text",
        "Hover ícone": "Hover icon",
        "ID estável": "Stable ID",
        "Idioma do modelo": "Template language",
        "Idioma inteligente": "Smart language",
        "Instrução Android": "Android instruction",
        "Instrução desktop": "Desktop instruction",
        "Instrução iPhone/iPad": "iPhone/iPad instruction",
        "Limpar estatísticas": "Clear statistics",
        "Locale": "Locale",
        "MO Loaded": "MO Loaded",
        "Maiúsculas": "Uppercase",
        "Mensagem": "Message",
        "Mensagem copiada": "Message copied",
        "Mensagem de erro": "Error message",
        "Mensagem de sucesso": "Success message",
        "Mesma aba": "Same tab",
        "Minúsculas": "Lowercase",
        "Modal Source": "Modal Source",
        "Modo": "Mode",
        "Modo de envio": "Send mode",
        "Mostrar ícone": "Show icon",
        "Nenhuma": "None",
        "Nova aba": "New tab",
        "Não foi possível copiar automaticamente.": "Automatic copy failed.",
        "Não mostrar novamente neste navegador": "Do not show again on this browser",
        "Número": "Number",
        "O frontend sempre usa o idioma do site. A área administrativa pode seguir o idioma do usuário logado.": "The frontend always uses the site language. The admin area can follow the logged-in user language.",
        "O idioma do modelo foi alterado.": "The template language changed.",
        "Opcional. Deixe vazio para usar o ícone padrão do WhatsApp.": "Optional. Leave empty to use the default WhatsApp icon.",
        "Padding horizontal": "Horizontal padding",
        "Padding vertical": "Vertical padding",
        "PO Loaded": "PO Loaded",
        "Passo 1: escolha o idioma.": "Step 1: choose the language.",
        "Passo 2: informe o número WhatsApp.": "Step 2: enter the WhatsApp number.",
        "Passo 3: crie o primeiro botão.": "Step 3: create the first button.",
        "Passo 4: copie o shortcode.": "Step 4: copy the shortcode.",
        "Personalizado": "Custom",
        "Peso da fonte": "Font weight",
        "Placeholders": "Placeholders",
        "Português": "Portuguese",
        "Principal": "Main",
        "Raio": "Radius",
        "Remover": "Remove",
        "Remover configurações e estatísticas ao desinstalar": "Remove settings and statistics on uninstall",
        "Requisição inválida.": "Invalid request.",
        "Resolved By": "Resolved By",
        "Restaurar os textos padrão para o idioma selecionado?": "Restore the default texts for the selected language?",
        "Restaurar padrão do idioma": "Restore language default",
        "Restaurar todos os botões conforme o idioma/modelo selecionado?": "Restore all buttons using their selected language/model?",
        "Restaurar TODOS os botões": "Restore ALL buttons",
        "Restaurar botão atual": "Restore current button",
        "Rótulo": "Label",
        "Rótulo Abrir WhatsApp": "Open WhatsApp label",
        "Rótulo Copiar novamente": "Copy again label",
        "Rótulo Fechar": "Close label",
        "Rótulo copiar novamente": "Copy again label",
        "Rótulo do botão abrir": "Open button label",
        "Rótulo do botão fechar": "Close button label",
        "SVG do ícone": "Icon SVG",
        "Salvar configurações": "Save settings",
        "Smart Copy shortcode": "Smart Copy shortcode",
        "Sobrescrever mensagem": "Override message",
        "Sombra": "Shadow",
        "Sombra no hover": "Hover shadow",
        "Sua mensagem foi copiada para a área de transferência.": "Your message has been copied to the clipboard.",
        "Substituir rótulo": "Override label",
        "Tamanho": "Size",
        "Tamanho do texto": "Text size",
        "Tamanho ícone": "Icon size",
        "Template": "Template",
        "Telefone": "Phone",
        "Título": "Title",
        "Título do modal": "Modal title",
        "Transformação": "Transform",
        "Transição": "Transition",
        "Usar configuração do botão": "Use button setting",
        "Usar configuração do painel": "Use panel setting",
        "Translation Loaded": "Translation Loaded",
        "Utilizar idioma do site": "Use site language",
        "Utilizar idioma do usuário logado": "Use logged-in user language",
        "Últimos 30 dias": "Last 30 days",
        "Usar mensagem do painel": "Use panel message",
        "Usar padrão do botão": "Use button default",
        "Usar rótulo do painel": "Use panel label",
        "Usar telefone do painel": "Use panel phone",
        "Variáveis disponíveis para mensagens dinâmicas.": "Variables available for dynamic messages.",
        "Variáveis para mensagens dinâmicas": "Variables for dynamic messages",
        "Verde WhatsApp": "WhatsApp Green",
        "Você não tem permissão para acessar esta página.": "You do not have permission to access this page.",
        "Você não tem permissão para executar esta ação.": "You do not have permission to perform this action.",
        "Ícone": "Icon",
    },
    "pt_BR": {
        "Assunto do e-mail": "Assunto do e-mail",
        "Abrir %s": "Abrir %s",
        "A mensagem inicial foi copiada para a área de transferência.": "A mensagem inicial foi copiada para a área de transferência.",
        "A mensagem inicial será copiada antes de abrir este canal.": "A mensagem inicial será copiada antes de abrir este canal.",
        "Canal": "Canal",
        "Changelog": "Changelog",
        "Cole na conversa.": "Cole na conversa.",
        "Configurações": "Configurações",
        "Copiar mensagem?": "Copiar mensagem?",
        "Consulta desde el sitio web": "Consulta pelo site",
        "Corpo do e-mail": "Corpo do e-mail",
        "Depois que a conversa abrir, cole a mensagem copiada.": "Depois que a conversa abrir, cole a mensagem copiada.",
        "Documentação": "Documentação",
        "Este campo já possui conteúdo. Deseja substituir pela sugestão deste canal?": "Este campo já possui conteúdo. Deseja substituir pela sugestão deste canal?",
        "Mensagem copiada.": "Mensagem copiada.",
        "Mensagem copiada. Cole na conversa.": "Mensagem copiada. Cole na conversa.",
        "Não foi possível copiar automaticamente. Selecione e copie a mensagem abaixo.": "Não foi possível copiar automaticamente. Selecione e copie a mensagem abaixo.",
        "Use placeholders como {{name}}, {{page_title}} e {{page_url}}.": "Use placeholders como {{name}}, {{page_title}} e {{page_url}}.",
        "Usar mensagem sugerida": "Usar mensagem sugerida",
        "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.": "Olá {{name}},\n\nEncontrei seu site e gostaria de receber informações sobre seus serviços.\n\nVocê poderia me ajudar com disponibilidade, preços e próximos passos?\n\nMuito obrigado.",
        "Hola {{name}},\n\nEncontré su sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría ayudarme con disponibilidad, precios y próximos pasos?\n\nMuchas gracias.": "Olá {{name}},\n\nEncontrei seu site e gostaria de receber mais informações sobre seus serviços.\n\nVocê poderia me ajudar com disponibilidade, preços e próximos passos?\n\nMuito obrigado.",
        "Hola {{name}},\n\nVi su página de Facebook desde el sitio web y me gustaría recibir más información sobre sus servicios.\n\n¿Podría indicarme disponibilidad, precios y cómo continuar?\n\nMuchas gracias.": "Olá {{name}},\n\nVi sua página do Facebook pelo site e gostaria de receber mais informações sobre seus serviços.\n\nVocê poderia me informar disponibilidade, preços e como continuar?\n\nMuito obrigado.",
        "Hola {{name}} 👋\n\nEncontré su perfil de Instagram desde el sitio web.\n\nMe gustaría recibir más información sobre sus servicios y disponibilidad.\n\n¡Muchas gracias!": "Olá {{name}} 👋\n\nEncontrei seu perfil do Instagram pelo site.\n\nGostaria de receber mais informações sobre seus serviços e disponibilidade.\n\nMuito obrigado!",
        "Automatic — current site language": "Automático — idioma atual do site",
        "Portuguese": "Português",
        "Spanish": "Espanhol",
        "Japanese": "Japonês",
        "Button appearance": "Aparência do botão",
        "Statistics": "Estatísticas",
        "Restore current button": "Restaurar botão atual",
        "Default target": "Destino padrão",
        "Template language": "Idioma do modelo",
        "Tourism": "Turismo",
        "Restaurant": "Restaurante",
        "Lawyer": "Advogado",
        "Doctor": "Médico",
        "Hotel": "Hotel",
        "Barbershop": "Barbearia",
        "Real estate": "Imobiliária",
        "Support": "Suporte",
        "Store": "Loja",
        "Freelancer": "Freelancer",
        "Consulting": "Consultoria",
        "Plan my trip on WhatsApp": "Planejar minha viagem no WhatsApp",
        "Get support on WhatsApp": "Receber suporte no WhatsApp",
        "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.": "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.",
        "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.": "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.",
        "Idioma do modelo": "Idioma do modelo",
        "Modo de envio": "Modo de envio",
        "Feedback do modo tradicional": "Feedback do modo tradicional",
        "Destino padrão": "Destino padrão",
        "Nova aba": "Nova aba",
        "Hoje": "Hoje",
        "Botão": "Botão",
        "Eventos locais por botão, sem armazenar IP bruto.": "Eventos locais por botão, sem armazenar IP bruto.",
        "Variáveis para mensagens dinâmicas": "Variáveis para mensagens dinâmicas",
        "Limpar estatísticas": "Limpar estatísticas",
        "Salvar configurações": "Salvar configurações",
        "Remover configurações e estatísticas ao desinstalar": "Remover configurações e estatísticas ao desinstalar",
        "Enviar eventos para GA4 quando gtag estiver disponível": "Enviar eventos para GA4 quando gtag estiver disponível",
        "Gerar hash de IP para deduplicação estatística": "Gerar hash de IP para deduplicação estatística",
        "Aparência do botão": "Aparência do botão",
        "Estatísticas": "Estatísticas",
        "Restaurar botão atual": "Restaurar botão atual",
        "Chat on WhatsApp": "Falar no WhatsApp",
        "Copied successfully": "Mensagem copiada",
        "Do not show again on this browser": "Não mostrar novamente neste navegador",
        "Message copied": "Mensagem copiada",
        "Your message has been copied to the clipboard.": "Sua mensagem foi copiada para a área de transferência.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Clique em Abrir WhatsApp e pressione Ctrl + V no campo da mensagem.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Toque em Abrir WhatsApp, toque no campo da mensagem e escolha Colar.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Toque em Abrir WhatsApp, toque e segure no campo da mensagem e escolha Colar.",
        "Open WhatsApp": "Abrir WhatsApp",
        "Open WhatsApp →": "Abrir WhatsApp →",
        "Close": "Fechar",
        "Copy again": "Copiar novamente",
        "Automatic copy failed. Select and copy the message below.": "Não foi possível copiar automaticamente. Selecione e copie a mensagem abaixo.",
        "Message copied. Opening WhatsApp...": "Mensagem copiada. Abrindo WhatsApp...",
        "Restore the default texts for the selected language?": "Restaurar os textos padrão para o idioma selecionado?",
        "%d ms": "%d ms",
        "principal": "principal",
    },
    "es_ES": {
        "Assunto do e-mail": "Asunto del e-mail",
        "Abrir %s": "Abrir %s",
        "A mensagem inicial foi copiada para a área de transferência.": "El mensaje inicial se copió al portapapeles.",
        "A mensagem inicial será copiada antes de abrir este canal.": "El mensaje inicial se copiará antes de abrir este canal.",
        "Canal": "Canal",
        "Changelog": "Registro de cambios",
        "Cole na conversa.": "Pégalo en la conversación.",
        "Configurações": "Configuración",
        "Copiar mensagem?": "¿Copiar mensaje?",
        "Consulta desde el sitio web": "Consulta desde el sitio web",
        "Corpo do e-mail": "Cuerpo del e-mail",
        "Depois que a conversa abrir, cole a mensagem copiada.": "Después de que se abra la conversación, pega el mensaje copiado.",
        "Documentação": "Documentación",
        "Este campo já possui conteúdo. Deseja substituir pela sugestão deste canal?": "Este campo ya tiene contenido. ¿Deseas sustituirlo por la sugerencia de este canal?",
        "Mensagem copiada.": "Mensaje copiado.",
        "Mensagem copiada. Cole na conversa.": "Mensaje copiado. Pégalo en la conversación.",
        "Não foi possível copiar automaticamente. Selecione e copie a mensagem abaixo.": "No se pudo copiar automáticamente. Selecciona y copia el mensaje a continuación.",
        "Use placeholders como {{name}}, {{page_title}} e {{page_url}}.": "Usa placeholders como {{name}}, {{page_title}} y {{page_url}}.",
        "Usar mensagem sugerida": "Usar mensaje sugerido",
        "Automatic — current site language": "Automático — idioma actual del sitio",
        "Portuguese": "Portugués",
        "Spanish": "Español",
        "Japanese": "Japonés",
        "Button appearance": "Apariencia del botón",
        "Statistics": "Estadísticas",
        "Restore current button": "Restaurar botón actual",
        "Default target": "Destino predeterminado",
        "Template language": "Idioma del modelo",
        "Tourism": "Turismo",
        "Restaurant": "Restaurante",
        "Lawyer": "Abogado",
        "Doctor": "Médico",
        "Hotel": "Hotel",
        "Barbershop": "Barbería",
        "Real estate": "Inmobiliaria",
        "Support": "Soporte",
        "Store": "Tienda",
        "Freelancer": "Freelancer",
        "Consulting": "Consultoría",
        "Plan my trip on WhatsApp": "Planificar mi viaje por WhatsApp",
        "Get support on WhatsApp": "Recibir soporte por WhatsApp",
        "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.": "Botones reutilizables con modo tradicional y Smart Copy para preservar mensajes formateados.",
        "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.": "Cada botón puede usar el modo tradicional o Smart Copy sin depender de Elementor.",
        "Idioma do modelo": "Idioma del modelo",
        "Modo de envio": "Modo de envío",
        "Feedback do modo tradicional": "Feedback del modo tradicional",
        "Destino padrão": "Destino predeterminado",
        "Nova aba": "Nueva pestaña",
        "Hoje": "Hoy",
        "Botão": "Botón",
        "Eventos locais por botão, sem armazenar IP bruto.": "Eventos locales por botón, sin almacenar IP sin procesar.",
        "Variáveis para mensagens dinâmicas": "Variables para mensajes dinámicos",
        "Limpar estatísticas": "Limpiar estadísticas",
        "Salvar configurações": "Guardar configuración",
        "Remover configurações e estatísticas ao desinstalar": "Eliminar configuraciones y estadísticas al desinstalar",
        "Enviar eventos para GA4 quando gtag estiver disponível": "Enviar eventos a GA4 cuando gtag esté disponible",
        "Gerar hash de IP para deduplicação estatística": "Generar hash de IP para deduplicación estadística",
        "Aparência do botão": "Apariencia del botón",
        "Estatísticas": "Estadísticas",
        "Restaurar botão atual": "Restaurar botón actual",
        "Chat on WhatsApp": "Escribir por WhatsApp",
        "Copied successfully": "Mensaje copiado",
        "Do not show again on this browser": "No volver a mostrar en este navegador",
        "Message copied": "Mensaje copiado",
        "Your message has been copied to the clipboard.": "El mensaje se copió correctamente al portapapeles.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Haz clic en Abrir WhatsApp y presiona Ctrl + V en el campo del mensaje.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Toca Abrir WhatsApp, toca el campo del mensaje y elige Pegar.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Toca Abrir WhatsApp, mantén pulsado el campo del mensaje y elige Pegar.",
        "Open WhatsApp": "Abrir WhatsApp",
        "Open WhatsApp →": "Abrir WhatsApp →",
        "Close": "Cerrar",
        "Copy again": "Copiar de nuevo",
        "Automatic copy failed. Select and copy the message below.": "No se pudo copiar automáticamente. Selecciona y copia el mensaje abajo.",
        "Message copied. Opening WhatsApp...": "Mensaje copiado. Abriendo WhatsApp...",
        "Restore the default texts for the selected language?": "¿Restaurar los textos predeterminados para el idioma seleccionado?",
        "%d ms": "%d ms",
        "principal": "principal",
    },
    "ja": {
        "Automatic — current site language": "自動 — 現在のサイト言語",
        "Portuguese": "ポルトガル語",
        "Spanish": "スペイン語",
        "Japanese": "日本語",
        "Button appearance": "ボタンの外観",
        "Statistics": "統計",
        "Restore current button": "現在のボタンを復元",
        "Default target": "既定の表示先",
        "Template language": "テンプレート言語",
        "Tourism": "観光",
        "Restaurant": "レストラン",
        "Lawyer": "弁護士",
        "Doctor": "医師",
        "Hotel": "ホテル",
        "Barbershop": "理容室",
        "Real estate": "不動産",
        "Support": "サポート",
        "Store": "店舗",
        "Freelancer": "フリーランサー",
        "Consulting": "コンサルティング",
        "Plan my trip on WhatsApp": "WhatsAppで旅行を相談",
        "Get support on WhatsApp": "WhatsAppでサポートを受ける",
        "Botões reutilizáveis com modo tradicional e Smart Copy para preservar mensagens formatadas.": "書式付きメッセージを保持するための従来モードとSmart Copyモードを備えた再利用可能なボタン。",
        "Cada botão pode usar o modo tradicional ou Smart Copy sem depender de Elementor.": "各ボタンはElementorに依存せず、従来モードまたはSmart Copyを使用できます。",
        "Idioma do modelo": "テンプレート言語",
        "Modo de envio": "送信モード",
        "Feedback do modo tradicional": "従来モードのフィードバック",
        "Destino padrão": "既定の表示先",
        "Nova aba": "新しいタブ",
        "Hoje": "今日",
        "Botão": "ボタン",
        "Eventos locais por botão, sem armazenar IP bruto.": "生のIPを保存しない、ボタン別のローカルイベント。",
        "Variáveis para mensagens dinâmicas": "動的メッセージ用の変数",
        "Limpar estatísticas": "統計をクリア",
        "Salvar configurações": "設定を保存",
        "Remover configurações e estatísticas ao desinstalar": "アンインストール時に設定と統計を削除",
        "Enviar eventos para GA4 quando gtag estiver disponível": "gtagが利用可能な場合にGA4へイベントを送信",
        "Gerar hash de IP para deduplicação estatística": "統計の重複排除用にIPハッシュを生成",
        "Aparência do botão": "ボタンの外観",
        "Estatísticas": "統計",
        "Restaurar botão atual": "現在のボタンを復元",
        "Chat on WhatsApp": "WhatsAppで相談する",
        "Copied successfully": "メッセージをコピーしました",
        "Do not show again on this browser": "このブラウザーでは再表示しない",
        "Message copied": "メッセージをコピーしました",
        "Your message has been copied to the clipboard.": "メッセージをクリップボードにコピーしました。",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "WhatsAppを開いて Ctrl + V を押してください。",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "WhatsAppを開き、メッセージ欄をタップして「ペースト」を選択してください。",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "WhatsAppを開き、メッセージ欄を長押しして「貼り付け」を選択してください。",
        "Open WhatsApp": "WhatsAppを開く",
        "Open WhatsApp →": "WhatsAppを開く →",
        "Close": "閉じる",
        "Copy again": "もう一度コピー",
        "Automatic copy failed. Select and copy the message below.": "自動コピーできませんでした。下のメッセージを選択してコピーしてください。",
        "Message copied. Opening WhatsApp...": "メッセージをコピーしました。WhatsAppを開いています...",
        "Restore the default texts for the selected language?": "選択した言語の既定テキストに戻しますか？",
        "%d ms": "%dミリ秒",
        "principal": "メイン",
    },
    "fr_FR": {
        "Chat on WhatsApp": "Discuter sur WhatsApp",
        "Copied successfully": "Message copié",
        "Do not show again on this browser": "Ne plus afficher sur ce navigateur",
        "Message copied": "Message copié",
        "Your message has been copied to the clipboard.": "Votre message a été copié dans le presse-papiers.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Cliquez sur Ouvrir WhatsApp puis appuyez sur Ctrl + V dans le champ du message.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Touchez Ouvrir WhatsApp, touchez le champ du message puis choisissez Coller.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Touchez Ouvrir WhatsApp, maintenez le champ du message puis choisissez Coller.",
        "Open WhatsApp": "Ouvrir WhatsApp",
        "Open WhatsApp →": "Ouvrir WhatsApp →",
        "Close": "Fermer",
        "Copy again": "Copier à nouveau",
        "Automatic copy failed. Select and copy the message below.": "La copie automatique a échoué. Sélectionnez et copiez le message ci-dessous.",
        "Message copied. Opening WhatsApp...": "Message copié. Ouverture de WhatsApp...",
        "Restore the default texts for the selected language?": "Restaurer les textes par défaut pour la langue sélectionnée ?",
        "%d ms": "%d ms",
        "principal": "principal",
    },
    "de_DE": {
        "Chat on WhatsApp": "Auf WhatsApp schreiben",
        "Copied successfully": "Nachricht kopiert",
        "Do not show again on this browser": "In diesem Browser nicht erneut anzeigen",
        "Message copied": "Nachricht kopiert",
        "Your message has been copied to the clipboard.": "Ihre Nachricht wurde in die Zwischenablage kopiert.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Klicken Sie auf WhatsApp öffnen und drücken Sie Strg + V im Nachrichtenfeld.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Tippen Sie auf WhatsApp öffnen, tippen Sie auf das Nachrichtenfeld und wählen Sie Einfügen.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Tippen Sie auf WhatsApp öffnen, halten Sie das Nachrichtenfeld gedrückt und wählen Sie Einfügen.",
        "Open WhatsApp": "WhatsApp öffnen",
        "Open WhatsApp →": "WhatsApp öffnen →",
        "Close": "Schließen",
        "Copy again": "Erneut kopieren",
        "Automatic copy failed. Select and copy the message below.": "Automatisches Kopieren fehlgeschlagen. Markieren und kopieren Sie die Nachricht unten.",
        "Message copied. Opening WhatsApp...": "Nachricht kopiert. WhatsApp wird geöffnet...",
        "Restore the default texts for the selected language?": "Die Standardtexte für die ausgewählte Sprache wiederherstellen?",
        "%d ms": "%d ms",
        "principal": "Haupt",
    },
    "it_IT": {
        "Chat on WhatsApp": "Scrivi su WhatsApp",
        "Copied successfully": "Messaggio copiato",
        "Do not show again on this browser": "Non mostrare di nuovo in questo browser",
        "Message copied": "Messaggio copiato",
        "Your message has been copied to the clipboard.": "Il messaggio è stato copiato negli appunti.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Fai clic su Apri WhatsApp e premi Ctrl + V nel campo del messaggio.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Tocca Apri WhatsApp, tocca il campo del messaggio e scegli Incolla.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Tocca Apri WhatsApp, tieni premuto il campo del messaggio e scegli Incolla.",
        "Open WhatsApp": "Apri WhatsApp",
        "Open WhatsApp →": "Apri WhatsApp →",
        "Close": "Chiudi",
        "Copy again": "Copia di nuovo",
        "Automatic copy failed. Select and copy the message below.": "Copia automatica non riuscita. Seleziona e copia il messaggio qui sotto.",
        "Message copied. Opening WhatsApp...": "Messaggio copiato. Apertura di WhatsApp...",
        "Restore the default texts for the selected language?": "Ripristinare i testi predefiniti per la lingua selezionata?",
        "%d ms": "%d ms",
        "principal": "principale",
    },
    "nl_NL": {
        "Chat on WhatsApp": "Chat via WhatsApp",
        "Copied successfully": "Bericht gekopieerd",
        "Do not show again on this browser": "Niet opnieuw tonen in deze browser",
        "Message copied": "Bericht gekopieerd",
        "Your message has been copied to the clipboard.": "Uw bericht is naar het klembord gekopieerd.",
        "Click Open WhatsApp and press Ctrl + V in the message field.": "Klik op WhatsApp openen en druk op Ctrl + V in het berichtveld.",
        "Tap Open WhatsApp, tap the message field and choose Paste.": "Tik op WhatsApp openen, tik op het berichtveld en kies Plakken.",
        "Tap Open WhatsApp, tap and hold the message field and choose Paste.": "Tik op WhatsApp openen, houd het berichtveld ingedrukt en kies Plakken.",
        "Open WhatsApp": "WhatsApp openen",
        "Open WhatsApp →": "WhatsApp openen →",
        "Close": "Sluiten",
        "Copy again": "Opnieuw kopiëren",
        "Automatic copy failed. Select and copy the message below.": "Automatisch kopiëren is mislukt. Selecteer en kopieer het onderstaande bericht.",
        "Message copied. Opening WhatsApp...": "Bericht gekopieerd. WhatsApp wordt geopend...",
        "Restore the default texts for the selected language?": "De standaardteksten voor de geselecteerde taal herstellen?",
        "%d ms": "%d ms",
        "principal": "primair",
    },
}


def source_files() -> list[Path]:
    files = []
    for path in ROOT.rglob("*"):
        if path.suffix not in SOURCE_SUFFIXES:
            continue
        if SKIP_PARTS & set(path.relative_to(ROOT).parts):
            continue
        files.append(path)
    return sorted(files)


def read_string(text: str, offset: int) -> tuple[str, int]:
    quote = text[offset]
    i = offset + 1
    escaped = False
    raw = quote
    while i < len(text):
        char = text[i]
        raw += char
        if escaped:
            escaped = False
        elif char == "\\":
            escaped = True
        elif char == quote:
            return ast.literal_eval(raw), i + 1
        i += 1
    raise ValueError("Unterminated string")


def extract_call_arguments(text: str, offset: int) -> list[str]:
    args = []
    i = offset
    depth = 1
    while i < len(text) and depth > 0:
        char = text[i]
        if char in ("'", '"'):
            value, i = read_string(text, i)
            args.append(value)
            continue
        if char == "(":
            depth += 1
        elif char == ")":
            depth -= 1
        i += 1
    return args


def extract_messages() -> dict[tuple[str, str], dict[str, object]]:
    messages: dict[tuple[str, str], dict[str, object]] = {}
    for path in source_files():
        text = path.read_text(encoding="utf-8")
        rel = path.relative_to(ROOT).as_posix()
        for match in I18N_RE.finditer(text):
            fn = match.group("fn")
            args = extract_call_arguments(text, match.end())
            if not args:
                continue
            context = ""
            msgid = args[0]
            if fn in {"_x", "_nx", "esc_html_x", "esc_attr_x"} and len(args) > 1:
                context = args[1]
            elif fn in {"_n", "_nx"} and len(args) > 1:
                msgid = args[0]
            key = (context, msgid)
            messages.setdefault(key, {"references": set()})
            messages[key]["references"].add(f"{rel}:{text.count(chr(10), 0, match.start()) + 1}")
    return dict(sorted(messages.items(), key=lambda item: (item[0][0], item[0][1])))


def parse_po(path: Path) -> dict[tuple[str, str], str]:
    if not path.exists():
        return {}
    lines = path.read_text(encoding="utf-8").splitlines()
    entries: dict[tuple[str, str], str] = {}
    context = ""
    msgid = None
    msgstr = None
    active = None

    def commit() -> None:
        nonlocal context, msgid, msgstr
        if msgid:
            entries[(context, msgid)] = msgstr or ""
            if context:
                entries[("", msgid)] = msgstr or ""
        context = ""
        msgid = None
        msgstr = None

    for line in lines + [""]:
        if not line:
            commit()
            active = None
            continue
        if line.startswith("#"):
            continue
        if line.startswith("msgctxt "):
            context = ast.literal_eval(line[8:])
            active = "ctx"
        elif line.startswith("msgid "):
            msgid = ast.literal_eval(line[6:])
            active = "id"
        elif line.startswith("msgstr "):
            msgstr = ast.literal_eval(line[7:])
            active = "str"
        elif line.startswith('"') and active:
            value = ast.literal_eval(line)
            if active == "ctx":
                context += value
            elif active == "id" and msgid is not None:
                msgid += value
            elif active == "str" and msgstr is not None:
                msgstr += value
    return entries


def po_escape(value: str) -> str:
    return value.replace("\\", "\\\\").replace('"', '\\"').replace("\n", "\\n")


def po_string(label: str, value: str) -> str:
    if "\n" not in value:
        return f'{label} "{po_escape(value)}"'
    parts = value.split("\n")
    lines = [f'{label} ""']
    for index, part in enumerate(parts):
        suffix = "\\n" if index < len(parts) - 1 else ""
        lines.append(f'"{po_escape(part)}{suffix}"')
    return "\n".join(lines)


def header(locale: str | None = None) -> str:
    now = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M+0000")
    language = locale or ""
    return "\n".join(
        [
            'msgid ""',
            'msgstr ""',
            f'"Project-Id-Version: DD Smart WhatsApp {VERSION}\\n"',
            f'"POT-Creation-Date: {now}\\n"',
            '"MIME-Version: 1.0\\n"',
            '"Content-Type: text/plain; charset=UTF-8\\n"',
            '"Content-Transfer-Encoding: 8bit\\n"',
            f'"X-Domain: {DOMAIN}\\n"',
            f'"Language: {language}\\n"',
            "",
        ]
    )


def write_po(path: Path, messages: dict[tuple[str, str], dict[str, object]], locale: str | None = None, translations: dict[tuple[str, str], str] | None = None) -> None:
    lines = [header(locale)]
    translations = translations or {}
    if locale in (None, "pt_BR"):
        manual = FALLBACK_TRANSLATIONS.get(locale or "", {})
    else:
        manual = {**FALLBACK_TRANSLATIONS.get("en_US", {}), **FALLBACK_TRANSLATIONS.get(locale or "", {})}
    for (context, msgid), meta in messages.items():
        refs = sorted(meta["references"])
        for ref in refs:
            lines.append(f"#: {ref}")
        if context:
            lines.append(po_string("msgctxt", context))
        lines.append(po_string("msgid", msgid))
        if locale is None:
            msgstr = ""
        else:
            existing = translations.get((context, msgid)) or translations.get(("", msgid)) or ""
            manual_value = manual.get(msgid)
            if manual_value and (msgid in FORCE_TRANSLATIONS or not existing or existing == msgid or set(existing) == {"?"}):
                msgstr = manual_value
            else:
                msgstr = existing or manual_value or msgid
        lines.append(po_string("msgstr", msgstr))
        lines.append("")
    path.write_text("\n".join(lines), encoding="utf-8", newline="\n")


def write_mo(path: Path, po_entries: dict[tuple[str, str], str]) -> None:
    catalog = {}
    for (context, msgid), msgstr in po_entries.items():
        key = f"{context}\x04{msgid}" if context else msgid
        catalog[key] = msgstr
    catalog[""] = (
        f"Project-Id-Version: DD Smart WhatsApp {VERSION}\n"
        "MIME-Version: 1.0\n"
        "Content-Type: text/plain; charset=UTF-8\n"
        "Content-Transfer-Encoding: 8bit\n"
    )
    keys = sorted(catalog)
    ids = b""
    strs = b""
    offsets = []
    for key in keys:
        msgid = key.encode("utf-8")
        msgstr = catalog[key].encode("utf-8")
        offsets.append((len(msgid), len(ids), len(msgstr), len(strs)))
        ids += msgid + b"\0"
        strs += msgstr + b"\0"
    keystart = 7 * 4
    valuestart = keystart + len(keys) * 8
    idstart = valuestart + len(keys) * 8
    strstart = idstart + len(ids)
    output = [
        struct.pack("Iiiiiii", 0x950412DE, 0, len(keys), keystart, valuestart, 0, 0)
    ]
    output.extend(struct.pack("ii", length, idstart + offset) for length, offset, _, _ in offsets)
    output.extend(struct.pack("ii", length, strstart + offset) for _, _, length, offset in offsets)
    output.append(ids)
    output.append(strs)
    path.write_bytes(b"".join(output))


def main() -> int:
    LANG_DIR.mkdir(exist_ok=True)
    messages = extract_messages()
    write_po(LANG_DIR / f"{DOMAIN}.pot", messages)
    for locale in LOCALES:
        existing = parse_po(LANG_DIR / f"{DOMAIN}-{locale}.po")
        prefixed_po = LANG_DIR / f"{DOMAIN}-{locale}.po"
        unprefixed_po = LANG_DIR / f"{locale}.po"
        write_po(prefixed_po, messages, locale, existing)
        content = prefixed_po.read_text(encoding="utf-8")
        unprefixed_po.write_text(content, encoding="utf-8", newline="\n")
        entries = parse_po(prefixed_po)
        write_mo(LANG_DIR / f"{DOMAIN}-{locale}.mo", entries)
        write_mo(LANG_DIR / f"{locale}.mo", entries)
    print(f"messages={len(messages)}")
    print("locales=" + ",".join(LOCALES))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
