<x-guest-layout>
    <div class="text-center lg:text-left">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">
            Account Aanmaken
        </h2>
        <p class="text-gray-600 mb-8">
            Maak een account aan om toegang te krijgen tot alle Canvas rapportage features
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Volledige naam')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Jouw volledige naam" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('E-mailadres')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="jouw.naam@tcr.nl" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Wachtwoord')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="new-password"
                          placeholder="Minimaal 8 karakters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Wachtwoord bevestigen')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password"
                          placeholder="Herhaal je wachtwoord" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full">
                <i class="fas fa-user-plus mr-2"></i>
                {{ __('Account Aanmaken') }}
            </x-primary-button>
        </div>

        <div class="text-center pt-4">
            <p class="text-sm text-gray-600">
                Al een account?
                <a href="{{ route('login') }}" class="text-tcr-green hover:underline font-medium">
                    Log hier in
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
