@extends('layouts.app')

@section('title', 'Meu perfil · Sistema Escolar')
@section('breadcrumb', 'Meu perfil')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">MINHA CONTA</p>
            <h1>Meu perfil</h1>
            <p class="page-description">Atualize seus dados pessoais e mantenha sua senha segura.</p>
        </div>
    </section>

    <div class="profile-layout">
        <aside class="panel profile-card">
            <span class="profile-card-avatar">{{ Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
            <strong class="profile-card-name">{{ $user->name }}</strong>
            <span class="profile-card-email">{{ $user->email }}</span>
            <span class="status status-{{ $user->situacao }} profile-card-status">{{ ucfirst($user->situacao) }}</span>
            <dl class="profile-card-meta">
                <div>
                    <dt>Perfil de acesso</dt>
                    <dd>{{ ucfirst($user->tipo_usuario) }}</dd>
                </div>
                <div>
                    <dt>Membro desde</dt>
                    <dd>{{ $user->created_at->translatedFormat('d \d\e F \d\e Y') }}</dd>
                </div>
            </dl>
        </aside>

        <div class="profile-forms">
            <section class="panel record-form">
                <div class="form-section">
                    <div class="form-section-heading">
                        <span>1</span>
                        <div>
                            <h2>Dados pessoais</h2>
                            <p>Nome e e-mail usados para identificação e acesso ao portal.</p>
                        </div>
                    </div>

                    <form id="form-account" method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="field field-full">
                                <label for="name">Nome completo <b>*</b></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="field field-full">
                                <label for="email">E-mail institucional <b>*</b></label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="form-actions">
                    <button type="submit" form="form-account" class="primary-button">Salvar alterações</button>
                </div>
            </section>

            <section class="panel record-form">
                <div class="form-section">
                    <div class="form-section-heading">
                        <span>2</span>
                        <div>
                            <h2>Segurança</h2>
                            <p>Altere sua senha periodicamente para manter sua conta protegida.</p>
                        </div>
                    </div>

                    <form id="form-password" method="POST" action="{{ route('profile.password') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="field field-full">
                                <label for="current_password">Senha atual <b>*</b></label>
                                <div class="auth-password-field">
                                    <input type="password" id="current_password" name="current_password" placeholder="Sua senha atual" required autocomplete="current-password">
                                    <button type="button" class="auth-password-toggle" data-toggle-password="current_password" aria-label="Mostrar senha">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="field">
                                <label for="password">Nova senha <b>*</b></label>
                                <div class="auth-password-field">
                                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                                    <button type="button" class="auth-password-toggle" data-toggle-password="password" aria-label="Mostrar senha">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="field">
                                <label for="password_confirmation">Confirmar nova senha <b>*</b></label>
                                <div class="auth-password-field">
                                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repita a nova senha" required autocomplete="new-password">
                                    <button type="button" class="auth-password-toggle" data-toggle-password="password_confirmation" aria-label="Mostrar senha">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="form-actions">
                    <button type="submit" form="form-password" class="primary-button">Alterar senha</button>
                </div>
            </section>
        </div>
    </div>
@endsection
