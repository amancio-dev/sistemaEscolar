@extends('layouts.app')
@section('title', 'Editar frequência · Sistema Escolar')
@section('breadcrumb', 'Editar frequência')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Editar frequência"
        description="Atualize os dados do registro selecionado." />
    <form class="panel record-form" method="POST" action="{{ route('frequencias.update', $record) }}">@csrf @method('PUT')
        @include('frequencias._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('frequencias.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
