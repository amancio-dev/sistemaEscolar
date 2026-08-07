@extends('layouts.app')
@section('title', 'Editar nota · Sistema Escolar')
@section('breadcrumb', 'Editar nota')
@section('content')
    <x-page-header eyebrow="ROTINA ACADÊMICA" title="Editar nota" description="Atualize os dados da avaliação selecionada." />
    <form class="panel record-form" method="POST" action="{{ route('notas.update', $record) }}">@csrf @method('PUT')
        @include('notas._form')<div class="form-actions"><a class="secondary-button"
                href="{{ route('notas.index') }}">Cancelar</a><button class="primary-button" type="submit">Salvar
                alterações</button></div>
    </form>
@endsection
