@extends('layouts.app')
@section('title', 'Frequências · Sistema Escolar')
@section('breadcrumb', 'Frequências')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Frequências" description="Controle a presença dos alunos nas aulas."
        :action-route="route('frequencias.create')" action-label="Nova frequência" />
    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('frequencias.index') }}"><svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg><input name="busca" value="{{ $search }}"
                    placeholder="Buscar por aluno, disciplina, turma, data ou situação"
                    aria-label="Buscar frequências"><button type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('frequencias.index') }}">Limpar</a>
                @endif
            </form><span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'frequência' : 'frequências' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Disciplina</th>
                        <th>Turma</th>
                        <th>Data da aula</th>
                        <th>Professor</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $frequencia)
                        <tr>
                            <td><strong>{{ $frequencia->aluno?->nome ?? 'Aluno não encontrado' }}</strong><small
                                    class="cell-subtitle">{{ $frequencia->aluno?->numero_matricula }}</small></td>
                            <td>{{ $frequencia->disciplina?->nome ?? '—' }}</td>
                            <td>{{ $frequencia->turma?->nome ?? '—' }}</td>
                            <td>{{ $frequencia->data_aula?->format('d/m/Y') }}</td>
                            <td>{{ $frequencia->professor?->nome ?? '—' }}</td>
                            <td><x-status :value="$frequencia->situacao" /></td>
                            <td class="row-actions"><a class="icon-button"
                                    href="{{ route('frequencias.edit', $frequencia) }}" aria-label="Editar frequência"><svg
                                        viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('frequencias.destroy', $frequencia) }}"
                                    data-confirm="Excluir este registro de frequência?">@csrf @method('DELETE')<button
                                        class="icon-button danger" type="submit" aria-label="Excluir frequência"><svg
                                            viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><strong>Nenhuma frequência encontrada</strong><span>Cadastre uma
                                        frequência ou altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
