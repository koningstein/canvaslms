<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v6.1.2/css/pro.min.css">

    @livewireStyles
    <title>Checklist</title>
    <style>
        .bg-orange-500 {
            background-color: #f59e0b;
        }
        .bg-orange-200 {
            background-color: #fed7aa;
        }
    </style>
    <!-- Alpine.js -->
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.8.2/alpine.js" defer></script>--}}
</head>
<body>
<!-- header -->
<header class="w-full px-6 bg-white">
    <div class="container mx-auto max-w-screen-xl md:flex justify-between items-center">
        <a href="#" class="block py-6 w-full text-center md:text-left md:w-auto text-gray-600 no-underline flex justify-center items-center">
            <img src="{{ asset('img/Logo_Techniekcollege_RGB_150_dpi.png') }}" alt="Techniekcollege Rotterdam" class="h-16 mr-4">
            <span class="font-bold text-xl text-gray-800">Checklist</span>
        </a>
        <div class="w-full md:w-auto mb-6 md:mb-0 text-center md:text-right">
            @guest
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-block no-underline bg-black text-white text-sm py-2 px-3">Sign Up</a>
                @endif
                <a href="{{ route('login') }}" class="inline-block no-underline bg-black text-white text-sm py-2 px-3">Login</a>
            @else
                <div class="flex items-center justify-end space-x-6">
                    <!-- Notification Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative text-gray-500 focus:outline-none focus:text-gray-900">
                            <i class="fad fa-bells text-lg"></i>
                            @if($unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 block h-2 w-2 bg-red-600 rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-20">

                            <!-- Header -->
                            <div class="py-3 px-4 border-b bg-gray-50">
                                <span class="text-sm font-semibold">Notificaties</span>
                                <span class="ml-2 text-xs text-gray-500">({{ $unreadNotifications->count() }})</span>
                            </div>

                            <!-- Notificaties -->
                            <div class="max-h-64 overflow-y-auto">
                                @forelse($unreadNotifications as $notification)
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-50">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                                                <i class="fas fa-exclamation-circle text-teal-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold">{{ $notification->data['message'] ?? 'Nieuwe notificatie' }}</p>
                                                <p class="text-xs text-gray-500">Opdracht: {{ $notification->data['assignment_name'] ?? 'Onbekend' }}</p>
                                                <p class="text-xs text-gray-500">Inleverdatum: {{ $notification->data['duedate'] ?? 'Onbekend' }}</p>
                                                <p class="text-xs text-gray-500">Status: {{ $notification->data['status'] ?? 'Onbekend' }}</p>
                                                <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <p class="px-4 py-3 text-sm text-gray-500">Geen nieuwe notificaties.</p>
                                @endforelse
                            </div>

                            <!-- Footer -->
                            <div class="py-2 px-4 border-t bg-gray-50">
                                <a href="#" class="block text-center text-sm text-teal-600 hover:underline">Bekijk alle notificaties</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Notification Dropdown -->

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <img src="{{ asset('img/user.svg') }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover">
                            <span>{{ Auth::user()->name }}</span>
                            <svg fill="currentColor" viewBox="0 0 20 20" class="w-5 h-5 transition-transform duration-200 transform" :class="{ 'rotate-180': open }">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20">
                            @hasanyrole('keyteacher|admin')
                            <a href="{{ route('admin.periods.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin</a>
                            @endhasanyrole
                            @hasanyrole('teacher')
                            <a href="{{ route('admin.students.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin</a>
                            @endhasanyrole
                            <hr>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log out</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                    <!-- End User Menu -->
                </div>
            @endguest
        </div>
    </div>
</header>
<!-- /header -->

<!-- nav -->
<nav class="w-full bg-white md:pt-0 px-6 relative border-t border-b border-gray-300">
    <div class="container mx-auto max-w-screen-xl md:flex justify-between items-center text-sm md:text-md md:justify-start">
        <div class="w-full md:w-3/4 text-center md:text-left py-4 flex flex-wrap justify-center items-stretch md:justify-start md:items-start">
            <a href="{{ route('page.home') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Home</a>
            <a href="{{ route('page.aboutus') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Over ons</a>
            <a href="{{ route('page.working-system') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Hoe werkt het?</a>
            <a href="{{ route('news.index') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Nieuws</a>
            <a href="{{ route('page.contact') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Contact</a>
            @hasanyrole('student')
            <a href="{{ route('student.results') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Studie voortgang</a>
            <a href="{{ route('student.learning-outcome.index') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Leeruitkomsten</a>
            @endhasanyrole
            @hasanyrole('guardian')
            <a href="{{ route('guardian.dashboard') }}" class="px-2 md:pl-0 md:mr-3 md:pr-3 text-gray-700 no-underline md:border-r border-gray-300">Dashboard</a>
            @endhasanyrole
        </div>
        <div class="w-full md:w-1/4 text-center md:text-right pb-4 md:p-0">
            {{--            <input type="search" placeholder="Search..." class="bg-gray-200 border text-sm p-1" />--}}
        </div>
    </div>
</nav>
<!-- /nav -->

@yield('content')

<!-- about -->
<div class="w-full px-6 py-12 text-left bg-gray-300 text-gray-700 leading-normal">
    <div class="container max-w-screen-xl mx-auto flex justify-center flex-wrap md:flex-no-wrap">
        <div class="w-full md:w-1/2">
            <h3 class="text-3xl mb-8 text-black leading-tight">Over de Studenten Voortgang Checklist</h3>
            <p class="mb-5">De Studenten Voortgang Checklist is ontworpen om studenten, ouders en docenten te helpen bij het nauwkeurig volgen van de studie voortgang. Ons platform biedt een gestroomlijnde manier om prestaties te monitoren, deadlines bij te houden en de voortgang van elke student te evalueren.</p>
            <p class="mb-5">Door inzichtelijke rapportages en eenvoudige tools aan te bieden, willen we het voor docenten gemakkelijker maken om individuele ondersteuning te bieden en studenten te helpen hun leerdoelen te bereiken. Studenten kunnen op elk moment hun eigen voortgang bekijken en direct zien waar ze staan.</p>
            <p>Onze missie is om een positieve impact te hebben op het leerproces door zowel studenten als docenten de middelen te geven om te slagen. We geloven in de kracht van onderwijs en streven ernaar om een tool te bieden die dat onderwijs naar een hoger niveau tilt.</p>
        </div>
        <div class="w-full md:w-1/2 flex justify-center items-center">
            <img src="{{ asset('img/Logo_Techniekcollege_RGB_150_dpi.png') }}" alt="Techniek College Rotterdam" class="w-2/3 h-auto">
        </div>
    </div>
</div>

<!-- /about -->
<!-- footer -->
<footer class="w-full bg-white px-6 border-t">
    <div class="container mx-auto max-w-screen-xl py-6 flex flex-wrap md:flex-no-wrap justify-between items-center text-sm">
        &copy;2024 Marcel Koningstein. All rights reserved.
        <div class="pt-4 md:p-0 text-center md:text-right text-xs">
            <a href="#" class="text-black no-underline hover:underline">Privacy Policy</a>
            <a href="#" class="text-black no-underline hover:underline ml-4">Terms &amp; Conditions</a>
            <a href="#" class="text-black no-underline hover:underline ml-4">Contact Us</a>
        </div>
    </div>
</footer>
<!-- /footer -->

@livewireScripts
</body>
</html>
