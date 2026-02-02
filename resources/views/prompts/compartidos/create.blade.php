@extends('layouts.main')

@section('content')

    {{-- ✅ OCULTAR NAVBAR / SIDEBAR SOLO EN ESTA VISTA --}}
    <style>
        /* Intenta cubrir varios layouts típicos (Bootstrap/AdminLTE/Plantillas) */
        nav,
        header,
        aside,
        .navbar,
        .topbar,
        .sidebar,
        .app-header,
        .app-sidebar,
        .main-header,
        .main-sidebar {
            display: none !important;
        }

        /* Si tu layout deja márgenes/paddings por el sidebar */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .container,
        .container-fluid {
            max-width: 100% !important;
        }

        /* Si tu layout tiene wrappers */
        .content-wrapper,
        .main-content,
        .page-content,
        main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
    </style>

    @php
        $promptUrl = route('prompts.show', $prompt);

        $contenido = strip_tags($prompt->contenido ?? '');
        $resumen = \Illuminate\Support\Str::limit($contenido, 140);

        $waText = "✨ PromptVault | Compartido\n"
            . "📌 Título: {$prompt->titulo}\n"
            . "📝 Resumen: {$resumen}\n"
            . "🔗 Link: {$promptUrl}\n";

        $gmailSubject = "PromptVault: {$prompt->titulo}";
        $gmailBody = "Hola,\n\nTe comparto este prompt desde PromptVault:\n\n"
            . "Título: {$prompt->titulo}\n"
            . "Resumen: {$resumen}\n"
            . "Link: {$promptUrl}\n\n"
            . "Notas:\n" . (old('notas') ?? '') . "\n\n"
            . "Saludos.";

        $mailto = "mailto:"
            . rawurlencode(old('email_destinatario') ?? '')
            . "?subject=" . rawurlencode($gmailSubject)
            . "&body=" . rawurlencode($gmailBody);

        $waLink = "https://wa.me/?text=" . rawurlencode($waText);
    @endphp

    <div class="pv-page">
        <div class="pv-wrap">

            <!-- Header -->
            <div class="pv-head">
                <div>
                    <h1>Compartir Prompt</h1>
                    <p>
                        Prompt:
                        <a href="{{ route('prompts.show', $prompt) }}">{{ $prompt->titulo }}</a>
                    </p>
                </div>

                <div class="pv-head-actions">
                    <a class="pv-btn pv-btn-ghost" href="{{ route('prompts.show', $prompt) }}">← Volver</a>
                </div>
            </div>

            <!-- Layout principal -->
            <div class="pv-grid">
                <!-- Preview -->
                <div class="pv-card">
                    <div class="pv-card-title">PREVIEW</div>
                    <div class="pv-prompt-title">{{ $prompt->titulo }}</div>
                    <div class="pv-prompt-desc">{{ $resumen }}</div>

                    <div class="pv-pills">
                        <span class="pv-pill">🔗 Link listo</span>
                        <span class="pv-pill pv-pill-soft">⚡ Compartir rápido</span>
                    </div>

                    <div class="pv-divider"></div>

                    <div class="pv-row">
                        <button type="button" class="pv-btn pv-btn-ghost" id="btnCopyLink">🔗 Copiar link</button>
                        <button type="button" class="pv-btn pv-btn-ghost" id="btnCopyPrompt">📋 Copiar prompt</button>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="pv-card">
                    <div class="pv-card-title">ACCIONES RÁPIDAS</div>

                    <a class="pv-btn pv-btn-wa" href="{{ $waLink }}" target="_blank" rel="noopener">
                        💬 Compartir por WhatsApp
                    </a>

                    <a class="pv-btn pv-btn-mail" href="{{ $mailto }}">
                        ✉️ Compartir por Gmail / Email
                    </a>

                    <div class="pv-small">
                        WhatsApp enviará <b>título + resumen + link</b>.
                        Email abrirá tu cliente con asunto/cuerpo listos.
                    </div>
                </div>
            </div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="pv-alert">
                    <div class="pv-alert-title">Revisa los errores:</div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form registrar compartido -->
            <div class="pv-card pv-form">
                <div class="pv-card-title">REGISTRAR COMPARTIDO</div>

                <form method="POST" action="{{ route('prompts.compartidos.store', $prompt) }}" class="pv-form-grid">
                    @csrf

                    <div class="pv-field">
                        <label for="nombre_destinatario">Nombre del destinatario *</label>
                        <input id="nombre_destinatario" name="nombre_destinatario" type="text"
                            value="{{ old('nombre_destinatario') }}" maxlength="140" required>
                        @error('nombre_destinatario') <div class="pv-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pv-field">
                        <label for="email_destinatario">Email del destinatario *</label>
                        <input id="email_destinatario" name="email_destinatario" type="email"
                            value="{{ old('email_destinatario') }}" maxlength="160" required>
                        @error('email_destinatario') <div class="pv-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pv-field pv-field-full">
                        <label for="notas">Notas (opcional)</label>
                        <textarea id="notas" name="notas" rows="4">{{ old('notas') }}</textarea>
                        @error('notas') <div class="pv-err">{{ $message }}</div> @enderror
                        <div class="pv-small">Estas notas se incluyen en el email y también quedan registradas.</div>
                    </div>

                    <div class="pv-actions pv-field-full">
                        <button type="submit" class="pv-btn pv-btn-primary">✅ Registrar compartido</button>

                        <a href="{{ route('prompts.compartidos.index', $prompt) }}" class="pv-btn pv-btn-ghost">
                            📜 Ver compartidos
                        </a>
                    </div>

                    <div class="pv-small pv-field-full">
                        Se registrará <b>fecha_compartido</b> automáticamente con la fecha/hora actual.
                    </div>
                </form>
            </div>

            <!-- Toast -->
            <div id="pvToast" class="pv-toast">Copiado ✅</div>

            <!-- Datos para JS -->
            <textarea id="promptFull" style="display:none;">{{ $prompt->contenido }}</textarea>
            <input type="hidden" id="promptLink" value="{{ $promptUrl }}">
        </div>
    </div>

    {{-- ✅ CSS PROPIO (no depende de Tailwind) --}}
    <style>
        .pv-page {
            min-height: 100vh;
            background: radial-gradient(1200px 600px at 20% 10%, rgba(99, 102, 241, .20), transparent 60%),
                radial-gradient(900px 600px at 80% 20%, rgba(34, 197, 94, .12), transparent 55%),
                linear-gradient(135deg, #0b1220, #0f172a 55%, #0b1220);
            color: #e5e7eb;
            padding: 28px 14px 60px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans";
        }

        .pv-wrap {
            max-width: 1100px;
            margin: 0 auto;
        }

        .pv-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 18px 0;
            margin-bottom: 14px;
        }

        .pv-head h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: .2px;
        }

        .pv-head p {
            margin: 6px 0 0;
            color: #aab3c3;
        }

        .pv-head a {
            color: #c7d2fe;
            text-decoration: none;
        }

        .pv-head a:hover {
            text-decoration: underline;
        }

        .pv-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 14px;
            margin-top: 10px;
        }

        .pv-card {
            background: rgba(15, 23, 42, 0.60);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .35);
            backdrop-filter: blur(10px);
        }

        .pv-card-title {
            font-size: 12px;
            letter-spacing: .18em;
            color: #9aa4b2;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .pv-prompt-title {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 8px;
            color: #f1f5f9;
        }

        .pv-prompt-desc {
            color: #cbd5e1;
            line-height: 1.55;
            font-size: 14px;
        }

        .pv-pills {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pv-pill {
            font-size: 12px;
            font-weight: 800;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(99, 102, 241, .14);
            border: 1px solid rgba(99, 102, 241, .22);
            color: #c7d2fe;
        }

        .pv-pill-soft {
            background: rgba(148, 163, 184, .10);
            border-color: rgba(148, 163, 184, .18);
            color: #e5e7eb;
        }

        .pv-divider {
            height: 1px;
            background: rgba(148, 163, 184, .14);
            margin: 14px 0;
        }

        .pv-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pv-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 14px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease, border-color .16s ease;
            user-select: none;
        }

        .pv-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, .25);
        }

        .pv-btn:active {
            transform: translateY(0) scale(.99);
        }

        .pv-btn-primary {
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            color: white;
            border-color: rgba(99, 102, 241, .25);
        }

        .pv-btn-ghost {
            background: rgba(255, 255, 255, .04);
            color: #e5e7eb;
            border-color: rgba(148, 163, 184, .18);
        }

        .pv-btn-ghost:hover {
            border-color: rgba(99, 102, 241, .40);
        }

        .pv-btn-wa {
            background: rgba(34, 197, 94, .14);
            border-color: rgba(34, 197, 94, .22);
            color: #bbf7d0;
        }

        .pv-btn-mail {
            background: rgba(99, 102, 241, .14);
            border-color: rgba(99, 102, 241, .22);
            color: #c7d2fe;
        }

        .pv-form {
            margin-top: 14px;
        }

        .pv-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .pv-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pv-field-full {
            grid-column: 1 / -1;
        }

        .pv-field label {
            font-weight: 900;
            color: #e5e7eb;
            font-size: 13px;
        }

        .pv-field input,
        .pv-field textarea {
            background: rgba(2, 6, 23, .35);
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 14px;
            padding: 12px 12px;
            color: #e5e7eb;
            outline: none;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        .pv-field input:focus,
        .pv-field textarea:focus {
            border-color: rgba(99, 102, 241, .55);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .18);
        }

        .pv-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pv-actions .pv-btn {
            width: auto;
            flex: 1;
            min-width: 220px;
        }

        .pv-small {
            margin-top: 6px;
            color: #aab3c3;
            font-size: 12px;
            line-height: 1.4;
        }

        .pv-err {
            color: #fca5a5;
            font-size: 12px;
            font-weight: 800;
        }

        .pv-alert {
            margin-top: 14px;
            border-radius: 18px;
            padding: 14px 16px;
            border: 1px solid rgba(248, 113, 113, .25);
            background: rgba(248, 113, 113, .08);
            color: #fecaca;
        }

        .pv-alert-title {
            font-weight: 900;
            margin-bottom: 6px;
        }

        .pv-alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .pv-toast {
            position: fixed;
            left: 50%;
            bottom: 26px;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, .92);
            border: 1px solid rgba(148, 163, 184, .18);
            padding: 10px 14px;
            border-radius: 14px;
            color: #e5e7eb;
            font-weight: 900;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .35);
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease;
        }

        .pv-toast.show {
            opacity: 1;
        }

        @media (max-width: 900px) {
            .pv-grid {
                grid-template-columns: 1fr;
            }

            .pv-head {
                align-items: flex-start;
            }

            .pv-actions .pv-btn {
                min-width: 180px;
            }

            .pv-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        const toast = document.getElementById('pvToast');

        function showToast(msg) {
            toast.textContent = msg || 'Listo ✅';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 1600);
        }

        async function copyText(text, msg) {
            try {
                await navigator.clipboard.writeText(text);
                showToast(msg);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast(msg);
            }
        }

        document.getElementById('btnCopyLink').addEventListener('click', () => {
            copyText(document.getElementById('promptLink').value, 'Link copiado 🔗');
        });

        document.getElementById('btnCopyPrompt').addEventListener('click', () => {
            copyText((document.getElementById('promptFull').value || '').trim(), 'Prompt copiado 📋');
        });
    </script>

@endsection