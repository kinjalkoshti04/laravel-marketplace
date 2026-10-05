@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         {{ $attributes->merge(['class' => 'mb-6 flex items-center justify-between rounded-md bg-green-50 px-4 py-3 text-sm text-green-800']) }}>
        <span>{{ session('status') }}</span>
        <button type="button" @click="show = false" class="ms-4 text-green-700 hover:text-green-900" aria-label="Dismiss">&times;</button>
    </div>
@endif
