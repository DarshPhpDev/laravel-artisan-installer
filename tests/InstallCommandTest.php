<?php

namespace DarshPhpDev\LaravelArtisanInstaller\Tests;

use DarshPhpDev\LaravelArtisanInstaller\Services\InstallerService;
use DarshPhpDev\LaravelArtisanInstaller\Exceptions\InstallerException;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\File;

class InstallCommandTest extends TestCase {
    protected $installer;

    protected function setUp(): void {
        parent::setUp();
        $this->installer = new InstallerService();

        // Backup the original .env file if it exists
        if (File::exists(base_path('.env'))) {
            File::move(base_path('.env'), base_path('.env.backup'));
        }

        // Ensure .env.example exists for testing
        if (!File::exists(base_path('.env.example'))) {
            File::put(base_path('.env.example'), "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n");
        }
    }

    protected function tearDown(): void {
        // Restore the original .env file if it was backed up
        if (File::exists(base_path('.env.backup'))) {
            File::move(base_path('.env.backup'), base_path('.env'));
        }

        // Clean up: Delete .env file if it was created during the test
        if (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }

        parent::tearDown();
    }

    public function testEnvFileCreation() {
        // Ensure .env file doesn't exist initially
        if (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }

        // Create .env file from .env.example
        $this->installer->updateEnvFile(['APP_NAME' => 'TestApp']);

        $this->assertFileExists(base_path('.env'));
    }

    public function testEnvFileUpdate() {
        // Create a fresh .env file
        File::put(base_path('.env'), "APP_NAME=Laravel\nAPP_ENV=local\n");

        // Update the .env file
        $this->installer->updateEnvFile(['APP_NAME' => 'TestApp']);

        $envContent = File::get(base_path('.env'));
        $this->assertStringContainsString('APP_NAME=TestApp', $envContent);
    }

    public function testEnvFileUpdateWithSpaces() {
        // Create a fresh .env file
        File::put(base_path('.env'), "APP_NAME=Laravel\nAPP_ENV=local\n");

        // Update the .env file with a value containing spaces
        $this->installer->updateEnvFile(['APP_NAME' => 'My App']);

        $envContent = File::get(base_path('.env'));
        $this->assertStringContainsString('APP_NAME="My App"', $envContent);
    }

    public function testEnvFileUpdateWithSpecialCharacters() {
        // Create a fresh .env file
        File::put(base_path('.env'), "APP_NAME=Laravel\nAPP_ENV=local\n");

        // Update the .env file with a value containing special characters
        $this->installer->updateEnvFile(['APP_NAME' => 'My "Awesome" App']);

        $envContent = File::get(base_path('.env'));
        $this->assertStringContainsString('APP_NAME="My \"Awesome\" App"', $envContent);
    }

    public function testEnvFileUpdateWithCommentedLines() {
        // Create a fresh .env file with commented lines
        File::put(base_path('.env'), "#APP_NAME=Laravel\nAPP_ENV=local\n");

        // Update the .env file
        $this->installer->updateEnvFile(['APP_NAME' => 'TestApp']);

        $envContent = File::get(base_path('.env'));
        $this->assertStringContainsString('APP_NAME=TestApp', $envContent);
        $this->assertStringNotContainsString('#APP_NAME', $envContent);
    }

    public function testEnvFileUpdateFailure() {
        // Test invalid key
        $this->expectException(InstallerException::class);
        $this->installer->updateEnvFile(['INVALID_KEY' => 'TestApp']);
    }
}