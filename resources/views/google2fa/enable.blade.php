<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Set up Google Authenticator</h3>

                    <p class="mb-4">
                        Scan the QR code below with your Google Authenticator app.
                    </p>

                    <div class="mb-4 inline-block">
                        {!! $QR_Image !!}
                    </div>

                    <p class="mb-4">
                        Or enter this key manually: <strong>{{ $secret }}</strong>
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Done
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
