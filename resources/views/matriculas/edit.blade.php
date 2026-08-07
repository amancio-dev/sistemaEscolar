@extends('layouts.app')
@section('title', 'Editar matrícula · Sistema Escolar')
@section('breadcrumb', 'Editar matrícula')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Editar matrícula"
        description="Atualize os dados da matrícula #{{ $record->id_matricula }}." />
    <form class="panel record-form" method="POST" action="{{ route('matriculas.update', $record) }}">@csrf @method('PUT')
        @include('matriculas._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('matriculas.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
