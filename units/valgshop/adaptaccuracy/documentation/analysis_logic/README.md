# Adapt Accuracy Analysis - Komplet Dokumentation

## 📋 Indholdsfortegnelse
1. [Oversigt](#oversigt)
2. [System Arkitektur](#system-arkitektur)
3. [Database Struktur](#database-struktur)
4. [Autopilot Logik](#autopilot-logik)
5. [Accuracy Beregninger](#accuracy-beregninger)
6. [Frontend Implementation](#frontend-implementation)
7. [API Dokumentation](#api-dokumentation)
8. [Progressive Features](#progressive-features)
9. [Fejlfinding](#fejlfinding)

## Oversigt

Adapt Accuracy Analysis systemet analyserer hvor præcise AI/ML forudsigelser (adapt_0, adapt_1, adapt_2, adapt_3) er sammenlignet med faktiske salgsdata. Systemet integrerer den komplekse autopilot logik fra `autopanel.php` for at give både original og autopilot-justeret accuracy analyse.

### Vigtige Principper
- **Minimum ordre filter**: Kun produkter med ≥5 ordrer analyseres
- **Ekstern beskyttelse**: Eksterne gaver (`is_external > 0`) justeres aldrig
- **Autopilot buffer**: 5% lager buffer, minimum 5 stk
- **Threshold baseret**: Forskellige multipliers baseret på % valgt og shop størrelse
- **Progressive sammenligning**: Shop-for-shop analyse med real-time progress
- **Shop søgning**: Real-time filtering af shop dropdown

## Progressive Features

### 🔍 Shop Search
- **Real-time søgning** i shop dropdown
- **Instant filtering** baseret på shop navn
- **Bevarelse af alle data** under søgning

### 📊 Progressive Shop Comparison
- **Confirm dialog** før start af sammenligning
- **Individual API calls** per shop (undgår timeouts)
- **Real-time progress bar** med estimeret tid
- **Frontend aggregation** af sammenligningsdata
- **Robust fejlhåndtering** - fortsætter selvom enkelte shops fejler

## Filer i denne dokumentation
- `database_schema.md` - Detaljeret database struktur
- `autopilot_logic.md` - Komplet autopilot algoritme dokumentation
- `accuracy_calculations.md` - Nøjagtigheds beregnings algoritmer
- `accuracy_definitions.md` - **Præcise definitioner af accuracy kategorier og hit rates**
- `api_reference.md` - API endpoint dokumentation
- `frontend_architecture.md` - Frontend komponent beskrivelse
- `flow_diagrams.md` - Proces flow diagrammer
- `troubleshooting.md` - Fejlfinding og debugging guide

## Quick Start
1. Læs `database_schema.md` for at forstå data strukturen
2. Gennemgå `autopilot_logic.md` for forretningslogik
3. **Læs `accuracy_definitions.md` for at forstå hvordan systemet vurderer præcision**
4. Se `flow_diagrams.md` for visual oversigt
5. Brug `api_reference.md` til integration

## System Requirements
- PHP 7.4+
- MySQL/MariaDB med gavefabrikken2024 database
- Chart.js for grafer
- Bootstrap 4+ for styling