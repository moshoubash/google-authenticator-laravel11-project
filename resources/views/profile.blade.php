<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Two-Factor Authentication') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Add additional security to your account using two-factor authentication.') }}
                        </p>
                    </header>

                    <div class="mt-6">
                        @if (auth()->user()->google2fa_secret)
                            <form method="POST" action="{{ route('2fa.disable') }}">
                                @csrf
                                <x-danger-button>
                                    {{ __('Disable 2FA') }}
                                </x-danger-button>
                            </form>
                        @else
                            <a href="{{ route('2fa.enable') }}">
                                <x-primary-button>
                                    {{ __('Enable 2FA') }}
                                </x-primary-button>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
