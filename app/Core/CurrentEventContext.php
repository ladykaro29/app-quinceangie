<?php
namespace App\Core;

/**
 * Contexto del Evento Actual en Ejecución (Multi-Tenancy)
 * Mantiene la instancia inmutable del evento resuelto para la petición HTTP
 */
class CurrentEventContext
{
    private static ?array $event = null;
    private static array $configs = [];

    public static function set(array $event, array $configs = []): void
    {
        self::$event = $event;
        self::$configs = $configs;
    }

    public static function get(): ?array
    {
        return self::$event;
    }

    public static function id(): ?int
    {
        return self::$event ? (int)self::$event['id'] : null;
    }

    public static function slug(): ?string
    {
        return self::$event ? self::$event['slug'] : null;
    }

    public static function config(string $key, $default = null)
    {
        return self::$configs[$key] ?? $default;
    }

    public static function allConfigs(): array
    {
        return self::$configs;
    }

    public static function isLoaded(): bool
    {
        return self::$event !== null;
    }
}
