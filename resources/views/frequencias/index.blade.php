@extends('layouts.app')

@section('title', 'Frequências · Sistema Escolar')
@section('breadcrumb', 'Frequências')

@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Frequências"
        description="Acompanhe presenças e faltas por aluno, período, turma e disciplina."
        :action-route="route('frequencias.create')" action-label="Nova frequência" />

    <section class="summary-cards attendance-summary" aria-label="Resumo dos registros filtrados">
        <article class="summary-card summary-card--rate">
            <span>Frequência geral</span>
            <strong>{{ $frequenciaPercentual !== null ? number_format($frequenciaPercentual, 1, ',', '.').'%' : '—' }}</strong>
            <small>Presenças + atrasos</small>
        </article>
        <article class="summary-card summary-card--success"><span>Presenças</span><strong>{{ (int) ($totals->presencas ?? 0) }}</strong></article>
        <article class="summary-card summary-card--danger"><span>Faltas</span><strong>{{ (int) ($totals->faltas ?? 0) }}</strong></article>
        <article class="summary-card summary-card--warning"><span>Justificadas</span><strong>{{ (int) ($totals->justificadas ?? 0) }}</strong></article>
        <article class="summary-card summary-card--info"><span>Atrasos</span><strong>{{ (int) ($totals->atrasos ?? 0) }}</strong></article>
    </section>

    <section class="panel attendance-filters">
        <div class="panel-heading">
            <div><p class="eyebrow">FILTROS</p><h2>Localizar registros</h2></div>
            @if (collect($filters)->filter()->isNotEmpty())
                <a class="text-link" href="{{ route('frequencias.index') }}">Limpar todos</a>
            @endif
        </div>
        <form class="filter-bar filter-bar--management" method="GET" action="{{ route('frequencias.index') }}">
            <label class="filter-search"><span>Busca rápida</span><input name="busca" value="{{ $filters['busca'] }}" placeholder="Aluno, matrícula, disciplina ou professor"></label>
            <label><span>Aluno</span><select name="aluno_id">
                <option value="">Todos os alunos</option>
                @foreach ($alunos as $aluno)
                    <option value="{{ $aluno->id_aluno }}" @selected((int) $filters['aluno_id'] === (int) $aluno->id_aluno)>{{ $aluno->nome }}</option>
                @endforeach
            </select></label>
            <label><span>Disciplina</span><select name="disciplina_id">
                <option value="">Todas</option>
                @foreach ($disciplinas as $disciplina)
                    <option value="{{ $disciplina->id_disciplina }}" @selected((int) $filters['disciplina_id'] === (int) $disciplina->id_disciplina)>{{ $disciplina->nome }}</option>
                @endforeach
            </select></label>
            <label><span>Turma</span><select name="turma_id">
                <option value="">Todas</option>
                @foreach ($turmas as $turma)
                    <option value="{{ $turma->id_turma }}" @selected((int) $filters['turma_id'] === (int) $turma->id_turma)>{{ $turma->nome }}</option>
                @endforeach
            </select></label>
            <label><span>Situação</span><select name="situacao">
                <option value="">Todas</option>
                @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'justificada' => 'Justificada', 'atrasado' => 'Atrasado'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['situacao'] === $value)>{{ $label }}</option>
                @endforeach
            </select></label>
            <label><span>Data inicial</span><input type="date" name="data_inicio" value="{{ $filters['data_inicio'] }}"></label>
            <label><span>Data final</span><input type="date" name="data_fim" value="{{ $filters['data_fim'] }}"></label>
            <button class="primary-button filter-submit" type="submit">Aplicar filtros</button>
        </form>
    </section>

    <section class="panel attendance-by-student">
        <div class="panel-heading">
            <div><p class="eyebrow">RESUMO POR ALUNO</p><h2>Quantidade de presenças e faltas</h2></div>
            <span class="result-count">{{ $resumoPorAluno->count() }} {{ $resumoPorAluno->count() === 1 ? 'aluno' : 'alunos' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr><th>Aluno</th><th>Aulas</th><th>Presenças</th><th>Faltas</th><th>Justificadas</th><th>Atrasos</th><th>Frequência</th></tr>
                </thead>
                <tbody>
                    @forelse ($resumoPorAluno as $resumo)
                        @php($taxa = (int) $resumo->total > 0 ? round((((int) $resumo->presencas + (int) $resumo->atrasos) / (int) $resumo->total) * 100, 1) : 0)
                        <tr>
                            <td><strong>{{ $resumo->aluno?->nome ?? 'Aluno não encontrado' }}</strong><small class="cell-subtitle">Matrícula {{ $resumo->aluno?->numero_matricula ?? '—' }}</small></td>
                            <td>{{ (int) $resumo->total }}</td>
                            <td><span class="count-badge count-badge--success">{{ (int) $resumo->presencas }}</span></td>
                            <td><span class="count-badge count-badge--danger">{{ (int) $resumo->faltas }}</span></td>
                            <td><span class="count-badge count-badge--warning">{{ (int) $resumo->justificadas }}</span></td>
                            <td><span class="count-badge count-badge--info">{{ (int) $resumo->atrasos }}</span></td>
                            <td><strong class="attendance-percentage {{ $taxa < 75 ? 'is-low' : '' }}">{{ number_format($taxa, 1, ',', '.') }}%</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><strong>Sem dados para resumir</strong><span>Nenhuma frequência corresponde aos filtros.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel table-panel">
        <div class="table-toolbar table-toolbar--simple">
            <div><strong>Histórico detalhado</strong><small>Edite ou exclua lançamentos individuais quando necessário.</small></div>
            <span class="result-count">{{ $records->total() }} {{ $records->total() === 1 ? 'registro' : 'registros' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr><th>Aluno</th><th>Disciplina</th><th>Turma</th><th>Data da aula</th><th>Professor</th><th>Situação</th><th class="actions-column">Ações</th></tr>
                </thead>
                <tbody>
                    @forelse ($records as $frequencia)
                        <tr>
                            <td><strong>{{ $frequencia->aluno?->nome ?? 'Aluno não encontrado' }}</strong><small class="cell-subtitle">{{ $frequencia->aluno?->numero_matricula }}</small></td>
                            <td>{{ $frequencia->disciplina?->nome ?? '—' }}</td>
                            <td>{{ $frequencia->turma?->nome ?? '—' }}</td>
                            <td>{{ $frequencia->data_aula?->format('d/m/Y') }}</td>
                            <td>{{ $frequencia->professor?->nome ?? '—' }}</td>
                            <td><x-status :value="$frequencia->situacao" /></td>
                            <td class="row-actions">
                                <a class="icon-button" href="{{ route('frequencias.edit', $frequencia) }}" aria-label="Editar frequência"><svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" /></svg></a>
                                <form method="POST" action="{{ route('frequencias.destroy', $frequencia) }}" data-confirm="Excluir este registro de frequência?">
                                    @csrf @method('DELETE')
                                    <button class="icon-button danger" type="submit" aria-label="Excluir frequência"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" /></svg></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><strong>Nenhuma frequência encontrada</strong><span>Cadastre uma frequência ou altere os filtros.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
