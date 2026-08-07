@extends('layouts.app')
@section('title', 'Cadastrar turma · Sistema Escolar')
@section('breadcrumb', 'Cadastrar turma')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Cadastrar turma"
        description="Defina os dados de organização da nova turma." />
    <form class="panel record-form" method="POST" action="{{ route('turmas.store') }}">@csrf @include('turmas._form')<div
            class="form-actions"><a class="secondary-button" href="{{ route('turmas.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar turma</button></div>
    </form>
@endsection
