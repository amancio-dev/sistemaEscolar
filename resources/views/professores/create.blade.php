@extends('layouts.app')
@section('title', 'Cadastrar professor · Sistema Escolar')
@section('breadcrumb', 'Cadastrar professor')
@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Cadastrar professor"
        description="Preencha os dados abaixo para adicionar um docente." />
    <form class="panel record-form" method="POST" action="{{ route('professores.store') }}">@csrf @include('professores._form')
        <div class="form-actions"><a class="secondary-button" href="{{ route('professores.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar professor</button></div>
    </form>
@endsection
