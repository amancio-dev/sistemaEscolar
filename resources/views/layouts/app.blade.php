<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal Acadêmico do Sistema Escolar">
    <title>@yield('title', 'Sistema Escolar')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='20' width='100' height='100' fill='%23b3121c'/><text x='50' y='70' font-size='50' text-anchor='middle' fill='white'>🎓</text></svg>">
    <meta name="theme-color" content="#7f0e16">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="app-shell">

        {{-- ── Institutional utility bar ─────────────────────────── --}}
        <div class="util-bar">
            <div class="util-bar-inner">
                <span class="util-bar-item util-bar-brand">
                    {{ auth()->user()->isAluno() ? 'Área do Aluno · Rede Federal de Ensino' : 'Portal Acadêmico · Rede Federal de Ensino' }}
                </span>
                <div class="util-bar-right">
                    <span class="util-bar-item">Ano letivo {{ now()->year }}</span>
                    <span class="util-bar-sep" aria-hidden="true">|</span>
                    <span class="util-bar-item">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>
            </div>
        </div>

        {{-- ── Main institutional header / nav ───────────────────── --}}
        <header class="site-header">
            <div class="site-header-inner">
                <a class="brand" href="{{ route('inicio') }}">
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
                        <small>{{ auth()->user()->isAluno() ? 'Área de Consulta do Aluno' : 'Gestão Acadêmica Integrada' }}</small>
                    </span>
                </a>

                <button class="menu-button" id="menu-button" type="button" aria-label="Abrir menu"
                    aria-controls="primary-nav" aria-expanded="false">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <nav class="primary-nav" id="primary-nav" aria-label="Navegação principal">
                    <a class="nav-link {{ request()->routeIs('inicio') ? 'is-active' : '' }}" href="{{ route('inicio') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4v9Zm10 7h6V11h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z" /></svg>
                        <span>Painel</span>
                    </a>

                    @if (auth()->user()->isAluno())
                        <a class="nav-link {{ request()->routeIs('portal.notas') ? 'is-active' : '' }}"
                            href="{{ route('portal.notas') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V2" /></svg>
                            <span>Minhas notas</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('portal.frequencias') ? 'is-active' : '' }}"
                            href="{{ route('portal.frequencias') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg>
                            <span>Minha frequência</span>
                        </a>
                    @else
                    <div class="nav-item-group {{ request()->routeIs('alunos.*', 'professores.*') ? 'is-active' : '' }}">
                        <button class="nav-link nav-link--toggle" type="button" aria-haspopup="true" aria-expanded="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                            <span>Pessoas</span>
                            <svg class="nav-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div class="nav-dropdown">
                            <a class="nav-dropdown-item {{ request()->routeIs('alunos.*') ? 'is-active' : '' }}" href="{{ route('alunos.index') }}">
                                <strong>Alunos</strong>
                                <small>Cadastro e situação dos estudantes</small>
                            </a>
                            <a class="nav-dropdown-item {{ request()->routeIs('professores.*') ? 'is-active' : '' }}" href="{{ route('professores.index') }}">
                                <strong>Professores</strong>
                                <small>Corpo docente e especialidades</small>
                            </a>
                        </div>
                    </div>

                    <div class="nav-item-group {{ request()->routeIs('turmas.*', 'disciplinas.*') ? 'is-active' : '' }}">
                        <button class="nav-link nav-link--toggle" type="button" aria-haspopup="true" aria-expanded="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18v16H3V4Zm0 5h18M8 4v16" /></svg>
                            <span>Organização Acadêmica</span>
                            <svg class="nav-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div class="nav-dropdown">
                            <a class="nav-dropdown-item {{ request()->routeIs('turmas.*') ? 'is-active' : '' }}" href="{{ route('turmas.index') }}">
                                <strong>Turmas</strong>
                                <small>Composição e período letivo</small>
                            </a>
                            <a class="nav-dropdown-item {{ request()->routeIs('disciplinas.*') ? 'is-active' : '' }}" href="{{ route('disciplinas.index') }}">
                                <strong>Disciplinas</strong>
                                <small>Componentes curriculares</small>
                            </a>
                        </div>
                    </div>

                    <div class="nav-item-group {{ request()->routeIs('matriculas.*', 'notas.*', 'frequencias.*') ? 'is-active' : '' }}">
                        <button class="nav-link nav-link--toggle" type="button" aria-haspopup="true" aria-expanded="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11 12 14 22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" /></svg>
                            <span>Rotina Acadêmica</span>
                            <svg class="nav-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div class="nav-dropdown">
                            <a class="nav-dropdown-item {{ request()->routeIs('matriculas.*') ? 'is-active' : '' }}" href="{{ route('matriculas.index') }}">
                                <strong>Matrículas</strong>
                                <small>Vínculo de alunos às turmas</small>
                            </a>
                            <a class="nav-dropdown-item {{ request()->routeIs('notas.*') ? 'is-active' : '' }}" href="{{ route('notas.index') }}">
                                <strong>Notas</strong>
                                <small>Avaliações e desempenho</small>
                            </a>
                            <a class="nav-dropdown-item {{ request()->routeIs('frequencias.*') ? 'is-active' : '' }}" href="{{ route('frequencias.index') }}">
                                <strong>Frequências</strong>
                                <small>Controle de presença</small>
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="nav-profile-mobile">
                        <span class="profile-avatar">{{ Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
                        <span><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->tipo_usuario) }}</small></span>
                        <div class="nav-profile-mobile-actions">
                            <a href="{{ route('profile.edit') }}" class="nav-profile-mobile-link">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 1 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                                <span>Perfil</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="nav-logout-mobile">
                                @csrf
                                <button type="submit">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" /></svg>
                                    <span>Sair</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                <div class="topbar-profile" aria-label="Usuário atual">
                    <span class="profile-avatar">{{ Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
                    <span><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->tipo_usuario) }}</small></span>
                    <svg class="nav-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>

                    <div class="profile-dropdown">
                        <div class="profile-dropdown-header">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>{{ auth()->user()->email }}</small>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="profile-dropdown-link">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 1 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                            <span>Meu perfil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="profile-dropdown-logout">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" /></svg>
                                <span>Sair do sistema</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <button class="nav-backdrop" id="nav-backdrop" type="button" aria-label="Fechar menu"></button>

        {{-- ── Breadcrumb strip ───────────────────────────────────── --}}
        <div class="breadcrumb-bar">
            <div class="breadcrumb">
                <a href="{{ route('inicio') }}">{{ auth()->user()->isAluno() ? 'Área do Aluno' : 'Portal Acadêmico' }}</a>
                <span>/</span>
                <strong>@yield('breadcrumb', 'Painel')</strong>
            </div>
        </div>

        <main class="content">
            @include('components.flash')
            @yield('content')
        </main>

        <footer class="site-footer">
            <div class="site-footer-inner">
                <span>Sistema Escolar · {{ auth()->user()->isAluno() ? 'Área do Aluno' : 'Portal Acadêmico' }}</span>
                <span>© {{ now()->year }} · Todos os direitos reservados</span>
            </div>
        </footer>
    </div>
</body>

</html>
