<?php
ini_set('session.save_path', '/tmp');
ini_set('session.cookie_lifetime', 86400);
session_start();

date_default_timezone_set('Europe/Paris');

ini_set('display_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $class = str_replace('App\\', '', $class);
    $parts = explode('\\', $class);
    $filename = array_pop($parts);
    $dirs = array_map('strtolower', $parts);
    $file = __DIR__ . '/../app/' . implode('/', $dirs) . '/' . $filename . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

$config = require_once __DIR__ . '/../config/app.php';

function view($view, $data = [])
{
    extract($data);
    $isAdmin = str_starts_with($view, 'admin');
    $layout = $data['layout'] ?? ($isAdmin ? 'admin' : 'main');

    ob_start();
    $viewFile = __DIR__ . '/../ressources/views/' . str_replace('.', '/', $view) . '.php';

    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "<div class='p-8 bg-red-100 text-red-700'>Vue non trouvée : {$view}</div>";
    }

    $content = ob_get_clean();
    require __DIR__ . '/../ressources/views/layouts/' . $layout . '.php';
}

function redirect($url)
{
    $url = '/' . ltrim($url, '/');
    header("Location: {$url}");
    exit;
}

function getCleanPath()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    if ($path !== '/' && str_ends_with($path, '/')) {
        $path = rtrim($path, '/');
    }

    return $path ?: '/';
}

class Router
{
    private $routes = [];
    private $currentPrefix = '';

    public function get($path, $handler) { $this->addRoute('GET', $path, $handler); }
    public function post($path, $handler) { $this->addRoute('POST', $path, $handler); }

    private function addRoute($method, $path, $handler) {
        $fullPath = $this->currentPrefix . '/' . ltrim($path, '/');
        $fullPath = ($fullPath === '//') ? '/' : rtrim($fullPath, '/');
        if ($fullPath === '') $fullPath = '/';
        $this->routes[$method][$fullPath] = $handler;
    }

    public function group($options, $callback) {
        $previousPrefix = $this->currentPrefix;
        $this->currentPrefix .= '/' . trim($options['prefix'] ?? '', '/');
        $callback($this);
        $this->currentPrefix = $previousPrefix;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = getCleanPath();

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . ($pattern === '/' ? '/' : rtrim($pattern, '/')) . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                [$controller, $action] = explode('@', $handler);
                $controllerClass = "App\\Controllers\\{$controller}";

                if (class_exists($controllerClass)) {
                    $instance = new $controllerClass();
                    return $instance->$action(...$matches);
                }
            }
        }

        http_response_code(404);
        view('errors.404');
    }
}

$router = new Router();
require __DIR__ . '/../routes/web.php';

$maintenanceMiddleware = new \App\Middleware\MaintenanceMiddleware();

$path = getCleanPath();

$maintenanceMiddleware->handle($path, function() use ($router) {
    $router->dispatch();
});