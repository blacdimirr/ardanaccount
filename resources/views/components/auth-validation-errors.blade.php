@props(['errors'])

@if ($errors->any())
    <x-alert type="danger" {{ $attributes }}>
        <p class="mb-1">{{ __('¡Ups! Algo salió mal.') }}</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
