@extends('layouts.app')

@section('title', 'Área do Aluno · Sistema Escolar')
@section('breadcrumb', 'Meu painel')

@section('content')
    @php
        $periodos = [
            'primeiro_bimestre' => '1º bimestre',
            'segundo_bimestre' => '2º bimestre',
            'terceiro_bimestre' => '3º bimestre',
            'quarto_bimestre' => '4º bimestre',
        ];
    @endphp

    <section class="welcome-banner student-welcome animate-fade-in-up">
        <div class="welcome-content">
            <p class="welcome-greeting">Olá, <strong>{{ explode(' ', auth()->user()->name)[0] }}</strong> 👋</p>
            <h1 class="welcome-title">Área do Aluno</h1>
            <p class="welcome-description">Consulte seus dados acadêmicos com privacidade. Você possui acesso somente às suas próprias informações.</p>
        </div>
        <div class="welcome-meta">
            @if ($aluno)
                <div class="student-id-card">
                    <span>Matrícula</span>
                    <strong>{{ $aluno->numero_matricula }}</strong>
                    <small><x-status :value="$aluno->situacao" /></small>
                </div>
            @endif
        </div>
    </section>

    @if (! $aluno)
        <section class="panel linkage-notice animate-fade-in-up">
            <span class="linkage-notice-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" /></svg>
            </span>
            <div>
                <h2>Cadastro acadêmico aguardando vínculo</h2>
                <p>Sua conta está ativa, mas ainda não foi vinculada a uma matrícula. Procure a secretaria para liberar suas notas e frequências.</p>
            </div>
        </section>
    @else
        <section class="stats-row student-stats animate-fade-in-up" style="animation-delay:.05s" aria-label="Meus indicadores">
            <a class="stat-card stat-card--primary" href="{{ route('portal.notas') }}">
                <span class="stat-icon stat-icon--rose"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V2" /></svg></span>
                <div class="stat-info">
                    <strong>{{ (int) ($gradeStats->total ?? 0) }}</strong>
                    <small>Notas lançadas</small>
                    <span class="stat-detail">Somente suas avaliações</span>
                </div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('portal.notas') }}">
                <span class="stat-icon stat-icon--amber"><svg viewBox="0 0 24 24"><path d="M4 18 9 13l4 4 7-9M18 8h2v2" /></svg></span>
                <div class="stat-info">
                    <strong>{{ $gradeStats?->media !== null ? number_format((float) $gradeStats->media, 1, ',', '.') : '—' }}</strong>
                    <small>Média geral</small>
                    <span class="stat-detail">Das notas cadastradas</span>
                </div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('portal.frequencias') }}">
                <span class="stat-icon stat-icon--emerald"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6" /></svg></span>
                <div class="stat-info">
                    <strong>{{ $frequenciaPercentual !== null ? number_format($frequenciaPercentual, 1, ',', '.').'%' : '—' }}</strong>
                    <small>Frequência</small>
                    <span class="stat-detail">Presenças e atrasos</span>
                </div>
            </a>
            <a class="stat-card stat-card--primary" href="{{ route('portal.frequencias', ['situacao' => 'ausente']) }}">
                <span class="stat-icon stat-icon--red"><svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12" /></svg></span>
                <div class="stat-info">
                    <strong>{{ (int) ($attendanceStats->faltas ?? 0) }}</strong>
                    <small>Faltas</small>
                    <span class="stat-detail">{{ (int) ($attendanceStats->justificadas ?? 0) }} justificadas à parte</span>
                </div>
            </a>
        </section>

        <div class="dashboard-layout student-dashboard-layout animate-fade-in-up" style="animation-delay:.1s">
            <div class="dashboard-col-main">
                <section class="panel dashboard-panel">
                    <div class="panel-heading">
                        <div><p class="eyebrow">DESEMPENHO</p><h2>Frequência geral</h2></div>
                        <a class="text-link" href="{{ route('portal.frequencias') }}">Ver detalhes →</a>
                    </div>
                    <div class="attendance-overview">
                        <div class="attendance-rate">
                            <strong>{{ $frequenciaPercentual !== null ? number_format($frequenciaPercentual, 1, ',', '.').'%' : '—' }}</strong>
                            <span>de frequência</span>
                        </div>
                        <div class="attendance-breakdown">
                            <span><i class="legend-dot legend-dot--present"></i><strong>{{ (int) ($attendanceStats->presencas ?? 0) }}</strong> presenças</span>
                            <span><i class="legend-dot legend-dot--absent"></i><strong>{{ (int) ($attendanceStats->faltas ?? 0) }}</strong> faltas</span>
                            <span><i class="legend-dot legend-dot--justified"></i><strong>{{ (int) ($attendanceStats->justificadas ?? 0) }}</strong> justificadas</span>
                            <span><i class="legend-dot legend-dot--late"></i><strong>{{ (int) ($attendanceStats->atrasos ?? 0) }}</strong> atrasos</span>
                        </div>
                    </div>
                </section>

                <section class="panel dashboard-panel">
                    <div class="panel-heading">
                        <div><p class="eyebrow">AVALIAÇÕES</p><h2>Notas recentes</h2></div>
                        <a class="text-link" href="{{ route('portal.notas') }}">Ver todas →</a>
                    </div>
                    @forelse ($recentNotes as $nota)
                        <div class="recent-item">
                            <span class="initial">{{ mb_strtoupper(mb_substr($nota->disciplina?->nome ?? '?', 0, 1)) }}</span>
                            <span><strong>{{ $nota->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong><small>{{ $nota->avaliacao }} · {{ $periodos[$nota->periodo] ?? $nota->periodo }}</small></span>
                            <span class="grade {{ (float) $nota->valor < 6 ? 'is-low' : '' }}">{{ number_format((float) $nota->valor, 1, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="empty-compact">Nenhuma nota lançada até o momento.</div>
                    @endforelse
                </section>
            </div>

            <div class="dashboard-col-side">
                <section class="panel dashboard-panel student-enrollment-card">
                    <div class="panel-heading"><div><p class="eyebrow">MATRÍCULA</p><h2>Situação acadêmica</h2></div></div>
                    <dl class="student-details">
                        <div><dt>Aluno</dt><dd>{{ $aluno->nome }}</dd></div>
                        <div><dt>Turma</dt><dd>{{ $matriculaAtiva?->turma?->nome ?? 'Não informada' }}</dd></div>
                        <div><dt>Ano letivo</dt><dd>{{ $matriculaAtiva?->ano_letivo ?? now()->year }}</dd></div>
                        <div><dt>Situação</dt><dd><x-status :value="$matriculaAtiva?->situacao ?? $aluno->situacao" /></dd></div>
                    </dl>
                </section>

                <section class="panel dashboard-panel">
                    <div class="panel-heading">
                        <div><p class="eyebrow">AULAS</p><h2>Registros recentes</h2></div>
                        <a class="text-link" href="{{ route('portal.frequencias') }}">Ver todos →</a>
                    </div>
                    @forelse ($recentAttendance as $frequencia)
                        <div class="recent-item">
                            <span class="initial">{{ $frequencia->data_aula?->format('d') }}</span>
                            <span><strong>{{ $frequencia->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong><small>{{ $frequencia->data_aula?->format('d/m/Y') }} · {{ $frequencia->turma?->nome ?? 'Sem turma' }}</small></span>
                            <x-status :value="$frequencia->situacao" />
                        </div>
                    @empty
                        <div class="empty-compact">Nenhuma frequência registrada.</div>
                    @endforelse
                </section>
            </div>
        </div>
    @endif
@endsection
