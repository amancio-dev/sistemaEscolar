@extends('layouts.app')
@section('title', 'Cadastrar matrícula · Sistema Escolar')
@section('breadcrumb', 'Cadastrar matrícula')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Cadastrar matrícula"
        description="Vincule um aluno a uma turma para o ano letivo." />
    <form class="panel record-form" method="POST" action="{{ route('matriculas.store') }}">@csrf @include('matriculas._form')
        <div class="form-actions"><a class="secondary-button" href="{{ route('matriculas.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar matrícula</button></div>
    </form>
@endsection
