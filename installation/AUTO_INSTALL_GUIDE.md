# 🚀 Auto Installation System Guide

This Laravel application includes a powerful dual-mode installation system that supports both **Manual** and **Automatic** installation processes.

## 📋 Table of Contents

1. [Overview](#overview)
2. [Installation Modes](#installation-modes)
3. [System Requirements](#system-requirements)
4. [Quick Start](#quick-start)
5. [Automatic Installation](#automatic-installation)
6. [Manual Installation](#manual-installation)
7. [Troubleshooting](#troubleshooting)

---

## Overview

The installation system provides two approaches to set up your Laravel application:

### 🚀 **Automatic Installation** (Recommended for Quick Setup)
- One-click installation process
- All steps run sequentially without manual intervention
- Automatically configures database, runs migrations, and starts the server
- Perfect for production deployment and quick testing

### 🔧 **Manual Installation** (Recommended for Learning)
- Step-by-step guided installation
- Install each component individually
- Full control over each step
- Perfect for development and troubleshooting

---

## Installation Modes

### Mode Comparison

| Feature | Auto Install | Manual Install |
|---------|--------------|----------------|
| **Setup Time** | 5-10 minutes | 15-30 minutes |
| **User Interaction** | Minimal (only database config) | Required at each step |
| **Control Level** | Automated | Full control |
| **Best For** | Production, Quick Setup | Learning, Debugging |
| **Error Handling** | Automatic with detailed logs | Manual intervention |
| **Server Startup** | Automatic | Manual |

---

## System Requirements

Before starting installation, ensure your system meets these requirements:

### Required Software
- **PHP**: Version 8.1 or higher
- **Composer**: Latest version
- **Node.js**: Version 16.0 or higher
- **NPM**: Latest version
- **MySQL**: Version 5.7 or higher

### Required PHP Extensions
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath

### Server Requirements
- Writable `storage/` directory
- Writable `bootstrap/cache/` directory
- Minimum 2GB RAM recommended
- Minimum 1GB free disk space

---

## Quick Start

### 1. Access Installation Page

Navigate to the installation URL in your browser:

```
http://127.0.0.1:8000/installation
```

Or if using Laragon:

```
http://jpos-2x.test/installation
```

### 2. System Check

The system will automatically verify:
- ✅ PHP version and extensions
- ✅ Composer availability
- ✅ Node.js and NPM
- ✅ Laravel project structure
- ✅ Directory permissions

### 3. Choose Installation Mode

Select your preferred installation mode:
- **Auto Install**: For fastest setup
- **Manual Install**: For step-by-step control

---

## Automatic Installation

### Step-by-Step Process

#### 1. **Start Auto Installation**
Click the **"⚡ Quick Auto Install"** button on the system check page.

#### 2. **Configure Database Settings**

Fill in your database configuration:

**Local Database (Required):**
```
Host:     127.0.0.1
Port:     3306
Database: jpos_db
Username: root
Password: [your password]
```

**Remote Database (Optional - Hibernate Mode):**
- Enable the "Hibernate Mode" checkbox
- Fill in remote database credentials
- Both databases will be synchronized automatically

#### 3. **Click "Start Auto Install"**

The system will automatically execute these steps:

1. ✅ **Composer Install** - Install PHP dependencies
2. ✅ **NPM Install** - Install JavaScript dependencies
3. ✅ **NPM Build** - Compile frontend assets
4. ✅ **Create .env File** - Generate environment configuration
5. ✅ **Database Configuration** - Update database credentials
6. ✅ **Create Databases** - Automatically create local/remote databases
7. ✅ **Generate App Key** - Create application encryption key
8. ✅ **Clear Cache** - Clear all Laravel caches
9. ✅ **Run Migrations** - Set up database tables
10. ✅ **Seed Database** - Populate with initial data
11. ✅ **Create Storage Link** - Link public storage
12. ✅ **Optimize Application** - Cache configurations for performance

#### 4. **Real-Time Progress Monitoring**

During installation, you'll see:
- Live progress bar (0-100%)
- Real-time log output (terminal-style)
- Step-by-step completion status
- Success/error indicators with color coding

#### 5. **Automatic Server Startup**

After successful installation:
- Click **"🚀 Start Laravel Server"** button
- Server starts automatically on port 8000
- Application opens automatically in your browser
- Access your application at: `http://127.0.0.1:8000`

### Installation Log

All installation activities are logged to:
```
storage/logs/auto-install.log
```

This log includes:
- Timestamp for each step
- Success/failure status
- Detailed error messages
- System output from commands

---

## Manual Installation

### Step-by-Step Process

#### 1. **System Requirements Check**
Verify all requirements are met before proceeding.

#### 2. **Composer Dependencies** (`/installation/composer`)
```bash
composer install
```
Installs all PHP packages from `composer.json`.

#### 3. **NPM Dependencies** (`/installation/npm-install`)
```bash
npm install
```
Installs all JavaScript packages from `package.json`.

#### 4. **Build Assets** (`/installation/npm-build`)
```bash
npm run build
```
Compiles CSS and JavaScript using Vite.

#### 5. **Environment Setup** (`/installation/env-setup`)
- Creates `.env` file from `.env.example`
- Prepares for configuration

#### 6. **Database Configuration** (`/installation/env-config`)
- Configure database credentials
- Optional: Enable Hibernate mode for dual database
- Auto-creates databases if they don't exist

#### 7. **Database Test** (`/installation/db-test`)
- Verifies database connection
- Checks if databases exist
- Creates databases if needed

#### 8. **Run Migrations** (`/installation/migrate`)
```bash
php artisan migrate --force
```
Creates all database tables.

#### 9. **Seed Database** (`/installation/seed-databases`)
```bash
php artisan db:seed --force
```
Populates database with initial data.

#### 10. **Generate App Key** (`/installation/generate-key`)
```bash
php artisan key:generate --force
```
Creates unique application encryption key.

#### 11. **Create Storage Link** (`/installation/storage-link`)
```bash
php artisan storage:link
```
Links public storage directory.

#### 12. **Installation Complete** (`/installation/complete`)
View installation summary and access your application.

---

## Hibernate Mode (Dual Database)

### What is Hibernate Mode?

Hibernate Mode enables your application to work with **two databases simultaneously**:
- **Local Database**: For development and offline work
- **Remote Database**: For production data synchronization

### When to Use Hibernate Mode?

- Multi-environment deployments
- Data synchronization requirements
- Backup and redundancy needs
- Development with production data access

### Configuration

1. Check "Enable Hibernate Mode" during installation
2. Provide both local and remote database credentials
3. System automatically:
   - Creates both databases
   - Runs migrations on both
   - Seeds both databases
   - Synchronizes data operations

---

## Troubleshooting

### Common Issues and Solutions

#### 1. **Installation Fails During Composer Install**

**Problem**: Composer installation timeout or memory error.

**Solution**:
```bash
# Increase timeout
composer install --no-interaction --timeout=600

# Increase memory limit
php -d memory_limit=-1 /path/to/composer install
```

#### 2. **NPM Install Fails**

**Problem**: Network issues or permission errors.

**Solution**:
```bash
# Clear NPM cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

#### 3. **Database Connection Failed**

**Problem**: Unable to connect to MySQL database.

**Solutions**:
- Verify MySQL service is running
- Check database credentials (host, port, username, password)
- Ensure user has permission to create databases
- Check firewall settings

#### 4. **Migration Fails**

**Problem**: Error during database migration.

**Solution**:
```bash
# Reset migrations
php artisan migrate:fresh --force

# Check specific migration error
php artisan migrate --force --verbose
```

#### 5. **Permission Denied Errors**

**Problem**: Unable to write to storage or cache directories.

**Solution** (Linux/Mac):
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Solution** (Windows):
- Right-click folders → Properties → Security
- Give full control to current user

#### 6. **Server Already Running**

**Problem**: Port 8000 already in use.

**Solution**:
```bash
# Find process using port 8000
netstat -ano | findstr :8000

# Kill process (Windows)
taskkill /PID [PID_NUMBER] /F

# Or use different port
php artisan serve --port=8080
```

#### 7. **Auto Install Hangs/Freezes**

**Problem**: Installation process stops responding.

**Solutions**:
1. Check `storage/logs/auto-install.log` for errors
2. Verify internet connection for package downloads
3. Increase PHP execution timeout in `php.ini`
4. Try manual installation mode instead

---

## Advanced Configuration

### Environment Variables

After installation, customize these `.env` variables:

```env
APP_NAME="Your App Name"
APP_URL=http://your-domain.com
APP_DEBUG=false
APP_ENV=production

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jpos_db
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

### Running in Production

After installation, optimize for production:

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build optimized assets
npm run build
```

---

## Reset Installation

If you need to start fresh:

### Option 1: Use Reset Button
Click **"🔄 Reset Configuration"** on the system check page.

### Option 2: Manual Reset
```bash
# Delete .env file
rm .env

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Drop and recreate database (optional)
php artisan db:wipe
```

---

## Support

### Getting Help

1. **Check Logs**: `storage/logs/laravel.log` and `storage/logs/auto-install.log`
2. **Review Documentation**: `/installation/INSTALLATION_GUIDE.md`
3. **System Check**: Revisit `/installation` for requirement verification

### Useful Commands

```bash
# Check Laravel version
php artisan --version

# View all routes
php artisan route:list

# Check environment
php artisan env

# Clear everything
php artisan optimize:clear
```

---

## Success Criteria

Installation is successful when:
- ✅ All 12 steps complete without errors
- ✅ `.env` file exists with correct configuration
- ✅ Database tables are created
- ✅ Application opens in browser
- ✅ Login page or home page displays correctly
- ✅ No errors in `storage/logs/laravel.log`

---

## Next Steps

After successful installation:

1. **Login**: Use default credentials (check seeders)
2. **Configure Settings**: Update app settings and company info
3. **Customize**: Modify branding and preferences
4. **Add Data**: Start adding your business data
5. **Backup**: Set up regular database backups

---

## Installation URLs

- **System Check**: `/installation`
- **Auto Install**: `/installation/auto-install`
- **Manual Steps**: `/installation/composer`, `/installation/npm-install`, etc.
- **Complete**: `/installation/complete`

---

**🎉 Congratulations!** Your Laravel application is now installed and ready to use!
