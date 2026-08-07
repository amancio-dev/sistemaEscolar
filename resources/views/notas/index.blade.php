@extends('layouts.app')
@section('title', 'Notas · Sistema Escolar')
@section('breadcrumb', 'Notas')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Notas" description="Registre e consulte os resultados das avaliações."
        :action-route="route('notas.create')" action-label="Nova nota" />
    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('notas.index') }}"><svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg><input name="busca" value="{{ $search }}"
                    placeholder="Buscar por aluno, disciplina, avaliação ou período" aria-label="Buscar notas"><button
                    type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('notas.index') }}">Limpar</a>
                @endif
            </form><span class="result-count">{{ $records->total() }}
                {{ $records->total() === 1 ? 'nota' : 'notas' }}</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Disciplina</th>
                        <th>Avaliação</th>
                        <th>Período</th>
                        <th>Turma</th>
                        <th>Nota</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @php($periodos = ['primeiro_bimestre' => '1º bimestre', 'segundo_bimestre' => '2º bimestre', 'terceiro_bimestre' => '3º bimestre', 'quarto_bimestre' => '4º bimestre'])
                    @forelse ($records as $nota)
                        <tr>
                            <td><strong>{{ $nota->aluno?->nome ?? 'Aluno não encontrado' }}</strong><small
                                    class="cell-subtitle">{{ $nota->aluno?->numero_matricula }}</small></td>
                            <td>{{ $nota->disciplina?->nome ?? '—' }}</td>
                            <td>{{ $nota->avaliacao }}</td>
                            <td>{{ $periodos[$nota->periodo] ?? $nota->periodo }}</td>
                            <td>{{ $nota->turma?->nome ?? '—' }}</td>
                            <td><span
                                    class="grade {{ (float) $nota->valor < 6 ? 'is-low' : '' }}">{{ number_format((float) $nota->valor, 1, ',', '.') }}</span>
                            </td>
                            <td class="row-actions"><a class="icon-button" href="{{ route('notas.edit', $nota) }}"
                                    aria-label="Editar nota"><svg viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('notas.destroy', $nota) }}"
                                    data-confirm="Excluir esta nota?">@csrf @method('DELETE')<button
                                        class="icon-button danger" type="submit" aria-label="Excluir nota"><svg
                                            viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><strong>Nenhuma nota encontrada</strong><span>Cadastre uma nota ou
                                        altere os termos da busca.</span></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $records->links('components.pagination') }}
    </section>
@endsection
