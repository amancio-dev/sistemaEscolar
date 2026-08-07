@extends('layouts.app')
@section('title', 'Editar disciplina · Sistema Escolar')
@section('breadcrumb', 'Editar disciplina')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Editar disciplina"
        description="Atualize os dados de {{ $record->nome }}." />
    <form class="panel record-form" method="POST" action="{{ route('disciplinas.update', $record) }}">@csrf @method('PUT')
        @include('disciplinas._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('disciplinas.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
