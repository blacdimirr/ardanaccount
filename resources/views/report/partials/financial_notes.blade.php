@if(isset($notes) && $notes->isNotEmpty())
    <div class="mt-4">
        <h6 class="mb-3">{{ __('Notas a los estados financieros') }}</h6>
        <div class="list-group">
            @foreach ($notes as $note)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $note->codigo_nota }}</strong> - {{ $note->titulo }}
                            <div class="text-muted small">{{ __('Period') }}: {{ optional($note->periodo)->format('Y-m-d') }}</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#nota-{{ $note->id }}" aria-expanded="false"
                            aria-controls="nota-{{ $note->id }}">
                            {{ __('View') }}
                        </button>
                    </div>
                    <div class="collapse mt-3" id="nota-{{ $note->id }}">
                        <div class="border rounded bg-light p-3">
                            {!! nl2br(e($note->contenido)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
