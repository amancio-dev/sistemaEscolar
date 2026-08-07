@extends('layouts.app')
@section('title', 'Cadastrar frequência · Sistema Escolar')
@section('breadcrumb', 'Cadastrar frequência')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Cadastrar frequência"
        description="Registre a presença de um aluno em aula." />
    <form class="panel record-form" method="POST" action="{{ route('frequencias.store') }}">@csrf @include('frequencias._form')
        <div class="form-actions"><a class="secondary-button" href="{{ route('frequencias.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar frequência</button></div>
    </form>
@endsection
