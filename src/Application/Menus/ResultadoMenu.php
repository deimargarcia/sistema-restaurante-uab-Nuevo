<?php
declare(strict_types=1);

namespace Restaurante\Application\Menus;

final readonly class ResultadoMenu
{
    private function __construct(public bool $exitoso, public string $mensaje)
    {
    }

    public static function exito(string $mensaje): self { return new self(true, $mensaje); }
    public static function rechazo(string $mensaje): self { return new self(false, $mensaje); }
}
