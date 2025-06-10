<x-guest-layout>
    <div class="text-center lg:text-left">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">
            Inloggen
        </h2>
        <p class="text-gray-600 mb-8">
            Vul je gegevens in om toegang te krijgen tot je dashboard
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('E-mailadres')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="jouw.naam@tcr.nl" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Wachtwoord')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password"
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-tcr-green shadow-sm focus:ring-tcr-green" name="remember" style="color: #386049;">
                <span class="ml-2 text-sm text-gray-600">{{ __('Onthoud mij') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-tcr-green hover:underline" href="{{ route('password.request') }}">
                    {{ __('Wachtwoord vergeten?') }}
                </a>
            @endif
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full">
                <i class="fas fa-sign-in-alt mr-2"></i>
                {{ __('Inloggen') }}
            </x-primary-button>
        </div>

        <div class="text-center pt-4">
            <p class="text-sm text-gray-600">
                Nog geen account?
                <a href="{{ route('register') }}" class="text-tcr-green hover:underline font-medium">
                    Registreer hier
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
