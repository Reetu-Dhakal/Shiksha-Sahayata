@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-medium">{{ $errors->first() }}</p>
        @if ($errors->count() > 1)
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    @if ($error !== $errors->first())
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>
@endif
