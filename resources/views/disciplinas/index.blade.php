@extends('layouts.app')
@section('title', 'Disciplinas · Sistema Escolar')
@section('breadcrumb', 'Disciplinas')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Disciplinas"
        description="Mantenha a grade curricular e as regras de avaliação." :action-route="route('disciplinas.create')"
        action-label="Nova disciplina" />
    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('disciplinas.index') }}"><svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg><input name="busca" value="{{ $search }}" placeholder="Buscar por nome, código ou descrição"
                    aria-label="Buscar disciplinas"><button type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('disciplinas.index') }}">Limpar</a>
                @endif
            </form><span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'disciplina' : 'disciplinas' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Código</th>
                        <th>Carga horária</th>
                        <th>Média mínima</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $disciplina)
                        <tr>
                            <td><strong>{{ $disciplina->nome }}</strong><small
                                    class="cell-subtitle">{{ $disciplina->descricao ? \Illuminate\Support\Str::limit($disciplina->descricao, 55) : 'Sem descrição' }}</small>
                            </td>
                            <td>{{ $disciplina->codigo }}</td>
                            <td>{{ $disciplina->carga_horaria }} h</td>
                            <td>{{ number_format((float) $disciplina->media_minima, 1, ',', '.') }}</td>
                            <td><x-status :value="$disciplina->situacao" /></td>
                            <td class="row-actions"><a class="icon-button"
                                    href="{{ route('disciplinas.edit', $disciplina) }}"
                                    aria-label="Editar {{ $disciplina->nome }}"><svg viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('disciplinas.destroy', $disciplina) }}"
                                    data-confirm="Excluir a disciplina {{ $disciplina->nome }}?">@csrf
                                    @method('DELETE')<button class="icon-button danger" type="submit"
                                        aria-label="Excluir {{ $disciplina->nome }}"><svg viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state"><strong>Nenhuma disciplina encontrada</strong><span>Cadastre uma
                                        disciplina ou altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
