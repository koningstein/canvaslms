# TCR Canvas Tool

Een uitgebreide Laravel-applicatie voor het monitoren en analyseren van studentenvoortgang via de Canvas LMS API. Ontwikkeld voor het Techniekcollege Rotterdam om docenten te helpen bij het volgen van studentprestaties.

## 🚀 Branch Strategie

- **`live`** - Stabiele, werkende versie voor productie
- **`main`** - AI development branch (kan instabiel zijn door AI experimenten)
- **`feature/*`** - Feature branches voor nieuwe ontwikkeling

**Gebruik altijd de `live` branch voor een volledig werkende versie van de applicatie.**

## ✨ Functies

### 📚 Multi-step Wizard
De applicatie biedt een stapsgewijze interface voor het selecteren van data:

1. **Cursus Selectie** - Kies cursussen uit Canvas
2. **Module Selectie** - Selecteer specifieke modules binnen cursussen
3. **Opdracht Groepen** - Kies welke assignment groups te monitoren
4. **Student Selectie** - Selecteer individuele studenten of hele secties
5. **Rapport Generatie** - Kies uit verschillende rapportformaten

### 📊 Rapportages

#### Basis Overzichten
- **Basis Kleur Overzicht** - Kleurgecodeerde status weergave
- **Ontbrekende Opdrachten** - Focus op niet-ingeleverde werk
- **Aandachtspunten** - Studenten die hulp nodig hebben

#### Cijfer & Prestatie Overzichten
- **Numerieke Cijfers** - Puntscores en cijfers weergave
- **Percentages** - Percentage behaald vs mogelijk
- **Gemiddelden** - Uitgebreide analyse met grafieken en trends

#### Geavanceerde Analyses
- **Deadline Overzicht** - Te laat ingeleverde opdrachten
- **Tijdlijn Analyse** - Wanneer opdrachten zijn ingeleverd
- **Competentie Overzicht** - MBO competentie voortgang

### 🎨 Visuele Features
- Kleurgecodeerde status indicators
- Interactieve grafieken (ApexCharts)
- Responsive design met Tailwind CSS
- Print-vriendelijke layouts
- Sticky headers voor grote tabellen

## 🛠 Technische Stack

- **Framework**: Laravel (PHP)
- **Frontend**: Livewire, Tailwind CSS, ApexCharts
- **Database**: MySQL/PostgreSQL
- **API Integration**: Canvas LMS API
- **Caching**: Laravel Cache (Redis recommended)

## 📁 Project Structuur

### Controllers
- `CourseController` - Basis cursus weergave
- `ResultController` - Rapportage logica en rendering

### Livewire Components
- `CourseSelector` - Cursus selectie interface
- `ModuleSelector` - Module selectie
- `AssignmentGroupSelector` - Assignment group selectie
- `StudentSelector` - Student selectie met sectie ondersteuning
- `ResultSelector` - Rapport type keuze

### Services
- `CanvasService` - Canvas API integratie
- Report Processors:
    - `ConfigurableReportProcessor`
    - `MissingReportProcessor`
    - `GradesReportProcessor`
    - `PercentagesReportProcessor`
- Analyzers:
    - `PerformanceAnalyzer`
    - `TrendAnalyzer`
    - `StatisticsCalculator`
    - `ChartDataGenerator`

### Views & Templates
- **Layouts**: `layoutadmin.blade.php` - Hoofdlayout met navigatie
- **Step Views**:
    - `courses/index.blade.php`
    - `modules/select.blade.php`
    - `students/select.blade.php`
    - `results/select.blade.php`
- **Report Views**:
    - `basic-color-report.blade.php`
    - `grades-report.blade.php`
    - `percentages-report.blade.php`
    - `missing-report.blade.php`
    - `attention-report.blade.php`
    - `averages-report.blade.php`

## ⚙️ Installatie

### Vereisten
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL/PostgreSQL
- Canvas LMS API toegang

### Setup

1. **Clone de repository**
   ```bash
   git clone [repository-url]
   cd tcr-canvas-tool
   git checkout live  # Voor stabiele versie
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment configuratie**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Canvas API configuratie**
   Voeg toe aan `.env`:
   ```env
   CANVAS_API_URL=https://[your-canvas-domain]
   CANVAS_API_TOKEN=your_api_token_here
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Start de applicatie**
   ```bash
   php artisan serve
   ```

## 🔧 Canvas API Setup

1. Log in op Canvas als administrator
2. Ga naar Account → Settings → Approved Integrations
3. Genereer een nieuwe API token
4. Kopieer de token naar je `.env` bestand

### Benodigde Permissions
- Cursussen lezen
- Modules lezen
- Assignments lezen
- Studenten data lezen
- Grades lezen

## 🎯 Gebruik

### Voor Docenten

1. **Start een nieuwe analyse**
    - Ga naar `/courses`
    - Zoek en selecteer je cursussen

2. **Verfijn je selectie**
    - Kies specifieke modules
    - Selecteer relevante assignment groups
    - Kies individuele studenten of hele secties

3. **Genereer rapporten**
    - Kies het gewenste rapport type
    - Bekijk de resultaten
    - Print of exporteer indien nodig

### Rapport Interpretatie

- **Groen** - Goed (≥75%)
- **Geel** - Voldoende (55-74%)
- **Rood** - Onvoldoende (<55%)
- **Blauw** - Ingeleverd (niet beoordeeld)
- **Oranje** - Niet ingeleverd

## 🔒 Beveiliging

- Authenticatie vereist voor alle functionaliteiten
- Canvas API tokens worden veilig opgeslagen
- Session-based data opslag voor wizard stappen
- CSRF bescherming op alle forms

## 🚧 Development Workflow

### Branch Strategie
- **`live`** - Productie-klare code
- **`main`** - AI development en experimenten (mogelijk instabiel)
- **`feature/feature-name`** - Nieuwe features en bug fixes

### Development Process
1. Maak een feature branch vanaf `live` voor nieuwe ontwikkeling
2. Ontwikkel en test je feature
3. Merge naar `live` na testing
4. `main` branch is gereserveerd voor AI development

### Voor AI Development (main branch)
De main branch bevat AI-experimenten en -integraties die mogelijk instabiel kunnen zijn.

### Voor Nieuwe Features
Gebruik altijd feature branches:
```bash
git checkout live
git pull origin live
git checkout -b feature/your-feature-name
# Ontwikkel je feature
git push origin feature/your-feature-name
# Maak PR naar live branch
```

## 📈 Performance

- Canvas API caching (10 minuten default)
- Lazy loading van assignment data
- Efficient database queries
- Optimized frontend rendering

## 🤝 Contributing

### Development Workflow
1. **Voor nieuwe features**: Maak een feature branch vanaf `live`
   ```bash
   git checkout live
   git checkout -b feature/your-feature-name
   ```

2. **Ontwikkel je feature**
    - Test thoroughly
    - Volg Laravel coding standards
    - Documenteer nieuwe functies

3. **Merge proces**
    - Maak PR naar `live` branch
    - Code review door team
    - Merge na approval

4. **Belangrijke regels**
    - Werk NOOIT direct op `main` (AI reserved)
    - Gebruik `live` als basis voor nieuwe features
    - Test altijd voor merge naar `live`

## 📞 Support

Voor vragen over de applicatie:
- Check eerst de documentatie
- Raadpleeg de Canvas API documentation
- Contact de development team

## 📄 License

Deze software is ontwikkeld voor het Techniekcollege Rotterdam en is bedoeld voor intern gebruik.

---

**Belangrijk**: Gebruik de `live` branch voor een stabiele ervaring. De `main` branch kan instabiel zijn door AI-development werkzaamheden.
