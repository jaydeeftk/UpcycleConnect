<?php
ini_set('session.save_path', '/tmp');
ini_set('session.cookie_lifetime', 86400);
session_start();

date_default_timezone_set('Europe/Paris');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
        echo "<div class='p-8 bg-red-100 text-red-700'>Vue non trouvée : {$view}<br>Fichier: {$viewFile}</div>";
    }

    $content = ob_get_clean();
    require __DIR__ . '/../ressources/views/layouts/' . $layout . '.php';
}

function redirect($url)
{
    header("Location: {$url}");
    exit;
}

function getCleanPath()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $prefixes = [
        '/UpcycleConnect-PA2526/frontend/public/index.php',
        '/UpcycleConnect-PA2526/frontend/public',
        '/UpcycleConnect-PA2526/frontend',
        '/UpcycleConnect-PA2526',
        '/UpcycleConnect'
    ];

    foreach ($prefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
            break;
        }
    }

    if ($path === '' || $path === false || $path === null) {
        return '/';
    }

    return $path;
}

class Router
{
    private $routes = [];
    private $currentPrefix = '';

    public function get($path, $handler)
    {
        $this->routes['GET'][$this->currentPrefix . $path] = $handler;
    }

    public function post($path, $handler)
    {
        $this->routes['POST'][$this->currentPrefix . $path] = $handler;
    }

    public function put($path, $handler)
    {
        $this->routes['PUT'][$this->currentPrefix . $path] = $handler;
    }

    public function delete($path, $handler)
    {
        $this->routes['DELETE'][$this->currentPrefix . $path] = $handler;
    }

    public function group($options, $callback)
    {
        $previousPrefix = $this->currentPrefix;
        $prefix = $options['prefix'] ?? '';

        $this->currentPrefix .= '/' . trim($prefix, '/');
        $this->currentPrefix = rtrim($this->currentPrefix, '/');

        $callback($this);

        $this->currentPrefix = $previousPrefix;
    }

    public function dispatch()
{
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getCleanPath();

    $bestMatch = null;
    $bestParams = [];
    $bestScore = -1;

    foreach ($this->routes[$method] ?? [] as $route => $handler) {

        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            $score = strlen($route) - substr_count($route, '{');

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $handler;
                $bestParams = $matches;
            }
        }
    }

    if ($bestMatch) {
        [$controller, $action] = explode('@', $bestMatch);
        $controllerClass = "App\\Controllers\\{$controller}";

        if (class_exists($controllerClass)) {
            $instance = new $controllerClass();
            if (method_exists($instance, $action)) {
                return $instance->$action(...$bestParams);
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

$uri = $_SERVER['REQUEST_URI'];

$maintenanceMiddleware->handle($uri, function() use ($router) {
    $router->dispatch();
});