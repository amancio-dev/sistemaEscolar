@extends('layouts.app')

@section('title', 'Professores · Sistema Escolar')
@section('breadcrumb', 'Professores')

@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Professores" description="Organize os dados da equipe docente."
        :action-route="route('professores.create')" action-label="Novo professor" />

    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('professores.index') }}">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg>
                <input name="busca" value="{{ $search }}"
                    placeholder="Buscar por nome, CPF, formação ou especialidade" aria-label="Buscar professores">
                <button type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('professores.index') }}">Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'professor' : 'professores' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Professor</th>
                        <th>Especialidade</th>
                        <th>E-mail</th>
                        <th>Contratação</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $professor)
                        <tr>
                            <td>
                                <div class="person-cell"><span
                                        class="initial">{{ mb_strtoupper(mb_substr($professor->nome, 0, 1)) }}</span><strong>{{ $professor->nome }}</strong>
                                </div>
                            </td>
                            <td>{{ $professor->especialidade ?: '—' }}</td>
                            <td>{{ $professor->email }}</td>
                            <td>{{ $professor->data_contratacao?->format('d/m/Y') ?? '—' }}</td>
                            <td><x-status :value="$professor->situacao" /></td>
                            <td class="row-actions">
                                <a class="icon-button" href="{{ route('professores.edit', $professor) }}"
                                    aria-label="Editar {{ $professor->nome }}"><svg viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('professores.destroy', $professor) }}"
                                    data-confirm="Excluir o professor {{ $professor->nome }}?">@csrf
                                    @method('DELETE')<button class="icon-button danger" type="submit"
                                        aria-label="Excluir {{ $professor->nome }}"><svg viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state"><strong>Nenhum professor encontrado</strong><span>Cadastre um
                                        professor ou altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
