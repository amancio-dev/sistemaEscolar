@extends('layouts.app')

@section('title', 'Chamada em lote · Sistema Escolar')
@section('breadcrumb', 'Chamada em lote')

@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Chamada em lote"
        description="Escolha a aula uma vez e registre a presença de toda a turma."
        :action-route="route('frequencias.index')" action-label="Ver histórico" />

    <section class="panel attendance-filters batch-context-panel" aria-labelledby="batch-context-title">
        <div class="panel-heading">
            <div>
                <p class="eyebrow">CONTEXTO DA AULA</p>
                <h2 id="batch-context-title">Turma, disciplina e data</h2>
            </div>
        </div>

        <form class="filter-bar batch-context-form" method="GET" action="{{ route('frequencias.chamada') }}">
            <label class="filter-search">
                <span>Alocação docente</span>
                <select name="alocacao_id" required autofocus>
                    <option value="">Selecione turma, disciplina e professor</option>
                    @foreach ($alocacoes as $item)
                        <option value="{{ $item->id_disciplina_professor }}" @selected((int) ($alocacao?->getKey()) === (int) $item->getKey())>
                            {{ $item->turma?->nome }} · {{ $item->disciplina?->nome }} · {{ $item->professor?->nome }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Data da aula</span>
                <input type="date" name="data_aula" value="{{ $dataAula }}" max="{{ today()->toDateString() }}" required>
            </label>
            <button class="primary-button filter-submit" type="submit">Carregar chamada</button>
        </form>

        @if ($alocacoes->isEmpty())
            <div class="access-notice batch-notice">
                <strong>Nenhuma alocação ativa disponível.</strong>
                @if (auth()->user()->tipo_usuario === 'administrador')
                    <span>Cadastre o vínculo entre professor, disciplina e turma antes de realizar a chamada.</span>
                    <a class="secondary-button" href="{{ route('alocacoes.create') }}">Criar alocação docente</a>
                @else
                    <span>Solicite à administração o vínculo da disciplina e da turma ao seu cadastro docente.</span>
                @endif
            </div>
        @endif
    </section>

    @if ($alocacao)
        <section class="summary-cards summary-cards--grades" aria-label="Resumo da chamada">
            <article class="summary-card summary-card--accent">
                <span>Turma</span>
                <strong>{{ $alocacao->turma?->nome }}</strong>
                <small>{{ $alocacao->turma?->serie }} · {{ ucfirst($alocacao->turma?->turno ?? '') }}</small>
            </article>
            <article class="summary-card summary-card--info">
                <span>Disciplina</span>
                <strong class="summary-card-text">{{ $alocacao->disciplina?->nome }}</strong>
                <small>{{ $alocacao->disciplina?->codigo }}</small>
            </article>
            <article class="summary-card summary-card--success">
                <span>Alunos ativos</span>
                <strong>{{ $matriculas->count() }}</strong>
                <small>com matrícula ativa</small>
            </article>
            <article class="summary-card summary-card--warning">
                <span>Já registrados</span>
                <strong>{{ $frequenciasExistentes->count() }}</strong>
                <small>em {{ Illuminate\Support\Carbon::parse($dataAula)->format('d/m/Y') }}</small>
            </article>
        </section>

        <form class="panel batch-attendance" method="POST"
            action="{{ route('frequencias.chamada.store', ['alocacao_id' => $alocacao->getKey(), 'data_aula' => $dataAula]) }}"
            data-batch-attendance>
            @csrf
            <input type="hidden" name="alocacao_id" value="{{ $alocacao->getKey() }}">
            <input type="hidden" name="data_aula" value="{{ $dataAula }}">

            <div class="table-toolbar table-toolbar--simple">
                <div>
                    <strong>Lista de presença</strong>
                    <small>{{ $alocacao->professor?->nome }} · {{ Illuminate\Support\Carbon::parse($dataAula)->translatedFormat('d \d\e F \d\e Y') }}</small>
                </div>
                @if ($matriculas->isNotEmpty())
                    <button class="secondary-button" type="button" data-mark-all-present>
                        Marcar todos presentes
                    </button>
                @endif
            </div>

            <div class="table-scroll">
                <table class="batch-attendance-table">
                    <caption class="sr-only">Situação de frequência dos alunos da turma</caption>
                    <thead>
                        <tr>
                            <th scope="col">Aluno</th>
                            <th scope="col">Matrícula</th>
                            <th scope="col">Situação</th>
                            <th scope="col">Justificativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matriculas as $index => $matricula)
                            @php
                                $existente = $frequenciasExistentes->get($matricula->aluno_id);
                                $situacao = old("frequencias.$index.situacao", $existente?->situacao ?? 'presente');
                                $justificativa = old("frequencias.$index.justificativa", $existente?->justificativa ?? '');
                                $aceitaJustificativa = in_array($situacao, ['ausente', 'justificada'], true);
                            @endphp
                            <tr data-attendance-row>
                                <td data-label="Aluno">
                                    <input type="hidden" name="frequencias[{{ $index }}][aluno_id]"
                                        value="{{ $matricula->aluno_id }}">
                                    <strong>{{ $matricula->aluno?->nome ?? 'Aluno não encontrado' }}</strong>
                                </td>
                                <td data-label="Matrícula">{{ $matricula->aluno?->numero_matricula ?? '—' }}</td>
                                <td data-label="Situação">
                                    <label class="sr-only" for="situacao-{{ $matricula->aluno_id }}">
                                        Situação de {{ $matricula->aluno?->nome }}
                                    </label>
                                    <select id="situacao-{{ $matricula->aluno_id }}"
                                        name="frequencias[{{ $index }}][situacao]" data-row-status required>
                                        @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'justificada' => 'Falta justificada', 'atrasado' => 'Atrasado'] as $value => $label)
                                            <option value="{{ $value }}" @selected($situacao === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="Justificativa">
                                    <label class="sr-only" for="justificativa-{{ $matricula->aluno_id }}">
                                        Justificativa de {{ $matricula->aluno?->nome }}
                                    </label>
                                    <input id="justificativa-{{ $matricula->aluno_id }}" type="text"
                                        name="frequencias[{{ $index }}][justificativa]"
                                        value="{{ $justificativa }}" maxlength="1000"
                                        placeholder="Motivo da ausência"
                                        data-row-justification @disabled(! $aceitaJustificativa)>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <strong>Nenhum aluno disponível para a chamada</strong>
                                        <span>Esta turma ainda não possui alunos com matrícula ativa.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($matriculas->isNotEmpty())
                <div class="form-actions">
                    <a class="secondary-button" href="{{ route('frequencias.index') }}">Cancelar</a>
                    <button class="primary-button" type="submit">Salvar chamada</button>
                </div>
            @endif
        </form>
    @elseif ($alocacoes->isNotEmpty())
        <section class="panel empty-state batch-empty-state">
            <strong>Selecione o contexto da aula</strong>
            <span>A lista da turma aparecerá aqui pronta para a chamada.</span>
        </section>
    @endif
@endsection
