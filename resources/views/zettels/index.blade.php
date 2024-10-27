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
    </div>
</x-app-layout>