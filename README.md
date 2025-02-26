<p align="center"><img src="/art/socialcard.png" alt="Laravel Artisan Installer"></p>

# 🚀 Laravel Artisan Installer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/darshphpdev/laravel-artisan-installer?style=flat-square)](https://packagist.org/packages/darshphpdev/laravel-artisan-installer)
[![Total Downloads](https://img.shields.io/packagist/dt/darshphpdev/laravel-artisan-installer?style=flat-square)](https://packagist.org/packages/darshphpdev/laravel-artisan-installer)
[![License](https://img.shields.io/badge/license-MIT-brightgreen)](LICENSE)

A Laravel package that provides an interactive command-line installer for easy application setup. This package helps streamline the initial configuration process of Laravel applications through an intuitive artisan command. ✨

## ✅ Features

- 🖥️ Interactive command-line interface
- ⚙️ Environment configuration setup 
- 🗄️ Database configuration wizard
- 🔄 Automated migration handling
- 📊 Progress indicators for each installation step
- 🎛️ Configurable default values

## 📦 Installation

Install the package via composer:

    composer require darshphpdev/laravel-artisan-installer

The package will automatically register its service provider in Laravel 5.5+ applications. For older versions, manually add the service provider in `config/app.php`:

    'providers' => [
        // ...
        DarshPhpDev\LaravelArtisanInstaller\InstallerServiceProvider::class,
    ],

## 🛠️ Configuration

Publish the configuration file:

    php artisan vendor:publish --tag=installer-config

This will create a `config/installer.php` file where you can customize default values.

## 🎮 Usage

Run the installer command:

    php artisan app:install

The installer will guide you through the following steps:

1. **🌍 Environment Configuration**
   - Application name
   - Application URL
   - Environment type (local/production)

2. **💾 Database Configuration**
   - Database connection type
   - Host configuration
   - Database credentials

3. **🔄 Migration**
   - Automatic database migration process

## ⚙️ Configuration Options

You can customize default values in `config/installer.php`:

    return [
        'defaults' => [
            'app_name' => 'Laravel',
            'app_url' => 'http://localhost',
            'app_env' => 'local',
            'db_connection' => 'mysql',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
        ],
    ];

## ⚠️ Error Handling

The installer includes comprehensive error handling with detailed error messages for common installation issues:
- 📝 Environment file creation failures
- 🔌 Database connection issues
- ❌ Migration errors

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. Let's make this installer even better together! 💪

## 📄 License

This package is open-sourced software licensed under the MIT license.

## 👥 Credits

- 👨‍💻 Author: Mustafa Ahmed
- 📧 Email: mustafa.softcode@gmail.com

---
Made with ❤️ for the Laravel community
