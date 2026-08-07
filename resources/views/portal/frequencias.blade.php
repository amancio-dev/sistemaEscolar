@extends('layouts.app')

@section('title', 'Minha frequência · Sistema Escolar')
@section('breadcrumb', 'Minha frequência')

@section('content')
    <x-page-header eyebrow="ÁREA DO ALUNO" title="Minha frequência"
        description="Acompanhe suas presenças, faltas, justificativas e atrasos por disciplina." />

    @if (! $aluno)
        <section class="panel linkage-notice">
            <span class="linkage-notice-icon"><svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" /></svg></span>
            <div><h2>Dados ainda não disponíveis</h2><p>Peça à secretaria para vincular sua conta ao cadastro acadêmico.</p></div>
        </section>
    @else
        <section class="summary-cards attendance-summary" aria-label="Resumo da frequência">
            <article class="summary-card summary-card--rate"><span>Frequência geral</span><strong>{{ $frequenciaPercentual !== null ? number_format($frequenciaPercentual, 1, ',', '.').'%' : '—' }}</strong><small>Presenças + atrasos</small></article>
            <article class="summary-card summary-card--success"><span>Presenças</span><strong>{{ (int) ($totals->presencas ?? 0) }}</strong></article>
            <article class="summary-card summary-card--danger"><span>Faltas</span><strong>{{ (int) ($totals->faltas ?? 0) }}</strong></article>
            <article class="summary-card summary-card--warning"><span>Justificadas</span><strong>{{ (int) ($totals->justificadas ?? 0) }}</strong></article>
            <article class="summary-card summary-card--info"><span>Atrasos</span><strong>{{ (int) ($totals->atrasos ?? 0) }}</strong></article>
        </section>

        <section class="panel attendance-by-student">
            <div class="panel-heading">
                <div><p class="eyebrow">POR DISCIPLINA</p><h2>Resumo das aulas</h2></div>
                <span class="result-count">{{ (int) ($totals->total ?? 0) }} registros</span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Disciplina</th><th>Aulas</th><th>Presenças</th><th>Faltas</th><th>Justificadas</th><th>Atrasos</th><th>Frequência</th></tr></thead>
                    <tbody>
                        @forelse ($resumoPorDisciplina as $resumo)
                            @php($taxa = (int) $resumo->total > 0 ? round((((int) $resumo->presencas + (int) $resumo->atrasos) / (int) $resumo->total) * 100, 1) : 0)
                            <tr>
                                <td><strong>{{ $resumo->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong><small class="cell-subtitle">{{ $resumo->disciplina?->codigo }}</small></td>
                                <td>{{ (int) $resumo->total }}</td>
                                <td><span class="count-badge count-badge--success">{{ (int) $resumo->presencas }}</span></td>
                                <td><span class="count-badge count-badge--danger">{{ (int) $resumo->faltas }}</span></td>
                                <td><span class="count-badge count-badge--warning">{{ (int) $resumo->justificadas }}</span></td>
                                <td><span class="count-badge count-badge--info">{{ (int) $resumo->atrasos }}</span></td>
                                <td><strong class="attendance-percentage {{ $taxa < 75 ? 'is-low' : '' }}">{{ number_format($taxa, 1, ',', '.') }}%</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state"><strong>Sem dados para resumir</strong><span>Nenhuma frequência foi registrada.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel table-panel">
            <form class="filter-bar" method="GET" action="{{ route('portal.frequencias') }}">
                <label class="filter-search"><span>Buscar</span><input name="busca" value="{{ $filters['busca'] }}" placeholder="Disciplina ou turma"></label>
                <label><span>Situação</span><select name="situacao">
                    <option value="">Todas</option>
                    @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'justificada' => 'Justificada', 'atrasado' => 'Atrasado'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['situacao'] === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <label><span>De</span><input type="date" name="data_inicio" value="{{ $filters['data_inicio'] }}"></label>
                <label><span>Até</span><input type="date" name="data_fim" value="{{ $filters['data_fim'] }}"></label>
                <button class="primary-button filter-submit" type="submit">Filtrar</button>
                @if (collect($filters)->filter()->isNotEmpty())
                    <a class="secondary-button" href="{{ route('portal.frequencias') }}">Limpar</a>
                @endif
            </form>

            <div class="table-toolbar table-toolbar--simple">
                <div><strong>Histórico de aulas</strong><small>Estes registros não podem ser alterados pelo perfil de aluno.</small></div>
                <span class="result-count">{{ $records->total() }} registros</span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Data</th><th>Disciplina</th><th>Turma</th><th>Situação</th><th>Justificativa</th></tr></thead>
                    <tbody>
                        @forelse ($records as $frequencia)
                            <tr>
                                <td><strong>{{ $frequencia->data_aula?->format('d/m/Y') }}</strong></td>
                                <td>{{ $frequencia->disciplina?->nome ?? '—' }}</td>
                                <td>{{ $frequencia->turma?->nome ?? '—' }}</td>
                                <td><x-status :value="$frequencia->situacao" /></td>
                                <td>{{ $frequencia->justificativa ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><strong>Nenhum registro encontrado</strong><span>Não há frequências para os filtros informados.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $records->links('components.pagination') }}
        </section>
    @endif
@endsection
