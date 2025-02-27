<?php

/**
 * Installation Command Handler
 * 
 * Provides an interactive command-line interface for installing and
 * configuring a Laravel application. Handles environment setup,
 * database configuration, and migration processes.
 */
namespace DarshPhpDev\LaravelArtisanInstaller\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use DarshPhpDev\LaravelArtisanInstaller\Services\InstallerService;
use DarshPhpDev\LaravelArtisanInstaller\Exceptions\InstallerException;
use PDO;

class InstallCommand extends Command {
    protected $signature = 'app:install';
    protected $description = 'Install the application interactively';

    protected $installer;

    protected $asciiLogo = "
    ______                      ____        __           __  _            
   / ____/_______  ___        / __ \____ _/ /__  _____/ /_(_)___  ___  
  / /_  / ___/ _ \/ _ \     / /_/ / __ `/ / _ \/ ___/ __/ / __ \/ _ \ 
 / __/ / /  /  __/  __/    / ____/ /_/ / /  __(__  ) /_/ / / / /  __/ 
/_/   /_/   \___/\___/    /_/    \__,_/_/\___/____/\__/_/_/ /_/\___/  

                  🇵🇸  From the River to the Sea 🇵🇸
                  ================================
                Welcome to Laravel Artisan Installer
    ";

    public function __construct(InstallerService $installer) {
        parent::__construct();
        $this->installer = $installer;
    }

    public function handle() {
        try {
            $this->displayWelcome();

            // Step 1: Environment Configuration
            $this->configureEnvironment();

            // Step 2: Database Setup
            $this->configureDatabase();

            // Step 3: Run Migrations
            $this->runMigrations();

            $this->displaySuccess();
        } catch (InstallerException $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return 1; // Exit with error code
        }
    }

    protected function displayWelcome() {
        $this->line("\n<fg=cyan>" . $this->asciiLogo . "</>");
        $this->line("\n<fg=yellow>✨ Welcome to the Laravel Application Installer!</>");
        $this->line("<fg=yellow>This wizard will guide you through the installation process.</>\n");
    }

    protected function configureEnvironment() {
        $this->info('📝 Step 1: Environment Configuration');
        $this->line('----------------------------------------');

        $progressBar = $this->output->createProgressBar(3);
        $progressBar->setFormat('   %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();
        $this->line('   Configuring application name...');

        try {
            // Task 1: Ask for app name
            $this->newLine();
            $appName = $this->ask('   🏷️  Application Name', config('installer.defaults.app_name'));
            $progressBar->advance();
            $this->line('   Setting application URL...');
            
            // Task 2: Ask for app URL
            $this->newLine();
            $appUrl = $this->ask('   🌐 Application URL', config('installer.defaults.app_url'));
            $progressBar->advance();
            $this->line('   Selecting environment...');
            
            // Task 3: Ask for app environment
            $this->newLine();
            $appEnv = $this->choice('   🔧 Application Environment', ['local', 'production'], config('installer.defaults.app_env'));
            $progressBar->advance();
            
            $progressBar->finish();
            $this->newLine();
            $this->line("\n<fg=green>✅ Environment configuration completed successfully!</>\n");

            $this->installer->updateEnvFile([
                'APP_NAME' => $appName,
                'APP_URL' => $appUrl,
                'APP_ENV' => $appEnv,
            ]);
        } catch (\Exception $e) {
            throw new InstallerException('Failed to configure environment: ' . $e->getMessage());
        }
    }

    protected function configureDatabase() {
        $this->info('💾 Step 2: Database Configuration');
        $this->line('----------------------------------------');

        $progressBar = $this->output->createProgressBar(6);
        $progressBar->setFormat('   %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Selecting database type...');
        $progressBar->start();

        try {
            // Task 1: Ask for database connection
            $dbConnection = $this->choice('   🔌 Database Connection', ['mysql', 'pgsql', 'sqlite'], config('installer.defaults.db_connection'));
            $progressBar->setMessage('Configuring database host...');
            $progressBar->advance();

            // Task 2: Ask for database host
            $this->newLine();
            $dbHost = $this->ask('   🖥️  Database Host', config('installer.defaults.db_host'));
            $progressBar->setMessage('Setting database port...');
            $progressBar->advance();

            // Task 3: Ask for database port
            $this->newLine();
            $dbPort = $this->ask('   🔌 Database Port', config('installer.defaults.db_port'));
            $progressBar->setMessage('Setting database name...');
            $progressBar->advance();

            // Task 4: Ask for database name
            $this->newLine();
            $dbName = $this->ask('   📁 Database Name', config('installer.defaults.db_database'));
            $progressBar->setMessage('Configuring database user...');
            $progressBar->advance();

            // Task 5: Ask for database user
            $this->newLine();
            $dbUser = $this->ask('   👤 Database User', config('installer.defaults.db_username'));
            $progressBar->setMessage('Setting database password...');
            $progressBar->advance();

            // Task 6: Ask for database password
            $this->newLine();
            $dbPassword = $this->secret('   🔑 Database Password',  config('installer.defaults.db_password'));
            $progressBar->advance();

            $progressBar->finish();
            $this->line("\n<fg=green>✅ Database configuration completed successfully!</>\n");

            // Update .env file
            $this->installer->updateEnvFile([
                'DB_CONNECTION' => $dbConnection,
                'DB_HOST' => $dbHost,
                'DB_PORT' => $dbPort,
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $dbUser,
                'DB_PASSWORD' => $dbPassword,
            ]);

            // Test database connection and create database if it doesn't exist
            $this->testDatabaseConnection($dbConnection, $dbHost, $dbPort, $dbName, $dbUser, $dbPassword);
        } catch (\Exception $e) {
            throw new InstallerException('Failed to configure database: ' . $e->getMessage());
        }
    }

    protected function testDatabaseConnection($connection, $host, $port, $database, $username, $password) {
        try {
            // Test connection without specifying the database
            $pdo = new PDO(
                "{$connection}:host={$host};port={$port}",
                $username,
                $password
            );

            // Check if the database exists
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$database}'");
            $databaseExists = $stmt->fetchColumn();

            if (!$databaseExists) {
                // Create the database
                $pdo->exec("CREATE DATABASE `{$database}`");
                $this->info("Database '{$database}' created successfully.");
            }
        } catch (\PDOException $e) {
            throw new InstallerException('Failed to connect to the database or create the database: ' . $e->getMessage());
        }
    }

    protected function runMigrations() {
        $this->info('🔄 Step 3: Running Migrations');
        $this->line('----------------------------------------');
    
        // Create a progress bar with 3 steps: Preparing, Running Migrations, Running Seeders (optional)
        $progressBar = $this->output->createProgressBar(3);
        $progressBar->setFormat('   %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();
    
        try {
            // Step 1: Prepare migrations
            $progressBar->setMessage('Preparing migrations...');
            sleep(1); // Simulate preparation time (optional)
            $progressBar->advance();
    
            // Step 2: Run migrations
            $progressBar->setMessage('Running migrations...');
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $progressBar->advance();
    
            // Step 3: Run seeders (if confirmed)
            if ($this->confirm('   🌱 Do you want to seed the database?', false)) {
                $progressBar->setMessage('Running seeders...');
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            }
            $progressBar->advance();
    
            $progressBar->finish();
            $this->line("\n<fg=green>✅ Migration completed successfully!</>\n");
        } catch (\Exception $e) {
            throw new InstallerException('Failed to run migrations: ' . $e->getMessage());
        }
    }

    protected function displaySuccess() {
        $successAscii = "
  _____ _                 _      __   __          _ 
 |_   _| |__   __ _ _ __ | | __  \ \ / /__  _   _| |
   | | | '_ \ / _` | '_ \| |/ /   \ V / _ \| | | | |
   | | | | | | (_| | | | |   <     | | (_) | |_| |_|
   |_| |_| |_|\__,_|_| |_|_|\_\    |_|\___/ \__,_(_)
                                
           (｡◕‿◕｡)  Happy Coding!  (｡◕‿◕｡)
        ";
        
        $this->line("\n<fg=green>" . $successAscii . "</>");
        $this->line("\n<fg=green>🎉 Installation completed successfully!</>");
        $this->line("<fg=yellow>Thank you for using Laravel Artisan Installer.</>\n");

        if ($this->confirm('Would you like to support this package by giving it a star? 🌟', true)) {
            $url = 'https://github.com/darshphpdev/laravel-artisan-installer';
            
            if (PHP_OS_FAMILY === 'Darwin') {
                exec('open ' . $url);
            } elseif (PHP_OS_FAMILY === 'Windows') {
                exec('start ' . $url);
            } elseif (PHP_OS_FAMILY === 'Linux') {
                exec('xdg-open ' . $url);
            } else {
                $this->line("\n<fg=yellow>Please visit: " . $url . "</>\n");
            }
            
            $this->line("\n<fg=green>Thank you for your support! 💖</>\n");
        }
    }
}