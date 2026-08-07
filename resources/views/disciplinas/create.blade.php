@extends('layouts.app')
@section('title', 'Cadastrar disciplina · Sistema Escolar')
@section('breadcrumb', 'Cadastrar disciplina')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Cadastrar disciplina"
        description="Adicione uma disciplina à grade curricular." />
    <form class="panel record-form" method="POST" action="{{ route('disciplinas.store') }}">@csrf @include('disciplinas._form')
        <div class="form-actions"><a class="secondary-button" href="{{ route('disciplinas.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar disciplina</button></div>
    </form>
@endsection
