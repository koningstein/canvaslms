<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="{{ asset('img/fav.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v5.12.1/css/pro.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
    <script src="{{ asset('js/scripts.js') }}" defer></script>
    @stack('styles')
    <!-- Alpine.js -->
{{--    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>--}}
    @livewireStyles
    <title>TCR Canvas Tool</title>
</head>
<body class="bg-gray-100">

<!-- Simple Navbar - NO HAMBURGER EVER -->
<nav class="bg-green-900 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">

            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('page.home') }}" class="flex items-center">
                    <img src="{{ asset('img/Logo_Techniekcollege_RGB_150_dpi.png') }}"
                         alt="TCR" class="h-10 mr-3">
                    <span class="text-white font-bold text-xl">Canvas Tool</span>
                </a>
            </div>

            <!-- Menu Items - ALWAYS VISIBLE -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('dashboard') }}"
                   class="text-white hover:text-yellow-300 px-3 py-2 text-sm font-medium">
                    Dashboard
                </a>

                <a href="{{ route('courses.index') }}"
                   class="text-white hover:text-yellow-300 px-3 py-2 text-sm font-medium">
                    Course Selector
                </a>

                <!-- Simple Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="text-white hover:text-yellow-300 px-3 py-2 text-sm font-medium">
                        Rapportage ▼
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Voortgang Analyse</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export Opties</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Statistieken</a>
                    </div>
                </div>

                <a href="#"
                   class="text-white hover:text-yellow-300 px-3 py-2 text-sm font-medium">
                    Tools
                </a>
            </div>

            <!-- User Section -->
            <div class="flex items-center">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center text-white hover:text-yellow-300 text-sm">
                            <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center mr-2">
                                <span class="text-white font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <span class="ml-1">▼</span>
                        </button>

                        <div x-show="open"
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200">
                            <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-200">
                                {{ Auth::user()->email }}
                            </div>
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profiel
                            </a>
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Uitloggen
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                       class="bg-green-800 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Inloggen
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="min-h-screen">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('topmenu')
        @yield('content')
    </div>
</main>
@stack('scripts')
@livewireScripts
</body>
</html>
