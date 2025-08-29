# Gavefabrikken Backend

A custom PHP MVC framework powering a multi-national gift/present e-commerce system serving Danish, Norwegian, and Swedish markets.

## 🎯 Project Overview

Gavefabrikken ("Gift Factory" in Danish) is a comprehensive e-commerce backend system designed for gift and present sales across Nordic countries. The system provides a robust platform for managing products, orders, and customer interactions with full multi-language and multi-currency support.

### 🌍 Supported Markets
- **Denmark**: [findgaven.dk](https://findgaven.dk/)
- **Norway**: [gavevalg.no](https://gavevalg.no/)
- **Sweden**: [dinjulklapp.se](https://dinjulklapp.se/)
- **Backend Admin**: [system.gavefabrikken.dk](https://system.gavefabrikken.dk/gavefabrikken_backend/)

## 🏗️ Architecture

### Core Framework Structure
- **Custom PHP MVC Framework** with ActiveRecord ORM
- **Entry Point**: `index.php` initializes the framework
- **Router**: Custom routing with authentication (`application/router.class.php`)
- **Controllers**: Follow `{name}Controller.php` pattern extending `baseController`
- **Models**: ActiveRecord models using PHP-ActiveRecord ORM
- **Views**: Template system with corresponding CSS/JS assets

### Key Framework Components
- `application/registry.class.php` - Dependency injection container
- `application/template.class.php` - Template rendering system
- `lib/ActiveRecord.php` - ORM layer for database operations
- `includes/init.php` - Bootstrap file with autoloader and DB setup

## 🚀 Key Features

### Multi-Country Support
- Separate configurations for Denmark, Norway, and Sweden
- Language constants: `LANG_DENMARK=1`, `LANG_NORWAY=4`, `LANG_SWEDEN=5`
- Country-specific URLs and Navision ERP integrations

### Authentication System
- **Two-tier authentication**: "shop" (customer-facing) and "backend" (admin)
- **JWT token-based** authentication with 20-hour session timeout
- **Public controllers** for unauthenticated access
- Custom session management with secure cookie parameters

### External Integrations
- **Navision ERP** system (separate configs for DA/NO/SE)
- **PostNord** shipping integration
- **Magento** stock management
- **Email system** via PHPMailer

## 📁 Directory Structure

```
├── app/                    # App-specific controllers (GFApp namespace)
├── application/            # Core framework components
├── bizlogic/              # Domain-specific business logic (GFBiz namespace)
├── component/             # Utility components and tools
├── controller/            # MVC controllers (100+ files)
├── includes/              # Configuration and initialization
├── lib/                   # Core libraries and ORM
├── model/                 # Database models and business entities
├── module/                # Standalone modules (presentsCms, saleportal)
├── reports/               # Report generation classes
├── service/               # Service layer components
├── thirdparty/            # External libraries (MPDF, PHPMailer, etc.)
├── units/                 # Modular application components (GFUnit namespace)
├── upload/                # File upload storage
├── views/                 # View templates
└── index.php              # Application entry point
```

## 🛠️ Development Setup

### Requirements
- **PHP** with extensions: MySQL, cURL, GD, mbstring
- **MySQL** database
- **Web server** (Apache/Nginx) or local PHP server

### Local Development
```bash
# Start local PHP server
php -S localhost:8000

# Or point your web server to the project root
```

### Configuration
- Database configuration in `includes/config.php`
- Current season: **2025** (`SALES_SEASON = 2025`)
- Connection string format: `mysql://user:pass@host/database?charset=utf8`

## 🔧 Development Patterns

### Adding New Features
1. Create controller in `controller/` extending `baseController`
2. Add required models in `model/`
3. Place complex business logic in `bizlogic/`
4. Update router public controllers array if unauthenticated access needed

### Controller Structure
- All controllers extend `baseController` (abstract class)
- Must implement abstract `index()` method
- URL pattern: `index.php?rt=controller/action`
- Authentication handled in router before controller execution

### Model Patterns
- Extend `BaseModel` which extends `ActiveRecord\Model`
- Support calculated attributes via `$calculated_attributes` static property
- Follow naming convention: `{entity}.class.php`

### Autoloading System
- **Custom autoloader** (`gfAutoloader`) handles namespaced classes
- `GFUnit\*` classes loaded from `units/` directory
- `GFApp\*` classes loaded from `app/` directory
- `GFBiz\*` classes loaded from `bizlogic/` directory
- Model classes loaded from `model/` and `reports/` directories

## 🧪 Testing

- **No automated test framework** configured
- **Manual testing** through web interface
- Test all functionality after making changes

## 📊 Error Handling & Logging

- Comprehensive logging via `SystemLog` model
- Transaction-based error handling in router
- Custom exception handling with rollback support
- Error reporting enabled in development environment

## 🔒 Security Features

- JWT secret management
- Session timeout configuration (20 hours)
- Database credential management
- Secure cookie parameters
- Transaction-based operations with rollback support

## 📈 Current Season

**Sales Season 2025** - The system is currently configured for the 2025 sales season with appropriate database and configuration settings.

## 🤝 Contributing

1. Follow the established MVC patterns
2. Use the custom autoloader for new classes
3. Place business logic in appropriate directories (`bizlogic/`, `units/`, etc.)
4. Test manually through the web interface
5. Ensure multi-country compatibility when applicable

## 📚 Additional Documentation

For detailed technical information, see [CLAUDE.md](CLAUDE.md) which contains comprehensive development guidelines and architectural details.

---

**Version**: 2025 Season  
**Framework**: Custom PHP MVC  
**Database**: MySQL with ActiveRecord ORM  
**Countries**: Denmark, Norway, Sweden