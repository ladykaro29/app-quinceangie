<?php
namespace App\Core;

/**
 * Renderizador de Vistas y Componentes PHP
 * Plataforma: XV Experience
 */
class View
{
    private static string $basePath = '';

    public static function init(): void
    {
        if (empty(self::$basePath)) {
            self::$basePath = dirname(__DIR__, 2);
        }
    }

    /**
     * Renderiza un componente modular inyectando variables locales
     */
    public static function component(string $path, array $data = []): void
    {
        self::init();
        $fullPath = self::$basePath . '/' . ltrim($path, '/');
        if (!str_ends_with($fullPath, '.php')) {
            $fullPath .= '.php';
        }

        if (file_exists($fullPath)) {
            extract($data, EXTR_SKIP);
            include $fullPath;
        } else {
            echo "<!-- Componente no encontrado: {$path} -->";
        }
    }

    /**
     * Retorna el contenido del componente como string
     */
    public static function renderComponent(string $path, array $data = []): string
    {
        ob_start();
        self::component($path, $data);
        return ob_get_clean();
    }
}
