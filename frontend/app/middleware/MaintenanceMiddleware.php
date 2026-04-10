<?php
namespace App\Middleware;

class MaintenanceMiddleware
{
    private static $file;

    public static function init()
    {
        self::$file = __DIR__ . '/../../.maintenance';
    }

    public static function isActive(): bool
    {
        self::init();
        return file_exists(self::$file);
    }

    public static function toggle()
    {
        self::init();
        if (file_exists(self::$file)) {
            unlink(self::$file);
        } else {
            file_put_contents(self::$file, '1');
        }
    }

    public static function handle(string $path): void
    {
        self::init();
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