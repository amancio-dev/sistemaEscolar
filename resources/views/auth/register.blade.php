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
    <p class="auth-subtitle">Preencha os dados abaixo para solicitar acesso ao portal acadêmico.</p>

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

        <div class="field">
            <label for="tipo_usuario">Perfil de acesso <b>*</b></label>
            <select id="tipo_usuario" name="tipo_usuario" required>
                <option value="" disabled {{ old('tipo_usuario') ? '' : 'selected' }}>Selecione um perfil</option>
                <option value="aluno" {{ old('tipo_usuario') === 'aluno' ? 'selected' : '' }}>Aluno</option>
                <option value="professor" {{ old('tipo_usuario') === 'professor' ? 'selected' : '' }}>Professor</option>
                <option value="administrador" {{ old('tipo_usuario') === 'administrador' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>

        <div id="cpf-field" class="field" hidden>
            <label for="cpf">CPF <b>*</b></label>
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" inputmode="numeric" maxlength="14" autocomplete="off">
            <p class="auth-hint">Alunos e professores usam o CPF (somente números) como senha de acesso.</p>
        </div>

        <div id="password-fields" class="form-grid form-grid--auth">
            <div class="field">
                <label for="password">Senha <b>*</b></label>
                <div class="auth-password-field">
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    <button type="button" class="auth-password-toggle" data-toggle-password="password" aria-label="Mostrar senha">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmar senha <b>*</b></label>
                <div class="auth-password-field">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repita a senha" autocomplete="new-password">
                    <button type="button" class="auth-password-toggle" data-toggle-password="password_confirmation" aria-label="Mostrar senha">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <p id="password-hint" class="auth-hint">A senha deve ter ao menos 8 caracteres, incluindo letras e números.</p>

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
