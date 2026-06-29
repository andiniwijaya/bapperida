<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Verifikasi dokumentasi deployment production tersedia.
 */
class DeploymentDocumentationTest extends TestCase
{
    public function test_deployment_documentation_files_exist(): void
    {
        $root = base_path();

        $requiredFiles = [
            'README.md',
            'VERSION',
            '.env.example',
            '.env.production.example',
            'docs/DEPLOYMENT.md',
            'docs/INSTALLATION.md',
            'docs/DEPLOYMENT_CHECKLIST.md',
            'docs/ENVIRONMENT.md',
            'docs/ARCHITECTURE.md',
            'docs/ADMIN_GUIDE.md',
            'docs/USER_GUIDE.md',
            'docs/BACKUP_RESTORE.md',
            'docs/MONITORING.md',
            'docs/MAIL.md',
            'deploy/supervisor-queue-worker.conf',
            'deploy/crontab.example',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists("{$root}/{$file}", "Missing deployment doc: {$file}");
        }
    }

    public function test_version_file_contains_production_release(): void
    {
        $version = trim(file_get_contents(base_path('VERSION')));

        $this->assertSame('1.0.0', $version);
    }

    public function test_production_env_example_has_required_security_flags(): void
    {
        $contents = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $contents);
        $this->assertStringContainsString('APP_DEBUG=false', $contents);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $contents);
        $this->assertStringContainsString('QUEUE_CONNECTION=database', $contents);
    }
}
