@extends('layouts.app')
@section('title', 'Matrículas · Sistema Escolar')
@section('breadcrumb', 'Matrículas')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Matrículas" description="Vincule alunos às turmas do ano letivo."
        :action-route="route('matriculas.create')" action-label="Nova matrícula" />
    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('matriculas.index') }}"><svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg><input name="busca" value="{{ $search }}"
                    placeholder="Buscar por aluno, matrícula, turma, ano ou situação" aria-label="Buscar matrículas"><button
                    type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('matriculas.index') }}">Limpar</a>
                @endif
            </form><span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'matrícula' : 'matrículas' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Data</th>
                        <th>Ano</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $matricula)
                        <tr>
                            <td><strong>#{{ $matricula->id_matricula }}</strong></td>
                            <td><strong>{{ $matricula->aluno?->nome ?? 'Aluno não encontrado' }}</strong><small
                                    class="cell-subtitle">{{ $matricula->aluno?->numero_matricula }}</small></td>
                            <td>{{ $matricula->turma?->nome ?? 'Turma não encontrada' }}</td>
                            <td>{{ $matricula->data_matricula?->format('d/m/Y') }}</td>
                            <td>{{ $matricula->ano_letivo }}</td>
                            <td><x-status :value="$matricula->situacao" /></td>
                            <td class="row-actions"><a class="icon-button"
                                    href="{{ route('matriculas.edit', $matricula) }}" aria-label="Editar matrícula"><svg
                                        viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('matriculas.destroy', $matricula) }}"
                                    data-confirm="Excluir a matrícula #{{ $matricula->id_matricula }}?">@csrf
                                    @method('DELETE')<button class="icon-button danger" type="submit"
                                        aria-label="Excluir matrícula"><svg viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><strong>Nenhuma matrícula encontrada</strong><span>Cadastre uma
                                        matrícula ou altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
