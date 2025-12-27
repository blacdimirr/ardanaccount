@php
    $messages = [
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
        'status' => session('status'),
    ];

    $hasMessages = collect($messages)->filter()->isNotEmpty() || $errors->any();
@endphp

@if ($hasMessages)
    <div {{ $attributes->merge(['class' => 'mb-3']) }}>
        @if ($messages['success'])
            <x-alert type="success" class="mb-3">{!! $messages['success'] !!}</x-alert>
        @endif

        @if ($messages['error'])
            <x-alert type="danger" class="mb-3">{!! $messages['error'] !!}</x-alert>
        @endif

        @if ($messages['warning'])
            <x-alert type="warning" class="mb-3">{!! $messages['warning'] !!}</x-alert>
        @endif

        @if ($messages['info'])
            <x-alert type="info" class="mb-3">{!! $messages['info'] !!}</x-alert>
        @endif

        @if ($messages['status'])
            <x-alert type="success" class="mb-3">{!! $messages['status'] !!}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="danger" class="mb-0">
                <p class="mb-1">{{ __('Hay errores en el formulario. Revisa los campos resaltados.') }}</p>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>
@endif
