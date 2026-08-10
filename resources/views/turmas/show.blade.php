@extends('layouts.app')

@section('title', $turma->nome . ' · Sistema Escolar')
@section('breadcrumb', 'Detalhes da turma')

@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" :title="$turma->nome"
        :description="$turma->serie . ' · ' . ucfirst($turma->turno) . ' · Ano letivo ' . $turma->ano_letivo">
        <x-slot:actions>
            @if (auth()->user()->tipo_usuario === 'administrador')
                <a class="secondary-button" href="{{ route('turmas.edit', $turma) }}">Editar turma</a>
            @endif
            <a class="primary-button" href="{{ route('frequencias.chamada', ['turma_id' => $turma->getKey()]) }}">
                Fazer chamada
            </a>
        </x-slot:actions>
    </x-page-header>

    <section class="summary-cards summary-cards--grades" aria-label="Resumo da turma">
        <article class="summary-card summary-card--accent">
            <span>Alunos ativos</span>
            <strong>{{ $turma->matriculas_ativas_count }}</strong>
            <small>de {{ $turma->limite_alunos }} vagas</small>
        </article>
        <article class="summary-card summary-card--success">
            <span>Vagas disponíveis</span>
            <strong>{{ $vagasDisponiveis }}</strong>
            <small>{{ $vagasDisponiveis === 1 ? 'vaga livre' : 'vagas livres' }}</small>
        </article>
        <article class="summary-card summary-card--info">
            <span>Disciplinas alocadas</span>
            <strong>{{ $turma->alocacoes->count() }}</strong>
            <small>vínculos docentes</small>
        </article>
        <article class="summary-card summary-card--warning">
            <span>Situação</span>
            <strong class="summary-card-text"><x-status :value="$turma->situacao" /></strong>
            <small>Sala {{ $turma->sala }}</small>
        </article>
    </section>

    <div class="dashboard-layout class-detail-layout">
        <section class="panel table-panel dashboard-col-main">
            <div class="table-toolbar table-toolbar--simple">
                <div>
                    <strong>Alunos matriculados</strong>
                    <small>Somente matrículas e alunos ativos.</small>
                </div>
                <span class="result-count">{{ $turma->matriculas->count() }} alunos</span>
            </div>
            <div class="table-scroll">
                <table>
                    <caption class="sr-only">Alunos com matrícula ativa na turma</caption>
                    <thead>
                        <tr><th scope="col">Aluno</th><th scope="col">Matrícula</th><th scope="col">Data de entrada</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($turma->matriculas as $matricula)
                            <tr>
                                <td><strong>{{ $matricula->aluno?->nome ?? 'Aluno não encontrado' }}</strong></td>
                                <td>{{ $matricula->aluno?->numero_matricula ?? '—' }}</td>
                                <td>{{ $matricula->data_matricula?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="empty-state"><strong>Turma sem alunos ativos</strong><span>Cadastre uma matrícula para compor esta turma.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel dashboard-panel dashboard-col-side">
            <div class="panel-heading">
                <div><p class="eyebrow">EQUIPE DOCENTE</p><h2>Disciplinas e professores</h2></div>
            </div>
            @forelse ($turma->alocacoes->sortBy(fn ($item) => $item->disciplina?->nome ?? '') as $alocacao)
                <div class="recent-item">
                    <span class="initial">{{ mb_strtoupper(mb_substr($alocacao->disciplina?->nome ?? '?', 0, 1)) }}</span>
                    <span>
                        <strong>{{ $alocacao->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong>
                        <small>{{ $alocacao->professor?->nome ?? 'Professor não encontrado' }}</small>
                    </span>
                </div>
            @empty
                <div class="empty-compact">Nenhuma disciplina alocada.</div>
            @endforelse
        </aside>
    </div>
@endsection
