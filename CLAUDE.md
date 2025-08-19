# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

This is a PHP-based web application without package managers. No build commands are required.

**Development Server:**
- Use a local PHP server or web server (Apache/Nginx) pointing to the project root
- Ensure PHP extensions are available: MySQL, cURL, GD, mbstring

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

### Application Architecture

**Multi-Country Support:**
- Configured for Denmark (dk), Norway (no), and Sweden (se)
- Country-specific URLs and Navision ERP integrations
- Language constants in `GFConfig` class

**Authentication System:**
- Two-tier auth: "shop" (customer-facing) and "backend" (admin)
- JWT token-based authentication with session management
- Public controllers defined in router for unauthenticated access

**Business Logic Organization:**
- `bizlogic/` - Domain-specific business logic classes
- `units/` - Modular application units with namespace `GFUnit`
- `app/` - App-specific controllers with namespace `GFApp`

### Database & External Systems

**Database:**
- MySQL using PHP-ActiveRecord ORM
- Models in `model/` directory follow `{entity}.class.php` naming
- Database configuration in `includes/config.php`

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

### Development Patterns

**Controller Structure:**
- All controllers extend `baseController`
- Must implement `index()` method
- URL pattern: `index.php?rt=controller/action`
- Authentication handled in router before controller execution

**Model Patterns:**
- Extend `BaseModel` which extends `ActiveRecord\Model`
- Support calculated attributes via `$calculated_attributes`
- Database connections configured in `init.php`

**Error Handling:**
- Comprehensive logging via `SystemLog` model
- Transaction-based error handling in router
- Custom exception handling with rollback support

### Configuration

**Environment Configuration:**
- All config in `includes/config.php` via `GFConfig` class constants
- Database, URLs, and API credentials centralized
- Season-based configuration (currently 2025)

**Security Notes:**
- Database credentials and API keys are hardcoded in config
- JWT secret is hardcoded in `init.php`
- Session management with 20-hour timeout

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