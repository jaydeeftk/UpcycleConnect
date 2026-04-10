<?php
namespace App\Middleware;

class MaintenanceMiddleware
{
    private static function getFile(): string
    {
        return '/tmp/.maintenance';
    }

    public static function isActive(): bool
    {
        return file_exists(self::getFile());
    }

    public static function toggle()
    {
        $file = self::getFile();
        if (file_exists($file)) {
            unlink($file);
        } else {
            file_put_contents($file, '1');
        }
    }

    public static function handle(string $path): void
    {
        if (!self::isActive()) return;

        $allowed = ['/admin', '/maintenance-login'];
        foreach ($allowed as $prefix) {
            if (str_starts_with($path, $prefix)) return;
        }

        http_response_code(503);
        require __DIR__ . '/../../ressources/views/front/maintenance/index.php';
        exit;
    }
}