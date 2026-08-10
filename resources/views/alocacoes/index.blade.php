@extends('layouts.app')

@section('title', 'Alocações docentes · Sistema Escolar')
@section('breadcrumb', 'Alocações docentes')

@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Alocações docentes"
        description="Defina quais professores lecionam cada disciplina nas turmas."
        :action-route="route('alocacoes.create')" action-label="Nova alocação" />

    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('alocacoes.index') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg>
                <input name="busca" value="{{ $search }}"
                    placeholder="Buscar por professor, disciplina, turma ou ano"
                    aria-label="Buscar alocações docentes">
                <button type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('alocacoes.index') }}">Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'alocação' : 'alocações' }}</span>
        </div>

        <div class="table-scroll">
            <table>
                <caption class="sr-only">Alocações de professores por disciplina e turma</caption>
                <thead>
                    <tr>
                        <th scope="col">Professor</th>
                        <th scope="col">Disciplina vinculada</th>
                        <th scope="col">Turma</th>
                        <th scope="col">Período letivo</th>
                        <th scope="col" class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $alocacao)
                        <tr>
                            <td>
                                <div class="person-cell">
                                    <span class="initial">{{ mb_strtoupper(mb_substr($alocacao->professor?->nome ?? '?', 0, 1)) }}</span>
                                    <strong>{{ $alocacao->professor?->nome ?? 'Professor não encontrado' }}</strong>
                                </div>
                                @if ($alocacao->professor?->especialidade)
                                    <small class="cell-subtitle">Especialidade cadastral: {{ $alocacao->professor->especialidade }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $alocacao->disciplina?->nome ?? 'Disciplina não encontrada' }}</strong>
                                <small class="cell-subtitle">{{ $alocacao->disciplina?->codigo ?? 'Sem código' }}</small>
                            </td>
                            <td>
                                <strong>{{ $alocacao->turma?->nome ?? 'Turma não encontrada' }}</strong>
                                <small class="cell-subtitle">{{ $alocacao->turma?->serie ?? 'Série não informada' }}</small>
                            </td>
                            <td>
                                {{ $alocacao->turma?->ano_letivo ?? '—' }}
                                <small class="cell-subtitle">
                                    {{ $alocacao->turma?->turno ? Illuminate\Support\Str::ucfirst($alocacao->turma->turno) : 'Turno não informado' }}
                                </small>
                            </td>
                            <td class="row-actions">
                                <a class="icon-button" href="{{ route('alocacoes.edit', $alocacao) }}"
                                    aria-label="Editar alocação de {{ $alocacao->professor?->nome ?? 'professor' }} em {{ $alocacao->disciplina?->nome ?? 'disciplina não encontrada' }} na turma {{ $alocacao->turma?->nome ?? 'não encontrada' }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('alocacoes.destroy', $alocacao) }}"
                                    data-confirm="Excluir esta alocação docente?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-button danger" type="submit"
                                        aria-label="Excluir alocação de {{ $alocacao->professor?->nome ?? 'professor' }} em {{ $alocacao->disciplina?->nome ?? 'disciplina não encontrada' }} na turma {{ $alocacao->turma?->nome ?? 'não encontrada' }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <strong>Nenhuma alocação docente encontrada</strong>
                                    <span>Cadastre uma alocação ou altere os termos da busca.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links('components.pagination') }}
    </section>
@endsection
