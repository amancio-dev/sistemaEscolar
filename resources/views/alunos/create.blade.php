@extends('layouts.app')

@section('title', 'Cadastrar aluno · Sistema Escolar')
@section('breadcrumb', 'Cadastrar aluno')

@section('content')
    <x-page-header eyebrow="GESTÃO DE PESSOAS" title="Cadastrar aluno"
        description="Preencha os dados abaixo para adicionar um estudante." />

    <form class="panel record-form" method="POST" action="{{ route('alunos.store') }}">
        @csrf
        @include('alunos._form')
        <div class="form-actions">
            <a class="secondary-button" href="{{ route('alunos.index') }}">Cancelar</a>
            <button class="primary-button" type="submit">Salvar aluno</button>
        </div>
    </form>
@endsection
