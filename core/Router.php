<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes["GET"][rtrim($path, "/")] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes["POST"][rtrim($path, "/")] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $path = rtrim($uri, "/");
        $path = $path === "" ? "/" : $path;

        $base = rtrim(APP_BASE_URL, "/");
        if ($base !== "" && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
            $path = $path === "" ? "/" : $path;
        }

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        call_user_func($handler);
    }
}
