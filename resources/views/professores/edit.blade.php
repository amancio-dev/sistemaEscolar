@extends('layouts.app')
@section('title', 'Editar professor · Sistema Escolar')
@section('breadcrumb', 'Editar professor')
@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Editar professor"
        description="Atualize os dados de {{ $record->nome }}." />
    <form class="panel record-form" method="POST" action="{{ route('professores.update', $record) }}">@csrf @method('PUT')
        @include('professores._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('professores.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
