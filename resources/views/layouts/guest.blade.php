<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCR Canvas Tool - Student Voortgang Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v5.12.1/css/pro.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #386049 0%, #2d4f3b 100%);
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .hero-illustration {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .btn-tcr-green {
            background-color: #386049;
        }

        .btn-tcr-green:hover {
            background-color: #2d4f3b;
        }

        .text-tcr-green {
            color: #386049;
        }

        .bg-tcr-green {
            background-color: #386049;
        }

        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-gray-50">
<!-- Navigation -->
<nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <img src="/img/Logo_Techniekcollege_RGB_150_dpi.png" alt="Techniekcollege Rotterdam" class="h-10 mr-4">
                <h1 class="text-xl font-bold text-gray-900">Canvas Tool</h1>
            </div>

            <div class="flex items-center space-x-4">
                <a href="/login" class="btn-tcr-green text-white hover:bg-opacity-80 px-4 py-2 rounded-lg transition duration-200 font-medium">
                    Inloggen
                </a>
                <a href="/register" class="btn-tcr-green text-white px-4 py-2 rounded-lg hover:bg-opacity-80 transition duration-200 font-medium">
                    Registreren
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Compact Hero Section -->
<section class="bg-tcr-green hero-illustration relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
        <div class="inline-block bg-white bg-opacity-20 rounded-full p-2 mb-4">
            <i class="fas fa-chart-line text-white text-xl"></i>
        </div>

        @if (request()->routeIs('login'))
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 leading-tight">
                Welkom Terug
            </h1>
            <p class="text-sm text-green-100 max-w-xl mx-auto leading-relaxed">
                Log in om toegang te krijgen tot je Canvas dashboard
            </p>
        @elseif (request()->routeIs('register'))
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 leading-tight">
                Account Aanmaken
            </h1>
            <p class="text-sm text-green-100 max-w-xl mx-auto leading-relaxed">
                Gratis toegang tot alle Canvas rapportage tools
            </p>
        @else
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 leading-tight">
                TCR Canvas Tool
            </h1>
            <p class="text-sm text-green-100 max-w-xl mx-auto leading-relaxed">
                Student voortgang monitoring voor MBO docenten
            </p>
        @endif
    </div>
</section>

<!-- Main Form Content (replaces Features Section) -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (request()->routeIs('login'))
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Inloggen op je Account
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Vul je gegevens in om toegang te krijgen tot je persoonlijke Canvas dashboard
                </p>
            </div>
        @elseif (request()->routeIs('register'))
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Maak je Account Aan
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Registreer je gratis en krijg direct toegang tot alle rapportage features
                </p>
            </div>
        @else
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Krachtige Features voor Docenten
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Alles wat je nodig hebt om je studenten effectief te begeleiden en hun voortgang te monitoren
                </p>
            </div>
        @endif

        <!-- Form Container -->
        <div class="max-w-md mx-auto">
            {{ $slot }}
        </div>
    </div>
</section>
<!-- CTA Section -->
<section class="py-20 bg-tcr-green">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        @if (request()->routeIs('login'))
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Klaar om aan de Slag?
            </h2>
            <p class="text-xl text-green-100 mb-8">
                Log in en krijg direct toegang tot je persoonlijke dashboard met alle Canvas rapportage tools.
                Start vandaag nog met het analyseren van student voortgang.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('register') }}" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>
                    Nog geen account? Registreer hier
                </a>
            </div>
        @elseif (request()->routeIs('register'))
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Bijna Klaar om te Beginnen!
            </h2>
            <p class="text-xl text-green-100 mb-8">
                Registreer je account en krijg direct toegang tot alle professionele rapportage features.
                Gratis voor alle TCR docenten - geen verborgen kosten.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('login') }}" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Al een account? Log hier in
                </a>
            </div>
        @else
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Klaar om te Beginnen?
            </h2>
            <p class="text-xl text-green-100 mb-8">
                Maak vandaag nog een account aan en krijg direct toegang tot alle features.
                Gratis voor alle TCR docenten.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('register') }}" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>
                    Account Aanmaken
                </a>
                <a href="{{ route('login') }}" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Direct Inloggen
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center mb-4">
                    <img src="/img/Logo_Techniekcollege_RGB_150_dpi.png" alt="TCR" class="h-8 mr-3 filter brightness-0 invert">
                    <h3 class="text-lg font-semibold">Canvas Tool</h3>
                </div>
                <p class="text-gray-400">
                    Student voortgang monitoring tool voor Techniekcollege Rotterdam docenten.
                </p>
            </div>

            <div>
                <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="/login" class="hover:text-white transition duration-200">Inloggen</a></li>
                    <li><a href="/register" class="hover:text-white transition duration-200">Registreren</a></li>
                    <li><a href="#" class="hover:text-white transition duration-200">Documentatie</a></li>
                    <li><a href="#" class="hover:text-white transition duration-200">Support</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-semibold mb-4">Contact</h4>
                <div class="text-gray-400 space-y-2">
                    <p><i class="fas fa-envelope mr-2"></i> support@tcr.nl</p>
                    <p><i class="fas fa-phone mr-2"></i> (010) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt mr-2"></i> Rotterdam, Nederland</p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; 2024 Techniekcollege Rotterdam. Alle rechten voorbehouden.</p>
        </div>
    </div>
</footer>

<!-- JavaScript for smooth scrolling and interactions -->
<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add subtle animations on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>
</body>
</html>
