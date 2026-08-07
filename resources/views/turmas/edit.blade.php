@extends('layouts.app')
@section('title', 'Editar turma · Sistema Escolar')
@section('breadcrumb', 'Editar turma')
@section('content')
    <x-page-header eyebrow="ORGANIZAÇÃO ACADÊMICA" title="Editar turma"
        description="Atualize os dados de {{ $record->nome }}." />
    <form class="panel record-form" method="POST" action="{{ route('turmas.update', $record) }}">@csrf @method('PUT')
        @include('turmas._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('turmas.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
