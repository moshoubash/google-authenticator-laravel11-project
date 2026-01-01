# Google Authenticator Integration Guide (Code Reference)

This guide provides the exact code and steps required to integrate Google Authenticator (2FA) into a Laravel 11 application. Follow these steps sequentially to replicate the feature.

## 1. Install Packages
Run the following command to install the Google2FA package and the QR code generator.
```bash
composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
```

## 2. Publish Configuration
Publish the package configuration file.
```bash
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
```

## 3. Register Service Provider & Alias
In Laravel 11, you might need to manually register the provider if auto-discovery fails or implementation details require it.
Open **`config/app.php`** and ensure the `providers` and `aliases` arrays are configured (merging with defaults if necessary).

```php
// config/app.php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    // ... (other config)

    'aliases' => Facade::defaultAliases()->merge([
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,
    ])->toArray(),

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */
        PragmaRX\Google2FALaravel\ServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\VoltServiceProvider::class, // if using Volt
    ])->toArray(),
];
```

## 4. Database Setup
Create a migration to add the secret column to the users table.

```bash
php artisan make:migration add_google2fa_column_to_users_table --table=users
```

**Migration File Content:**
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->text('google2fa_secret')->nullable()->after('password');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('google2fa_secret');
    });
}
```
Run the migration:
```bash
php artisan migrate
```

## 5. Register Middleware
Register the middleware alias in **`bootstrap/app.php`**.

```php
// bootstrap/app.php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
        ]);
    })
    // ...
```

## 6. Create Controller
Create **`app/Http/Controllers/Google2FAController.php`**.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Google2FAController extends Controller
{
    public function enableTwoFactor(Request $request)
    {
        $google2fa = app('pragmarx.google2fa');
        $user = $request->user();

        // 1. Generate Secret
        $secret = $google2fa->generateSecretKey();

        // 2. Save Secret to User
        $user->google2fa_secret = $secret;
        $user->save();

        // 3. Generate QR Code
        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('google2fa.enable', ['QR_Image' => $QR_Image, 'secret' => $secret]);
    }

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();
        $user->google2fa_secret = null;
        $user->save();

        return redirect('profile')->with('status', 'Two-Factor Authentication disabled.');
    }
}
```

## 7. Create Views
Create the directory `resources/views/google2fa`.

**A. Enable Page (`resources/views/google2fa/enable.blade.php`)**
```blade
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
                    
                    <p class="mb-4">Scan the QR code below with your Google Authenticator app.</p>

                    <div class="mb-4 inline-block">
                        {!! $QR_Image !!}
                    </div>

                    <p class="mb-4">Or enter this key manually: <strong>{{ $secret }}</strong></p>

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
```

**B. Verification Page (`resources/views/google2fa/index.blade.php`)**
*This is the view config `google2fa.php` looks for by default.*

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('2FA Verification') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">One Time Password</h3>
                    <p class="mb-4">Please enter the One Time Password from your Google Authenticator app.</p>

                    <form action="{{ route('2fa.verify') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <input type="text" name="one_time_password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required autofocus autocomplete="one-time-code" />
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Verify
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## 8. Define Routes
Update **`routes/web.php`** to protect routes and add management routes.

```php
use App\Http\Controllers\Google2FAController;

// Protect Dashboard or other routes
Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

// Protect Profile
Route::middleware(['auth', '2fa'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

// 2FA Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/enable', [Google2FAController::class, 'enableTwoFactor'])->name('2fa.enable');
    Route::post('/2fa/disable', [Google2FAController::class, 'disableTwoFactor'])->name('2fa.disable');
    
    // The route the verification form submits to
    Route::post('/2fa/verify', function () {
        return redirect(route('dashboard'));
    })->name('2fa.verify')->middleware('2fa');
    
    // Note: The '2fa' middleware on the verify route handles the verification logic automatically.
});
```

## 9. Update User Profile
Add the buttons to your profile page (e.g., `resources/views/profile.blade.php`).

```blade
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
```
