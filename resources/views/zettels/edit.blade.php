<x-app-layout>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('zettels.update', $zettel) }}">
            @csrf
            @method('patch')

            <!-- Campo Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $zettel->title) }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <!-- Campo Body -->
            <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-700">{{ __('Body') }}</label>
                <textarea
                    name="body"
                    id="body"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >{{ old('body', $zettel->body) }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <!-- Campo Reference -->
            <div class="mb-4">
                <label for="reference" class="block text-sm font-medium text-gray-700">{{ __('Reference') }}</label>
                <input
                    type="text"
                    name="reference"
                    id="reference"
                    value="{{ old('reference', $zettel->reference) }}"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>

            <div class="mt-4 space-x-2">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <a href="{{ route('zettels.index') }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>
