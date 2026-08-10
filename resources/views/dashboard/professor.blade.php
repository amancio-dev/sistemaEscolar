@extends('layouts.app')

@section('title', 'Painel docente · Sistema Escolar')
@section('breadcrumb', 'Painel docente')

@section('content')
    <section class="welcome-banner animate-fade-in-up">
        <div class="welcome-content">
            <p class="welcome-greeting">Olá, <strong>{{ explode(' ', auth()->user()->name)[0] }}</strong> 👋</p>
            <h1 class="welcome-title">Painel Docente</h1>
            <p class="welcome-description">Acesse suas turmas e realize as rotinas acadêmicas em poucos passos.</p>
        </div>
        <div class="welcome-meta">
            <div class="welcome-date">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg>
                <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
        </div>
    </section>

    @if (! $professor)
        <section class="panel linkage-notice">
            <div>
                <h2>Cadastro docente ainda não vinculado</h2>
                <p>Peça à administração para concluir o vínculo da sua conta antes de acessar turmas e lançamentos.</p>
            </div>
        </section>
    @else
        <section class="stats-row animate-fade-in-up" aria-label="Indicadores docentes">
            <a class="stat-card stat-card--primary" href="{{ route('turmas.index') }}">
                <span class="stat-icon stat-icon--emerald"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18v16H3V4Zm0 5h18M8 4v16" /></svg></span>
                <div class="stat-info"><strong>{{ $turmasCount }}</strong><small>Minhas turmas</small><span class="stat-detail">turmas ativas</span></div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('turmas.index') }}">
                <span class="stat-icon stat-icon--sky"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg></span>
                <div class="stat-info"><strong>{{ $alunosCount }}</strong><small>Alunos</small><span class="stat-detail">matrículas ativas</span></div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('notas.index') }}">
                <span class="stat-icon stat-icon--amber"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V2" /></svg></span>
                <div class="stat-info"><strong>{{ $notasCount }}</strong><small>Notas lançadas</small><span class="stat-detail">por este professor</span></div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('frequencias.chamada') }}">
                <span class="stat-icon stat-icon--red"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" /></svg></span>
                <div class="stat-info"><strong>{{ $frequenciasHoje }}</strong><small>Registros hoje</small><span class="stat-detail">fazer chamada</span></div>
            </a>
        </section>

        <div class="dashboard-layout animate-fade-in-up">
            <section class="panel dashboard-panel dashboard-col-main">
                <div class="panel-heading">
                    <div><p class="eyebrow">MINHA ORGANIZAÇÃO</p><h2>Turmas e disciplinas</h2></div>
                    <a class="text-link" href="{{ route('turmas.index') }}">Ver turmas →</a>
                </div>
                @forelse ($alocacoes as $alocacao)
                    <div class="recent-item">
                        <span class="initial">{{ mb_strtoupper(mb_substr($alocacao->disciplina?->nome ?? '?', 0, 1)) }}</span>
                        <span>
                            <strong>{{ $alocacao->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong>
                            <small>{{ $alocacao->turma?->nome }} · {{ $alocacao->turma?->serie }}</small>
                        </span>
                        <a class="text-link" href="{{ route('frequencias.chamada', ['alocacao_id' => $alocacao->getKey()]) }}">Chamada →</a>
                    </div>
                @empty
                    <div class="empty-compact">Nenhuma alocação docente ativa.</div>
                @endforelse
            </section>

            <aside class="panel dashboard-panel dashboard-col-side">
                <div class="panel-heading">
                    <div><p class="eyebrow">ATIVIDADE RECENTE</p><h2>Últimas frequências</h2></div>
                </div>
                @forelse ($recentAttendance as $frequencia)
                    <div class="recent-item">
                        <span class="initial">{{ mb_strtoupper(mb_substr($frequencia->aluno?->nome ?? '?', 0, 1)) }}</span>
                        <span>
                            <strong>{{ $frequencia->aluno?->nome ?? 'Aluno não encontrado' }}</strong>
                            <small>{{ $frequencia->turma?->nome }} · {{ $frequencia->data_aula?->format('d/m/Y') }}</small>
                        </span>
                        <x-status :value="$frequencia->situacao" />
                    </div>
                @empty
                    <div class="empty-compact">Nenhuma frequência registrada.</div>
                @endforelse
            </aside>
        </div>
    @endif
@endsection
