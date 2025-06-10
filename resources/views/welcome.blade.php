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

<!-- Hero Section -->
<section class="bg-tcr-green hero-illustration relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <div class="inline-block bg-white bg-opacity-20 rounded-full p-3 mb-8 pulse-slow">
            <i class="fas fa-chart-line text-white text-3xl"></i>
        </div>

        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
            Student Voortgang<br>
            <span class="text-green-200">Inzichtelijk Gemaakt</span>
        </h1>

        <p class="text-xl text-green-100 mb-8 max-w-3xl mx-auto leading-relaxed">
            Beheer en analyseer de voortgang van je studenten in Canvas met krachtige rapportage tools
            speciaal ontwikkeld voor MBO Software Developer docenten.
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <a href="/register" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                <i class="fas fa-rocket mr-2"></i>
                Direct Beginnen
            </a>
            <a href="/login" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Inloggen
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Krachtige Features voor Docenten
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Alles wat je nodig hebt om je studenten effectief te begeleiden en hun voortgang te monitoren
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="feature-card bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8 border border-blue-200">
                <div class="bg-blue-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-palette text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Kleur Overzichten</h3>
                <p class="text-gray-700 leading-relaxed">
                    Krijg direct inzicht in de voortgang van je studenten met intuïtieve kleurcodering.
                    Groen voor goed, geel voor voldoende, rood voor aandacht.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="feature-card bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-8 border border-green-200">
                <div class="bg-green-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-calculator text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Cijfer Analyses</h3>
                <p class="text-gray-700 leading-relaxed">
                    Bekijk numerieke cijfers, percentages en gemiddelden per student en per opdracht.
                    Inclusief trend analyses en prestatie statistieken.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-8 border border-red-200">
                <div class="bg-red-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Aandachtspunten</h3>
                <p class="text-gray-700 leading-relaxed">
                    Identificeer automatisch studenten die extra begeleiding nodig hebben op basis van
                    ontbrekende opdrachten en onvoldoende resultaten.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="feature-card bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-8 border border-purple-200">
                <div class="bg-purple-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-filter text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Flexibele Selectie</h3>
                <p class="text-gray-700 leading-relaxed">
                    Kies precies welke cursussen, modules, opdracht groepen en studenten je wilt analyseren.
                    Volledig configureerbare rapportages.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="feature-card bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-8 border border-yellow-200">
                <div class="bg-yellow-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-file-export text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Export Opties</h3>
                <p class="text-gray-700 leading-relaxed">
                    Exporteer rapporten naar Excel voor verdere analyse of print direct vanuit de browser.
                    Ideaal voor ouderavonden en evaluaties.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="feature-card bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-8 border border-indigo-200">
                <div class="bg-indigo-600 rounded-lg p-3 w-12 h-12 flex items-center justify-center mb-6">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Real-time Data</h3>
                <p class="text-gray-700 leading-relaxed">
                    Directe koppeling met Canvas API voor actuele gegevens. Geen handmatige exports
                    of verouderde data meer.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How it Works Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Hoe Werkt Het?
            </h2>
            <p class="text-xl text-gray-600">
                In vier eenvoudige stappen naar inzichtelijke rapporten
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="bg-blue-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-white font-bold text-xl">1</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Selecteer Cursussen</h3>
                <p class="text-gray-600">Kies de Canvas cursussen die je wilt analyseren</p>
            </div>

            <div class="text-center">
                <div class="bg-green-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-white font-bold text-xl">2</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Kies Modules</h3>
                <p class="text-gray-600">Selecteer specifieke modules en opdracht groepen</p>
            </div>

            <div class="text-center">
                <div class="bg-purple-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-white font-bold text-xl">3</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Selecteer Studenten</h3>
                <p class="text-gray-600">Kies welke studenten of groepen je wilt includeren</p>
            </div>

            <div class="text-center">
                <div class="bg-red-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <span class="text-white font-bold text-xl">4</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Genereer Rapport</h3>
                <p class="text-gray-600">Bekijk je gepersonaliseerde voortgangsrapport</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-tcr-green">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
            Klaar om te Beginnen?
        </h2>
        <p class="text-xl text-green-100 mb-8">
            Maak vandaag nog een account aan en krijg direct toegang tot alle features.
            Gratis voor alle TCR docenten.
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <a href="/register" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                <i class="fas fa-user-plus mr-2"></i>
                Account Aanmaken
            </a>
            <a href="/login" class="bg-white text-tcr-green px-8 py-4 rounded-lg hover:bg-gray-100 transition duration-200 font-semibold text-lg shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Direct Inloggen
            </a>
        </div>
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
