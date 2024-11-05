<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('zettels.store') }}">
            @csrf
            <div>
                <label for="title">{{ __('Title') }}</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    placeholder="{{ __('Enter the title') }}"
                    value="{{ old('title') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <div class="mt-4">
                <textarea
                    name="body"
                    placeholder="{{ __('What\'s on your mind?') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">{{ old('body') }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div class="mt-4">
                <label for="reference">{{ __('Reference') }}</label>
                <input
                    type="text"
                    name="reference"
                    id="reference"
                    placeholder="{{ __('Enter a reference (optional)') }}"
                    value="{{ old('reference') }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>
            <x-primary-button class="mt-4">{{ __('Zettel') }}</x-primary-button>
        </form>

        <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
            @foreach ($zettels as $zettel)
            <div class="p-6 flex space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 -scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-gray-800">{{ $zettel->user->name }}</span>
                            <small class="ml-2 text-sm text-gray-600">{{ $zettel->created_at->format('j M Y, g:i a') }}</small>
                        </div>
                        @unless ($zettel->created_at->eq($zettel->updated_at))
                        <small class="text-sm text-gray-600"> &middot; {{ __('edited') }}</small>
                        @endunless
                    </div>
                    <h2 class="mt-4 text-lg text-gray-900">{{ $zettel->title }}</h2>
                    <p class="mt-2 text-gray-800">{{ $zettel->body }}</p>
                    @if ($zettel->reference)
                    <small class="mt-2 text-sm text-gray-600">Referencia: {{ $zettel->reference }}</small>
                    @endif

                    @if ($zettel->user->is(auth()->user()))
                    <x-dropdown>
                        <x-slot name="trigger">
                            <button>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('zettels.edit', $zettel)">
                                {{ __('Edit') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

    </div>
</x-app-layout>