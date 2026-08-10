@extends('layouts.app')
@section('title', 'Turmas · Sistema Escolar')
@section('breadcrumb', 'Turmas')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Turmas" description="Consulte composição, capacidade, disciplinas e responsáveis."
        :action-route="auth()->user()->tipo_usuario === 'administrador' ? route('turmas.create') : null"
        action-label="Nova turma" />
    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('turmas.index') }}"><svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg><input name="busca" value="{{ $search }}"
                    placeholder="Buscar por nome, série, turno, sala ou professor" aria-label="Buscar turmas"><button
                    type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('turmas.index') }}">Limpar</a>
                @endif
            </form><span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'turma' : 'turmas' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Série</th>
                        <th>Turno</th>
                        <th>Sala</th>
                        <th>Responsável</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $turma)
                        <tr>
                            <td><strong>{{ $turma->nome }}</strong><small class="cell-subtitle">Ano letivo
                                    {{ $turma->ano_letivo }}</small></td>
                            <td>{{ $turma->serie }}</td>
                            <td>{{ ucfirst($turma->turno) }}</td>
                            <td>{{ $turma->sala }}</td>
                            <td>{{ $turma->professorResponsavel?->nome ?? 'Não definido' }}</td>
                            <td><x-status :value="$turma->situacao" /></td>
                            <td class="row-actions">
                                <a class="icon-button" href="{{ route('turmas.show', $turma) }}"
                                    aria-label="Consultar {{ $turma->nome }}"><svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                    </svg></a>
                                @if (auth()->user()->tipo_usuario === 'administrador')
                                <a class="icon-button" href="{{ route('turmas.edit', $turma) }}"
                                    aria-label="Editar {{ $turma->nome }}"><svg viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('turmas.destroy', $turma) }}"
                                    data-confirm="Excluir a turma {{ $turma->nome }}?">@csrf @method('DELETE')<button
                                        class="icon-button danger" type="submit"
                                        aria-label="Excluir {{ $turma->nome }}"><svg viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><strong>Nenhuma turma encontrada</strong><span>Cadastre uma turma
                                        ou altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
