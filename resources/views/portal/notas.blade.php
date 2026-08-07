@extends('layouts.app')

@section('title', 'Minhas notas · Sistema Escolar')
@section('breadcrumb', 'Minhas notas')

@section('content')
    @php
        $periodos = [
            'primeiro_bimestre' => '1º bimestre',
            'segundo_bimestre' => '2º bimestre',
            'terceiro_bimestre' => '3º bimestre',
            'quarto_bimestre' => '4º bimestre',
        ];
    @endphp

    <x-page-header eyebrow="ÁREA DO ALUNO" title="Minhas notas"
        description="Consulte somente as avaliações vinculadas à sua matrícula." />

    @if (! $aluno)
        <section class="panel linkage-notice">
            <span class="linkage-notice-icon"><svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 3.6 2.4 17.2A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.8L13.7 3.6a2 2 0 0 0-3.4 0Z" /></svg></span>
            <div><h2>Dados ainda não disponíveis</h2><p>Peça à secretaria para vincular sua conta ao cadastro acadêmico.</p></div>
        </section>
    @else
        <section class="summary-cards summary-cards--grades" aria-label="Resumo das notas">
            <article class="summary-card"><span>Notas lançadas</span><strong>{{ (int) ($summary->total ?? 0) }}</strong></article>
            <article class="summary-card summary-card--accent"><span>Média geral</span><strong>{{ $summary?->media !== null ? number_format((float) $summary->media, 1, ',', '.') : '—' }}</strong></article>
            <article class="summary-card"><span>Maior nota</span><strong>{{ $summary?->maior !== null ? number_format((float) $summary->maior, 1, ',', '.') : '—' }}</strong></article>
            <article class="summary-card"><span>Menor nota</span><strong>{{ $summary?->menor !== null ? number_format((float) $summary->menor, 1, ',', '.') : '—' }}</strong></article>
        </section>

        <section class="panel table-panel">
            <form class="filter-bar" method="GET" action="{{ route('portal.notas') }}">
                <label class="filter-search"><span>Buscar</span><input name="busca" value="{{ $search }}" placeholder="Disciplina ou avaliação"></label>
                <label><span>Período</span><select name="periodo">
                    <option value="">Todos os bimestres</option>
                    @foreach ($periodos as $value => $label)
                        <option value="{{ $value }}" @selected($periodo === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <button class="primary-button filter-submit" type="submit">Filtrar</button>
                @if ($search !== '' || $periodo !== '')
                    <a class="secondary-button" href="{{ route('portal.notas') }}">Limpar</a>
                @endif
            </form>

            <div class="table-toolbar table-toolbar--simple">
                <div><strong>Resultados</strong><small>Informações disponíveis apenas para consulta.</small></div>
                <span class="result-count">{{ $records->total() }} {{ $records->total() === 1 ? 'nota' : 'notas' }}</span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Disciplina</th><th>Avaliação</th><th>Período</th><th>Turma</th><th>Nota</th></tr></thead>
                    <tbody>
                        @forelse ($records as $nota)
                            <tr>
                                <td><strong>{{ $nota->disciplina?->nome ?? '—' }}</strong><small class="cell-subtitle">{{ $nota->disciplina?->codigo }}</small></td>
                                <td>{{ $nota->avaliacao }}</td>
                                <td>{{ $periodos[$nota->periodo] ?? $nota->periodo }}</td>
                                <td>{{ $nota->turma?->nome ?? '—' }}</td>
                                <td><span class="grade {{ (float) $nota->valor < 6 ? 'is-low' : '' }}">{{ number_format((float) $nota->valor, 1, ',', '.') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="empty-state"><strong>Nenhuma nota encontrada</strong><span>Não há avaliações para os filtros informados.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $records->links('components.pagination') }}
        </section>
    @endif
@endsection
