@extends('layouts.app')

@section('title', 'Editar aluno · Sistema Escolar')
@section('breadcrumb', 'Editar aluno')

@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Editar aluno" description="Atualize os dados de {{ $record->nome }}." />

    <form class="panel record-form" method="POST" action="{{ route('alunos.update', $record) }}">
        @csrf @method('PUT')
        @include('alunos._form')
        <div class="form-actions">
            <a class="secondary-button" href="{{ route('alunos.index') }}">Cancelar</a>
            <button class="primary-button" type="submit">Salvar alterações</button>
        </div>
    </form>
@endsection
