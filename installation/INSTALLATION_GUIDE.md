# Laravel Installation System

This Laravel application includes a built-in web-based installation wizard that guides you through the complete setup process.

## Accessing the Installation Wizard

Visit the installation page at:
```
http://your-domain/installation
```

Or locally:
```
http://127.0.0.1:8000/installation
```

## Installation Steps

The wizard will guide you through the following steps:

### 1. System Requirements Check
- Verifies PHP version (8.1+) and required extensions
- Checks for Composer and Node.js/NPM
- Validates Laravel project structure
- Displays system information

### 2. Composer Installation
- Installs PHP dependencies from `composer.json`
- May take several minutes on first run

### 3. NPM Installation
- Installs JavaScript dependencies from `package.json`
- Required for frontend asset compilation

### 4. Asset Building
- Compiles and optimizes CSS and JavaScript using Vite
- Creates production-ready assets

### 5. Environment Setup
- Creates `.env` file from `.env.example`
- Prepares configuration file for customization

### 6. Database Configuration
- Configure local database connection
- **Optional:** Enable Hibernate mode for dual database support (local + remote)
- Supports MySQL databases
- Auto-creates databases if they don't exist

### 7. Database Connection Test
- Verifies database credentials
- Checks if databases exist
- Creates missing databases automatically

### 8. Database Migrations
- Creates all database tables and structure
- Runs on both databases if Hibernate mode is enabled

### 9. Database Seeding (Optional)
- Populates databases with initial data
- Can be skipped if no seeders exist

### 10. Application Key Generation
- Generates unique encryption key
- Required for application security

### 11. Storage Link Creation
- Creates symbolic link for public file storage
- Links `public/storage` to `storage/app/public`

### 12. Setup Complete
- Installation finished
- Provides link to access application
- Option to reset configuration

## Features

### Hibernate Mode (Dual Database)
Enable this feature to configure and maintain two separate databases:
- **Local Database**: Primary database for local operations
- **Remote Database**: Secondary database for synchronization/backup

When enabled, migrations and seeds run on both databases automatically.

### Progress Tracking
- Step-by-step wizard interface
- Visual feedback for each operation
- Loading animations during processing
- Error handling with helpful messages

### Configuration Reset
At any time during or after installation, you can reset the configuration:
- Deletes `.env` file
- Clears session data
- Returns to system requirements check

## Routes

All installation routes are prefixed with `/installation`:

| Route | Method | Description |
|-------|--------|-------------|
| `/installation` | GET | System requirements check |
| `/installation/proceed` | POST | Proceed with setup |
| `/installation/composer` | GET/POST | Composer installation |
| `/installation/npm-install` | GET/POST | NPM installation |
| `/installation/npm-build` | GET/POST | Asset building |
| `/installation/env-setup` | GET/POST | Create .env file |
| `/installation/env-config` | GET/POST | Configure database |
| `/installation/db-test` | GET/POST | Test database connection |
| `/installation/migrate` | GET/POST | Run migrations |
| `/installation/seed-databases` | GET/POST | Seed databases |
| `/installation/generate-key` | GET/POST | Generate app key |
| `/installation/storage-link` | GET/POST | Create storage link |
| `/installation/complete` | GET | Setup complete |
| `/installation/reset` | POST | Reset configuration |

## Files Structure

```
app/
  Http/
    Controllers/
      InstallationController.php    # Main installation logic

resources/
  views/
    layouts/
      installation.blade.php        # Installation layout
    installation/
      system-check.blade.php        # Step 1: Requirements
      composer.blade.php             # Step 2: Composer
      npm-install.blade.php          # Step 3: NPM
      npm-build.blade.php            # Step 4: Build
      env-setup.blade.php            # Step 5: Env setup
      env-config.blade.php           # Step 6: DB config
      db-test.blade.php              # Step 7: DB test
      migrate.blade.php              # Step 8: Migrations
      seed-databases.blade.php       # Step 9: Seeding
      generate-key.blade.php         # Step 10: Key
      storage-link.blade.php         # Step 11: Storage
      complete.blade.php             # Step 12: Complete

routes/
  web.php                           # Installation routes
```

## Requirements

Before starting installation, ensure you have:
- PHP 8.1 or higher
- Composer installed
- Node.js 16+ and NPM installed
- MySQL database server running
- Required PHP extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath

## Troubleshooting

### Installation hangs or times out
- Long operations (composer/npm install) may take 5-10 minutes
- Check your internet connection
- Refresh the page and retry

### Database connection fails
- Verify MySQL service is running
- Check database credentials
- Ensure database user has CREATE DATABASE privileges

### Permission errors
- Ensure `storage/` directory is writable
- Ensure `bootstrap/cache/` directory is writable
- On Windows, run as administrator if needed

### Reset not working
- Manually delete `.env` file from project root
- Clear browser cookies/session
- Revisit `/installation`

## Manual Installation Alternative

If you prefer manual installation, run these commands:

```bash
composer install
npm install
npm run build
cp .env.example .env
# Edit .env with your database credentials
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

## Security Note

**Important:** For production environments, consider removing or protecting the installation routes after initial setup to prevent unauthorized access.

You can do this by:
1. Commenting out the installation routes in `routes/web.php`
2. Adding middleware to restrict access
3. Deleting the `InstallationController.php` and installation views

## Support

For issues or questions about the installation process:
- Check Laravel documentation: https://laravel.com/docs
- Review error messages in the installation wizard
- Check server error logs
- Ensure all system requirements are met
