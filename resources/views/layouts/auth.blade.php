<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Escolar')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='20' width='100' height='100' fill='%23c41230'/><text x='50' y='70' font-size='50' text-anchor='middle' fill='white'>🎓</text></svg>">
    <meta name="theme-color" content="#4c0715">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-body">
    <div class="auth-shell">
        <aside class="auth-showcase">
            <div class="auth-showcase-glow" aria-hidden="true"></div>
            <div class="auth-showcase-grid" aria-hidden="true"></div>

            <div class="auth-brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48">
                        <circle cx="24" cy="24" r="21.5" fill="none" stroke="currentColor" stroke-width="1.4" />
                        <circle cx="24" cy="24" r="17.5" fill="none" stroke="currentColor" stroke-width="1" opacity=".55" />
                        <path d="M24 12 39 18.5 24 25 9 18.5 24 12Z" fill="currentColor" />
                        <path d="M14.5 20.6V27c0 3 4.2 6.2 9.5 6.2s9.5-3.2 9.5-6.2v-6.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M35.5 19v9.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                </span>
                <span class="brand-text">
                    <strong>Sistema Escolar</strong>
                    <small>Portal Acadêmico · Rede Federal de Ensino</small>
                </span>
            </div>

            <div class="auth-showcase-copy">
                <p class="eyebrow" style="color:var(--accent-soft)">Gestão Acadêmica Integrada</p>
                <h1>Um só lugar para conduzir toda a vida acadêmica da instituição.</h1>
                <p>Matrículas, notas, frequência e corpo docente — reunidos em um portal único, seguro e sempre disponível.</p>
            </div>

            <ul class="auth-showcase-points">
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                    <span>Cadastro centralizado de alunos e professores</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                    <span>Lançamento de notas e controle de frequência</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                    <span>Painel institucional com indicadores em tempo real</span>
                </li>
            </ul>

            <p class="auth-showcase-footer">© {{ now()->year }} Sistema Escolar · Todos os direitos reservados</p>
        </aside>

        <main class="auth-panel">
            <div class="auth-panel-inner">
                @include('components.flash')
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
