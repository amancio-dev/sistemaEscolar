@extends('layouts.app')

@section('title', 'Alunos · Sistema Escolar')
@section('breadcrumb', 'Alunos')

@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Alunos"
        description="Cadastre, consulte e atualize os dados dos estudantes." :action-route="route('alunos.create')" action-label="Novo aluno" />

    <section class="panel table-panel">
        <div class="table-toolbar">
            <form class="search-form" method="GET" action="{{ route('alunos.index') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg>
                <input name="busca" value="{{ $search }}" placeholder="Buscar por nome, matrícula, CPF ou e-mail"
                    aria-label="Buscar alunos">
                <button type="submit">Buscar</button>
                @if ($search !== '')
                    <a href="{{ route('alunos.index') }}">Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $records->total() }} {{ $records->total() === 1 ? 'aluno' : 'alunos' }}</span>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Matrícula</th>
                        <th>CPF</th>
                        <th>E-mail</th>
                        <th>Situação</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $student)
                        <tr>
                            <td>
                                <div class="person-cell"><span
                                        class="initial">{{ mb_strtoupper(mb_substr($student->nome, 0, 1)) }}</span><strong>{{ $student->nome }}</strong>
                                </div>
                            </td>
                            <td>{{ $student->numero_matricula }}</td>
                            <td>{{ $student->cpf }}</td>
                            <td>{{ $student->email ?: '—' }}</td>
                            <td><x-status :value="$student->situacao" /></td>
                            <td class="row-actions">
                                <a class="icon-button" href="{{ route('alunos.edit', $student) }}"
                                    aria-label="Editar {{ $student->nome }}"><svg viewBox="0 0 24 24">
                                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />
                                    </svg></a>
                                <form method="POST" action="{{ route('alunos.destroy', $student) }}"
                                    data-confirm="Excluir o aluno {{ $student->nome }}?">
                                    @csrf @method('DELETE')
                                    <button class="icon-button danger" type="submit"
                                        aria-label="Excluir {{ $student->nome }}"><svg viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7" />
                                        </svg></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state"><strong>Nenhum aluno encontrado</strong><span>Cadastre um aluno ou
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
