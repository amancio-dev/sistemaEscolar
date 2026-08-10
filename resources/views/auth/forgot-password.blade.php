@extends('layouts.auth')

@section('title', 'Recuperar senha · Sistema Escolar')

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

    <a class="auth-back-link" href="{{ route('login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
        <span>Voltar para o login</span>
    </a>

    <p class="eyebrow">Recuperação de acesso</p>
    <h1 class="auth-title">Esqueceu sua senha?</h1>
    <p class="auth-subtitle">Informe o e-mail da conta administrativa. Enviaremos um link seguro para redefinir sua senha.</p>

    <form class="auth-form" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">E-mail institucional <b>*</b></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seunome@instituicao.edu.br" required autofocus autocomplete="username">
        </div>

        <button type="submit" class="primary-button auth-submit">
            <span>Enviar link de redefinição</span>
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </form>

    <p class="auth-switch">
        Lembrou a senha?
        <a href="{{ route('login') }}">Entrar</a>
    </p>
@endsection
