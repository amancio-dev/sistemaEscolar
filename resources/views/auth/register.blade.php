@extends('layouts.auth')

@section('title', 'Criar conta · Sistema Escolar')

@section('content')
    <div class="auth-form-mark auth-form-mark--mobile">
        <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 48 48">
                <circle cx="24" cy="24" r="21.5" fill="none" stroke="currentColor" stroke-width="1.4" />
                <path d="M24 12 39 18.5 24 25 9 18.5 24 12Z" fill="currentColor" />
                <path d="M14.5 20.6V27c0 3 4.2 6.2 9.5 6.2s9.5-3.2 9.5-6.2v-6.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M35.5 19v9.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
        </span>
    </div>

    <p class="eyebrow">Novo por aqui</p>
    <h1 class="auth-title">Criar sua conta</h1>
    <p class="auth-subtitle">Crie seu acesso de aluno para consultar suas próprias notas e frequências.</p>

    <form class="auth-form" method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="field">
            <label for="name">Nome completo <b>*</b></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Seu nome completo" required autofocus autocomplete="name">
        </div>

        <div class="field">
            <label for="email">E-mail institucional <b>*</b></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seunome@instituicao.edu.br" required autocomplete="username">
        </div>

        <div id="cpf-field" class="field">
            <label for="cpf">CPF <b>*</b></label>
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00"
                inputmode="numeric" maxlength="14" data-mask="cpf" required autocomplete="off">
            <p class="auth-hint">Sua senha inicial será o CPF com somente números.</p>
        </div>

        <div class="access-notice">
            <strong>Acesso limitado e seguro</strong>
            <span>Contas criadas aqui recebem somente o perfil de aluno. Perfis administrativos são concedidos pela escola.</span>
        </div>

        <button type="submit" class="primary-button auth-submit">
            <span>Criar conta</span>
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </form>

    <p class="auth-switch">
        Já possui cadastro?
        <a href="{{ route('login') }}">Entrar</a>
    </p>
@endsection
