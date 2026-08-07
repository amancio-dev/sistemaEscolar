@extends('layouts.app')
@section('title', 'Cadastrar nota · Sistema Escolar')
@section('breadcrumb', 'Cadastrar nota')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Cadastrar nota" description="Registre o resultado de uma avaliação." />
    <form class="panel record-form" method="POST" action="{{ route('notas.store') }}">@csrf @include('notas._form')<div
            class="form-actions"><a class="secondary-button" href="{{ route('notas.index') }}">Cancelar</a><button
                class="primary-button" type="submit">Salvar nota</button></div>
    </form>
@endsection
