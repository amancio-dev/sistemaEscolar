@extends('layouts.app')

@section('title', 'Cadastrar alocação docente · Sistema Escolar')
@section('breadcrumb', 'Cadastrar alocação docente')

@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Cadastrar alocação docente"
        description="Associe um professor a uma disciplina e turma ativas." />

    <form class="panel record-form" method="POST" action="{{ route('alocacoes.store') }}">
        @csrf
        @include('alocacoes._form')

        <div class="form-actions">
            <a class="secondary-button" href="{{ route('alocacoes.index') }}">Cancelar</a>
            <button class="primary-button" type="submit">Salvar alocação</button>
        </div>
    </form>
@endsection
