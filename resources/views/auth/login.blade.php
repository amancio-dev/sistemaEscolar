@extends('layouts.auth')

@section('title', 'Entrar · Sistema Escolar')

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

    <p class="eyebrow">Acesso ao portal</p>
    <h1 class="auth-title">Entrar na sua conta</h1>
    <p class="auth-subtitle">Informe suas credenciais institucionais para acessar o sistema.</p>

    <form class="auth-form" method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        @php
            $userTypes = ['administrador', 'professor', 'aluno'];
            $selectedUserType = in_array(old('tipo_usuario'), $userTypes, true) ? old('tipo_usuario') : 'administrador';
        @endphp

        <div class="field">
            <label for="tipo_usuario">Perfil de acesso <b>*</b></label>
            <select id="tipo_usuario" name="tipo_usuario" required autofocus data-login-user-type>
                <option value="administrador" @selected($selectedUserType === 'administrador')>Admin</option>
                <option value="professor" @selected($selectedUserType === 'professor')>Professor</option>
                <option value="aluno" @selected($selectedUserType === 'aluno')>Aluno</option>
            </select>
        </div>

        <div class="field">
            <label for="email">E-mail institucional <b>*</b></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seunome@instituicao.edu.br" required autocomplete="username">
        </div>

        <div class="field" data-login-password-field @if ($selectedUserType !== 'administrador') hidden @endif>
            <label for="password">Senha <b>*</b></label>
            <div class="auth-password-field">
                <input type="password" id="password" name="password" placeholder="••••••••" @required($selectedUserType === 'administrador') @disabled($selectedUserType !== 'administrador') autocomplete="current-password">
                <button type="button" class="auth-password-toggle" data-toggle-password="password" aria-label="Mostrar senha">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                </button>
            </div>
        </div>

        <div class="field" data-login-cpf-field @if ($selectedUserType === 'administrador') hidden @endif>
            <label for="cpf">CPF <b>*</b></label>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00"
                inputmode="numeric" maxlength="14" data-mask="cpf" @required($selectedUserType !== 'administrador')
                @disabled($selectedUserType === 'administrador') autocomplete="off" aria-describedby="login-cpf-hint">
            <p class="auth-hint" id="login-cpf-hint">Para alunos e professores, o CPF é a credencial de acesso.</p>
        </div>

        <div class="auth-form-row">
            <label class="auth-checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Manter conectado</span>
            </label>
            <a class="text-link" href="{{ route('password.request') }}" data-admin-password-link
                @if ($selectedUserType !== 'administrador') hidden @endif>Esqueci minha senha</a>
        </div>

        <button type="submit" class="primary-button auth-submit">
            <span>Entrar</span>
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </form>

    <p class="auth-switch">
        Ainda não possui cadastro?
        <a href="{{ route('register') }}">Criar conta</a>
    </p>
@endsection
