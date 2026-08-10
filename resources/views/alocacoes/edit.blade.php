@extends('layouts.app')

@section('title', 'Editar alocação docente · Sistema Escolar')
@section('breadcrumb', 'Editar alocação docente')

@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Editar alocação docente"
        description="Atualize o vínculo entre professor, disciplina e turma." />

    <form class="panel record-form" method="POST" action="{{ route('alocacoes.update', $record) }}">
        @csrf
        @method('PUT')
        @include('alocacoes._form')

        <div class="form-actions">
            <a class="secondary-button" href="{{ route('alocacoes.index') }}">Cancelar</a>
            <button class="primary-button" type="submit">Salvar alterações</button>
        </div>
    </form>
@endsection
