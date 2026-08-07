@extends('layouts.app')

@section('title', 'Painel · Sistema Escolar')
@section('breadcrumb', 'Painel')

@section('content')
    {{-- ── Welcome Banner ──────────────────────────────────────── --}}
    <section class="welcome-banner animate-fade-in-up">
        <div class="welcome-content">
            <p class="welcome-greeting">
                @php
                    $hour = (int) now()->format('H');
                    $greeting = match(true) {
                        $hour < 12 => 'Bom dia',
                        $hour < 18 => 'Boa tarde',
                        default => 'Boa noite',
                    };
                @endphp
                {{ $greeting }}, <strong>{{ explode(' ', auth()->user()->name)[0] }}</strong> 👋
            </p>
            <h1 class="welcome-title">Painel Administrativo</h1>
            <p class="welcome-description">
                Acompanhe o desempenho da escola e gerencie todos os módulos em um só lugar.
            </p>
        </div>
        <div class="welcome-meta">
            <div class="welcome-date">
                <svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg>
                <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
            <div class="welcome-year-badge">
                <span>Ano Letivo</span>
                <strong>{{ now()->year }}</strong>
            </div>
        </div>
    </section>

    {{-- ── Primary Stats (4 main) ──────────────────────────────── --}}
    <section class="stats-row animate-fade-in-up" style="animation-delay:.05s" aria-label="Indicadores principais">
        <a class="stat-card stat-card--primary" href="{{ route('alunos.index') }}">
            <span class="stat-icon stat-icon--red">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['alunos'] }}">0</strong>
                <small>Alunos</small>
                <span class="stat-detail">{{ $activeStudents }} ativos</span>
            </div>
        </a>
        <a class="stat-card stat-card--primary" href="{{ route('professores.index') }}">
            <span class="stat-icon stat-icon--amber">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['professores'] }}">0</strong>
                <small>Professores</small>
                <span class="stat-detail">{{ $activeProfessors }} ativos</span>
            </div>
        </a>
        <a class="stat-card stat-card--primary" href="{{ route('turmas.index') }}">
            <span class="stat-icon stat-icon--emerald">
                <svg viewBox="0 0 24 24"><path d="M3 4h18v16H3V4Zm0 5h18M8 4v16" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['turmas'] }}">0</strong>
                <small>Turmas</small>
                <span class="stat-detail">{{ $activeTurmas }} ativas</span>
            </div>
        </a>
        <a class="stat-card stat-card--primary" href="{{ route('disciplinas.index') }}">
            <span class="stat-icon stat-icon--violet">
                <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['disciplinas'] }}">0</strong>
                <small>Disciplinas</small>
            </div>
        </a>
    </section>

    {{-- ── Secondary Stats (3 operational) ─────────────────────── --}}
    <section class="stats-row stats-row--secondary animate-fade-in-up" style="animation-delay:.1s">
        <a class="stat-card stat-card--compact" href="{{ route('matriculas.index') }}">
            <span class="stat-icon stat-icon--sky">
                <svg viewBox="0 0 24 24"><path d="M9 11 12 14 22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['matriculas'] }}">0</strong>
                <small>Matrículas</small>
                <span class="stat-detail">{{ $activeEnrollments }} ativas</span>
            </div>
        </a>
        <a class="stat-card stat-card--compact" href="{{ route('notas.index') }}">
            <span class="stat-icon stat-icon--rose">
                <svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V2" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['notas'] }}">0</strong>
                <small>Notas</small>
                @if($gradeStats['average'])
                    <span class="stat-detail">Média {{ number_format((float) $gradeStats['average'], 1, ',', '.') }}</span>
                @endif
            </div>
        </a>
        <a class="stat-card stat-card--compact" href="{{ route('frequencias.index') }}">
            <span class="stat-icon stat-icon--teal">
                <svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg>
            </span>
            <div class="stat-info">
                <strong class="counter-value" data-target="{{ $totals['frequencias'] }}">0</strong>
                <small>Frequências</small>
                @if($todayAttendance['total'] > 0)
                    <span class="stat-detail">{{ $todayAttendance['presente'] }} presenças hoje</span>
                @endif
            </div>
        </a>
    </section>

    {{-- ── Dashboard Main Grid ─────────────────────────────────── --}}
    <div class="dashboard-layout animate-fade-in-up" style="animation-delay:.15s">

        {{-- Left Column --}}
        <div class="dashboard-col-main">

            {{-- Performance Overview --}}
            @if($gradeStats['average'] || $todayAttendance['total'] > 0)
            <section class="panel dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">DESEMPENHO</p>
                        <h2>Visão geral</h2>
                    </div>
                </div>
                <div class="performance-grid">
                    @if($gradeStats['average'])
                    <div class="performance-card">
                        <div class="performance-ring performance-ring--grade">
                            <span class="performance-value">{{ number_format((float) $gradeStats['average'], 1, ',', '.') }}</span>
                        </div>
                        <div class="performance-info">
                            <strong>Média geral</strong>
                            <small>{{ $gradeStats['above'] }} acima · {{ $gradeStats['below'] }} abaixo</small>
                        </div>
                    </div>
                    @endif
                    @if($todayAttendance['total'] > 0)
                    <div class="performance-card">
                        @php
                            $attendanceRate = $todayAttendance['total'] > 0
                                ? round(($todayAttendance['presente'] / $todayAttendance['total']) * 100)
                                : 0;
                        @endphp
                        <div class="performance-ring performance-ring--attendance">
                            <span class="performance-value">{{ $attendanceRate }}%</span>
                        </div>
                        <div class="performance-info">
                            <strong>Presença hoje</strong>
                            <small>{{ $todayAttendance['presente'] }} de {{ $todayAttendance['total'] }} registros</small>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- Recent Enrollments --}}
            <section class="panel dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">MATRÍCULAS RECENTES</p>
                        <h2>Últimas movimentações</h2>
                    </div>
                    <a class="text-link" href="{{ route('matriculas.index') }}">Ver todas →</a>
                </div>
                @forelse ($recentEnrollments as $matricula)
                    <div class="recent-item">
                        <span class="initial">{{ mb_strtoupper(mb_substr($matricula->aluno?->nome ?? '?', 0, 1)) }}</span>
                        <span>
                            <strong>{{ $matricula->aluno?->nome ?? 'Aluno não encontrado' }}</strong>
                            <small>{{ $matricula->turma?->nome ?? '—' }} · {{ $matricula->data_matricula?->format('d/m/Y') }}</small>
                        </span>
                        <x-status :value="$matricula->situacao" />
                    </div>
                @empty
                    <div class="empty-compact">Nenhuma matrícula registrada.</div>
                @endforelse
            </section>
        </div>

        {{-- Right Column --}}
        <div class="dashboard-col-side">

            {{-- Quick Access --}}
            <section class="panel dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">ACESSO RÁPIDO</p>
                        <h2>Módulos</h2>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action" href="{{ route('alunos.create') }}">
                        <span class="quick-action-icon quick-action-icon--red">
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm4 2 2 2 4-4" /></svg>
                        </span>
                        <span class="quick-action-text">
                            <strong>Novo aluno</strong>
                            <small>Cadastrar estudante</small>
                        </span>
                        <svg class="quick-action-arrow" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                    <a class="quick-action" href="{{ route('matriculas.create') }}">
                        <span class="quick-action-icon quick-action-icon--sky">
                            <svg viewBox="0 0 24 24"><path d="M9 11 12 14 22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" /></svg>
                        </span>
                        <span class="quick-action-text">
                            <strong>Nova matrícula</strong>
                            <small>Vincular aluno</small>
                        </span>
                        <svg class="quick-action-arrow" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                    <a class="quick-action" href="{{ route('notas.create') }}">
                        <span class="quick-action-icon quick-action-icon--amber">
                            <svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V2" /></svg>
                        </span>
                        <span class="quick-action-text">
                            <strong>Lançar nota</strong>
                            <small>Registrar avaliação</small>
                        </span>
                        <svg class="quick-action-arrow" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                    <a class="quick-action" href="{{ route('frequencias.create') }}">
                        <span class="quick-action-icon quick-action-icon--emerald">
                            <svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg>
                        </span>
                        <span class="quick-action-text">
                            <strong>Registrar frequência</strong>
                            <small>Presença de alunos</small>
                        </span>
                        <svg class="quick-action-arrow" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </div>
            </section>

            {{-- Recent Students --}}
            <section class="panel dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">ALUNOS RECENTES</p>
                        <h2>Últimos cadastros</h2>
                    </div>
                    <a class="text-link" href="{{ route('alunos.index') }}">Ver todos →</a>
                </div>
                @forelse ($recentStudents as $student)
                    <div class="recent-item">
                        <span class="initial">{{ mb_strtoupper(mb_substr($student->nome, 0, 1)) }}</span>
                        <span>
                            <strong>{{ $student->nome }}</strong>
                            <small>Matrícula {{ $student->numero_matricula }}</small>
                        </span>
                        <x-status :value="$student->situacao" />
                    </div>
                @empty
                    <div class="empty-compact">Nenhum aluno cadastrado.</div>
                @endforelse
            </section>

            {{-- Recent Professors --}}
            @if($recentProfessors->count() > 0)
            <section class="panel dashboard-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">EQUIPE DOCENTE</p>
                        <h2>Professores</h2>
                    </div>
                    <a class="text-link" href="{{ route('professores.index') }}">Ver todos →</a>
                </div>
                @foreach ($recentProfessors as $professor)
                    <div class="recent-item">
                        <span class="initial" style="color: #d97706; background: #fffbeb;">{{ mb_strtoupper(mb_substr($professor->nome, 0, 1)) }}</span>
                        <span>
                            <strong>{{ $professor->nome }}</strong>
                            <small>{{ $professor->especialidade ?: $professor->formacao ?: 'Sem especialidade' }}</small>
                        </span>
                        <x-status :value="$professor->situacao" />
                    </div>
                @endforeach
            </section>
            @endif

        </div>
    </div>
@endsection
