<div id="ai-chat-widget-container"
    style="display: block; position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: 'Segoe UI', sans-serif;">

    <!-- Ventana del Chat -->
    <div id="ai-chat-window"
        style="display: none; width: 380px; height: 550px; background: #1e293b; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); flex-direction: column; overflow: hidden; border: 1px solid #334155; transition: all 0.3s ease;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 16px; color: white; font-weight: 600; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🤖</span>
                <span>Asistente IA</span>
            </div>
            <button onclick="toggleAiChat()"
                style="background: rgba(255,255,255,0.2); border: none; color: white; cursor: pointer; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: background 0.2s;">&times;</button>
        </div>

        <!-- Área de Mensajes -->
        <div id="ai-chat-messages"
            style="flex: 1; padding: 20px; overflow-y: auto; background: #0f172a; color: #e2e8f0; font-size: 14px; scroll-behavior: smooth;">
            <div style="margin-bottom: 20px; opacity: 0.7; font-size: 13px; text-align: center; color: #94a3b8;">
                <p>Hola, soy tu asistente virtual.</p>
                <p>¿En qué te puedo ayudar con tus prompts hoy?</p>
            </div>
        </div>

        <!-- Área de Input -->
        <div style="padding: 16px; background: #1e293b; border-top: 1px solid #334155;">
            <form id="ai-chat-form" onsubmit="sendAiMessage(event)"
                style="display: flex; gap: 10px; position: relative;">
                <input type="text" id="ai-chat-input" placeholder="Escribe tu mensaje..." autocomplete="off"
                    style="flex: 1; padding: 12px 16px; border-radius: 24px; border: 1px solid #475569; background: #334155; color: white; outline: none; font-size: 14px; transition: border-color 0.2s;">
                <button type="submit"
                    style="background: #6366f1; color: white; border: none; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s, background 0.2s;">
                    ➤
                </button>
            </form>
        </div>
    </div>

    <!-- Botón Flotante (Toggle) -->
    <button id="ai-chat-toggle" onclick="toggleAiChat()"
        style="position: absolute; bottom: 0; right: 0; width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border: none; box-shadow: 0 4px 20px rgba(79, 70, 229, 0.5); cursor: pointer; font-size: 32px; display: flex; align-items: center; justify-content: center; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        🤖
    </button>
</div>

<style>
    /* Estilos para el contenido Markdown de la IA */
    .ai-message-content {
        white-space: pre-wrap;
        /* Esto respeta los saltos de línea */
        line-height: 1.6;
    }

    .ai-message-content strong {
        color: #fff;
        font-weight: 700;
    }

    .ai-message-content em {
        color: #cbd5e1;
        font-style: italic;
    }

    .ai-message-content ul,
    .ai-message-content ol {
        margin: 8px 0;
        padding-left: 20px;
    }

    .ai-message-content li {
        margin-bottom: 4px;
    }

    .ai-message-content code {
        background: #334155;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Consolas', monospace;
        font-size: 0.9em;
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .ai-message-content pre {
        background: #020617;
        padding: 12px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 10px 0;
        border: 1px solid #334155;
    }

    .ai-message-content pre code {
        background: transparent;
        padding: 0;
        border: none;
        color: #a5b4fc;
    }

    /* Burbujas de mensaje */
    .msg-ai {
        background: #334155;
        color: #e2e8f0;
        padding: 12px 16px;
        border-radius: 18px 18px 18px 4px;
        margin-right: auto;
        max-width: 90%;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        word-wrap: break-word;
        position: relative;
    }

    .msg-ai:hover .ai-message-actions {
        opacity: 0.7;
        height: auto;
    }

    .msg-user {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: white;
        padding: 12px 16px;
        border-radius: 18px 18px 4px 18px;
        margin-left: auto;
        max-width: 85%;
        margin-bottom: 12px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        word-wrap: break-word;
    }

    /* Scrollbar personalizado */
    #ai-chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #ai-chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    #ai-chat-messages::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 3px;
    }

    #ai-chat-messages::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* Animación de carga */
    @keyframes pulse {
        0% {
            opacity: 0.4;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 0.4;
        }
    }

    .typing-indicator span {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: pulse 1.4s infinite both;
        margin: 0 2px;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* Copy Button and Actions */
    .ai-message-actions {
        text-align: right;
        margin-top: 10px;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        height: 0;
        overflow: hidden;
    }

    .ai-message-actions:hover {
        opacity: 1 !important;
    }

    .copy-btn {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 12px;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.2s;
    }

    .copy-btn:hover {
        color: #e2e8f0;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
        #ai-chat-window {
            width: 100%;
            height: 100%;
            border-radius: 0;
            border: none;
        }

        #ai-chat-widget-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
    }
</style>

<script>
    let isChatOpen = false;

    function toggleAiChat() {
        const windowEl = document.getElementById('ai-chat-window');
        const toggleBtn = document.getElementById('ai-chat-toggle');
        const inputEl = document.getElementById('ai-chat-input');

        isChatOpen = !isChatOpen;

        if (isChatOpen) {
            windowEl.style.display = 'flex';
            toggleBtn.style.display = 'none';
            // Pequeño delay para asegurar que el display flex se aplicó antes del focus
            setTimeout(() => inputEl.focus(), 50);
        } else {
            windowEl.style.display = 'none';
            toggleBtn.style.display = 'flex';
        }
    }

    async function sendAiMessage(e) {
        e.preventDefault();
        const input = document.getElementById('ai-chat-input');
        const message = input.value.trim();
        if (!message) return;

        // 1. Mostrar mensaje del usuario INMEDIATAMENTE
        addMessageToUi(message, 'user');
        input.value = '';

        // 2. Mostrar indicador de carga
        const loadingId = addLoadingToUi();

        try {
            // 3. Enviar al backend
            const response = await fetch("{{ route('ai.chat.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();

            // Quitar loading
            document.getElementById(loadingId).remove();

            if (response.ok) {
                addMessageToUi(data.reply, 'ai');
            } else {
                addMessageToUi('⚠️ Error: ' + (data.error || 'No se pudo conectar.'), 'ai');
            }

        } catch (error) {
            if (document.getElementById(loadingId)) document.getElementById(loadingId).remove();
            addMessageToUi('⚠️ Error de conexión. Verifica tu internet.', 'ai');
            console.error(error);
        }
    }

    function addMessageToUi(text, sender) {
        const container = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = sender === 'user' ? 'msg-user' : 'msg-ai';

        if (sender === 'user') {
            div.textContent = text;
        } else { // Mensaje de la IA con botón de copiar
            const rawText = text || ''; // Asegura que no sea nulo
            const contentHTML = `<div class="ai-message-content">${parseMarkdown(rawText)}</div>`;
            const actionsHTML = `
                <div class="ai-message-actions">
                    <button class="copy-btn" title="Copiar al portapapeles">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V2Zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H6ZM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1H2Z"/></svg>
                        <span class="copy-text">Copiar</span>
                    </button>
                </div>
            `;
            div.innerHTML = contentHTML + actionsHTML;

            // Lógica para el botón de copiar
            const copyBtn = div.querySelector('.copy-btn');
            const copyTextSpan = div.querySelector('.copy-text');
            copyBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                navigator.clipboard.writeText(rawText).then(() => {
                    copyTextSpan.textContent = '¡Copiado!';
                    copyBtn.style.color = '#4ade80';
                    setTimeout(() => { copyTextSpan.textContent = 'Copiar'; copyBtn.style.color = '#94a3b8'; }, 2000);
                });
            });
        }

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function addLoadingToUi() {
        const container = document.getElementById('ai-chat-messages');
        const id = 'loading-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'msg-ai';
        div.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        return id;
    }

    function parseMarkdown(text) {
        if (!text) return '';

        // Escapar HTML básico por seguridad
        let safe = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        // Bloques de código
        safe = safe.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

        // Código en línea
        safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Negritas
        safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Listas (simple)
        safe = safe.replace(/^\s*-\s+(.*)/gm, '<li>$1</li>');

        return safe;
    }
</script>