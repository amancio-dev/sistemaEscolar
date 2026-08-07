@extends('layouts.app')

@section('title', 'Acesso não permitido · Sistema Escolar')
@section('breadcrumb', 'Acesso não permitido')

@section('content')
    <section class="panel linkage-notice">
        <span class="linkage-notice-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" /></svg>
        </span>
        <div>
            <p class="eyebrow">PERMISSÃO DO PERFIL</p>
            <h2>Acesso não permitido</h2>
            <p>{{ $exception->getMessage() ?: 'Seu perfil não possui permissão para acessar esta área.' }}</p>
            <a class="primary-button" href="{{ route('inicio') }}" style="margin-top:16px">Voltar ao meu painel</a>
        </div>
    </section>
@endsection
