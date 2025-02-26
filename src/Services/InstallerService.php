<?php

/**
 * Installer Service
 * 
 * Handles core installation functionality including environment
 * file management and configuration updates.
 */

namespace DarshPhpDev\LaravelArtisanInstaller\Services;

use Illuminate\Support\Facades\File;
use DarshPhpDev\LaravelArtisanInstaller\Exceptions\InstallerException;

class InstallerService {
    public function updateEnvFile(array $data) {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        // If .env file doesn't exist, create it from .env.example
        if (!File::exists($envPath)) {
            if (!File::exists($envExamplePath)) {
                throw new InstallerException('.env.example file not found. Cannot create .env file.');
            }

            if (!File::copy($envExamplePath, $envPath)) {
                throw new InstallerException('Failed to create .env file from .env.example.');
            }
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            // Wrap value in quotes if it contains spaces or special characters
            if (preg_match('/\s/', $value) || preg_match('/[#\'\"]/', $value)) {
                $value = '"' . addslashes($value) . '"';
            }

            // Uncomment the line if it exists
            $envContent = preg_replace(
                "/^#\s*{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );

            // Update the line if it exists
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );

            // Verify that the key was added or updated
            if (!preg_match("/^{$key}=/m", $envContent)) {
                throw new InstallerException("Failed to update .env file: Invalid key '{$key}'.");
            }
        }

        if (!File::put($envPath, $envContent)) {
            throw new InstallerException('Failed to write to .env file.');
        }
    }
}