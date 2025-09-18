# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

This is a PHP-based web application without package managers. No build commands are required.

**Development Server:**
- Use a local PHP server or web server (Apache/Nginx) pointing to the project root
- Ensure PHP extensions are available: MySQL, cURL, GD, mbstring


## Task Workflow Instructions

You are working in a project where each task is organized in a folder .tasks/taskno/ with 3 files:

- Requirements.md → contains the user requirements  . (read this for context)
- Solution.md → contains the technical solution       (read this solution description made by our developer)
- Summary.md → must be written last                   (when done update this)

Your role:  
Both Requirements.md and Solution.md should exists before starting working, you must always produce the content for Summary.md.  
Summary.md should be a short, precise summary of the changes and the solution, written in an easy-to-read bullet point format.  

You must only return the text that belongs in Summary.md (no extra explanations, no code blocks).  

**Testing:**
- No automated test framework is configured
- Test manually through web interface

## Architecture Overview

This is a custom PHP MVC framework for a gift/present e-commerce system called "Gavefabrikken" serving Danish, Norwegian, and Swedish markets.

### Core Framework Structure

**MVC Pattern:**
- `index.php` - Entry point that initializes the framework
- `application/router.class.php` - Custom router handling URL routing and authentication
- `controller/` - Controllers following pattern `{name}Controller.php` extending `baseController`
- `model/` - ActiveRecord models using PHP-ActiveRecord ORM
- `views/` - View templates with corresponding CSS/JS assets

**Key Framework Components:**
- `application/registry.class.php` - Dependency injection container
- `application/template.class.php` - Template rendering system
- `lib/ActiveRecord.php` - ORM layer for database operations
- `includes/init.php` - Bootstrap file with autoloader and DB setup

**Framework Bootstrap Process:**
1. `index.php` defines site path and includes `init.php`
2. `init.php` loads configuration, sets up autoloading, and initializes ActiveRecord
3. Router is instantiated and controller path is set
4. Template system is loaded
5. Router loads and executes the appropriate controller

### Application Architecture

**Multi-Country Support:**
- Configured for Denmark (dk), Norway (no), and Sweden (se)
- Country-specific URLs and Navision ERP integrations
- Language constants in `GFConfig` class (`LANG_DENMARK=1`, `LANG_NORWAY=4`, `LANG_SWEDEN=5`)

**Authentication System:**
- Two-tier auth: "shop" (customer-facing) and "backend" (admin)
- JWT token-based authentication with session management (20-hour timeout)
- Public controllers defined in router for unauthenticated access
- Session configuration in router with custom cookie parameters

**Business Logic Organization:**
- `bizlogic/` - Domain-specific business logic classes
- `units/` - Modular application units with namespace `GFUnit`
- `app/` - App-specific controllers with namespace `GFApp`

**Autoloading System:**
- Custom autoloader (`gfAutoloader`) handles namespaced classes
- `GFUnit\*` classes loaded from `units/` directory
- `GFApp\*` classes loaded from `app/` directory
- `GFBiz\*` classes loaded from `bizlogic/` directory
- Model classes loaded from `model/` and `reports/` directories

### Database & External Systems

**Database:**
- MySQL using PHP-ActiveRecord ORM
- Models in `model/` directory follow `{entity}.class.php` naming
- Database configuration in `includes/config.php`
- Connection string: `mysql://user:pass@host/database?charset=utf8`

**External Integrations:**
- Navision ERP system (separate configs for DA/NO/SE)
- PostNord shipping integration
- Magento stock management
- Email system via PHPMailer

### Key Directories

- `controller/` - MVC controllers (100+ files)
- `model/` - Database models and business entities
- `bizlogic/` - Complex business logic separated from controllers
- `units/` - Modular application components
- `thirdparty/` - External libraries (MPDF, PHPMailer, etc.)
- `upload/` - File upload storage
- `reports/` - Report generation classes
- `service/` - Service layer components
- `component/` - Utility components and tools
- `module/` - Standalone modules (presentsCms, saleportal)

### Development Patterns

**Controller Structure:**
- All controllers extend `baseController` (abstract class)
- Must implement abstract `index()` method
- URL pattern: `index.php?rt=controller/action`
- Authentication handled in router before controller execution
- Controllers receive `$registry` object in constructor

**Model Patterns:**
- Extend `BaseModel` which extends `ActiveRecord\Model`
- Support calculated attributes via `$calculated_attributes` static property
- Database connections configured in `init.php`
- Models follow naming convention: `{entity}.class.php`

**Error Handling:**
- Comprehensive logging via `SystemLog` model
- Transaction-based error handling in router
- Custom exception handling with rollback support
- Error reporting enabled in development

### Configuration

**Environment Configuration:**
- All config in `includes/config.php` via `GFConfig` class constants
- Database, URLs, and API credentials centralized
- Season-based configuration (currently 2025: `SALES_SEASON = 2025`)

**Multi-Country URLs:**
- Denmark: `https://findgaven.dk/`
- Norway: `https://gavevalg.no/`
- Sweden: `https://dinjulklapp.se/`
- Backend: `https://system.gavefabrikken.dk/gavefabrikken_backend/`

**Security Notes:**
- Database credentials and API keys are hardcoded in config
- JWT secret is hardcoded in `init.php`
- Session management with 20-hour timeout
- Navision integrations have production and development endpoints

## Working with this Codebase

**Adding New Features:**
1. Create controller in `controller/` extending `baseController`
2. Add any required models in `model/`
3. Place business logic in `bizlogic/` if complex
4. Update router public controllers array if unauthenticated access needed

**Database Changes:**
- Models use PHP-ActiveRecord conventions
- No migration system - manual SQL changes required
- Connection configuration in `init.php` using `GFConfig` constants

**Multi-Country Development:**
- Use language constants from `GFConfig`
- Country-specific logic often switches on `LANG_DENMARK`, `LANG_NORWAY`, `LANG_SWEDEN`
- Separate Navision configurations per country