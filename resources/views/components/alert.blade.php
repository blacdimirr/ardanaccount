@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $typeClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ][$type] ?? 'alert-info';
@endphp

<div {{ $attributes->merge(['class' => trim("alert {$typeClass}" . ($dismissible ? ' alert-dismissible fade show' : ''))]) }} role="alert">
    {{ $slot }}
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
    @endif
</div>
