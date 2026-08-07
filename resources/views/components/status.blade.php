@props(['value'])

@php
    $labels = [
        'ativo' => 'Ativo',
        'ativa' => 'Ativa',
        'inativo' => 'Inativo',
        'inativa' => 'Inativa',
        'afastado' => 'Afastado',
        'transferido' => 'Transferido',
        'transferida' => 'Transferida',
        'concluido' => 'Concluído',
        'concluida' => 'Concluída',
        'trancada' => 'Trancada',
        'cancelada' => 'Cancelada',
        'presente' => 'Presente',
        'ausente' => 'Ausente',
        'justificada' => 'Justificada',
        'atrasado' => 'Atrasado',
    ];
@endphp

<span class="status status-{{ $value }}">{{ $labels[$value] ?? ucfirst($value) }}</span>
